<?php


namespace greenshiftquery\Blocks;
defined('ABSPATH') OR exit;


class ListingQuery{

	public function __construct(){
		add_action('init', array( $this, 'init_handler' ));
		$this->action();
	}

	public function init_handler(){
		register_block_type(__DIR__, array(
			'render_callback' => array( $this, 'render_block' ),
			'attributes'      => $this->attributes
		)
		);
	}

	protected $attributes = array(
		'dynamicGClasses' => array(
			'type' => 'array',
			'default' => []
		),
		'cat' => array(
			'type' => 'array',
			'default' => null
		),
		'tag' => array(
			'type' => 'array',
			'default' => null
		),
		'cat_exclude' => array(
			'type' => 'array',
			'default' => null
		),
		'tag_exclude' => array(
			'type' => 'array',
			'default' => null
		),
		'tax_name' => array(
			'type' => 'string',
			'default' => '',
		),
		'tax_slug' => array(
			'type' => 'array',
			'default' => null
		),
		'tax_slug_exclude' => array(
			'type' => 'array',
			'default' => null
		),
		'user_id' => array(
			'type' => 'array',
			'default' => null
		),
		'type' => array(
			'type' => 'string',
			'default' => 'all',
		),
		'ids' => array(
			'type' => 'array',
			'default' => null
		),
		'order' => array(
			'type' => 'string',
			'default' => 'desc',
		),
		'orderby' => array(
			'type' => 'string',
			'default' => 'date',
		),
		'meta_key' => array(
			'type' => 'string',
			'default' => '',
		),
		'show' => array(
			'type' => 'number',
			'default' => 12,
		),
		'offset' => array(
			'type' => 'string',
			'default' => '',
		),
		'enable_pagination' => array(
			'type' => 'string',
			'default' => '0',
		),
		'filter_enable_pagination' => array(
			'type' => 'string',
			'default' => '',
		),

		'listargs'=> array(
			'type'=>'object',
			'default'=> array(
				'image'=> '1',
				'button'=> '1',
				'review'=> false,
				'userrating'=> false,
				'reviewkey'=>'_wc_average_rating',
				'reviewcirclecolor'=> '#cc0000',
				'contentpos'=>'titleexc',
				'metastretchdisable'=>'',
				'readmore'=> '',
				'readmoretext'=> '',
				'headingtag'=> 'h2',
				'section'=>array(),
				'background'=>'',
				'togglecontent'=>'content',
				'togglelink'=>'no',
				'height'=>'',
				'imageWidth'=>'',
				'imageHeight'=>'160',
				'margins'=>array(
					'top'=> null,
					'right'=> null,
					'bottom'=> null,
					'left'=> null
				),
				'borderradius'=>''
			)
		),
		'labelBtn' => array(
			'type' => 'string',
			'default' => 'Show Next'
		),
	);

	protected function action(){
		add_action( 'wp_ajax_gspb_al_render_preview', array( $this, 'render_preview' ) );
	}

	protected function normalize_arrays( &$settings, $fields = ['cat', 'tag', 'ids', 'taxdropids', 'field', 'cat_exclude', 'tag_exclude', 'postid', 'tax_slug', 'tax_slug_exclude', 'user_id'] ) {
        foreach( $fields as $field ) {
            if ( ! isset( $settings[ $field ] ) || ! is_array( $settings[ $field ] ) || empty( $settings[ $field ] ) ) {
				$settings[ $field ] = null;
                continue;
            }
			$ids = '';
			$last = count( $settings[ $field ] );
			foreach ($settings[ $field ] as $item ){
				$ids .= $item['id'];
				if (0 !== --$last) {
					$ids .= ',';
				}
			}
            $settings[ $field ] = $ids;
        }
    }

