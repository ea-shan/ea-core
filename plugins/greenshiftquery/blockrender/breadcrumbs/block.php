<?php


namespace greenshiftquery\Blocks;
defined('ABSPATH') OR exit;


class Breadcrumbs{

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
		'dynamicGClasses' => array(
			'type' => 'array',
			'default' => []
		),
        'separator'       => array(
            'type'    => 'string',
            'default' => ' / ',
        ),
		'show_home' => array(
			'type'    => 'boolean',
			'default' => true,
		),
		'disable_current' => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'homelabel'       => array(
            'type'    => 'string',
            'default' => 'Home',
        ),
	);

	public function render_block($settings = array(), $inner_content=''){
		extract($settings);
		$out = '';
		$blockId = 'gspb_id-' . esc_attr($id);
		$data_attributes = \gspb_getDataAttributesfromDynamic($settings);
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => $blockId . ' gspb-breadcrumbs',
				...$data_attributes
			)
		);
		
		$out = '<div aria-label="Breadcrumb" ' .$wrapper_attributes . gspb_AnimationRenderProps($animation) . '>';
		$out .= $this->generate_breadcrumb($separator, $show_home, $homelabel, $disable_current);
		$out .= '</div>';
		return $out;
	}

	public function generate_breadcrumb($separator = '/', $show_home = true, $homelabel = 'Home', $disable_current = false) {
		$out = '';
		$separator = '<span class="gspb-separator-breadcrumbs">' . $separator . '</span>';
		if((is_singular() || is_archive() || is_page()) && !is_admin()){
			// Display the Home link if enabled
			if ($show_home) {
				$out .= '<a href="' . home_url() . '">'.$homelabel.'</a>' . $separator;
			}
			
			// Check if on a single page or archive page
			if (is_singular()) {
				global $post;
				$post_type = get_post_type();
				
				// Get the hierarchical taxonomy terms for custom post types
				if ($post_type !== 'post') {
		
					$ancestors = get_post_ancestors($post->ID);
				
					if (!empty($ancestors)) {
						$ancestors = array_reverse($ancestors);
						
						foreach ($ancestors as $ancestor_id) {
							$out .= '<a href="' . get_permalink($ancestor_id) . '">' . get_the_title($ancestor_id) . '</a>' . $separator;
						}
					}else{
						$custom_taxonomies = get_object_taxonomies($post_type);
		
						if(!empty($custom_taxonomies)){
							$taxonomy = $custom_taxonomies[0];
							$terms = get_the_terms($post->ID, $taxonomy);
							
							if ($terms && !is_wp_error($terms)) {
								$term = array_shift($terms);
								$ancestors = array_reverse(get_ancestors($term->term_id, $taxonomy));
								
								foreach ($ancestors as $ancestor_id) {
									$ancestor = get_term($ancestor_id, $taxonomy);
									
									$out .= '<a href="' . get_term_link($ancestor) . '">' . $ancestor->name . '</a>' . $separator;
								}
		
								$out .= '<a href="' . get_term_link($term) . '">' . $term->name . '</a>' . $separator;
								
							}
						}
					
					}
		
				} else {
					// For regular posts, display categories
					$categories = get_the_category();
					
					if ($categories) {
					$categories = array_reverse($categories);
					
					foreach ($categories as $category) {
						$ancestors = get_ancestors($category->cat_ID, 'category');
						
						if ($ancestors) {
							$ancestors = array_reverse($ancestors);
							
							foreach ($ancestors as $ancestor_id) {
								$ancestor = get_category($ancestor_id);
								
								$out .= '<a href="' . get_category_link($ancestor) . '">' . $ancestor->name . '</a>' . $separator;
							}
							$out .= '<a href="' . get_category_link($category) . '">' . $category->name . '</a>' . $separator;
							break;
						}else{
							$out .= '<a href="' . get_category_link($category) . '">' . $category->name . '</a>' . $separator;
						}
						
					}
				}
				}
				if (!$disable_current) {
					$out .= '<span class="gspb-current-breadcrumb">' . get_the_title() . '</span>';
				}
			} elseif (is_page()) {
				global $post;
				// For pages, display the parent pages
				
				$ancestors = get_post_ancestors($post->ID);
				
				if (!empty($ancestors)) {
					$ancestors = array_reverse($ancestors);
					
					foreach ($ancestors as $ancestor_id) {
						$out .= '<a href="' . get_permalink($ancestor_id) . '">' . get_the_title($ancestor_id) . '</a>' . $separator;
					}
				}
				
				$out .= '<span class="gspb-current-breadcrumb">' . get_the_title() . '</span>';
			} elseif (is_archive()) {
				// For archive pages, display the term name
				
				$taxonomies = get_taxonomies(array('hierarchical' => true), 'objects');
				
				foreach ($taxonomies as $taxonomy) {
					$term = get_queried_object();
					
					if (is_tax($taxonomy->name)) {
						$ancestors = array_reverse(get_ancestors($term->term_id, $taxonomy->name));
						
						foreach ($ancestors as $ancestor_id) {
							$ancestor = get_term($ancestor_id, $taxonomy->name);
							
							$out .= '<a href="' . get_term_link($ancestor) . '">' . $ancestor->name . '</a>' . $separator;
						}
						
						$out .= '<span class="gspb-current-breadcrumb">' . $term->name . '</span>';
					}
				}
			} else {
				// For other pages, display the page title
				$out .= '<span class="gspb-current-breadcrumb" aria-current="page">' . get_the_title() . '</span>';
			}
		}
		return $out;
	}
}

new Breadcrumbs;