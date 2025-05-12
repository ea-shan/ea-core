<?php


namespace greenshiftquery\Blocks;

defined('ABSPATH') or exit;


class RepeaterQuery
{

	public function __construct()
	{
		add_action('init', array($this, 'init_handler'));
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
		'limit' => array(
			'type'    => 'number',
			'default' => null,
		),
		'animation' => array(
			'type' => 'object',
			'default' => array(),
		),
		'sourceType'       => array(
			'type'    => 'string',
			'default' => 'latest_item',
		),
		'repeaterType'       => array(
			'type'    => 'string',
			'default' => 'acf',
		),
		'postId'       => array(
			'type'    => 'number',
			'default' => 0,
		),
		'post_type' => array(
			'type' => 'string',
			'default' => 'post'
		),
		'dynamicField' => array(
			'type' => 'string',
			'default' => ''
		),
		'isSlider' => array(
			'type' => 'boolean',
			'default' => false,
		),
		'repeaterField' => array(
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
		'taxonomy' => array(
			'type' => 'string',
			'default' => 'category'
		),
		'extra_filters' => array(
			'type' => 'object',
			'default' => []
		),
		'api_filters' => array(
			'type' => 'object',
			'default' => []
		),
		'query_filters' => array(
			'type' => 'object',
			'default' => []
		),
		'query_extra' => array(
			'type' => 'object',
			'default' => []
		),
		'container_link' => array(
			'type' => 'boolean',
			'default' => false
		),
		'linkNewWindow' => array(
			'type' => 'boolean',
			'default' => false
		),
		'linkNoFollow' => array(
			'type' => 'boolean',
			'default' => false
		),
		'linkSponsored' => array(
			'type' => 'boolean',
			'default' => false
		),
		'linkTitleField' => array(
			'type' => 'string',
			'default' => ''
		),
		'linkTypeField' => array(
			'type' => 'string',
			'default' => ''
		),
	);

	protected function normalize_arrays(&$settings, $fields = ['cat', 'tag', 'ids', 'taxdropids', 'field', 'cat_exclude', 'tag_exclude', 'postid', 'tax_slug', 'tax_slug_exclude', 'user_id'])
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

	protected function loop_inner_atts($block, $innerblocks, $value)
	{
		foreach ($innerblocks as $index => $innerBlock) {
			if (!empty($innerBlock['attrs']['repeaterField'])) {
				$block['innerBlocks'][$index]['attrs']['repeaterArray'] = $value;
			}
			if (!empty($innerBlock['innerBlocks'])) {
				$this->loop_inner_atts($block['innerBlocks'][$index], $innerBlock['innerBlocks'], $value);
			}
		}
		return $block;
	}

	protected function addKeyToRepeaterLevels(&$array, $keyToAdd, $repeaterArray)
	{
		foreach ($array as $key => &$value) {
			if (is_array($value)) {
				if (isset($value['repeaterField'])) {
					$value[$keyToAdd] = $repeaterArray;
				}
				$this->addKeyToRepeaterLevels($value, $keyToAdd, $repeaterArray);
			}
		}
	}

	protected function loop_inner_blocks($blocks, $value)
	{
		// Loop through each block.
		foreach ($blocks as $block) {
			// Do something with the current block.
			// For example, you could output the block's content:
			//echo $block['innerHTML'];
			$this->addKeyToRepeaterLevels($block, 'repeaterArray', $value);

			$block_content = (new \WP_Block(
				$block
			)
			)->render(array('dynamic' => true));
			echo $block_content;
		}
	}

	public function gspb_grid_constructor($settings, $content, $block, $wrapper = true, $extra_data = [], $runindex = 0)
	{
		extract($settings);
		if (isset($align)) {
			if ($align == 'full') {
				$alignClass = 'alignfull';
			} elseif ($align == 'wide') {
				$alignClass = 'alignwide';
			} elseif ($align == '') {
				$alignClass = '';
			}
		} else {
			$alignClass = '';
		}
		$result = [];
		if (empty($dynamicField) && !empty($repeaterArray) && !empty($repeaterField)) {
			$getrepeatable = GSPB_get_value_from_array_field($repeaterField, $repeaterArray);
			if ($repeaterType == 'relationpostobj' || $repeaterType == 'relationpostids') {			
				if ($repeaterType == 'relationpostids') {
					if (!empty($getrepeatable) && !is_array($getrepeatable)) {
						$ids = wp_parse_id_list($getrepeatable);
					} else {
						$ids = $getrepeatable;
					}
					if(!empty($ids)){
						$args = array(
							'post__in' => $ids,
							'numberposts' => '-1',
							'orderby' => 'post__in',
							'ignore_sticky_posts' => 1,
							'post_type' => 'any'
						);
						$args = apply_filters('gspb_relationpostids_query_args', $args, $block);
						$getrepeatable = get_posts($args);
					}
				}
				if (!empty($getrepeatable)) {
					if (!is_array($getrepeatable)) {
						$getrepeatable = [$getrepeatable];
					}
					$posts = [];
					foreach ($getrepeatable as $key => $value) {
						if (is_object($value) && !empty($value->ID)) {
							$posts[$key] = (array) $value;
							$posts[$key]['thumbnail_url'] = get_the_post_thumbnail_url($value->ID, 'full');
							$posts[$key]['author'] = get_the_author_meta('display_name', $value->post_author);
							$posts[$key]['date'] = get_the_date('', $value->ID);
							$posts[$key]['modified_date'] = get_the_modified_date('', $value->ID);
							$posts[$key]['permalink'] = get_the_permalink($value->ID);
	
							$remove_keys = ['post_author', 'post_date', 'post_date_gmt', 'post_status', 'comment_status', 'ping_status', 'post_password', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt', 'post_parent', 'menu_order', 'post_type', 'post_mime_type', 'filter'];
							foreach ($remove_keys as $keyname) {
								unset($posts[$key][$keyname]);
							}
							$custom_fields = get_post_meta($value->ID);
							foreach ($custom_fields as $fieldindex => $fieldvalue) {
								if (is_serialized($fieldvalue[0])) {
									$fieldvalue[0] = maybe_unserialize($fieldvalue[0]);
								}
								if (!empty($fieldvalue[0])) {
									$posts[$key][$fieldindex] = GSPB_field_array_to_value($fieldvalue[0], ', ');
								}
							}
						}
					}
					$getrepeatable = $posts;
				}
			}
		}else{
			if ($sourceType == 'latest_item') {
				global $post;
				if (is_object($post)) {
					$postId = $post->ID;
				}
			} else {
				$postId = (isset($postId) && $postId > 0) ? (int)$postId : 0;
				if ($postId == 0) {
					$args = array(
						'post_type' => $post_type,
						'posts_per_page'  => 1,
						'fields' => 'ids',
						'post_status' => 'publish'
					);
					$latest_cpt = get_posts($args);
					$postId = $latest_cpt[0];
				}
			}
			if ($repeaterType == 'taxonomy') {
				$filters = greenshiftquery_sanitize_multi_array($extra_filters);
				//print_r($filters);
				$taxonomy = !empty($filters['taxonomy']) ? $filters['taxonomy'] : 'category';
				if(!empty($filters['show_current'])){
					if(is_tax() || is_category() || is_tag()){
                        if (!empty($filters['get_current_data'])) {
                            $term = get_queried_object();
                            $getrepeatable = get_term($term->term_id, $taxonomy);
                        } else {
                            $term = get_queried_object();
                            $args = array(
                                'parent' => $term->term_id
                            );
                            if (!empty($filters['number'])) {
                                $args['number'] = (int)$filters['number'];
                            } else {
                                $args['number'] = 12;
                            }
                            $getrepeatable = get_terms($taxonomy, $args);
                        }
					}else{
						$getrepeatable = get_the_terms($postId, $taxonomy);
					}
				}else{
					$args = ['parent' => 0];
					if(!empty($filters['include'])){
						$args['include'] = sanitize_text_field($filters['include']);
					}
					if(!empty($filters['exclude'])){
						$args['exclude'] = sanitize_text_field($filters['exclude']);
					}
					if(!empty($filters['orderby'])){
						$args['orderby'] = sanitize_text_field($filters['orderby']);
					}
					if(!empty($filters['show_empty'])){
						$args['hide_empty'] = false;
					}
					if(!empty($filters['order']) && $filters['order'] == 'DESC'){
						$args['order'] = sanitize_text_field($filters['order']);
					}
					if(!empty($filters['meta_key'])){
						$args['meta_key'] = sanitize_text_field($filters['meta_key']);
						if(!empty($filters['meta_value'])){
							$args['meta_value'] = sanitize_text_field($filters['meta_value']);
						}
						if(!empty($filters['meta_compare'])){
							$args['meta_compare'] = sanitize_text_field($filters['meta_compare']);
						}
					}
					$getrepeatable = get_terms($taxonomy, $args);
				}
				if (!empty($getrepeatable) && !is_wp_error($getrepeatable)) {
					if (!is_array($getrepeatable)) {
						$getrepeatable = [$getrepeatable];
					}
					$terms = [];
					$posts_by_term = [];
					if(!empty($filters['cross_enabled'])){
						$cross_post_type = !empty($filters['cross_post_type']) ? $filters['cross_post_type'] : 'post';
						$cross_limit = !empty($filters['cross_limit']) ? (int)$filters['cross_limit'] : 10;
						$term_ids = wp_list_pluck($getrepeatable, 'term_id');
						$args = [
							'post_type' => $cross_post_type, // Replace with your custom post type if needed
							'posts_per_page' => $cross_limit, // Get all posts, we'll limit them later
							'tax_query' => [
								[
									'taxonomy' => $taxonomy,
									'terms' => $term_ids,
									'field' => 'term_id',
								],
							],
							'orderby' => 'date',
							'order' => 'DESC',
							'no_found_rows' => true,
						];

						$posts = get_posts($args);

						foreach ($posts as $post) {
							$post_terms = wp_get_post_terms($post->ID, $taxonomy, ['fields' => 'ids']);
							
							foreach ($post_terms as $post_term) {
								$posts_by_term[$post_term][] = [
									'title' => get_the_title($post->ID),
									'link' => get_permalink($post->ID),
									'featured_image' => get_the_post_thumbnail_url($post->ID, 'full'),
									'author' => get_the_author_meta('display_name', $post->post_author),
									'date' => get_the_date('', $post->ID),
									'modified_date' => get_the_modified_date('', $post->ID),
									'permalink' => get_the_permalink($post->ID)
								];
							}
						}
					}
					foreach ($getrepeatable as $key => $value) {
						if (is_object($value) && !empty($value->term_id)) {
							$terms[$key] = (array) $value;
							$terms[$key]['permalink'] = get_term_link($value);
	
							$remove_keys = ['term_id', 'term_group', 'term_taxonomy_id', 'taxonomy', 'parent', 'filter'];
							foreach ($remove_keys as $keyname) {
								unset($terms[$key][$keyname]);
							}
							$custom_fields = get_term_meta($value->term_id);
							foreach ($custom_fields as $fieldindex => $fieldvalue) {
								if (is_serialized($fieldvalue[0])) {
									$fieldvalue[0] = maybe_unserialize($fieldvalue[0]);
								}
								if (!empty($fieldvalue[0])) {
									$terms[$key][$fieldindex] = GSPB_field_array_to_value($fieldvalue[0], ', ');
								}
							}
							if(!empty($posts_by_term) && !empty($posts_by_term[$value->term_id])){
								$terms[$key]['crossposts'] = $posts_by_term[$value->term_id];
							}
						}
					}
					$getrepeatable = $terms;
				}
			}else if ($repeaterType == 'users') {
				$filters = greenshiftquery_sanitize_multi_array($extra_filters);
				$source = !empty($filters['source']) ? $filters['source'] : 'ids';
				$args = [];	
				if($source == 'ids'){
					if(!empty($filters['ids'])){
						$ids = wp_parse_id_list($filters['ids']);
						if(!empty($ids)){
							$args['include'] = $ids;
						}
					}
				}else if($source == 'role'){
					if(!empty($filters['role'])){
						$args['role'] = trim($filters['role']);
					}
				}else if($source == 'field'){
					if(!empty($filters['field'])){
						$field = trim($filters['field']);
						$field = GSPB_make_dynamic_from_metas($filters['field'], $postId);
						$ids = wp_parse_id_list($field);
						if(!empty($ids)){
							$args['include'] = $ids;
						}else{
							$args['role'] = 'noexistedrole';
						}
					}
				}else if($source == 'current'){
					$args['include'] = get_current_user_id();
				}else if($source == 'author'){
					$args['include'] = get_post_field( 'post_author', $postId );
				}
				if(!empty($filters['orderby'])){
					$args['orderby'] = sanitize_text_field($filters['orderby']);
				}
				if(!empty($filters['order']) && $filters['order'] == 'DESC'){
					$args['order'] = sanitize_text_field($filters['order']);
				}
				if(!empty($filters['meta_key'])){
					$args['meta_key'] = sanitize_text_field($filters['meta_key']);
					if(!empty($filters['meta_value'])){
						$args['meta_value'] = sanitize_text_field($filters['meta_value']);
					}
					if(!empty($filters['meta_compare'])){
						$args['meta_compare'] = sanitize_text_field($filters['meta_compare']);
					}
				}
				if(!empty($filters['number'])){
					$args['number'] = (int)$filters['number'];
				}else{
					$args['number'] = 12;
				}
				if(!empty($args)){
					$users = get_users($args);
					if (!empty($users)) {
						$items = [];
						$posts_by_user = [];
						if(!empty($filters['cross_enabled'])){
							$cross_post_type = !empty($filters['cross_post_type']) ? $filters['cross_post_type'] : 'post';
							$cross_limit = !empty($filters['cross_limit']) ? (int)$filters['cross_limit'] : 10;
							$user_ids = wp_list_pluck($users, 'ID');
							$args = [
								'post_type' => $cross_post_type, // Replace with your custom post type if needed
								'posts_per_page' => $cross_limit, // Get all posts, we'll limit them later
								'author__in' => $user_ids,
								'orderby' => 'date',
								'order' => 'DESC',
								'no_found_rows' => true,
							];
	
							$posts = get_posts($args);
	
							foreach ($posts as $post) {
								$author_id = $post->post_author;
								$posts_by_user[$author_id][] = [
									'title' => get_the_title($post->ID),
									'link' => get_permalink($post->ID),
									'featured_image' => get_the_post_thumbnail_url($post->ID, 'full'),
									'author' => get_the_author_meta('display_name', $post->post_author),
									'date' => get_the_date('', $post->ID),
									'modified_date' => get_the_modified_date('', $post->ID),
									'permalink' => get_the_permalink($post->ID)
								];
								
							}
						}
						foreach ($users as $key => $value) {
							//$items[$key] = (array) $value;
							$items[$key]['permalink'] = get_author_posts_url($value->ID);
							$items[$key]['avatar'] = get_avatar_url($value->ID, ['size' => !empty($filters['size']) ? (int)$filters['size'] : 96]);
							$items[$key]['display_name'] = $value->display_name;
							$items[$key]['user_email'] = $value->user_email;
							$items[$key]['user_url'] = $value->user_url;
							$items[$key]['user_registered'] = $value->user_registered;
							$items[$key]['roles'] = GSPB_field_array_to_value($value->roles, ', ');
							$items[$key]['description'] = $value->description;
							$items[$key]['user_nicename'] = $value->user_nicename;
	
							$custom_fields = get_user_meta($value->ID);
							foreach ($custom_fields as $fieldindex => $fieldvalue) {
								if($fieldindex == 'rich_editing' || $fieldindex == 'syntax_highlighting' || $fieldindex == 'comment_shortcuts' || $fieldindex == 'admin_color' || $fieldindex == 'show_admin_bar_front' || $fieldindex == 'use_ssl' || $fieldindex == 'show_welcome_panel' || $fieldindex == 'locale' || $fieldindex == 'wp_capabilities' || $fieldindex == 'wp_user_level' || $fieldindex == 'dismissed_wp_pointers' || $fieldindex == 'session_tokens' || $fieldindex == 'wp_dashboard_quick_press_last_post_id' || $fieldindex == 'wp_user-settings-time' || $fieldindex == 'wp_capabilities' || $fieldindex == 'wp_user-settings' || $fieldindex == 'wp_user-settings-time'){
									continue;
								}
								if (is_serialized($fieldvalue[0])) {
									$fieldvalue[0] = maybe_unserialize($fieldvalue[0]);
								}
								if (!empty($fieldvalue[0])) {
									$items[$key][$fieldindex] = GSPB_field_array_to_value($fieldvalue[0], 'no');
								}
							}
							if(!empty($posts_by_user) && !empty($posts_by_user[$value->ID])){
								$items[$key]['crossposts'] = $posts_by_user[$value->ID];
							}
						}
					}else{
						$items = [];
					}
				}else{
					$items = [];
				}
				$getrepeatable = $items;
	
			}else if ($repeaterType == 'comments') {
				$filters = greenshiftquery_sanitize_multi_array($extra_filters);
				$source = !empty($filters['source']) ? $filters['source'] : '';
				$args = [];	
				if($source == 'ids'){
					if(!empty($filters['ids'])){
						$ids = wp_parse_id_list($filters['ids']);
						if(!empty($ids)){
							$args['comment__in'] = $ids;
						}
					}
				}else if($source == 'post_ids'){
					if(!empty($filters['ids'])){
						$ids = wp_parse_id_list($filters['ids']);
						if(!empty($ids)){
							$args['post__in'] = $ids;
						}
					}
				}else if($source == 'author_ids'){
					if(!empty($filters['ids'])){
						$ids = wp_parse_id_list($filters['ids']);
						if(!empty($ids)){
							$args['author__in'] = $ids;
						}
					}
				}else if($source == 'post'){
					$args['post_id'] = $postId;
				}
				if(!empty($filters['orderby'])){
					$args['orderby'] = sanitize_text_field($filters['orderby']);
				}
				if(!empty($filters['order']) && $filters['order'] == 'DESC'){
					$args['order'] = sanitize_text_field($filters['order']);
				}
				if(!empty($filters['meta_key'])){
					$args['meta_key'] = sanitize_text_field($filters['meta_key']);
					if(!empty($filters['meta_value'])){
						$args['meta_value'] = sanitize_text_field($filters['meta_value']);
					}
					if(!empty($filters['meta_compare'])){
						$args['meta_compare'] = sanitize_text_field($filters['meta_compare']);
					}
				}
				if(!empty($filters['number'])){
					$args['number'] = (int)$filters['number'];
				}else{
					$args['number'] = 12;
				}
				if(!empty($filters['post_type'])){
					$args['post_type'] = sanitize_text_field($filters['post_type']);
				}
				$comments = get_comments($args);
				if (!empty($comments)) {
					$items = [];
					foreach ($comments as $key => $value) {
						//$items[$key] = (array) $value;
						$comment_id = $value->comment_ID;
						$post_id = $value->comment_post_ID;
						$author_id = $value->user_id; // Author ID
						$items[$key]['permalink'] = get_comment_link($comment_id);
						$items[$key]['content'] = $value->comment_content;
						$items[$key]['date'] = $value->comment_date;
						$items[$key]['date_gmt'] = $value->comment_date_gmt;
						$items[$key]['time'] = $value->comment_time;
						$items[$key]['post_permalink'] = get_the_permalink($post_id);
						$items[$key]['post_title'] = get_the_title($post_id);

						$items[$key]['author'] = $value->comment_author;
						$items[$key]['author_email'] = $value->comment_author_email;
						$items[$key]['author_url'] = $value->comment_author_url;
						$items[$key]['author_IP'] = $value->comment_author_IP;

						if ($author_id) {
							$user = get_userdata($author_id);
							if ($user) {
								$items[$key]['display_name'] = $user->display_name;
								$items[$key]['user_nicename'] = $user->user_nicename;
								$items[$key]['avatar'] = get_avatar_url($author_id, ['size' => !empty($filters['size']) ? (int)$filters['size'] : 96]);
							}
						}

						$custom_fields = get_comment_meta($value->comment_ID);
						foreach ($custom_fields as $fieldindex => $fieldvalue) {
							if($fieldindex == 'rich_editing' || $fieldindex == 'syntax_highlighting' || $fieldindex == 'comment_shortcuts' || $fieldindex == 'admin_color' || $fieldindex == 'show_admin_bar_front' || $fieldindex == 'use_ssl' || $fieldindex == 'show_welcome_panel' || $fieldindex == 'locale' || $fieldindex == 'wp_capabilities' || $fieldindex == 'wp_user_level' || $fieldindex == 'dismissed_wp_pointers' || $fieldindex == 'session_tokens' || $fieldindex == 'wp_dashboard_quick_press_last_post_id' || $fieldindex == 'wp_user-settings-time' || $fieldindex == 'wp_capabilities' || $fieldindex == 'wp_user-settings' || $fieldindex == 'wp_user-settings-time'){
								continue;
							}
							if (is_serialized($fieldvalue[0])) {
								$fieldvalue[0] = maybe_unserialize($fieldvalue[0]);
							}
							if (!empty($fieldvalue[0])) {
								$items[$key][$fieldindex] = GSPB_field_array_to_value($fieldvalue[0], 'no');
							}
						}
					}
				}else{
					$items = [];
				}
				$getrepeatable = $items;

			}else if ($repeaterType == 'ce') {
				if(defined('\ContentEgg\PLUGIN_PATH') && class_exists('\ContentEgg\application\components\GreenshiftIntegrator')){
					$filters = greenshiftquery_sanitize_multi_array($extra_filters);
					$getrepeatable = \ContentEgg\application\components\GreenshiftIntegrator::getProductData($postId, $filters);
					if (!empty($getrepeatable)) {
						if (!is_array($getrepeatable)) {
							$getrepeatable = [$getrepeatable];
						}
						$items = [];
						$allowed_fields = \ContentEgg\application\components\GreenshiftIntegrator::getAllowedProductFields();
						foreach ($getrepeatable as $key => $value) {
							if(!empty($value) && is_array($value) && is_array($allowed_fields)){
								$filtered = wp_array_slice_assoc($value, $allowed_fields);
								if(!empty($filtered)){
									$items[$key] = $filtered;
								}
							}
						}
						$getrepeatable = $items;
					}
				}else{
					$getrepeatable = [];
				}
			}else if($repeaterType == 'site_data'){
				$filters = greenshiftquery_sanitize_multi_array($extra_filters);
				$option_name = !empty($filters['option_name']) ? $filters['option_name'] : '';
				$getrepeatable = get_option($option_name); 
				if (!is_array($getrepeatable)) {
                    $getrepeatable = [];
                }else{
                    $items = [];
                    foreach($getrepeatable as $key => $value){
                        $items[$key] = (array) $value;
                    }
                    $getrepeatable = $items;
                }
			}else if ($repeaterType == 'acfsiteoption' && function_exists('get_field')) {
                $filters = greenshiftquery_sanitize_multi_array($extra_filters);
                $option_name = !empty($filters['option_name']) ? $filters['option_name'] : '';
                $getrepeatable = get_field($option_name, 'option');
                if (!is_array($getrepeatable)) {
                    $getrepeatable = [];
                }else{
                    $items = [];
                    foreach($getrepeatable as $key => $value){
                        $items[$key] = (array) $value;
                    }
                    $getrepeatable = $items;
                }
            } else if($repeaterType == 'transient'){
				$filters = greenshiftquery_sanitize_multi_array($extra_filters);
				$option_name = !empty($filters['option_name']) ? $filters['option_name'] : '';
				$getrepeatable = get_transient($option_name); 
				if(!is_array($getrepeatable)){
					$getrepeatable = [];
				}
			} else if($repeaterType == 'metajson') {
                $getrepeatable = GSPB_get_custom_field_value($postId, $field, 'no');
                if (!empty($getrepeatable)) {
                    $getrepeatable = json_decode($getrepeatable, true);
                }
                if (!is_array($getrepeatable)) {
                    $getrepeatable = [];
                }
            } else if($repeaterType == 'api_request') {
				$cache = false;
				$getrepeatable = [];
				$filters = greenshiftquery_dynamic_placeholders_array($api_filters, $extra_data, $runindex);
                $apiUrl = !empty($filters['apiUrl']) ? (strpos($filters['apiUrl'], '%') !== false ? urldecode($filters['apiUrl']) : $filters['apiUrl']) : '';
				$apiUrlcache = !empty($filters['apiUrlcache']) ? $filters['apiUrlcache'] : '';
				$apiUrltransient = !empty($filters['apiUrltransient']) ? $filters['apiUrltransient'] : '';
				if(!empty($apiUrlcache) && !empty($apiUrltransient)){
					$transient_name = trim($apiUrltransient);
					$transient_value = get_transient($transient_name);
					if (false === $transient_value) {
						$getrepeatable = \gspb_api_connector_run($filters, $runindex);
					}else{
						$getrepeatable = $transient_value;
						$cache = true;
					}
				}else{
					if(!empty($apiUrl)){
						$getrepeatable = \gspb_api_connector_run($filters, $runindex);
					}
				}
                if (!is_array($getrepeatable)) {
                    $getrepeatable = [];
                } else{
					$singleData = !empty($filters['apiSingleData']) ? $filters['apiSingleData'] : '';
					if(!empty($singleData)){
						$getrepeatable = [$getrepeatable];
					}
				}
				// Check if we have Jet Engine Post Builder data
				if(!empty($getrepeatable) && is_array($getrepeatable) && !empty($getrepeatable[0]['post_title']) && !empty($getrepeatable[0]['post_content']) && !empty($getrepeatable[0]['ID']) && !$cache){
                    $posts = [];
                    foreach ($getrepeatable as $key => $value) {
						$ID = $value['ID'];
						$AuthorID = $value['post_author'];
						$posts[$key] = (array) $value;
						$posts[$key]['thumbnail_url'] = get_the_post_thumbnail_url($ID, 'full');
						$posts[$key]['author'] = get_the_author_meta('display_name', $AuthorID);
						$posts[$key]['date'] = get_the_date('', $ID);
						$posts[$key]['modified_date'] = get_the_modified_date('', $ID);
						$posts[$key]['permalink'] = get_the_permalink($ID);

						$remove_keys = [
							'post_author',
							'post_date',
							'post_date_gmt',
							'post_status',
							'comment_status',
							'ping_status',
							'post_password',
							'to_ping',
							'pinged',
							'post_modified',
							'post_modified_gmt',
							'post_parent',
							'menu_order',
							'post_type',
							'post_mime_type',
							'filter',
							'guid'
						];
						foreach ($remove_keys as $keyname) {
							unset($posts[$key][$keyname]);
						}
						$custom_fields = get_post_meta($ID);
						foreach ($custom_fields as $fieldindex => $fieldvalue) {
							if (is_serialized($fieldvalue[0])) {
								$fieldvalue[0] = maybe_unserialize($fieldvalue[0]);
							}
							if (!empty($fieldvalue[0])) {
								$posts[$key][$fieldindex] = GSPB_field_array_to_value($fieldvalue[0], ', ');
							}
						}

                    }
                    $getrepeatable = $posts;
				}
				// Get Proper WP JSON Data
				if(!empty($getrepeatable) && is_array($getrepeatable) && !empty($getrepeatable[0]['title']['rendered']) && !$cache){
					$posts = [];
					$media_ids = [];
					foreach ($getrepeatable as $key => $value) {
						$posts[$key] = (array) $value;
						$posts[$key]['title'] = $value['title']['rendered'];
						$posts[$key]['content'] = $value['content']['rendered'];
						$posts[$key]['excerpt'] = $value['content']['rendered'];
						$posts[$key]['modified_date'] = get_date_from_gmt($value['modified_gmt'], get_option('date_format') . ' ' . get_option('time_format'));
						$posts[$key]['date'] = get_date_from_gmt($value['date_gmt'], get_option('date_format') . ' ' . get_option('time_format'));
						$remove_keys = [
							'modified',
							'date',
							'date_gmt',
							'status',
							'comment_status',
							'ping_status',
							'password',
							'to_ping',
							'pinged',
							'modified_gmt',
							'post_modified_gmt',
							'parent',
							'order',
							'type',
							'mime_type',
							'filter',
							'guid',
							'excerpt'
						];
						foreach ($remove_keys as $keyname) {
							unset($posts[$key][$keyname]);
						}
						// Get Featured Media
						if (!empty($value['featured_media']) && $value['featured_media'] != 0) {
							$media_ids[] = $value['featured_media'];
						}
					}
					if(!empty($media_ids)){
						
                        $media_ids = array_unique($media_ids);
                        $id_query = implode( ',', $media_ids );
                        $domain = parse_url($apiUrl, PHP_URL_HOST);
						$media_count = count($media_ids);
                        $media_api_url = "https://{$domain}/wp-json/wp/v2/media?include={$id_query}&per_page={$media_count}";
                        
                        $media_response = wp_safe_remote_get($media_api_url);
                        if (!is_wp_error($media_response)) {
                            $media_items = json_decode(wp_remote_retrieve_body($media_response), true);
                            
                            // Step 3: Build a mapping where key = media id and value = source URL
                            $media_mapping = [];
                            if (!empty($media_items) && is_array($media_items)) {
                                foreach ($media_items as $media_item) {
                                    if (!empty($media_item['id']) && !empty($media_item['source_url'])) {
                                        $media_mapping[$media_item['id']] = $media_item['source_url'];
                                    }
                                }
                            }
                            
                            // Now update your posts with the corresponding thumbnail URLs
                            foreach ($posts as $key => $value) {
                                if (!empty($value['featured_media']) && isset($media_mapping[$value['featured_media']])) {
                                    $posts[$key]['thumbnail_url'] = $media_mapping[$value['featured_media']];
                                }
                            }
                        }
                    }
					$getrepeatable = $posts;
				}
				if(!empty($apiUrlcache) && !empty($apiUrltransient) && !empty($getrepeatable) && is_array($getrepeatable) && !$cache){
					set_transient($transient_name, $getrepeatable, $apiUrlcache);
				}
            } else if($repeaterType == 'query_args') {
				$getrepeatable = [];
				$filters = greenshiftquery_dynamic_placeholders_array($query_filters);
				$query_args = !empty($filters) ? $filters : '';
				if(!empty($query_args)){
					$getrepeatable = get_posts($query_args);
				}
                if (!is_array($getrepeatable)) {
                    $getrepeatable = [];
                }
				if(!empty($getrepeatable) && is_array($getrepeatable)){
                    $posts = [];
                    foreach ($getrepeatable as $key => $value) {
						$ID = $value->ID;
						$AuthorID = $value->post_author;
						$posts[$key] = (array) $value;
						$posts[$key]['thumbnail_url'] = get_the_post_thumbnail_url($ID, 'full');
						$posts[$key]['author'] = get_the_author_meta('display_name', $AuthorID);
						$posts[$key]['date'] = get_the_date('', $ID);
						$posts[$key]['modified_date'] = get_the_modified_date('', $ID);
						$posts[$key]['permalink'] = get_the_permalink($ID);

						$remove_keys = [
							'post_author',
							'post_date',
							'post_date_gmt',
							'post_status',
							'comment_status',
							'ping_status',
							'post_password',
							'to_ping',
							'pinged',
							'post_modified',
							'post_modified_gmt',
							'post_parent',
							'menu_order',
							'post_type',
							'post_mime_type',
							'filter',
							'guid'
						];
						foreach ($remove_keys as $keyname) {
							unset($posts[$key][$keyname]);
						}
						$custom_fields = get_post_meta($ID);
						foreach ($custom_fields as $fieldindex => $fieldvalue) {
							if (is_serialized($fieldvalue[0])) {
								$fieldvalue[0] = maybe_unserialize($fieldvalue[0]);
							}
							if (!empty($fieldvalue[0])) {
								$posts[$key][$fieldindex] = GSPB_field_array_to_value($fieldvalue[0], ', ');
							}
						}

                    }
                    $getrepeatable = $posts;
				}
            } else if($repeaterType == 'taxonomymeta') {
				$filters = greenshiftquery_sanitize_multi_array($extra_filters);
				//print_r($filters);
				$taxonomy = !empty($filters['taxonomy']) ? $filters['taxonomy'] : 'category';
				$dynamicField = !empty($filters['taxonomy_meta_field']) ? $filters['taxonomy_meta_field'] : '';
				if(is_tax() || is_category() || is_tag()){
					$getrepeatable = GSPB_get_custom_field_value(0, $dynamicField, 'flatarray', 'taxonomymeta');
				}else{
					$getrepeatable = [];
				}
			} else if($repeaterType == 'idstourl') {
                $getrepeatable = GSPB_get_custom_field_value($postId, $dynamicField, 'flatarray');
                $arrays = [];
                if (!empty($getrepeatable) && is_array($getrepeatable)) {
                    foreach($getrepeatable as $key => $value){
                        $attachment_id = $value;
                        $attachment = get_post($attachment_id);
                        $attachment_meta = wp_get_attachment_metadata($attachment_id);
                        $attachment_url = wp_get_attachment_url($attachment_id);
                        $attachment_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
                        $attachment_title = $attachment->post_title;
                        $attachment_caption = $attachment->post_excerpt;
                        $attachment_description = $attachment->post_content;
                        $attachment_mime_type = $attachment->post_mime_type;
                        $attachment_date = $attachment->post_date;
                        $attachment_modified = $attachment->post_modified;
                        
                        $arrays[$key] = [
                            'id' => $attachment_id,
                            'url' => $attachment_url,
                            'title' => $attachment_title,
                            'alt' => $attachment_alt,
                            'caption' => $attachment_caption,
                            'description' => $attachment_description,
                            'mime_type' => $attachment_mime_type,
                            'date' => $attachment_date,
                            'modified' => $attachment_modified,
                            'meta' => $attachment_meta
                        ];
                    }
                }
                $getrepeatable = $arrays;
            } else if(!empty($dynamicField)) {
				if ($repeaterType == 'acf' && function_exists('get_field')) {
					$getrepeatable = get_field($dynamicField, $postId);
				}else if ($repeaterType == 'relationpostobj' || $repeaterType == 'relationpostids') {
					if (function_exists('get_field')) {
						$getrepeatable = get_field($dynamicField, $postId);
					} else {
						$getrepeatable = GSPB_get_custom_field_value($postId, $dynamicField, 'flatarray');
					}
					if ($repeaterType == 'relationpostids' || !is_array($getrepeatable)) {
						if (!empty($getrepeatable) && !is_array($getrepeatable)) {
							$ids = wp_parse_id_list($getrepeatable);
						} else {
							$ids = $getrepeatable;
						}
						if(!empty($ids)){
							$args = array(
								'post__in' => $ids,
								'numberposts' => '-1',
								'orderby' => 'post__in',
								'ignore_sticky_posts' => 1,
								'post_type' => 'any'
							);
							$args = apply_filters('gspb_relationpostids_query_args', $args, $block);
							$getrepeatable = get_posts($args);
						}
					}
					if (!empty($getrepeatable)) {
						if (!is_array($getrepeatable)) {
							$getrepeatable = [$getrepeatable];
						}
						$posts = [];
						foreach ($getrepeatable as $key => $value) {
							if (is_object($value) && !empty($value->ID)) {
								$posts[$key] = (array) $value;
								$posts[$key]['thumbnail_url'] = get_the_post_thumbnail_url($value->ID, 'full');
								$posts[$key]['author'] = get_the_author_meta('display_name', $value->post_author);
								$posts[$key]['date'] = get_the_date('', $value->ID);
								$posts[$key]['modified_date'] = get_the_modified_date('', $value->ID);
								$posts[$key]['permalink'] = get_the_permalink($value->ID);
		
								$remove_keys = ['post_author', 'post_date', 'post_date_gmt', 'post_status', 'comment_status', 'ping_status', 'post_password', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt', 'post_parent', 'menu_order', 'post_type', 'post_mime_type', 'filter'];
								foreach ($remove_keys as $keyname) {
									unset($posts[$key][$keyname]);
								}
								$custom_fields = get_post_meta($value->ID);
								foreach ($custom_fields as $fieldindex => $fieldvalue) {
									if (is_serialized($fieldvalue[0])) {
										$fieldvalue[0] = maybe_unserialize($fieldvalue[0]);
									}
									if (!empty($fieldvalue[0])) {
										$posts[$key][$fieldindex] = GSPB_field_array_to_value($fieldvalue[0], ', ');
									}
								}
							}
						}
						$getrepeatable = $posts;
					}
				}else if ($repeaterType == 'acpt') {
					$getrepeatable = get_post_meta($postId, $dynamicField, true);
					if(!empty($getrepeatable) && is_array($getrepeatable)){
						$getrepeatable = gspb_acptConvertArray($getrepeatable);
					}
				}else {
					$getrepeatable = GSPB_get_custom_field_value($postId, $dynamicField, 'flatarray');
				}
			}
		}

		if (!empty($getrepeatable) && is_string($getrepeatable)) {
			$decoded = json_decode($getrepeatable, true);
			if (json_last_error() === JSON_ERROR_NONE) {
				$getrepeatable = $decoded;
			}
		}
		
		if (!empty($getrepeatable) && is_array($getrepeatable)) {
			$result = $getrepeatable;
		}
		$result = apply_filters('gspb_repeater_args_id', $result, $postId);
		ob_start();
		$block_instance = (is_array($block)) ? $block : $block->parsed_block;
		//echo '<pre>'; print_r($block_instance); echo '</pre>';
		$blockId = 'gspbgrid_id-' . esc_attr($block_instance['attrs']['id']);
		$blockIdInner = 'gspbgrid_id-' . esc_attr($block_instance['attrs']['id']).'-inner';
		//$wrapper_attributes = get_block_wrapper_attributes(array('class' => $blockId . ' ' . $alignClass));
	?>
		<?php if (!empty($result) && !empty($block_instance['innerBlocks'])) : ?>
			<?php if (isset($limit) && $limit > 0) : ?>
				<?php $result = array_slice($result, 0, $limit); ?>
			<?php endif; ?>
			<?php if($wrapper):?>
				<div class="<?php echo esc_attr($blockId . ' wp-block-greenshift-blocks-repeater ' . $alignClass . ' ' . (isset($className) ? $className : ''));?>" <?php echo gspb_AnimationRenderProps($animation, $interactionLayers, ".gspbgrid_item"); ?>>
					<div class="gspbgrid_list_builder <?php echo $isSlider ? 'swiper' : ''; ?>">
						<ul class="wp-block-repeater-template <?php echo $blockIdInner;?> <?php echo $isSlider ? ' swiper-wrapper' : ''; ?>">
							<?php $i = 0;
							foreach ($result as $key => $value) : ?>
								<?php $i++; ?>
								<?php if (is_object($value)) {
									$value = (array)$value;
								} ?>
								<li class="gspbgrid_item swiper-slide repeater-id-<?php echo (int)$key; ?>">
									<?php    
										if($container_link && !empty($linkTypeField) && !empty($value[$linkTypeField])){
											$link = esc_url($value[$linkTypeField]);
											$title = (!empty($linkTitleField) && !empty($value[$linkTitleField])) ? esc_attr($value[$linkTitleField]) : '';
											$newWindow = (!empty($linkNewWindow)) ? ' target="_blank"' : '';
											$linkNoFollow = (!empty($linkNoFollow)) ? ' rel="nofollow"' : '';
											$linkSponsored = (!empty($linkSponsored)) ? ' rel="sponsored"' : '';
											echo '<a class="gspbgrid_item_link" title="' . $title . '" href="' . $link . '"'.$newWindow.$linkNoFollow.$linkSponsored.'></a>';
										}   
									?>
									<?php $this->loop_inner_blocks($block_instance['innerBlocks'], $value); ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			<?php else: ?>
				<?php $i = 0;
					foreach ($result as $key => $value) : ?>
						<?php $i++; ?>
						<?php if (is_object($value)) {
							$value = (array)$value;
						} ?>
						<?php $this->loop_inner_blocks($block_instance['innerBlocks'], $value); ?>
					<?php endforeach; ?>
			<?php endif; ?>
		<?php endif; ?>
	<?php
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	public function render_block($settings = array(), $inner_content = '', $block = '')
	{
		extract($settings);
		$output = $this->gspb_grid_constructor($settings, $inner_content, $block);
		return $output;
	}
}

new RepeaterQuery;