	public function gspb_list_constructor( $settings, $content = null ) {
		$defaults = array(
			'data_source' => 'cat',
			'cat' => '',
			'cat_name' => '',
			'tag' => '',
			'cat_exclude' => '',
			'tag_exclude' => '',	
			'ids' => '',	
			'orderby' => '',
			'order' => 'DESC',	
			'meta_key' => '',
			'show' => 10,
			'user_id' => '',
			'type' => '',
			'offset' => '',
			'show_date' => '',	
			'post_type' => '',
			'tax_name' => '',
			'tax_slug' => '',
			'tax_slug_exclude' => '',
			'enable_pagination' => '',
			'filter_enable_pagination' => '',
			'price_range' => '',		
			'filterpanel' => '',
			'filterheading' => '',
			'taxdrop' => '',
			'taxdroplabel' => '',
			'alldroplabel' => '',
			'taxdropids' => '',
			'listargs' => '',
		
		); 
		$build_args = wp_parse_args($settings, $defaults);  
		extract($build_args);   
		if ($enable_pagination == '2') {
			$infinitescrollwrap = ' gspb_aj_pag_clk_wrap';
		} elseif ($enable_pagination == '3') {
			$infinitescrollwrap = ' gspb_aj_pag_auto_wrap';
		} else {
			$infinitescrollwrap = '';
		} 
		$containerid = 'gspb_filterid_' . mt_rand(); 
		$ajaxoffset = (int)$show + (int)$offset;
		ob_start(); 
		?>
		<?php gspb_vc_filterpanel_render($filterpanel, $containerid, $taxdrop, $taxdroplabel, $taxdropids, $filterheading, $alldroplabel);?>
		<?php
			global $wp_query; 
			$argsfilter = new \GSPB_Postfilters($build_args);
			$args = $argsfilter->extract_filters();
		
			$args = apply_filters('gspb_module_args_query', $args);
			$wp_query = new \WP_Query($args);
			do_action('gspb_after_module_args_query', $wp_query);
		
		?>
		<?php if ( $wp_query->have_posts() ) : ?>
			<?php 
				if(!empty($args['paged'])){unset($args['paged']);}
				$jsonargs = json_encode($args);
				$json_innerargs = $listargs;
			?>
			<div class="gspb_list_builder review_visible_circle <?php echo ''.$infinitescrollwrap;?>" data-filterargs='<?php echo ''.$jsonargs.'';?>' data-template="listbuilder" id="<?php echo esc_attr($containerid);?>" data-innerargs='<?php echo ''.$json_innerargs.'';?>' data-perpage='<?php echo ''.$show.'';?>'>
				
				<?php $i=0; while ( $wp_query->have_posts() ) : $wp_query->the_post(); $i++;  ?>
					<?php include(GREENSHIFTQUERY_DIR_PATH.'parts/listbuilder.php'); ?>
				<?php endwhile; ?>
				<?php if ($enable_pagination == '1') :?>
					<div class="clearfix"></div>
					<div class="pagination"><?php the_posts_pagination();?></div>
				<?php elseif ($enable_pagination == '2' || $enable_pagination == '3' ) :?>
					<?php  wp_enqueue_script('gspbajaxpagination');?> 
					<div class="gspb_ajax_pagination"><span data-offset="<?php echo esc_attr($ajaxoffset);?>" data-containerid="<?php echo esc_attr($containerid);?>" class="gspb_ajax_pagination_btn def_btn"><?php echo esc_html($labelBtn);?></span></div>      
				<?php endif ;?>
			</div>
			<div class="clearfix"></div>
		<?php endif; wp_reset_query(); ?>
		
		<?php 
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	public function render_preview(){
		$settings = $_POST['settings'];
		if(!empty($settings['listargs'])){
			if(!empty($settings['listargs']['contshortcode'])){
            	$rhshortcontent = str_replace('"', '\'', $settings['listargs']['contshortcode']);
            	$settings['listargs']['contshortcode'] = urlencode($rhshortcontent);
        	}
			if(!empty( $settings['listargs']['section'])){
				foreach($settings['listargs']['section'] as $index=>$section){
					if(!empty($section['imageMapper'])){
						$imagearray = array();
						foreach($section['imageMapper'] as $image){
							$imageindex = $image['image']['id'];
							$valueindex = $image['value'];
							$imagearray[$imageindex] = $valueindex;
						}
						$settings['listargs']['section'][$index]['imageMapper'] = $imagearray;
					}
				}
			}
			
			$settings['listargs'] = json_encode( $settings['listargs']);
		}
		$this->normalize_arrays( $settings );

		if ( !empty( $settings['filterpanel'] ) ) {
            $settings['filterpanel'] = gspb_filter_empty_values( $settings['filterpanel'] );
            $settings['filterpanel'] = rawurlencode( json_encode( $settings['filterpanel'] ) );
        }
		$preview = $this->gspb_list_constructor( $settings );
		wp_send_json_success( $preview );
	}

	public function render_block($settings = array(), $inner_content='', $block=[]){
		extract($settings);
		if (!empty($settings['listargs']['togglelink']) && $settings['listargs']['togglelink'] != 'no') {
			wp_enqueue_script('gsquerytoggler');
		}
		if(!empty($settings['listargs'])){
			
			if(!empty( $settings['listargs']['section'])){
				foreach($settings['listargs']['section'] as $index=>$section){
					if(!empty($section['imageMapper'])){
						$imagearray = array();
						foreach($section['imageMapper'] as $image){
							$imageindex = $image['image']['id'];
							$valueindex = $image['value'];
							$imagearray[$imageindex] = $valueindex;
						}
						$settings['listargs']['section'][$index]['imageMapper'] = $imagearray;
					}
				}
			}
			
			if(!empty($settings['listargs']['section'][0]['t'])){
				foreach($settings['listargs']['section'] as $index=>$section){
					unset($settings['listargs']['section'][$index]['t']);
				}
			}
			$settings['listargs'] = json_encode( $settings['listargs']);
		}
		$this->normalize_arrays( $settings );
		
		if ( !empty( $settings['filterpanel'] ) ) {
            $settings['filterpanel'] = gspb_filter_empty_values( $settings['filterpanel'] );
            $settings['filterpanel'] = rawurlencode( json_encode( $settings['filterpanel'] ) );
        }

		if (!empty($settings['filterpanelenable'])) {
			wp_enqueue_script('gspbfilterpanel');
			$scriptvars = array(
				'filternonce' => wp_create_nonce('filterpanel'),
				'ajax_url' => admin_url('admin-ajax.php', 'relative'),
			);
			wp_localize_script('gspbfilterpanel', 'gspbfiltervars', $scriptvars);
		}
		if (!empty($settings['enable_pagination']) && ($settings['enable_pagination'] == '2' || $settings['enable_pagination'] == '3')) {
			wp_enqueue_script('gspbajaxpagination');
			$scriptvars = array(
				'filternonce' => wp_create_nonce('filterpanel'),
				'ajax_url' => admin_url('admin-ajax.php', 'relative'),
			);
			wp_localize_script('gspbajaxpagination', 'gspbpaginationvars', $scriptvars);
		}

		$block_instance = (isset($block) && is_array($block)) ? $block : $block->parsed_block;
		$json_block = rawurlencode(json_encode($block_instance));
		$blockid = 'gspb_filterid_'.$settings['id'];
		$blockid = str_replace('-','_', $blockid);
		wp_enqueue_script('greenshiftloop');
		wp_add_inline_script('greenshiftloop', 'var '.esc_attr($blockid).'="'.$json_block.'"', 'before');

		$output = str_replace( "{{ content }}", $this->gspb_list_constructor( $settings ), $inner_content );
		return $output;
	}
}

new ListingQuery;