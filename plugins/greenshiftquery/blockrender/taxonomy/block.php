<?php


namespace Greenshift\Blocks;
defined('ABSPATH') OR exit;


class ProductTaxonomy{

	public function __construct(){
		add_action('init', array( $this, 'init_handler' ));
	}

	public function init_handler(){
		register_block_type(__DIR__, array(
                'render_callback' => array( $this, 'render_block' ),
                'attributes'      => $this->attributes
            )
		);
	}

	public $attributes = array(
        'dynamicGClasses' => array(
			'type' => 'array',
			'default' => []
		),
		'id' => array(
			'type'    => 'string',
			'default' => null,
		),
		'inlineCssStyles' => array(
			'type'    => 'string',
			'default' => '',
		),
		'animation' => array(
			'type' => 'object',
			'default' => array(),
		),
        'postType' => array(
            'type' => 'string',
            'default' => 'post'
        ),
        'taxonomy' => array(
            'type' => 'string',
            'default' => 'category'
        ),
        'cardType' => array(
            'type' => 'string',
            'default' => 'grid'
        ),
        'showEmpty' => array(
            'type' => 'boolean',
            'default' => true
        ),
        'showCount' => array(
            'type' => 'boolean',
            'default' => true
        ),
        'orderBy' => array(
            'type' => 'string',
            'default' => 'name'
        ),
        'order' => array(
            'type' => 'string',
            'default' => 'ASC'
        ),
        'termsInclude' => array(
            'type' => 'string',
            'default' => ''
        ),
        'childOf' => array(
            'type' => 'string',
            'default' => ''
        ),
        'termsExclude' => array(
            'type' => 'string',
            'default' => ''
        ),
        'showHierarchy' => array(
            'type' => 'boolean',
            'default' => false
        ),
        'showImage' => array(
            'type' => 'boolean',
            'default' => true
        ),
        'excludeFromCurrent' => array(
          'type' => 'boolean',
          'default' => false
        ),
        'showChild' => array(
            'type' => 'boolean',
            'default' => false
        ),
		'showImageFromMeta' => array(
			'type' => 'boolean',
			'default' => false
		),
		'taxonomyImageMeta' => array(
			'type' => 'string',
			'default' => ''
		),
		'postLabel' => array(
			'type' => 'string',
        	'default' => 'items'
        ),
        'number' => array(
			'type' => 'number',
        	'default' => 200
		),
        'imageSize' => array(
            'type' => 'string',
            'default' => 'thumbnail'
        ),
		'interactionLayers' => array(
			'type' => 'array',
			'default' => array()
		)
	);

	public function render_block($settings = array(), $inner_content=''){
		extract($settings);
		$image_meta = $showImageFromMeta ? $taxonomyImageMeta : '';
		
        if($cardType === 'list' || $cardType === 'cards') $terms = self::get_terms($taxonomy, $showEmpty, $orderBy, $order, $termsInclude, $termsExclude, $showHierarchy, $excludeFromCurrent, $showChild, $image_meta, $number, $imageSize, $childOf);
        else $terms = self::get_terms($taxonomy, $showEmpty, $orderBy, $order, $termsInclude, $termsExclude, false, $excludeFromCurrent, $showChild, $image_meta, $number, $imageSize, $childOf);
	
        if(empty($terms)) return false;

		$blockId = 'gspb_id-' . esc_attr($id);
		$blockClassName = 'gspb-taxonomybox '.$blockId.' '.(!empty($className) ? $className : '').' ';

		$out = '<div id="'.$blockId.'" class="'.$blockClassName.'"'.gspb_AnimationRenderProps($animation, $interactionLayers).'>';
            $out .= '<div class="gspb_taxonomy_value">';
            $out .= self::view($cardType, $terms, $showCount, $taxonomy, $blockId, $showImage, $postLabel);
            $out .= '</div>';
		$out .='</div>';
		return $out;
	}

