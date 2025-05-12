<?php


namespace greenshiftquery\Blocks;

defined('ABSPATH') or exit;


class GridQuery
{

	public function __construct()
	{
		add_action('init', array($this, 'init_handler'));
		$this->action();
	}

	public function init_handler()
	{
		register_block_type(
			__DIR__,
			array(
				'render_callback' => array($this, 'render_block'),
				'attributes'      => $this->attributes
			)
		);
	}

	protected $attributes = array(
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
		'dynamicGClasses' => array(
			'type' => 'array',
			'default' => []
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
		'isSlider' => array(
			'type' => 'boolean',
			'default' => false,
		),
		'align' => array(
			'type' => 'string',
		),
		'custom_field_key' => array(
			'type' => 'string',
			'default' => ''
		),
		'custom_field_value' => array(
			'type' => 'string',
			'default' => ''
		),
		'custom_field_compare' => array(
			'type' => 'string',
			'default' => 'equal'
		),
		'conditions_arr' => array(
			'type' => 'array',
			'default' => []
		),
		'type_of_condition' => array(
			'type' => 'string',
			'default' => 'and'
		),
        'is_enable_custom_code' => array(
            'type' => 'boolean',
            'default' => false
        ),
        'htmlCode' => array(
            'type' => 'array',
            'default' => []
        ),
		'container_image_size' => array(
			'type' => 'string',
			'default' => 'medium'
		),
		'container_image' => array(
			'type' => 'boolean',
			'default' => false
		),
		'noMoreLabel'	=> array(
			'type' => 'string',
			'default' => ''
		),
		'additional_field'	=> array(
			'type' => 'string',
			'default' => ''
		),
		'additional_type'	=> array(
			'type' => 'string',
			'default' => ''
		),
		'title'	=> array(
			'type' => 'string',
			'default' => ''
		),
		'interactionLayers' => array(
			'type' => 'array',
			'default' => array()
		),
		'animation' => array(
			'type' => 'object',
			'default' => array(),
		),
		'linkNewWindow' => array(
			'type' => 'boolean',
			'default' => false
		),
		'enableSearchFilters' => array(
			'type' => 'boolean',
			'default' => false
		),
		'searchQueryId' => array(
			'type' => 'string',
			'default' => ''
		),
		'labelBtn' => array(
			'type' => 'string',
			'default' => 'Show Next'
		),
		'filterpanelenable' => array(
			'type' => 'string',
			'default' => ''
		),
		'taxdropids' => array(
			'type' => 'string',
			'default' => ''
		),
		'filter_enable_pagination' => array(
			'type' => 'string',
			'default' => '0',
		),
		'wrapperClasses' => array(
			'type' => 'string',
			'default' => '',
		),
		'itemClasses' => array(
			'type' => 'string',
			'default' => '',
		),
		'itemTag' => array(
			'type' => 'string',
			'default' => 'li',
		),
		'wrapperTag' => array(
			'type' => 'string',
			'default' => 'ul',
		),
	);

	protected function action()
	{
		add_action('wp_ajax_gspb_grid_render_preview', array($this, 'render_preview'));
		add_action('wp_ajax_gspb_filter_render_preview', array($this, 'render_filter_preview'));
		add_action('wp_ajax_gspb_grid_convert_shortcode', array($this, 'render_grid_convert_shortcode'));
	}

	protected function normalize_arrays(&$settings, $fields = ['cat', 'tag', 'ids', 'field', 'cat_exclude', 'tag_exclude', 'postid', 'tax_slug', 'tax_slug_exclude', 'user_id'])
	{
		foreach ($fields as $field) {
			if (!isset($settings[$field]) || !is_array($settings[$field]) || empty($settings[$field])) {
				$settings[$field] = null;
				continue;
			}
			$ids = '';
			$last = count($settings[$field]);
			foreach ($settings[$field] as $item) {
				$ids .= $item['id'];
				if (0 !== --$last) {
					$ids .= ',';
				}
			}
			$settings[$field] = $ids;
		}
	}

	public function extractInlineCssStyles($array){
		$inlineCssStyles = '';
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$inlineCssStyles .= $this->extractInlineCssStyles($value);
			} elseif ($key == 'inlineCssStyles') {
				$inlineCssStyles .= $value;
				unset($array[$key]);
			}
		}
		return $inlineCssStyles;
	}

	public function gspb_grid_constructor($settings, $content, $block)
	{
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
            'is_enable_custom_code' => '',
            'htmlCode' => '',
			'additional_field' => '',
			'additional_type' => '',
			'enableSearchFilters' => '',
			'searchQueryId' => '',
			'filter_enable_pagination' => '',

		);
		//print_r($settings);
		$build_args = wp_parse_args($settings, $defaults);
		extract($build_args);
		if ($enable_pagination == '2' || $filter_enable_pagination == '2') {
			$infinitescrollwrap = ' gspb_aj_pag_clk_wrap';
		} elseif ($enable_pagination == '3' || $filter_enable_pagination == '3') {
			$infinitescrollwrap = ' gspb_aj_pag_auto_wrap';
		} else {
			$infinitescrollwrap = '';
		}
		$containerid = 'gspb_filterid_' . esc_attr($settings['id']);
		$ajaxoffset = (int)$show + (int)$offset;
		if (isset($align)) {
			if ($align == 'full') {
				$alignClass = 'alignfull';
			} elseif ($align == 'wide') {
				$alignClass = 'alignwide';
			} elseif ($align == '') {
				$alignClass = '';
			}
		} else {
			$alignClass = 'alignwide';
		}
		ob_start();
		$block_instance = (is_array($block)) ? $block : $block->parsed_block;
		$blockId = 'gspbgrid_id-' . esc_attr($block_instance['attrs']['id']);
		$data_attributes = \gspb_getDataAttributesfromDynamic($settings);
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => $blockId . ' gspbgrid_list_builder wp-block-query ' . $alignClass . ' ' . $containerid . ' ' . $infinitescrollwrap .($isSlider ? ' swiper' : ''),
				...$data_attributes
			)
		);
		$tagItem = $itemTag ? $itemTag : 'li';
		$tagWrapper = $wrapperTag ? $wrapperTag : 'ul';
		?>
			<?php 		
			$new_instance = $block_instance;
			$inlineStyles = $this->extractInlineCssStyles($new_instance);
			if(!empty($inlineStyles)){
				echo '<style type="text/css" scoped data-type="gspb-grid-inline-css">' . $inlineStyles . '</style>';
				$block_instance = $new_instance;
			}
			?>
			<?php
			global $wp_query;
			$argsfilter = new \GSPB_Postfilters($build_args);
			$args = $argsfilter->extract_filters();

			$args = apply_filters('gspb_module_args_query', $args);
			$args = apply_filters('gspb_module_args_query_id', $args, $block_instance['attrs']['id']);
			$wp_query = new \WP_Query($args);
			do_action('gspb_after_module_args_query', $wp_query);

			$pages = $wp_query->max_num_pages;
			$current_page = max(1, get_query_var('paged'));
			$prev_page = max(1, $current_page - 1); 
			$next_page = $current_page + 1; 

			$paginationtype = '';
			if ($filter_enable_pagination === '1') {
				$paginationtype = "numericpagi";
			} else if ($filter_enable_pagination === '2') {
				$paginationtype = "loadmore";
			} else if ($filter_enable_pagination === '3'){
				$paginationtype = "infinitescroll";
			}

			?>
			<?php if ($wp_query->have_posts()) : ?>
				<?php
				if (!empty($args['paged'])) {
					unset($args['paged']);
				}
				$jsonargs = json_encode($args);
				$json_innerargs = $listargs;
				?>
				<div id="<?php echo esc_attr($containerid);?>" <?php echo '' . $wrapper_attributes . gspb_AnimationRenderProps($animation, $interactionLayers, ".gspbgrid_item"); ?> data-filterargs='<?php echo '' . ($filterpanel || $enable_pagination == '2' || $enable_pagination == '3') ? $jsonargs : "" . ''; ?>' data-template="querybuilder" id="<?php echo esc_attr($containerid); ?>" data-innerargs='<?php echo '' . $json_innerargs . ''; ?>' data-perpage='<?php echo '' . $show . ''; ?>'  data-paginationtype="<?php echo $paginationtype; ?>">
					<?php if ($title) : ?>
						<div class="gspbgrid-block__title">
							<?php echo esc_html($title); ?>
						</div>
					<?php endif; ?>
					<?php if (!empty($settings['filterpanelenable'])) : ?>
						<?php gspb_vc_filterpanel_render($filterpanel, $containerid, $taxdrop, $taxdroplabel, $taxdropids, $filterheading, $alldroplabel); ?>
					<?php endif; ?>

					<<?php echo $tagWrapper; ?> class="wp-block-post-template<?php echo $isSlider ? ' swiper-wrapper' : ''; ?><?php echo $wrapperClasses ? ' '.esc_attr($wrapperClasses) : ''; ?>" data-currentpage="<?php echo $current_page; ?>" data-maxpage="<?php echo $pages; ?>" data-paginationid="currentpage-<?php echo $containerid; ?>">
						<?php $i = 0;
						while ($wp_query->have_posts()) : $wp_query->the_post();
							$i++;
                            ?>
							<?php include(GREENSHIFTQUERY_DIR_PATH . 'parts/querybuilder.php'); ?>
                            <?php if ($settings["is_enable_custom_code"]) {
                                foreach ($settings["htmlCode"] as $block) {
                                    if ($block["position"] == $i) {
                                        $content_filtered = wp_kses( $block["html"], 'post' );?>
                                        <li class="gspbgrid_item swiper-slide ad-id-<?php echo (int)$i; ?>">
                                            <?php echo ''.do_shortcode($content_filtered); ?>
                                        </li>
                                        <?php
                                    }
                                }
                            } ?>
						<?php endwhile; ?>
					</<?php echo $tagWrapper; ?>>
					<?php if ($enable_pagination == '1') : ?>
						<div class="clearfix"></div>
						<div class="pagination"><?php the_posts_pagination(); ?></div>
					<?php elseif ($enable_pagination == '2' || $enable_pagination == '3') : ?>
						<div class="gspb_ajax_pagination gspb_ajax_pagination_outer">
							<span data-offset="<?php echo esc_attr($ajaxoffset); ?>" data-containerid="<?php echo esc_attr($containerid); ?>" class="gspb_ajax_pagination_btn">
								<?php if($enable_pagination == '2'):?>
									<?php echo esc_html($labelBtn);?>
								<?php endif;?>
							</span>
						</div>
					<?php endif; ?>

					<?php if ($filter_enable_pagination === '1') : ?>
						<div class="gspb-filter__ajx-pagination" data-type="numericpagi">
							<?php if( $pages > 1 ) : ?>
							<ul class="gspb-filter__ajx-pages">
								<?php 
							
									?>
									<li class="gspb-filter__ajx-page prev-<?php echo $containerid; ?>" data-connection="<?php echo $containerid; ?>" data-key="gspbpagination" data-page="<?php echo $prev_page; ?>" data-type="prev" style="display:none" data-paginationtype="numericpagi">Prev</li>
									<?php
							
									for ($i=1; $i <= $pages; $i++) { 
										?>
										<li class="gspb-filter__ajx-page" data-connection="<?php echo $containerid; ?>" data-key="gspbpagination" data-page="<?php echo $i; ?>" data-paginationtype="numericpagi"><?php echo $i; ?></li>
										<?php
									}
								
									
								if ($current_page < $pages) {
									?>
									<li class="gspb-filter__ajx-page next-<?php echo $containerid; ?>" data-connection="<?php echo $containerid; ?>" data-key="gspbpagination" data-page="<?php echo $next_page; ?>" data-type="next" data-paginationtype="numericpagi">Next</li>
									<?php
								}
								
								?>
							</ul>
							<?php endif; ?>
						</div>
					<?php elseif ($filter_enable_pagination == '2' || $filter_enable_pagination == '3') : ?>
						<div class="gspb-filter__ajx-pagination gspb-filter__pagination-outer">
							<div class="gspb-filter__ajx-inner">
								<span data-offset="<?php echo esc_attr($ajaxoffset); ?>" data-connection="<?php echo esc_attr($containerid); ?>" class="gspb-filter__load_moreajx_btn" id="<?php echo $containerid; ?>-ajxloadmore" data-type="loadmorepagi" data-key="gspbpagination" data-paginationtype="<?php echo $paginationtype; ?>" >
									<?php if($filter_enable_pagination == '2'):?>
										<?php echo esc_html($labelBtn);?>
									<?php endif;?>
								</span>
							</div>
						</div>
					<?php endif; ?>

				</div>
				<div class="clearfix"></div>
			<?php else:?>
				<?php if($noMoreLabel):?>
					<div class="gspb_no_more_posts">
						<?php echo esc_html($noMoreLabel);?>
					</div>
				<?php endif;?>
			<?php endif;wp_reset_query(); ?>

		<?php if($data_source == 'autoshop'):?>
			<div style="display:none">
				<?php echo do_blocks('<!-- wp:query {"queryId":10,"query":{"perPage":9,"pages":0,"offset":0,"postType":"product","order":"asc","orderBy":"title","author":"","search":"","exclude":[],"sticky":"","inherit":true,"__woocommerceAttributes":[],"__woocommerceStockStatus":["instock","outofstock","onbackorder"]},"displayLayout":{"type":"flex","columns":4},"namespace":"woocommerce/product-query"} --><div class="wp-block-query"><!-- wp:post-template {"__woocommerceNamespace":"woocommerce/product-query/product-template"} --><!-- /wp:post-template --></div><!-- /wp:query -->');?>
			</div>
		<?php endif;?>
	<?php
		$output = ob_get_contents();
		if (ob_get_level() > 0) {
			ob_end_clean();
		}
		return $output;
	}

	public function gspb_grid_get_posts($settings)
	{
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
			'price_range' => '',
			'filterpanel' => '',
			'filterheading' => '',
			'taxdrop' => '',
			'taxdroplabel' => '',
			'alldroplabel' => '',
			'taxdropids' => '',
			'listargs' => '',
            'is_enable_custom_code' => '',
            'htmlCode' => '',
			'additional_field' => '',
			'additional_type' => '',
		);
		$build_args = wp_parse_args($settings, $defaults);
		extract($build_args);

		global $wp_query;
		$argsfilter = new \GSPB_Postfilters($build_args);
		$args = $argsfilter->extract_filters();

		do_action('wpc_filtered_query_end');

		$args = apply_filters('gspb_module_args_query', $args);
		$args = apply_filters('gspb_module_args_query_id', $args, $settings['id']);
		$wp_query = new \WP_Query($args);
		do_action('gspb_after_module_args_query', $wp_query);
		if (count($wp_query->posts)) {
			$posts = $wp_query->posts;
			$postfull = array();
			foreach ($posts as $post) {
				$postid = $post->ID;
				$postdate = get_the_date('', $postid);
				$postdatemodified = get_the_modified_date('', $postid);
				$authorname = get_the_author_meta('display_name', $post->post_author);
				$imageMedium = get_the_post_thumbnail($postid, 'medium');
				$imageFullLink = '';
				$thumbnail_id = get_post_thumbnail_id($postid);
				if ($thumbnail_id) {
					$thumbnaillink = wp_get_attachment_image_src($thumbnail_id, 'full');
					$thumbnail_url = $thumbnaillink[0];
					$imageFullLink = $thumbnail_url;
				}
				$imageFull = get_the_post_thumbnail($postid, 'full');
				$metas = get_post_meta($postid);
				$avatarURL = get_avatar_url($post->post_author);
				$postdata = array('postDate' => $postdate, 'postDateModified' => $postdatemodified, 'authorName' => $authorname, 'imageMedium' => $imageMedium, 'imageFull' => $imageFull, 'gsID' => $postid, 'metas' => $metas, 'imageFullLink' => $imageFullLink, 'avatarURL' => $avatarURL);
				if ($post->post_type == 'product') {
					$_product = wc_get_product($postid);
					$rating = $_product->get_average_rating();
					$postdata['wooRating'] = $rating;
					$postdata['wooStars'] = '<div class="star-rating" role="img">' . wc_get_star_rating_html($rating, $_product->get_rating_count()) . '</div>';
					if($_product->is_type( 'variable' )){
						$postdata['wooPrice'] = '<span class="gspb-variable-price">'.$_product->get_price_html().'</span>';
					}else{
						$postdata['wooPrice'] = $_product->get_price_html();
					}
					$postdata['wooCategories'] = wc_get_product_category_list($postid);
					$postdata['wooAvailability'] = empty($_product->get_availability()['availability']) ? __('In stock', 'greenshiftquery') : '<span class="' . $_product->get_availability()['class'] . '">' . $_product->get_availability()['availability'] . '</span>';
					$postdata['wooDiscount'] = self::get_discount_percentage($_product);
					$postdata['wooThumbnail'] = $_product->get_image();
					$postdata['wooDescription'] = $_product->get_short_description();
					if ( $_product->is_on_sale() ) {
						$sale_end = get_post_meta( $postid, '_sale_price_dates_to', true );
						$postdata['wooSaleEnd'] = $sale_end;
					}
				}
				if ($post->post_type == 'post') {
					$postdata['postCategories'] = get_the_term_list($postid, 'category', '', ', ', '');
				}
				$postdata['imageSizes'] = gspb_get_image_sizes();
				$postfull[] = (object) array_merge((array)$post, $postdata);
			};
			return $postfull;
		} else {
			return 'no items';
		}
	}

	static function get_discount_percentage($product)
	{

		$percentage = '';

		if ($product->is_type('variable')) {
			$percentages = array();

			// Get all variation prices
			$prices = $product->get_variation_prices();

			// Loop through variation prices
			foreach ($prices['price'] as $key => $price) {
				// Only on sale variations
				if ($prices['regular_price'][$key] !== $price) {
					// Calculate and set in the array the percentage for each variation on sale
					$percentages[] = round(100 - (floatval($prices['sale_price'][$key]) / floatval($prices['regular_price'][$key]) * 100));
				}
			}
			// We keep the highest value
            if(is_array($percentages) && count($percentages)){
                $percentage = max($percentages) . '%';
            }else{
                $percentage = '';
            }
		} elseif ($product->is_type('grouped')) {
			$percentages = array();

			// Get all variation prices
			$children_ids = $product->get_children();

			// Loop through variation prices
			foreach ($children_ids as $child_id) {
				$child_product = wc_get_product($child_id);

				$regular_price = (float) $child_product->get_regular_price();
				$sale_price    = (float) $child_product->get_sale_price();

				if ($sale_price != 0 || !empty($sale_price)) {
					// Calculate and set in the array the percentage for each child on sale
					$percentages[] = round(100 - ($sale_price / $regular_price * 100));
				}
			}
			// We keep the highest value
            if(is_array($percentages) && count($percentages)){
                $percentage = max($percentages) . '%';
            }else{
                $percentage = '';
            }
		} else {
			$regular_price = (float) $product->get_regular_price();
			$sale_price    = (float) $product->get_sale_price();

			if ($sale_price != 0 || !empty($sale_price)) {
				$percentage    = round(100 - ($sale_price / $regular_price * 100)) . '%';
			} else {
				return '';
			}
		}

		return $percentage;
	}

	public function render_preview()
	{
		$settings = $_POST['settings'];
		$this->normalize_arrays($settings);

		if (!empty($settings['filterpanel'])) {
			$settings['filterpanel'] = gspb_filter_empty_values($settings['filterpanel']);
			$settings['filterpanel'] = rawurlencode(json_encode($settings['filterpanel']));
		}
		$preview = $this->gspb_grid_get_posts($settings);
		wp_send_json_success($preview);
	}
	public function render_grid_convert_shortcode()
	{
		$content = $_POST['content'];
		$content = stripcslashes($content);
		$output = array(
			'content' => do_shortcode($content)
		);
		wp_send_json_success($output);
	}


	public function render_filter_preview()
	{
		$settings = $_POST['settings'];
		$this->normalize_arrays($settings);

		if (!empty($settings['filterpanel'])) {
			$settings['filterpanel'] = gspb_filter_empty_values($settings['filterpanel']);
			$settings['filterpanel'] = rawurlencode(json_encode($settings['filterpanel']));
		}
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
			'is_enable_custom_code' => '',
			'htmlCode' => '',

		);
		$build_args = wp_parse_args($settings, $defaults);
		extract($build_args);
		ob_start();
		gspb_vc_filterpanel_render($filterpanel, '', $taxdrop, $taxdroplabel, $taxdropids, $filterheading, $alldroplabel);
		$output = ob_get_contents();
		ob_end_clean();
		wp_send_json_success($output);
	}

	public function render_block($settings = array(), $inner_content = '', $block = '')
	{
		extract($settings);
		$this->normalize_arrays($settings);

		if (!empty($settings['filterpanel'])) {
			$settings['filterpanel'] = gspb_filter_empty_values($settings['filterpanel']);
			$settings['filterpanel'] = rawurlencode(json_encode($settings['filterpanel']));
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
		if (!empty($settings['listargs']['togglelink']) && $settings['listargs']['togglelink'] != 'no') {
			wp_enqueue_script('gsquerytoggler');
		}

		$block_instance = (is_array($block)) ? $block : $block->parsed_block;
		$json_block = rawurlencode(json_encode($block_instance));
		$blockid = 'gspb_filterid_'.\greenshift_sanitize_id_key($settings['id']);
		$blockid = str_replace('-','_', $blockid);
		wp_enqueue_script('greenshiftloop');
		wp_add_inline_script('greenshiftloop', 'var '.$blockid.'="'.$json_block.'"', 'before');

		$output = $this->gspb_grid_constructor($settings, $inner_content, $block);
		return $output;
	}
}

new GridQuery;
