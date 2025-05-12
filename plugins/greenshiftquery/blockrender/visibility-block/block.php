<?php


namespace greenshiftquery\Blocks;

defined('ABSPATH') or exit;


class VisibilityBlock
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
		'id' => array(
			'type'    => 'string',
			'default' => null,
		),
		'inlineCssStyles' => array(
			'type'    => 'string',
			'default' => '',
		),
		'data_source' => array(
			'type' => 'string',
			'default' => 'cat'
		),
		'post_type' => array(
			'type' => 'string',
			'default' => 'post'
		),
		'cat' => array(
			'type' => 'array',
			'default' => []
		),
		'tag' => array(
			'type' => 'array',
			'default' => []
		),
		'cat_exclude' => array(
			'type' => 'array',
			'default' => []
		),
		'tag_exclude' => array(
			'type' => 'array',
			'default' => []
		),
		'tax_name' => array(
			'type' => 'string',
			'default' => '',
		),
		'tax_slug' => array(
			'type' => 'array',
			'default' => []
		),
		'tax_slug_exclude' => array(
			'type' => 'array',
			'default' => []
		),
		'price_range' => array(
			'type' => 'string',
			'default' => ''
		),
		'user_id' => array(
			'type' => 'array',
			'default' => []
		),
		'type' => array(
			'type' => 'string',
			'default' => 'all',
		),
		'ids' => array(
			'type' => 'array',
			'default' => []
		),
		'user_logged_in' => array(
			'type' => 'boolean',
			'default' => false
		),
		'user_roles' => array(
			'type' => 'array',
			'default' => []
		),
		'query_by' => array(
			'type' => 'string',
			'default' => 'post_type'
		),
		'not_show_for_selected' => array(
			'type' => 'boolean',
			'default' => false
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
		'url_path_field' => array(
			'type' => 'string',
			'default' => ''
		),
		'is_pagination' => array(
			'type' => 'boolean',
			'default' => false
		),
		'referal_source_field' => array(
			'type' => 'string',
			'default' => ''
		),
		'date_time_from_field' => array(
			'type' => 'string',
			'default' => ''
		),
		'date_time_to_field' => array(
			'type' => 'string',
			'default' => ''
		),
		'conditions_arr' => array(
			'type' => 'array',
			'default' => []
		),
		'type_of_condition' => array(
			'type' => 'string',
			'default' => 'and'
		),
		'name_of_cookie' => array(
			'type' => 'string',
			'default' => ''
		),
		'compare_type_cookie' => array(
			'type' => 'string',
			'default' => 'exist'
		),
		'equal_cookie' => array(
			'type' => 'string',
			'default' => ''
		),
		'woocommerce_type' => array(
			'type' => 'string',
			'default' => ''
		),
		'woocommerce_field' => array(
			'type' => 'number',
			'default' => 0
		),
		'comment_type' => array(
			'type' => 'string',
			'default' => ''
		),
		'comment_field' => array(
			'type' => 'number',
			'default' => 0
		),
		'repeaterField' => array(
			'type' => 'string',
			'default' => ''
		),
		'hasParent' => array(
			'type' => 'boolean',
			'default' => false
		),
		'hasChildren' => array(
			'type' => 'boolean',
			'default' => false
		),
		'recursive_date_from' => array(
			'type' => 'object',
			'default' => [
				"hours" => "",
				"minutes" => "",
				"day" => ""
			]
		),
		'recursive_date_to' => array(
			'type' => 'object',
			'default' => [
				"hours" => "",
				"minutes" => "",
				"day" => ""
			]
		),
	);

	public function render_block($settings = array(), $inner_content = '')
	{
		extract($settings);

		$dataSource = $settings['data_source'];

		$isVisibility = true;

		if ($query_by === 'post_type') {
			switch ($dataSource) {
				case 'ids':
					$isVisibility = $this->filter_by_post_id($settings['ids'], $settings['not_show_for_selected']);
					break;
				case 'cat':
					$isVisibility = $this->filter_by_post_cat_or_tag($settings['cat'], $settings['cat_exclude'], $settings['tag'], $settings['tag_exclude'], $settings['not_show_for_selected']);
					break;
				case 'cpt':
					$isVisibility = $this->filter_by_cpt($settings['post_type'], $settings['tax_name'], $settings['tax_slug'], $settings['tax_slug_exclude'], $settings['cat'], $settings['cat_exclude'], $settings['tag'], $settings['tag_exclude'], $settings['price_range'], $settings['type'], $settings['not_show_for_selected'], $settings['hasParent'], $settings['hasChildren']);
					break;
				default:
					break;
			}
		} else if ($query_by === 'taxonomy' && !empty($settings['tax_name'])) {
            if (isset(get_queried_object()->term_id) && get_queried_object()->taxonomy == $tax_name) {
                $terms_id = array(get_queried_object()->term_id);
            } else {
                global $post;
                $terms_id = array_column(wp_get_post_terms($post->ID, $tax_name), 'term_id');
            }
            if(empty($terms_id)) {
                $isVisibility = false;
            }else{
				$tax_include_ids = !empty($settings['tax_slug']) ? array_column($settings['tax_slug'], 'value') : [];
				$tax_exclude_ids = !empty($settings['tax_slug_exclude']) ? array_column($settings['tax_slug_exclude'], 'value') : [];

				if (!$settings['not_show_for_selected']) {
					if (
						(!empty($tax_include_ids) && !(count($terms_id) > count(array_diff($terms_id, $tax_include_ids)))) ||
						(!empty($tax_exclude_ids) && count($terms_id) > count(array_diff($terms_id, $tax_exclude_ids)))
					) {
						$isVisibility = false;
					}
				} else {
					if (
						(empty($tax_include_ids) && empty($tax_exclude_ids)) ||
						(!empty($tax_include_ids) && count($terms_id) > count(array_diff($terms_id, $tax_include_ids))) ||
						(!empty($tax_exclude_ids) && count($terms_id) === count(array_diff($terms_id, $tax_exclude_ids)))
					) {
						$isVisibility = false;
					}
				}
			}
		} else if ($query_by === 'user') {
			// filter by logged user and roles
			if (!$this->filter_by_user($user_logged_in, $user_roles, $user_id, $settings['not_show_for_selected'])) $isVisibility = false;
			if(!empty($settings['custom_field_key']) && !empty($settings['custom_field_value'])){
                if (!filter_by_custom_meta($settings['custom_field_compare'], $settings['custom_field_key'], $settings['custom_field_value'], $settings['not_show_for_selected'], $type = 'user')) $isVisibility = false;
            }
		} else if ($query_by === 'custom_meta') {
			if (!$this->filter_by_custom_meta($settings['custom_field_compare'], $settings['custom_field_key'], $settings['custom_field_value'], $settings['not_show_for_selected'])) $isVisibility = false;
		}else if ($query_by === 'repeater') {
			if (!$this->filter_by_repeater($settings['custom_field_compare'], $settings['custom_field_key'], $settings['custom_field_value'], $settings['not_show_for_selected'], $settings['repeaterArray'])) $isVisibility = false;
		} else if ($query_by === 'taxonomy_meta') {
			if (!$this->filter_by_taxonomy_meta($settings['custom_field_compare'], $settings['custom_field_key'], $settings['custom_field_value'], $settings['not_show_for_selected'])) $isVisibility = false;
		} else if ($query_by === 'url_path') {
            $condition = false;
			if($is_pagination){
                $condition = is_paged() ? false : true;
            }else if(!empty($url_path_field)){
                if (strpos($url_path_field, "REGEX") === 0){
                    $url_path_field = str_replace("REGEX", "", $url_path_field);
                    $condition = preg_match($url_path_field, $_SERVER['QUERY_STRING']) ? false : true;
                } else {
                	$condition = strpos($_SERVER['QUERY_STRING'], $url_path_field) === false ? true : false;
                }
            }
            if ($condition) {
				if ($settings['not_show_for_selected']) {
					$isVisibility = true;
				} else {
					$isVisibility = false;
				}
			} else {
				if ($settings['not_show_for_selected']) {
					$isVisibility = false;
				} else {
					$isVisibility = true;
				}
			}
		} else if ($query_by === 'referal_source') {
			if (!empty($referal_source_field) && (empty($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER'] !== $referal_source_field)) {
				if ($settings['not_show_for_selected']) {
					$isVisibility = true;
				} else {
					$isVisibility = false;
				}
			} else {
				if ($settings['not_show_for_selected']) {
					$isVisibility = false;
				} else {
					$isVisibility = true;
				}
			}
		} else if ($query_by === 'date_time') {
			$timestampCurrent = current_time('timestamp', 0);
			$timestampFrom = strtotime($date_time_from_field);
			$timestampTo = strtotime($date_time_to_field);

			if ($timestampFrom && $timestampTo && !($timestampCurrent > $timestampFrom && $timestampCurrent < $timestampTo)) {
				if ($settings['not_show_for_selected']) {
					$isVisibility = true;
				} else {
					$isVisibility = false;
				}
			} else {
				if ($settings['not_show_for_selected']) {
					$isVisibility = false;
				} else {
					$isVisibility = true;
				}
			}
		}else if ($query_by === 'post_date') {
            global $post;
            if(!is_object($post)) return '';
            $date_post = get_the_date('U', $post->ID);
            $timestampCurrent = current_time('timestamp', 0);
            
            // Parse range values
            $range_start = 0;
            $range_end = 0;
            if(strpos($custom_field_value, '-') !== false){
                $range = explode('-', $custom_field_value);
                $range_start = intval($range[0]);
                $range_end = intval($range[1]); 
            } else {
                $range_end = intval($custom_field_value);
            }

            // Convert days to seconds
            $range_start_seconds = $range_start * 24 * 60 * 60;
            $range_end_seconds = $range_end * 24 * 60 * 60;

            // Check if post date is within range of days from current date
            $days_diff = ($timestampCurrent - $date_post);
            $is_in_range = ($days_diff >= $range_start_seconds && $days_diff <= $range_end_seconds);

            if (!$is_in_range) {
                if ($not_show_for_selected) {
                    $isVisibility = true;
                } else {
                    $isVisibility = false;
                }
            } else {
                if ($not_show_for_selected) {
                    $isVisibility = false;
                } else {
                    $isVisibility = true;
                }
            }
		} else if ($query_by === 'recursive_date') {
			$currentDay = (int)date('N');
			$currentTime = current_time('H:i');
			
			$fromDay = isset($recursive_date_from['day']) ? (int)$recursive_date_from['day'] : null;
			$fromTime = sprintf('%02d:%02d', 
				isset($recursive_date_from['hours']) ? (int)$recursive_date_from['hours'] : 0,
				isset($recursive_date_from['minutes']) ? (int)$recursive_date_from['minutes'] : 0
			);
		
			$toDay = isset($recursive_date_to['day']) ? (int)$recursive_date_to['day'] : null;
			$toTime = sprintf('%02d:%02d', 
				isset($recursive_date_to['hours']) ? (int)$recursive_date_to['hours'] : 0,
				isset($recursive_date_to['minutes']) ? (int)$recursive_date_to['minutes'] : 0
			);
		
			$isVisibility = false;
		
			if ($fromDay !== null && $toDay !== null && $fromTime && $toTime) {
				if ($fromDay <= $toDay) {
					// Simple case: fromDay to toDay within the same week
					$isVisibility = ($currentDay >= $fromDay && $currentDay <= $toDay) &&
									($currentDay != $fromDay || $currentTime >= $fromTime) &&
									($currentDay != $toDay || $currentTime <= $toTime);
				} else {
					// Complex case: fromDay to toDay spans across weeks
					$isVisibility = ($currentDay >= $fromDay || $currentDay <= $toDay) &&
									($currentDay != $fromDay || $currentTime >= $fromTime) &&
									($currentDay != $toDay || $currentTime <= $toTime);
				}
			}
		
			if ($settings['not_show_for_selected']) {
				$isVisibility = !$isVisibility;
			}
		} else if ($query_by === 'mobile_view') {
			if ($settings['not_show_for_selected']) {
				if (wp_is_mobile()) {
					$isVisibility = false;
				} else {
					$isVisibility = true;
				}
			} else {
				if (wp_is_mobile()) {
					$isVisibility = true;
				} else {
					$isVisibility = false;
				}
			}
		} else if ($query_by === 'by_cookie' && !empty($settings['name_of_cookie'])) {
			if (
				($settings['compare_type_cookie'] === 'exist' && !empty($_COOKIE[$settings['name_of_cookie']])) ||
				$settings['compare_type_cookie'] === 'equal' && !empty($_COOKIE[$settings['name_of_cookie']]) && $_COOKIE[$settings['name_of_cookie']] === $settings['equal_cookie']
			) {
				if ($settings['not_show_for_selected']) {
					$isVisibility = false;
				} else {
					$isVisibility = true;
				}
			} else {
				if ($settings['not_show_for_selected']) {
					$isVisibility = true;
				} else {
					$isVisibility = false;
				}
			}
		} else if ($query_by === 'woocommerce') {
            $condition = false;
            if(class_exists('WooCommerce')) {
                if($woocommerce_type == 'related' && is_singular('product')){
                    $postid = get_the_ID();
                    $related = wc_get_related_products($postid);
                    if(!empty($related)) $condition = true;
                }else if($woocommerce_type == 'upsell' && is_singular('product')){
                    global $product;
                    if(is_object($product)){
                        $upsells = $product->get_upsell_ids();
                        if(!empty($upsells)) $condition = true;
                    }
                }else if($woocommerce_type == 'external' || $woocommerce_type == 'grouped' || $woocommerce_type == 'simple' || $woocommerce_type == 'variable'){
                    global $product;
                    if(is_object($product)){
                        if($woocommerce_type == 'external' && $product->is_type('external')) $condition = true;
						if($woocommerce_type == 'grouped' && $product->is_type('grouped')) $condition = true;
						if($woocommerce_type == 'simple' && $product->is_type('simple')) $condition = true;
						if($woocommerce_type == 'variable' && $product->is_type('variable')) $condition = true;
                    }
                }else if($woocommerce_type == 'cart_items' && !empty($woocommerce_field)){
                    global $woocommerce;
                    if(is_object($woocommerce) && $woocommerce->cart != null){
                        $value = $woocommerce->cart->get_cart_contents_count();
						if(strpos($woocommerce_field, '-') !== false){
							$woocommerce_field = explode('-', $woocommerce_field);
							$woocommerce_field = $woocommerce_field[0] <= $value && $woocommerce_field[1] >= $value;
							$condition = $woocommerce_field;
						}else{
							if($value > $woocommerce_field) $condition = true;
						}
                    }
                }else if($woocommerce_type == 'cart_zero'){
                    global $woocommerce;
                    if(is_object($woocommerce) && $woocommerce->cart != null){
                        $value = $woocommerce->cart->get_cart_contents_count();
                        if($value < 1) $condition = true;
                    }
                }else if($woocommerce_type == 'cart_total' && !empty($woocommerce_field)){
                    global $woocommerce;
                    if(is_object($woocommerce) && $woocommerce->cart != null){
                        $value = $woocommerce->cart->get_total('raw');
						if(strpos($woocommerce_field, '-') !== false){
							$woocommerce_field = explode('-', $woocommerce_field);
							$woocommerce_field = $woocommerce_field[0] <= $value && $woocommerce_field[1] >= $value;
							$condition = $woocommerce_field;
						}else{
							if($value > $woocommerce_field) $condition = true;
						}
                    }
                }else if($woocommerce_type == 'product_purchased' && !empty($woocommerce_field)){
                    if(is_user_logged_in()){
                        $current_user = wp_get_current_user();
                        if ( wc_customer_bought_product( $current_user->user_email, $current_user->ID, $woocommerce_field ) ) {
                            $condition = true;
                        }
                    }
                }else if($woocommerce_type == 'stock' && !empty($woocommerce_field)){
                    global $product;
                    if(is_object($product)){
                        $stock = $product->get_stock_quantity();
                        if($stock < $woocommerce_field) $condition = true;
                    }
                }
            }
            if ($condition) {
                if ($not_show_for_selected) {
                    $isVisibility = false;
                } else {
                    $isVisibility = true;
                }
            } else {
                if ($not_show_for_selected) {
                    $isVisibility = true;
                } else {
                    $isVisibility = false;
                }
            }
        }else if ($query_by === 'comment') {
            $condition = false;
            if($comment_type == 'opened'){
				global $post;
				if(is_object($post) && comments_open($post->ID)){
					$condition = true;
				}
			}else if($comment_type == 'number'){
				global $post;
				if(is_object($post) && get_comments_number($post->ID) && !empty($comment_field) && get_comments_number($post->ID) > $comment_field ){
					$condition = true;
				}
			}
            if ($condition) {
                if ($not_show_for_selected) {
                    $isVisibility = false;
                } else {
                    $isVisibility = true;
                }
            } else {
                if ($not_show_for_selected) {
                    $isVisibility = true;
                } else {
                    $isVisibility = false;
                }
            }
        }


		if (!empty($conditions_arr)) {
			$isVisibilityConditions = [];
			foreach ($conditions_arr as $key => $condition) {
				$isVisibilityConditions[$key] = true;

				if ($condition['query_by'] === 'taxonomy') {

                    if (isset(get_queried_object()->term_id) && get_queried_object()->taxonomy == $condition['tax_name']) {
                        $terms_id = array(get_queried_object()->term_id);
                    } else {
                        global $post;
                        $terms_id = array_column(wp_get_post_terms($post->ID, $condition['tax_name']), 'term_id');
                    }

                    if(empty($terms_id)) {
                        $isVisibilityConditions[$key] = false;
                    }else{
						$tax_include_ids = !empty($condition['tax_slug']) ? array_column($condition['tax_slug'], 'value') : [];
						$tax_exclude_ids = !empty($condition['tax_slug_exclude']) ? array_column($condition['tax_slug_exclude'], 'value') : [];
	
						if (!$settings['not_show_for_selected']) {
							if (
								(!empty($tax_include_ids) && !(count($terms_id) > count(array_diff($terms_id, $tax_include_ids)))) ||
								(!empty($tax_exclude_ids) && count($terms_id) > count(array_diff($terms_id, $tax_exclude_ids)))
							) {
								$isVisibilityConditions[$key] = false;
							}
						} else {
							if (
								(empty($tax_include_ids) && empty($tax_exclude_ids)) ||
								(!empty($tax_include_ids) && count($terms_id) > count(array_diff($terms_id, $tax_include_ids))) ||
								(!empty($tax_exclude_ids) && count($terms_id) === count(array_diff($terms_id, $tax_exclude_ids)))
							) {
								$isVisibilityConditions[$key] = false;
							}
						}
					}
				} else if ($condition['query_by'] === 'custom_meta') {

					if (!$this->filter_by_custom_meta($condition['custom_field_compare'], $condition['custom_field_key'], $condition['custom_field_value'], $settings['not_show_for_selected'])) $isVisibilityConditions[$key] = false;
				} else if ($condition['query_by'] === 'taxonomy_meta') {

					if (!$this->filter_by_taxonomy_meta($condition['custom_field_compare'], $condition['custom_field_key'], $condition['custom_field_value'], $settings['not_show_for_selected'])) $isVisibilityConditions[$key] = false;
				} else if ($condition['query_by'] === 'url_path') {

                    $url_path_field = !empty($condition['url_path_field']) ? $condition['url_path_field'] : '';
                    $condition = false;
                    if (strpos($url_path_field, "REGEX") === 0){
                        $url_path_field = str_replace("REGEX", "", $url_path_field);
						$condition = preg_match($url_path_field, $_SERVER['QUERY_STRING']) ? false : true;
                    } else {
                        $condition = strpos($_SERVER['QUERY_STRING'], $url_path_field) === false ? true : false;
                    }
                    if (!empty($url_path_field) && $condition) {
						if ($settings['not_show_for_selected']) {
							$isVisibilityConditions[$key] = true;
						} else {
							$isVisibilityConditions[$key] = false;
						}
					} else {
						if ($settings['not_show_for_selected']) {
							$isVisibilityConditions[$key] = false;
						} else {
							$isVisibilityConditions[$key] = true;
						}
					}
				} else if ($condition['query_by'] === 'referal_source') {
					if (!empty($condition['referal_source_field']) && (empty($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER'] !== $condition['referal_source_field'])) {
						if ($settings['not_show_for_selected']) {
							$isVisibilityConditions[$key] = true;
						} else {
							$isVisibilityConditions[$key] = false;
						}
					} else {
						if ($settings['not_show_for_selected']) {
							$isVisibilityConditions[$key] = false;
						} else {
							$isVisibilityConditions[$key] = true;
						}
					}
				} else if ($condition['query_by'] === 'by_cookie' && !empty($condition['name_of_cookie'])) {
					if (
						($condition['compare_type_cookie'] === 'exist' && !empty($_COOKIE[$condition['name_of_cookie']])) ||
						$condition['compare_type_cookie'] === 'equal' && !empty($_COOKIE[$condition['name_of_cookie']]) && $_COOKIE[$condition['name_of_cookie']] === $condition['equal_cookie']
					) {
						if ($settings['not_show_for_selected']) {
							$isVisibilityConditions[$key] = false;
						} else {
							$isVisibilityConditions[$key] = true;
						}
					} else {
						if ($settings['not_show_for_selected']) {
							$isVisibilityConditions[$key] = true;
						} else {
							$isVisibilityConditions[$key] = false;
						}
					}
				}
			}


			if (!$settings['not_show_for_selected']) {
				if (
					($type_of_condition === 'and' && (in_array(false, $isVisibilityConditions) || !$isVisibility)) ||
					($type_of_condition === 'or' && !(in_array(true, $isVisibilityConditions) || $isVisibility))
				) {
					$result = false;
				} else $result = true;
			} else {
				if (
					($type_of_condition === 'and' && (in_array(true, $isVisibilityConditions) || $isVisibility)) ||
					($type_of_condition === 'or' && !(in_array(false, $isVisibilityConditions) || !$isVisibility))
				) {
					$result = true;
				} else $result = false;
			}
		} else $result = $isVisibility;

		if (!$result) return;

		$out = $inner_content;

		return $out;
	}

	public function filter_by_custom_meta($compare, $key = '', $value = '', $not_show_for_selected = false, $type = 'post')
	{
		if (empty($key)) return true;

		if($type == 'post'){
			global $post;
			$post_meta = GSPB_get_custom_field_value($post->ID, $key);
		}else if($type == 'user'){
			$user_id = get_current_user_id();
			if(empty($user_id)) return false;
			$post_meta = get_user_meta($user_id, $key, true);
		}

		if (empty($post_meta) && $compare != 'exist' && $compare != 'noexist') return false;

		if (strpos($value, '|') !== false) {
			$value = explode('|', $value);
			foreach ($value as $key => $val) {
				$value[$key] = trim($val);
				if(strpos($value[$key], '{TIMESTRING:') !== false){
					$pattern = '/\{TIMESTRING:(.*?)\}/';
					preg_match($pattern, $value[$key], $matches);
					$value[$key] = $matches[1];
					$value[$key] = strtotime($value[$key]);
				}
			}
			$post_meta = strtotime($post_meta);
		}else{
			if(strpos($value, '{TIMESTRING:') !== false){
				$pattern = '/\{TIMESTRING:(.*?)\}/';
				preg_match($pattern, $value, $matches);
				$value = $matches[1];
				$value = strtotime($value);
				$post_meta = strtotime($post_meta);
			}
		}

		$result = true;

		switch ($compare) {
			case 'equal':
			case 'BETWEEN':
				if (is_array($value)) {
					$result = $post_meta > $value[0] && $post_meta < $value[1];
				} else $result = $post_meta == $value;
				break;
			case 'exist':
				$result = !empty($post_meta);
				break;
			case 'noexist':
				$result = empty($post_meta);
				break;
			case 'less':
				$result = $post_meta < $value;
				break;
			case '<=':
			case 'less_equal':
				$result = $post_meta <= $value;
				break;
			case '>=':
			case 'more_equal':
				$result = $post_meta >= $value;
				break;
			case 'more':
				$result = $post_meta > $value;
				break;
			default:
				break;
		}

		return $not_show_for_selected ? !$result : $result;
	}
	public function filter_by_repeater($compare, $key = '', $value = '', $not_show_for_selected = false, $repeaterArray = [])
	{

		if (!empty($key) && !empty($repeaterArray)){
			$post_meta = GSPB_get_value_from_array_field($key, $repeaterArray);
	
			if (empty($post_meta) && $compare != 'exist' && $compare != 'noexist') return false;
	
			if (strpos($value, '|') !== false) {
				$value = explode('|', $value);
				foreach ($value as $key => $val) {
					$value[$key] = trim($val);
					if(strpos($value[$key], '{TIMESTRING:') !== false){
						$pattern = '/\{TIMESTRING:(.*?)\}/';
						preg_match($pattern, $value[$key], $matches);
						$value[$key] = $matches[1];
						$value[$key] = strtotime($value[$key]);
					}
				}
				$post_meta = strtotime($post_meta);
			}else{
				if(strpos($value, '{TIMESTRING:') !== false){
					$pattern = '/\{TIMESTRING:(.*?)\}/';
					preg_match($pattern, $value, $matches);
					$value = $matches[1];
					$value = strtotime($value);
					$post_meta = strtotime($post_meta);
				}
			}
	
			$result = true;
	
			switch ($compare) {
				case 'equal':
				case 'BETWEEN':
					if (is_array($value)) {
						$result = $post_meta > $value[0] && $post_meta < $value[1];
					} else $result = $post_meta == $value;
					break;
				case 'exist':
					$result = !empty($post_meta);
					break;
				case 'noexist':
					$result = empty($post_meta);
					break;
				case 'less':
					$result = $post_meta < $value;
					break;
				case '<=':
				case 'less_equal':
					$result = $post_meta <= $value;
					break;
				case '>=':
				case 'more_equal':
					$result = $post_meta >= $value;
					break;
				case 'more':
					$result = $post_meta > $value;
					break;
				default:
					break;
			}
	
			return $not_show_for_selected ? !$result : $result;
		}else{
			return true;
		}

	}
	public function filter_by_taxonomy_meta($compare, $key = '', $value = '', $not_show_for_selected = false)
	{
		if (empty($key)) return true;
		if (is_tax() || is_category() || is_tag()) {
			$post_meta = get_term_meta(get_queried_object_id(), $key, true);
		} else {
			return false;
		}

		if (empty($post_meta) && $compare != 'exist' && $compare != 'noexist') return false;

		$result = true;

		switch ($compare) {
			case 'equal':
				$result = $post_meta == $value;
				break;
			case 'exist':
				$result = !empty($post_meta);
				break;
			case 'noexist':
				$result = empty($post_meta);
				break;
			case 'less':
				$result = $post_meta < $value;
				break;
			case 'more':
				$result = $post_meta > $value;
				break;
			default:
				break;
		}

		return $not_show_for_selected ? !$result : $result;
	}

	public function filter_by_cpt($post_type, $tax_name, $tax_slug = [], $tax_slug_exclude = [], $cat = [], $cat_exclude = [], $tag = [], $tag_exclude = [], $price_range = '', $type = 'all', $not_show_for_selected = false, $hasParent = false, $hasChildren = false)
	{
		$postId = get_the_ID();

		if (!$not_show_for_selected && $post_type != get_post_type($postId)) return false;
		if ($not_show_for_selected && $post_type === get_post_type($postId) && !(!empty($tax_name) && (!empty($tax_slug) || !empty($tax_slug_exclude)))) return false;
		if ($hasParent && !$not_show_for_selected && !wp_get_post_parent_id($postId)) return false;
		if ($hasParent && $not_show_for_selected && wp_get_post_parent_id($postId)) return false;
		if ($hasChildren && !$not_show_for_selected && count(get_children(array('post_parent' => $postId))) === 0) return false;
		if ($hasChildren && $not_show_for_selected && count(get_children(array('post_parent' => $postId))) > 0) return false;
		if ($not_show_for_selected && $post_type === get_post_type($postId) && $post_type === 'product' && !(!empty($cat) || !empty($cat_exclude) || !empty($tax_slug) || !empty($tax_slug_exclude) || !empty($price_range) || $type !== 'all')) return false;

		$result = true;
		if (!empty($tax_name) && (!empty($tax_slug) || !empty($tax_slug_exclude))) {
			$post_terms = wp_get_post_terms($postId, $tax_name, array("fields" => "ids"));

			if (!empty($tax_slug)) {
				$ids = array_column($tax_slug, 'value');

				$post_in_cat = array_intersect($post_terms, $ids);

				if ($not_show_for_selected && !empty($post_in_cat)) $result = false;
				if (!$not_show_for_selected && !array_filter($post_in_cat)) $result = false;
			}

			if (!empty($tax_slug_exclude)) {
				$ids = array_column($tax_slug_exclude, 'value');

				$post_in_cat = array_intersect($post_terms, $ids);

				if ($not_show_for_selected && !empty($post_in_cat)) $result = true;
				if (!$not_show_for_selected && array_filter($post_in_cat)) $result = false;
			}
		}
		if ($post_type !== 'product') {
		} else {
			$_product = wc_get_product($postId);
			$post_terms = wp_get_post_terms($postId, 'product_cat', array("fields" => "ids"));
			$post_tags = wp_get_post_terms($postId, 'product_tag', array("fields" => "ids"));

			if (!empty($cat)) {
				$ids = array_column($cat, 'id');

				$post_in_cat = array_intersect($post_terms, $ids);
				if ($not_show_for_selected && !empty($post_in_cat)) $result = false;
				if (!$not_show_for_selected && !array_filter($post_in_cat)) $result = false;
			}

			if (!empty($cat_exclude)) {
				$ids = array_column($cat_exclude, 'id');

				$post_in_cat = array_intersect($post_terms, $ids);
				if ($not_show_for_selected && !empty($post_in_cat)) $result = true;
				if (!$not_show_for_selected && array_filter($post_in_cat)) $result = false;
			}

			if (!empty($tag)) {
				$ids = array_column($tag, 'id');

				$post_in_cat = array_intersect($post_tags, $ids);
				if ($not_show_for_selected && !empty($post_in_cat)) $result = false;
				if (!$not_show_for_selected && !array_filter($post_in_cat)) $result = false;
			}

			if (!empty($tag_exclude)) {
				$ids = array_column($tag_exclude, 'id');

				$post_in_cat = array_intersect($post_tags, $ids);
				if ($not_show_for_selected && !empty($post_in_cat)) $result = true;
				if (!$not_show_for_selected && array_filter($post_in_cat)) $result = false;
			}


			if (!empty($price_range)) {
				$price_range_array = array_map('trim', explode("-", $price_range));
				$_product_price = (int) $_product->get_price();

				if (!$not_show_for_selected && ($_product_price < (int) $price_range_array[0] || $_product_price > (int) $price_range_array[1])) $result = false;
				if ($not_show_for_selected && !($_product_price < (int) $price_range_array[0] || $_product_price > (int) $price_range_array[1])) $result = false;
			}

			if ($type === 'featured') {
				$post_visibility = wp_get_post_terms($postId, 'product_visibility', array('fields' => 'names'));
				if (!$not_show_for_selected && !in_array($type, $post_visibility)) $result = false;
				if ($not_show_for_selected && in_array($type, $post_visibility)) $result = false;
			} else if ($type === 'sale') {
				$product_ids_on_sale = wc_get_product_ids_on_sale();
				if (!$not_show_for_selected && !in_array($postId, $product_ids_on_sale)) $result = false;
				if ($not_show_for_selected && in_array($postId, $product_ids_on_sale)) $result = false;
			} else if ($type === 'recentviews') {
				$viewed_products = !empty($_COOKIE['woocommerce_recently_viewed']) ? (array) explode('|', $_COOKIE['woocommerce_recently_viewed']) : array();
				$viewed_products = array_reverse(array_filter(array_map('absint', $viewed_products)));

				if (!$not_show_for_selected && !in_array($postId, $viewed_products)) $result = false;
				if ($not_show_for_selected && in_array($postId, $viewed_products)) $result = false;
			} elseif ($type === 'saled') {
				if (!$not_show_for_selected && $_product->get_total_sales() === 0) $result = false;
				if ($not_show_for_selected && $_product->get_total_sales() !== 0) $result = false;
			}
		}

		return $result;
	}

	public function filter_by_post_cat_or_tag($cat = [], $cat_exclude = [], $tag = [], $tag_exclude = [], $not_show_for_selected = false)
	{
		$postid = get_the_ID();
		$post_terms = wp_get_post_terms($postid, 'category', array("fields" => "ids"));
		$post_tags = wp_get_post_terms($postid, 'post_tag', array("fields" => "ids"));

		$result = true;

		if (!empty($cat)) {
			$ids = array_column($cat, 'id');

			$post_in_cat = array_intersect($post_terms, $ids);

			if ($not_show_for_selected && !empty($post_in_cat)) $result = false;
			if (!$not_show_for_selected && !array_filter($post_in_cat)) $result = false;
		}

		if (!empty($cat_exclude)) {
			$ids = array_column($cat_exclude, 'id');

			$post_in_cat = array_intersect($post_terms, $ids);

			if ($not_show_for_selected && !empty($post_in_cat)) $result = true;
			if (!$not_show_for_selected && array_filter($post_in_cat)) $result = false;
		}

		if (!empty($tag)) {
			$ids = array_column($tag, 'id');

			$post_in_cat = array_intersect($post_tags, $ids);
			if ($not_show_for_selected && !empty($post_in_cat)) $result = false;
			if (!$not_show_for_selected && !array_filter($post_in_cat)) $result = false;
		}

		if (!empty($tag_exclude)) {
			$ids = array_column($tag_exclude, 'id');

			$post_in_cat = array_intersect($post_tags, $ids);
			if ($not_show_for_selected && !empty($post_in_cat)) $result = true;
			if (!$not_show_for_selected && array_filter($post_in_cat)) $result = false;
		}

		return $result;
	}

	public function filter_by_post_id($ids = [], $not_show_for_selected = false)
	{
		if (empty($ids)) return true;

		foreach ($ids as $id) {
			if ($not_show_for_selected && (is_single($id['id']) || is_page($id['id']))) return false;
			if (is_single($id['id']) || is_page($id['id'])) return true;
		}

		return $not_show_for_selected;
	}

	public function filter_by_user($user_must_logged, $allowed_roles = [], $user_id = [], $not_show_for_selected = false)
	{
		$userid = get_current_user_id();

		$user = get_userdata($userid);

		if (count($allowed_roles) > 0) {
			if (!empty($user)) {
				$user_has_role = false;
				foreach ($allowed_roles as $allowed_role) {
					if (in_array($allowed_role['value'], (array) $user->roles)) $user_has_role = true;
				}

				if (!$user_has_role && !$not_show_for_selected) return false;
				if ($user_has_role && $not_show_for_selected) return false;
			} else {
				if (!$not_show_for_selected) return false;
			}
		}

		if (!empty($user_id) && !in_array($userid, array_column($user_id, 'id')) && !$not_show_for_selected) return false;
		if (!empty($user_id) && in_array($userid, array_column($user_id, 'id')) && $not_show_for_selected) return false;

		if ($user_must_logged && !$userid && !$not_show_for_selected) return false;
		if ($user_must_logged && $userid && !count($allowed_roles) && $not_show_for_selected) return false;

		return true;
	}
}

new VisibilityBlock;