    static function view($type, $terms, $showCount, $taxonomy, $blockId, $showImage, $postLabel) {
        $html = '';
        switch ($type){
            case 'grid':
                $html = self::grid_view($terms, $showCount, $postLabel);
                break;
			case 'grid_hover':
				$html = self::grid_hover_view($terms, $showCount, $postLabel);
				break;
            case 'list':
                $html = self::list_view($terms, $showCount);
                break;
            case 'alphabetical':
                $html = self::alphabetical_view($terms, $taxonomy, $showCount, $blockId);
                break;
            case 'cards':
                $html = self::cards_view($terms, $showCount, $showImage);
                break;
            default:
                $html = 'error!';
                break;
        }

        return $html;
    }

    static function grid_view($terms, $showCount, $postLabel){
        $html = '<div class="gspb-posts-grid-wrap">';
        foreach ($terms as $term) {
            if(is_object($term)){
                $html .= '<a href="' . get_term_link($term->term_id) . '">';
        
                    if(!empty($term->thumbnail_url)) {
                        $width = !empty($term->thumbnail_width) ? ' width="'.$term->thumbnail_width.'"' : '';
                        $height = !empty($term->thumbnail_height) ? ' height="'.$term->thumbnail_height.'"' : '';
                        $html .= '<img loading="lazy" '.$width.$height.' src="' . $term->thumbnail_url . '" alt="' . $term->name . '" class="grid-thumb"/>';
                    }
                
                    $html .= '<div><div class="post-name">' . $term->name . '</div>';
                    if($showCount) {
                        $html .= '<div class="count">' . $term->count . ' ' . $postLabel . '</div>';
                    }
                    $html .= '</div>';
                $html .= '</a>';
            }
        }
        $html .= '</div>';
        return $html;
    }
	
	static function grid_hover_view($terms, $showCount, $postLabel){
		$html = '<div class="gspb-posts-grid-wrap gspb-posts-grid-hover-wrap">';
		foreach ($terms as $term) {
            if(is_object($term)){
                $html .= '<a href="' . get_term_link($term->term_id) . '" style="background-image: url(' . $term->thumbnail_url . ')">';
                
                    $html .= '<div class="post-name">' . $term->name . '</div>';
                    $html .= '<div class="on-hover">';
                        $html .= '<div class="post-name">' . $term->name . '</div>';
                        if($showCount) {
                            $html .= '<div class="count">' . $term->count . ' ' . $postLabel . '</div>';
                        }
                    $html .= '</div>';
                $html .= '</a>';
            }
		}
		$html .= '</div>';
		return $html;
	}

    static function list_view($terms, $showCount){
        $html = '<div class="gspb-posts-list-wrap">';
            $html .= self::show_term_tree($terms, $showCount);
        $html .= '</div>';
        return $html;
    }

    static function show_term_tree($terms, $showCount) {
        $html = '<ul>';
            foreach ($terms as $term) {
                if(is_object($term)){
                    $html .= '<li>';
                    $html .= '<a href="' . get_term_link($term->term_id) . '">';
                        if(!empty($term->thumbnail_url)) {
                            $width = !empty($term->thumbnail_width) ? ' width="'.$term->thumbnail_width.'"' : '';
                            $height = !empty($term->thumbnail_height) ? ' height="'.$term->thumbnail_height.'"' : '';
                            $html .= '<img loading="lazy" '.$width.$height.' src="' . $term->thumbnail_url . '" alt="' . $term->name . '" class="grid-thumb"/>';
                        }
                        $html .= '<div><span class="post-name">' . $term->name . ' </span>';
                        if($showCount) {
                            $html .= '<span class="count">(' . $term->count . ')</span>';
                        }
                        $html .= '</div>';
                    $html .= '</a>';
                    if(!empty($term->child)){
                        $html .= self::show_term_tree($term->child, $showCount);
                    }
                    $html .= '</li>';
                }
            }
        $html .= '</ul>';

        return $html;
    }

    static function alphabetical_view($terms, $taxonomy, $showCount, $blockId){
        $letter_keyed_terms = array();

        $term_letter_links = '';
        $term_titles = '';

        if( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            foreach( $terms as $term ) {
                $first_letter = mb_substr( $term->name, 0, 1, 'UTF-8' );

                if( is_numeric( $first_letter ) ) {
                    $first_letter = '0-9';
                } else {
                    $first_letter = mb_strtoupper( $first_letter, 'UTF-8' );
                }

                if ( !array_key_exists( $first_letter, $letter_keyed_terms ) ) {
                    $letter_keyed_terms[ $first_letter ] = array();
                }

                $letter_keyed_terms[ $first_letter ][] = $term;
            }

            foreach( $letter_keyed_terms as $letter => $terms ) {

                $term_letter_links .= '<li><a href="#'.$blockId.'-'.$letter.'">'.$letter.'</a></li>';

                $term_titles .= '<div class="single-letter" id="'.$blockId.'-'.$letter.'"><div class="letter_tag">'.$letter.'<div class="return_to_letters"><span><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 122.88 120.64"><g><path d="M108.91,66.6c1.63,1.55,3.74,2.31,5.85,2.28c2.11-0.03,4.2-0.84,5.79-2.44l0.12-0.12c1.5-1.58,2.23-3.6,2.2-5.61 c-0.03-2.01-0.82-4.01-2.37-5.55C102.85,37.5,84.9,20.03,67.11,2.48c-0.05-0.07-0.1-0.13-0.16-0.19C65.32,0.73,63.19-0.03,61.08,0 c-2.11,0.03-4.21,0.85-5.8,2.45l-0.26,0.27C37.47,20.21,19.87,37.65,2.36,55.17C0.82,56.71,0.03,58.7,0,60.71 c-0.03,2.01,0.7,4.03,2.21,5.61l0.15,0.15c1.58,1.57,3.66,2.38,5.76,2.41c2.1,0.03,4.22-0.73,5.85-2.28l47.27-47.22L108.91,66.6 L108.91,66.6z M106.91,118.37c1.62,1.54,3.73,2.29,5.83,2.26c2.11-0.03,4.2-0.84,5.79-2.44l0.12-0.12c1.5-1.57,2.23-3.6,2.21-5.61 c-0.03-2.01-0.82-4.02-2.37-5.55C101.2,89.63,84.2,71.76,67.12,54.24c-0.05-0.07-0.11-0.14-0.17-0.21 c-1.63-1.55-3.76-2.31-5.87-2.28c-2.11,0.03-4.21,0.85-5.8,2.45C38.33,71.7,21.44,89.27,4.51,106.8l-0.13,0.12 c-1.54,1.53-2.32,3.53-2.35,5.54c-0.03,2.01,0.7,4.03,2.21,5.61l0.15,0.15c1.58,1.57,3.66,2.38,5.76,2.41 c2.1,0.03,4.22-0.73,5.85-2.28l45.24-47.18L106.91,118.37L106.91,118.37z"/></g></svg></span></div></div></div>';
                $term_titles .= '<div class="tax-wrap">';

                foreach( $terms as $term ) {

                    $thumbnail = $thumbnail_url = '';

                    $term_titles .= '<a class="single-letter-link" id="taxonomy-'. $term->term_id .'"  href="' . esc_url( get_term_link( $term ) ) . '">' . $term->name;

                    if($showCount) {
                        $term_titles .= ' <span class="count">(' . $term->count . ')</span>';
                    }

                    $term_titles .= '</a>';
                }

                $term_titles .= '</div>';
            }
        }

        $res = '<div class="gspb-posts-alphabetical-wrap"><div><div class="alphabet-filter">
					<style scoped>
					    .alphabet-filter .head-wrapper {margin-bottom: 25px;}
						.alphabet-filter .list-inline{margin:0;list-style:none;padding: 0;background: #f4f2f3;padding: 10px 16px}
						.alphabet-filter .list-inline a{text-decoration: unset}
						.alphabet-filter .list-inline>li{display:inline-block;padding-right:5px;padding-left:5px;margin:0}
						.alphabet-filter .list-inline>li:first-child{margin-left: 0;padding-left:0}
                        .alphabet-filter .return_to_letters{float:right;cursor:pointer}
						.alphabet-filter .return_to_letters span svg{fill:lightgrey;width: 11px;}
						.alphabet-filter a.single-letter-link{text-decoration: none !important; display:flex; align-items:center; justify-content: center;}
                        .alphabet-filter a.single-letter-link .count{margin:0 5px}
						.alphabet-filter a.single-letter-link img{ max-width: 100%;}
						.alphabet-filter a.single-letter-link:hover, .alphabet-filter a.compact-tax-link:hover{ box-shadow: none;}
						.alphabet-filter a.logo-tax-link img{max-width: 100px; max-height: 55px}
						.alphabet-filter .single-letter {border-bottom: 1px solid rgba(206,206,206,.3);margin-bottom: 15px;}
						.alphabet-filter .single-letter .letter_tag{margin-bottom: 3px;}
						.alphabet-filter .tax-wrap {margin-bottom: 15px;}
					</style>
					<div class="head-wrapper" id="gs-taxonomy-menu">
						<ul class="list-inline">
							'. $term_letter_links .'
						</ul>
					</div>
					<div class="body-wrapper clearfix">
							'. $term_titles .'
					</div>
				</div></div></div>';

        $res .= '
                <script>
                    let scrollbtn = document.querySelectorAll(".alphabet-filter .list-inline a");
                    for (let i = 0; i < scrollbtn.length; i++) {
                        scrollbtn[i].addEventListener("click", (e) => {
                            e.preventDefault()
                            let toId = e.currentTarget.getAttribute("href");
                            let element = document.querySelectorAll(toId);
                            if(element.length) element[0].scrollIntoView({behavior: "smooth"});
                        }, false);
                    }
                    let scrolltop = document.querySelectorAll(".alphabet-filter .return_to_letters span");
                    for (let i = 0; i < scrolltop.length; i++) {
                        scrolltop[i].addEventListener("click", (e) => {
                            e.preventDefault();
                            document.getElementById("gs-taxonomy-menu").scrollIntoView({behavior: "smooth"});
                        }, false);
                    }
                </script>';

        return $res;
    }

    static function cards_child_tree($terms) {
        $html = '';
        foreach ($terms as $term) {
            if(is_object($term)){
            $html .= '<a href="'.get_term_link($term->term_id).'">';
                $html .= '<span class="post-name">'.$term->name.', </span>';
            $html .= '</a>';
            }

            if(!empty($term->child)){
                $html .= self::cards_child_tree($term->child);
            }
        }
        return $html;
    }

    static function cards_view($terms, $showCount, $showImage){
        $html = '<div class="gspb-posts-cards-wrap">';
        foreach ($terms as $term) {
            if(is_object($term)){
                $html .= '<div class="card-item">';
                    $html .= '<div class="titles">';
                        $html .= '<div class="main">';
                            $html .= '<a href="'.get_term_link($term->term_id).'">';
                                $html .= '<span class="post-name">' . $term->name . '</span>';
                                if($showCount) {
                                    $html .= '<span class="post-count"> (' . $term->count . ')</span>';
                                }
                            $html .= '</a>';
                        $html .= '</div>';
    
                        if(!empty($term->child)){
                            $html .= '<div class="child">';
                            $html .= self::cards_child_tree($term->child);
                            $html .= '</div>';
                        }
                    $html .= '</div>';
    
                        if(!empty($term->thumbnail_url) && $showImage) {
                            $html .= '<a href="'.get_term_link($term->term_id).'">';
                                $width = !empty($term->thumbnail_width) ? ' width="'.$term->thumbnail_width.'"' : '';
                                $height = !empty($term->thumbnail_height) ? ' height="'.$term->thumbnail_height.'"' : '';
                                $html .= '<img loading="lazy" '.$width.$height.' src="' . $term->thumbnail_url . '" alt="' . $term->name . '" class="grid-thumb"/>';
                            $html .= '</a>';
                        }
                $html .= '</div>';
            }
        }
        $html .= '</div>';
        return $html;
    }

    static function get_taxonomies($post_type){
        $result = [];

        $taxonomies = get_object_taxonomies($post_type, 'objects');

        foreach ($taxonomies as $taxonomy) {
            $termsCount = count(get_terms([
                'taxonomy' => $taxonomy->name,
                'hide_empty' => false,
            ]));
            if($taxonomy->show_in_rest && $termsCount > 0) $result[] = ['value' => $taxonomy->name, 'label' => $taxonomy->label];
        }

        return $result;
    }

    static function get_terms($taxonomy, $show_empty = true, $order_by = 'name', $order = 'ASC', $include = '', $exclude = '', $hierarchy = false, $excludeFromCurrent = false, $showChild = false, $image_meta = '', $number = 200, $imageSize = 'thumbnail', $childOf = '') {
        $include = strlen($include) > 0 ? explode(',', $include) : $include;
        $exclude = strlen($exclude) > 0 ? explode(',', $exclude) : $exclude;
		
        if($excludeFromCurrent && (is_tax() || is_category() || is_tag())) {
          if(empty($exclude)) $exclude = [get_queried_object()->term_id];
          else $exclude[] = get_queried_object()->term_id;
        }

        $args = [
            'orderby'       => $order_by,
            'order'         => $order,
            'hide_empty'    => !$show_empty,
            'include'       => $include,
            'exclude'       => $exclude,
        ];

        if($childOf) $args['child_of'] = (int)$childOf;

        if($number) $args['number'] = $number;

        if($showChild && (is_tax() || is_category() || is_tag())) {
            $parent = get_queried_object()->term_id;
            $args['parent'] = $parent;
        }

        if($hierarchy) {
            $args['parent'] = 0;
            $parent_terms = get_terms($taxonomy, $args);
            unset($args['parent']);

            $result_terms = self::set_term_tree($parent_terms, $args, $taxonomy);

        } else {
            $terms = get_terms($taxonomy, $args);
            $result_terms = [];

            foreach ($terms as $key => $term) {
                $result_terms[$key] = $term;
                if(!is_object($term)) continue;
                $thumbnail_url = '';

//                if ( $taxonomy == 'product_cat' ) {
//                    $image_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
//
//                    if ($image_id) {
//                        $thumbnail_url = wp_get_attachment_url( $image_id );
//                    } else {
//                        $thumbnail_url = wc_placeholder_img_src();
//                    }
//
//                    $result_terms[$key]->thumbnail_url = $thumbnail_url;
//                }
				
				if(!empty($image_meta)) {
                    $thumb_width = '';
                    $thumb_height = '';
					$thumb_url = get_term_meta( $term->term_id, $image_meta, true );
					if (is_array($thumb_url)) $thumb_url = $thumb_url[0];
					if(is_numeric($thumb_url)) {
                        $thumb = wp_get_attachment_image_src($thumb_url, $imageSize);
                        $thumb_url = !empty($thumb[0]) ? $thumb[0] : '';
                        $thumb_width = !empty($thumb[1]) ? $thumb[1] : '';
                        $thumb_height = !empty($thumb[2]) ? $thumb[2] : '';
                    }
					$thumb_url = esc_url($thumb_url);

                    if(is_object($result_terms[$key])){
                        $result_terms[$key]->thumbnail_url = $thumb_url;
                        if(!empty($thumb_width)) $result_terms[$key]->thumbnail_width = $thumb_width;
                        if(!empty($thumb_height)) $result_terms[$key]->thumbnail_height = $thumb_height;
                    }
					
				}
            }
        }

        return $result_terms;
    }

    static function set_term_tree($terms, $args, $taxonomy) {
        $res = [];

        foreach ($terms as $key => $parent_term) {
            $args['parent'] = $parent_term->term_id;
            $child_terms = get_terms($taxonomy, $args);

            $child_res = [];
            if(!empty($child_terms)) $child_res = self::set_term_tree($child_terms, $args, $taxonomy);

            $res[$key] = $parent_term;
            $res[$key]->child = $child_res;

            $thumbnail_url = $thumb_height = $thumb_width ='';

            if ( $taxonomy == 'product_cat' ) {
                $image_id = get_term_meta( $parent_term->term_id, 'thumbnail_id', true );
                if ($image_id) {
                    $thumb = wp_get_attachment_image_src($thumbnail_url);
                    $thumbnail_url = !empty($thumb[0]) ? $thumb[0] : '';
                    $thumb_width = !empty($thumb[1]) ? $thumb[1] : '';
                    $thumb_height = !empty($thumb[2]) ? $thumb[2] : '';
                } else {
                    $thumbnail_url = wc_placeholder_img_src();
                }

                $res[$key]->thumbnail_url = $thumbnail_url;
                if(!empty($thumb_width)) $res[$key]->thumbnail_width = $thumb_width;
                if(!empty($thumb_height)) $res[$key]->thumbnail_height = $thumb_height;
            }
        }

        return $res;
    }

}

new ProductTaxonomy;