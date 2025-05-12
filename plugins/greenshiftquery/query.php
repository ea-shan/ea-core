<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

add_action('wp_ajax_gspb_filterpost', 'ajax_action_gspb_filterpost');
add_action('wp_ajax_nopriv_gspb_filterpost', 'ajax_action_gspb_filterpost');

add_action('wp_ajax_gssal_taxonomies_list', 'gssal_taxonomies_list');
add_action('wp_ajax_gssal_taxonomy_terms', 'gssal_taxonomy_terms');
add_action('wp_ajax_gssal_multi_taxonomy_terms', 'gssal_multi_taxonomy_terms');
add_action('wp_ajax_gssal_taxonomy_terms_search', 'gssal_taxonomy_terms_search');
add_action('wp_ajax_gssal_products_title_list', 'gssal_products_title_list');
add_action('wp_ajax_gssal_post_type_el', 'gssal_post_type_el');


//////////////////////////////////////////////////////////////////
// Sanitize multi array
//////////////////////////////////////////////////////////////////
function greenshiftquery_sanitize_multi_array($data)
{
	foreach ($data as $key => $value) {
		if (is_array($value)) {
			$data[$key] = greenshiftquery_sanitize_multi_array($value);
		} else {
			$val = sanitize_text_field($value);
			$data[$key] = $val;
		}
	}
	return $data;
}

function greenshiftquery_dynamic_placeholders_array($data, $extra_data = [], $runindex = 0)
{
	if(!is_array($data)) return $data;
	foreach ($data as $key => $value) {
		if (is_array($value)) {
			$data[$key] = greenshiftquery_dynamic_placeholders_array($value);
		} else {
			$val = sanitize_text_field($value);
			if(function_exists('greenshift_dynamic_placeholders')){
				$val = greenshift_dynamic_placeholders($val, $extra_data, $runindex);
			}
			$data[$key] = $val;
		}
	}
	return $data;
}

function gsSearchInlineCssStyles($array) {
    $result = '';
    foreach($array as $key => $value) {
        if($key === 'inlineCssStyles') {
			$dynamic_style = wp_kses_post($value);
			$dynamic_style = gspb_get_final_css($dynamic_style);
			$dynamic_style = gspb_quick_minify_css($dynamic_style);
			$dynamic_style = htmlspecialchars_decode($dynamic_style);
            $result .= $dynamic_style;
        } else if(is_array($value)) {
            $result .= gsSearchInlineCssStyles($value);
        }
    }
    return $result;
}

function ajax_action_gspb_filterpost()
{
	check_ajax_referer('filterpanel', 'security');
	$args = (!empty($_POST['filterargs'])) ? gspb_sanitize_multi_arrays(json_decode(stripslashes($_POST['filterargs']), true)) : array();
	$innerargs = (!empty($_POST['innerargs'])) ? json_decode(stripslashes($_POST['innerargs']), true) : array();
	$blockinstance = (!empty($_POST['blockinstance'])) ? json_decode(stripslashes($_POST['blockinstance']), true) : array();
	$offset = (!empty($_POST['offset'])) ? intval($_POST['offset']) : 0;
	$template = (!empty($_POST['template'])) ? sanitize_text_field($_POST['template']) : '';
	$sorttype = (!empty($_POST['sorttype'])) ? gspb_sanitize_multi_arrays(json_decode(stripslashes($_POST['sorttype']), true)) : '';
	$tax = (!empty($_POST['tax'])) ? gspb_sanitize_multi_arrays(json_decode(stripslashes($_POST['tax']), true)) : '';
	$containerid = (!empty($_POST['containerid'])) ? sanitize_text_field( $_POST['containerid'] ) : '';
	if ($template == '') return;
	$response = $page_sorting = '';

	if ($offset != '') {
		$args['offset'] = $offset;
	}
	$offsetnext = (!empty($args['posts_per_page'])) ? (int)$offset + $args['posts_per_page'] : (int)$offset + 12;
	$perpage = (!empty($args['posts_per_page'])) ? $args['posts_per_page'] : 12;
	$args['no_found_rows'] = true;
	$args['post_status'] = 'publish';

	if (!empty($sorttype) && is_array($sorttype)) { //if sorting panel  
		$filtertype = $filtermetakey = $filtertaxkey = $filtertaxtermslug = $filterorder = $filterdate = $filterorderby = $filterpricerange = $filtertaxcondition = '';
		$page_sorting = ' data-sorttype=\'' . json_encode($sorttype) . '\'';
		extract($sorttype);
		if ($filterorderby) {
			$args['orderby'] = $filterorderby;
		}
		if (!empty($filtertype) && $filtertype == 'comment') {
			$args['orderby'] = 'comment_count';
		}
		if ($filtertype == 'meta' && !empty($filtermetakey)) { //if meta key sorting
			if (!empty($args['meta_value'])) {
				$args['meta_query'] = array(array(
					'key' => $args['meta_key'],
					'value' => $args['meta_value'],
					'compare' => '=',
				));
				unset($args['meta_value']);
			}
			$args['orderby'] = 'meta_value_num date';
			$args['meta_key'] = esc_html($filtermetakey);
		}
		if ($filtertype == 'pricerange' && !empty($filterpricerange)) { //if meta key sorting
			$price_range_array = array_map('trim', explode("-", $filterpricerange));
			$keymeta = (!empty($args['post_type']) && $args['post_type'] == 'product') ? '_price' : 'rehub_main_product_price';
			$args['meta_query'][] = array(
				'key'     => $keymeta,
				'value'   => $price_range_array,
				'type'    => 'numeric',
				'compare' => 'BETWEEN',
			);
			if ($filterorderby == 'view' || $filterorderby == 'thumb' || $filterorderby == 'discount' || $filterorderby == 'price') {
				$args['orderby'] = 'meta_value_num';
			}
			if ($filterorderby == 'view') {
				$args['meta_key'] = 'rehub_views';
			}
			if ($filterorderby == 'thumb') {
				$args['meta_key'] = 'post_hot_count';
			}
			if ($filterorderby == 'wish') {
				$args['meta_key'] = 'post_wish_count';
			}
			if ($filterorderby == 'discount') {
				$args['meta_key'] = '_rehub_offer_discount';
			}
			if ($filterorderby == 'price') {
				$args['meta_key'] = $keymeta;
			}
		}
		if ($filtertype == 'tax' && !empty($filtertaxkey) && !empty($filtertaxtermslug)) { //if taxonomy sorting
			if (!empty($args['tax_query']) && !$filtertaxcondition) {
				unset($args['tax_query']);
			}
			if (is_array($filtertaxtermslug)) {
				$filtertaxtermslugarray = $filtertaxtermslug;
			} else {
				$filtertaxtermslugarray = array_map('trim', explode(",", $filtertaxtermslug));
			}
			if ($filtertaxcondition) {
				$args['tax_query'][] = array(
					'taxonomy' => $filtertaxkey,
					'field'    => 'slug',
					'terms'    => $filtertaxtermslugarray,
				);
			} else {
				$args['tax_query'] = array(
					array(
						'taxonomy' => $filtertaxkey,
						'field'    => 'slug',
						'terms'    => $filtertaxtermslugarray,
					)
				);
			}
		}
		if ($tax && $filtertype != 'tax') {
			$args['tax_query'] = array(
				array(
					'taxonomy' => $tax['filtertaxkey'],
					'field'    => 'slug',
					'terms'    => $tax['filtertaxtermslug'],
				)
			);
		}
		if ($filterorder) {
			$args['order'] = $filterorder;
		}
		if ($filterdate) { //if date sorting
			if (!empty($args['date_query']) || $filterdate == 'all') {
				if (isset($args['date_query'])) {
					unset($args['date_query']);
				}
			}
			if ($filterdate == 'day') {
				$args['date_query'][] = array(
					'after'  => '1 day ago',
				);
			}
			if ($filterdate == 'week') {
				$args['date_query'][] = array(
					'after'  => '7 days ago',
				);
			}
			if ($filterdate == 'month') {
				$args['date_query'][] = array(
					'after'  => '1 month ago',
				);
			}
			if ($filterdate == 'year') {
				$args['date_query'][] = array(
					'after'  => '1 year ago',
				);
			}
		}
		if ($args['post_type'] == 'product') {
			$args['tax_query'][] = array(
				'relation' => 'AND',
				array(
					'taxonomy' => 'product_visibility',
					'field'    => 'name',
					'terms'    => 'exclude-from-catalog',
					'operator' => 'NOT IN',
				)
			);
		}
	} else { // if infinite scroll

	}

	$wp_query = new \WP_Query($args);
	$i = 1 + $offset;
	$count = 1;

	$inlinestyle = !empty($blockinstance) ? gsSearchInlineCssStyles($blockinstance) : '';


	if ($wp_query->have_posts()) {
		ob_start();
		if($inlinestyle){
			echo '<style scoped>'.$inlinestyle.'</style>';
		}
		while ($wp_query->have_posts()) {
			$wp_query->the_post();
			if (!empty($innerargs)) {
				extract($innerargs);
			}
			include(GREENSHIFTQUERY_DIR_PATH . 'parts/' . $template . '.php');
			$count++;
			$i++;
		}
		wp_reset_query();
		$response .= ob_get_clean();
		if ($offset === 0 && $count >= $perpage){
		     $response .='<div class="gspb_ajax_pagination"><span data-offset="'.$offsetnext.'" data-containerid="'.$containerid.'"'.$page_sorting.' class="gspb_ajax_pagination_btn"></span></div>';
		} 
	} else {
		$response .= '<div class="clearfix flexbasisclear gcnomoreclass"><span class="no_more_posts"><span></div>';
	}

	wp_send_json_success($response);
	exit;
}
function gssal_products_title_list()
{
	global $wpdb;

	//$post_types = get_post_types( array('public'   => true) );
	//$placeholdersformat = array_fill(0, count( $post_types ), '%s');
	//$postformat = implode(", ", $placeholdersformat);

	$post_types = get_post_types( array('public' => true) );
	unset( $post_types['attachment'] );
	unset( $post_types['nav_menu_item'] );
	$post_type_names = [];
	foreach ( $post_types as $post_type ) {
	$post_type_names[] = "'" . $post_type . "'";
	}
	$postformat = implode(', ', $post_type_names);

	$where = "WHERE post_type IN (".$postformat.")";
	if (!empty($_POST['post_type'])) {
		$where = "WHERE post_type IN ('" . sanitize_text_field($_POST['post_type']) . "')";
	}

	$query = [
		"select" => "SELECT SQL_CALC_FOUND_ROWS ID, post_title FROM {$wpdb->posts}",
		"where"  => $where,
		"like"   => "AND post_title NOT LIKE %s",
		"offset" => "LIMIT %d, %d"
	];

	$search_term = '';
	if (!empty($_POST['search'])) {
		$search_term = $wpdb->esc_like($_POST['search']) . '%';
		$query['like'] = 'AND post_title LIKE %s';
	}

	$offset = 0;
	$search_limit = 100;
	if (isset($_POST['page']) && intval($_POST['page']) && $_POST['page'] > 1) {
		$offset = $search_limit * absint($_POST['page']);
	}

	$final_query = $wpdb->prepare(implode(' ', $query), $search_term, $offset, $search_limit);
	// Return saved values

	if (!empty($_POST['saved']) && is_array($_POST['saved'])) {
		$saved_ids = greenshiftquery_sanitize_multi_array($_POST['saved']);
		$placeholders = array_fill(0, count($saved_ids), '%d');
		$format = implode(', ', $placeholders);

		$new_query = [
			"select" => $query['select'],
			"where"  => $query['where'],
			"id"     => "AND ID IN( $format )",
			"order"  => "ORDER BY field(ID, " . implode(",", $saved_ids) . ")"
		];

		$final_query = $wpdb->prepare(implode(" ", $new_query), $saved_ids);
	}

	$results = $wpdb->get_results($final_query);
	$total_results = $wpdb->get_row("SELECT FOUND_ROWS() as total_rows;");
	$response_data = [
		'results'       => [],
		'total_count'   => $total_results->total_rows
	];

	if ($results) {
		foreach ($results as $result) {
			$response_data['results'][] = [
				'value'    => $result->ID,
				'id'    => $result->ID,
				'label'  => esc_html($result->post_title)
			];
		}
	}

	wp_send_json_success($response_data);
}
function gssal_post_type_el()
{
	$post_types = get_post_types(array('public' => true));
	$post_types_list = array();
	foreach ($post_types as $post_type) {
		if ($post_type !== 'revision' && $post_type !== 'nav_menu_item' && $post_type !== 'attachment') {
			$post_types_list[] = array(
				'label' => $post_type,
				'value' => $post_type
			);
		}
	}
	wp_send_json_success($post_types_list);
}
function gssal_taxonomies_list()
{
	$exclude_list = array_flip([
		'nav_menu', 'link_category', 'post_format',
		'elementor_library_type', 'elementor_library_category', 'action-group'
	]);
	$response_data = [
		'results' => []
	];
	$args = [];
	foreach (get_taxonomies($args, 'objects') as $taxonomy => $object) {
		if (isset($exclude_list[$taxonomy])) {
			continue;
		}

		$taxonomy = esc_html($taxonomy);
		$response_data['results'][] = [
			'value'    => $taxonomy,
			'label'  => esc_html($object->label),
		];
	}
	wp_send_json_success($response_data);
}
function gssal_taxonomy_terms()
{
	$response_data = [
		'results' => []
	];

	if (empty($_POST['taxonomy'])) {
		wp_send_json_success($response_data);
	}

	$taxonomy = sanitize_text_field($_POST['taxonomy']);
	$selected = isset($_POST['selected']) ? $_POST['selected'] : '';
	$terms = get_terms([
		'taxonomy'   => $taxonomy,
		'hide_empty' => false,
		'number' => 15,
		'exclude' => $selected
	]);

	foreach ($terms as $term) {
		$response_data['results'][] = [
			'id'        => $term->slug,
			'label'     => esc_html($term->name),
			'value'     => $term->term_id
		];
	}

	wp_send_json_success($response_data);
}
function gssal_taxonomy_terms_search()
{
	global $wpdb;
	$taxonomy = isset($_POST['taxonomy']) ? sanitize_text_field($_POST['taxonomy']) : '';
	$query = [
		"select" => "SELECT SQL_CALC_FOUND_ROWS a.term_id AS id, b.name as name, b.slug AS slug
                    FROM {$wpdb->term_taxonomy} AS a
                    INNER JOIN {$wpdb->terms} AS b ON b.term_id = a.term_id",
		"where"  => "WHERE a.taxonomy = '{$taxonomy}'",
		"like"   => "AND (b.slug LIKE '%s' OR b.name LIKE '%s' )",
		"offset" => "LIMIT %d, %d"
	];

	$search_term = '%' . $wpdb->esc_like($_POST['search']) . '%';
	$offset = 0;
	$search_limit = 100;

	$final_query = $wpdb->prepare(implode(' ', $query), $search_term, $search_term, $offset, $search_limit);
	// Return saved values

	$results = $wpdb->get_results($final_query);

	$total_results = $wpdb->get_row("SELECT FOUND_ROWS() as total_rows;");
	$response_data = [
		'results'       => [],
	];

	if ($results) {
		foreach ($results as $result) {
			$response_data['results'][] = [
				'id'        => esc_html($result->slug),
				'label'     => esc_html($result->name),
				'value'     => (int)$result->id
			];
		}
	}

	wp_send_json_success($response_data);
}

//////////////////////////////////////////////////////////////////
// Sanitize Arrays
//////////////////////////////////////////////////////////////////
function gspb_sanitize_multi_arrays($data = array())
{
	if (!is_array($data) || empty($data)) {
		return array();
	}
	foreach ($data as $k => $v) {
		if (!is_array($v) && !is_object($v)) {
			if ($k == 'contshortcode') {
				$data[sanitize_key($k)] = wp_kses_post($v);
			} elseif ($k == 'attrelpanel') {
				$data[sanitize_key($k)] = filter_var($v, FILTER_SANITIZE_SPECIAL_CHARS);
			} else {
				$data[sanitize_key($k)] = sanitize_text_field($v);
			}
		}
		if (is_array($v)) {
			$data[$k] = gspb_sanitize_multi_arrays($v);
		}
	}
	return $data;
}

//////////////////////////////////////////////////////////////////
// Function for extract args from blocks
//////////////////////////////////////////////////////////////////
if (!class_exists('GSPB_Postfilters')) {
	class GSPB_Postfilters
	{
		public $filter_args = array(
			'data_source' => 'cat',
			'cat' => '',
			'cat_name' => '',
			'tag' => '',
			'cat_exclude' => '',
			'tag_exclude' => '',
			'ids' => '',
			'orderby' => '',
			'order ' => 'DESC',
			'meta_key' => '',
			'show' => 12,
			'offset' => '',
			'show_date' => '',
			'post_type' => '',
			'tax_name' => '',
			'tax_slug' => '',
			'type' => '',
			'tax_slug_exclude' => '',
			'post_formats' => '',
			'badge_label ' => '1',
			'enable_pagination' => '',
			'filter_enable_pagination' => '',
			'price_range' => '',
			'show_coupons_only' => '',
			'user_id' => '',
			'searchtitle' => '',
			'addition_field'=> '',
			'additional_type' => '',
			'enableSearchFilters' => '',
			'searchQueryId' => '',

		);
		function __construct($filter_args = array())
		{
			$this->set_opt($filter_args);
			return $this;
		}
		function set_opt($filter_args = array())
		{
			$this->filter_args = (object) array_merge($this->filter_args, (array) $filter_args);
		}
		public function extract_filters()
		{

			$filter_args = &$this->filter_args;

			if ($filter_args->data_source == 'ids' && $filter_args->ids != '') {
				$ids = array_map('trim', explode(",", $filter_args->ids));
				$args = array(
					'post__in' => $ids,
					'numberposts' => '-1',
					'orderby' => 'post__in',
					'ignore_sticky_posts' => 1,
					'post_type' => 'any',
					'no_found_rows' => 1,
					'posts_per_page' => '-1'
				);
			} elseif ($filter_args->data_source == 'cpt') {
				$args = array(
					'post_type' => $filter_args->post_type,
					'posts_per_page'   => (int)$filter_args->show,
					'order' => $filter_args->order,
					'post_status' => 'publish',
				);
				if ($filter_args->post_type == 'product') {
					if ($filter_args->cat != '') {
						$cat = array_map('trim', explode(",", $filter_args->cat));
						$args['tax_query'][] = array(
							'relation' => 'AND',
							array(
								'taxonomy' => 'product_cat',
								'field'    => 'term_id',
								'terms'    => $cat,
							)
						);
					}
					if ($filter_args->cat_exclude != '') {
						$cat_exclude = array_map('trim', explode(",", $filter_args->cat_exclude));
						$args['tax_query'][] = array(
							'relation' => 'AND',
							array(
								'taxonomy' => 'product_cat',
								'field'    => 'term_id',
								'terms'    => $cat_exclude,
								'operator' => 'NOT IN'
							)
						);
					}
					if ($filter_args->tag != '') {
						$tag = array_map('trim', explode(",", $filter_args->tag));
						$args['tax_query'][] = array(
							'relation' => 'AND',
							array(
								'taxonomy' => 'product_tag',
								'field'    => 'term_id',
								'terms'    => $tag,
							)
						);
					}
					if ($filter_args->tag_exclude != '') {
						$tag_exclude = array_map('trim', explode(",", $filter_args->tag_exclude));
						$args['tax_query'][] = array(
							'relation' => 'AND',
							array(
								'taxonomy' => 'product_tag',
								'field'    => 'term_id',
								'terms'    => $tag_exclude,
								'operator' => 'NOT IN'
							)
						);
					}
					if ($filter_args->type != '') {

						if ($filter_args->type == 'featured') {
							$args['tax_query'][] = array(
								'relation' => 'AND',
								array(
									'taxonomy' => 'product_visibility',
									'field'    => 'name',
									'terms'    => 'featured',
									'operator' => 'IN',
								)
							);
						} elseif ($filter_args->type == 'sale') {
							$product_ids_on_sale = wc_get_product_ids_on_sale();
							$args['post__in'] = array_merge(array(0), $product_ids_on_sale);
						} elseif ($filter_args->type == 'related') {
							if (is_singular('product')) {
								global $product;
								$product_ids = wc_get_related_products($product->get_id(), 10);
								$args['post__in'] = array_merge(array(0), $product_ids);
								$args['no_found_rows'] = 1;
							}
						}elseif ($filter_args->type == 'upsell') {
							if (is_singular('product')) {
								global $product;
								$upsells = $product->get_upsell_ids();
								$args['post__in'] = array_merge(array(0), $upsells);
								$args['no_found_rows'] = 1;
							}
						} elseif ($filter_args->type == 'recentviews') {
							$viewed_products = !empty($_COOKIE['woocommerce_recently_viewed']) ? (array) explode('|', $_COOKIE['woocommerce_recently_viewed']) : array();
							$viewed_products = array_reverse(array_filter(array_map('absint', $viewed_products)));
							$args['post__in'] = $viewed_products;
							$args['no_found_rows'] = 1;
						} elseif ($filter_args->type == 'saled') {
							$args['meta_query'][] = array(
								'key'     => 'total_sales',
								'value'   => '0',
								'compare' => '!=',
							);
						}
					}
					$args['tax_query'][] = array(
						'relation' => 'AND',
						array(
							'taxonomy' => 'product_visibility',
							'field'    => 'name',
							'terms'    => 'exclude-from-catalog',
							'operator' => 'NOT IN',
						)
					);

					if ('yes' === get_option('woocommerce_hide_out_of_stock_items')) {
						$args['tax_query'][] = array(
							'relation' => 'AND',
							array(
								'taxonomy' => 'product_visibility',
								'field'    => 'name',
								'terms'    => 'outofstock',
								'operator' => 'NOT IN',
							)
						);
					}
				}
			} elseif ($filter_args->data_source == 'auto' || $filter_args->data_source == 'autoshop') {
				$args = array(
					'posts_per_page'   => (int)$filter_args->show,
					'order' => $filter_args->order,
					'post_type' => $filter_args->post_type,
					'post_status' => 'publish',
				);
				if ($filter_args->enable_pagination == '') {
					$filter_args->enable_pagination = '1';
				}
				if (is_category()) {
					$args['post_type'] = 'post';
					$catid = get_query_var('cat');
					$args['cat'] = $catid;
				} elseif (is_tag()) {
					$args['post_type'] = 'post';
					$tagid = get_queried_object_id();
					$args['tax_query'] = array(
						array(
							'taxonomy' => 'post_tag',
							'field'    => 'id',
							'terms'    => array($tagid),
						)
					);
				} elseif (is_tax()) {
					if (is_tax('blog_category')) {
						$args['post_type'] = 'blog';
						$tagid = get_queried_object_id();
						$args['tax_query'] = array(
							array(
								'taxonomy' => 'blog_category',
								'field'    => 'id',
								'terms'    => array($tagid),
							)
						);
					} elseif (is_tax('blog_tag')) {
						$args['post_type'] = 'blog';
						$tagid = get_queried_object_id();
						$args['tax_query'] = array(
							array(
								'taxonomy' => 'blog_tag',
								'field'    => 'id',
								'terms'    => array($tagid),
							)
						);
					} elseif (is_tax('dealstore')) {
						$args['post_type'] = 'post';
						$tagid = get_queried_object_id();
						$args['tax_query'] = array(
							array(
								'taxonomy' => 'dealstore',
								'field'    => 'id',
								'terms'    => array($tagid),
							)
						);
					} elseif (is_tax('store')) {
						$args['post_type'] = 'product';
						$tagid = get_queried_object_id();
						$args['tax_query'] = array(
							array(
								'taxonomy' => 'store',
								'field'    => 'id',
								'terms'    => array($tagid),
							)
						);
					} elseif (is_tax('product_cat')) {
						$args['post_type'] = 'product';
						$tagid = get_queried_object_id();
						$args['tax_query'] = array(
							array(
								'taxonomy' => 'product_cat',
								'field'    => 'id',
								'terms'    => array($tagid),
							)
						);
					} elseif (is_tax('product_tag')) {
						$args['post_type'] = 'product';
						$tagid = get_queried_object_id();
						$args['tax_query'] = array(
							array(
								'taxonomy' => 'product_tag',
								'field'    => 'id',
								'terms'    => array($tagid),
							)
						);
					} else {
						$tag = get_queried_object();;
						$args['tax_query'] = array(
							array(
								'taxonomy' => $tag->taxonomy,
								'field'    => 'id',
								'terms'    => array($tag->term_id),
							)
						);
					}
				} elseif (is_search()) {
					$args['post_type'] = 'any';
					$searchid = get_search_query();
					$args['s'] = esc_attr($searchid);
				} elseif (is_post_type_archive()) {
					$type = get_query_var('post_type');
					$args['post_type'] = array($type);
				} elseif (is_date()) {
					if (is_day()) {
						$args['date_query'] = array(
							array(
								'year'  => get_query_var('year'),
								'month' => get_query_var('monthnum'),
								'day'   => get_query_var('day'),
							)
						);
					} elseif (is_month()) {
						$args['date_query'] = array(
							array(
								'year'  => get_query_var('year'),
								'month' => get_query_var('monthnum'),
							)
						);
					} elseif (is_year()) {
						$args['date_query'] = array(
							array(
								'year'  => get_query_var('year'),
							)
						);
					}
				} elseif (is_author()) {
					$args['post_type'] = array('post', 'blog', 'product');
					$curauth = (get_query_var('author_name')) ? get_user_by('slug', get_query_var('author_name')) : get_userdata(get_query_var('author'));
					$author_ID = $curauth->ID;
					$args['author'] = (int)$author_ID;
				} else if (!empty($_GET['wishlistids'])) {
					$wishlistids = explode(',', esc_html($_GET['wishlistids']));
					$args['post__in'] = $wishlistids;
					$args['orderby'] = 'post__in';
					$args['ignore_sticky_posts'] = 1;
					$args['post_type'] = 'any';
					$args['posts_per_page'] = (int)$filter_args->show;
				}
				if (class_exists('WooCommerce')) {
					if ($filter_args->data_source == 'autoshop') {
						$args['post_type'] = 'product';
					}
					if (is_shop() || is_product_taxonomy()) {
						$args['post_type'] = 'product';
						if (isset($_GET['rating_filter']) && $args['post_type'] == 'product') {
							$visibility_terms = array();
							$rating_filter = array_filter(array_map('absint', explode(',', wp_unslash($_GET['rating_filter']))));
							$product_visibility_terms = wc_get_product_visibility_term_ids();
							foreach ($rating_filter as $rating) {
								$visibility_terms[] = $product_visibility_terms['rated-' . $rating];
							}
							$args['tax_query'][] = array(
								'relation' => 'AND',
								array(
									'taxonomy' => 'product_visibility',
									'field' => 'term_taxonomy_id',
									'terms' => $visibility_terms,
								)
							);
						}
						$_chosen_attributes = WC_Query::get_layered_nav_chosen_attributes();
						if (!empty($_chosen_attributes)) {
							foreach ($_chosen_attributes as $_chosen_attribute_tax => $_chosen_attribute) {
								$filter_name = 'filter_' . wc_attribute_taxonomy_slug($_chosen_attribute_tax);
								if (isset($_GET[$filter_name]) && $args['post_type'] == 'product') {
									$args['tax_query'][] = array(
										'taxonomy' => $_chosen_attribute_tax,
										'field' => 'slug',
										'terms' => $_chosen_attribute['terms']
									);
								}
							}
						}
						$args['tax_query'][] = array(
							'relation' => 'AND',
							array(
								'taxonomy' => 'product_visibility',
								'field'    => 'name',
								'terms'    => 'exclude-from-catalog',
								'operator' => 'NOT IN',
							)
						);
						if ('yes' === get_option('woocommerce_hide_out_of_stock_items')) {
							$args['tax_query'][] = array(
								'relation' => 'AND',
								array(
									'taxonomy' => 'product_visibility',
									'field'    => 'name',
									'terms'    => 'outofstock',
									'operator' => 'NOT IN',
								)
							);
						}

						if (!empty($_GET['orderby'])) {
							$ordering_args = WC()->query->get_catalog_ordering_args();
							$args['orderby'] = $ordering_args['orderby'];
							$args['order'] = $ordering_args['order'];
							if ($ordering_args['meta_key']) {
								$args['meta_key'] = $ordering_args['meta_key'];
							}
						}
					}
				}
			} elseif ($filter_args->data_source == 'wishlist') {
				$args = array(
					'post_status' => 'publish',
					'ignore_sticky_posts' => 1,
					'orderby' => 'post__in',
					'posts_per_page'   => (int)$filter_args->show,
					'post_type' => $filter_args->post_type,
				);
				if (is_singular()) {
					if (!empty($_GET['wishlistids'])) {
						$wishlistids = explode(',', esc_html($_GET['wishlistids']));
					} else {
						if (is_user_logged_in()) { // user is logged in
							global $current_user;
							$user_id = $current_user->ID; // current user
							$likedposts = get_user_meta($user_id, "_wished_posts", true);
						} else {
							$ip = gspb_get_user_ip(); // user IP address
							$likedposts = get_transient('re_guest_wishes_' . $ip);
						}
						$wishlistids = $likedposts;
					}
					if (!empty($wishlistids)) {
						$wishlistids = array_reverse($wishlistids);
						foreach ($wishlistids as $wishlistid) {
							if ('publish' != get_post_status($wishlistid)) {
								if (!empty($user_id)) {
									$postkeyip = 'post-' . $wishlistid;
									unset($likedposts[$postkeyip]);
									unset($wishlistids[$postkeyip]);
									update_user_meta($user_id, "_wished_posts", $likedposts);
								} else {
									$keydelete = array_search($wishlistid, $likedposts);
									unset($likedposts[$keydelete]);
									unset($wishlistids[$keydelete]);
									set_transient('re_guest_wishes_' . $ip, $likedposts, 30 * DAY_IN_SECONDS);
								}
							}
						}
						$args['post__in'] = $wishlistids;
					}else{
						$args['post__in'] = array(0);
					}
				}
			} elseif ($filter_args->data_source == 'related') {
				$args = array(
					'post_status' => 'publish',
					'ignore_sticky_posts' => 1,
					'orderby' => 'post__in',
					'posts_per_page' => (int)$filter_args->show,
					'post_type' => $filter_args->post_type,
				);
				if (is_singular()) {
					global $post;
					$args['post__not_in'] = array($post->ID);
					$relative_ids = array();

					if (!empty($filter_args->tax_name)) {
						$relatives = get_the_terms($post->ID, $filter_args->tax_name);
						if (!empty($relatives) && !is_wp_error($relatives)) {
							foreach ($relatives as $individual_relative) $relative_ids[] = $individual_relative->term_id;
							$args['tax_query'][] = array(
								'relation' => 'AND',
								array(
									'taxonomy' => $filter_args->tax_name,
									'field'    => 'term_id',
									'terms'    => $relative_ids,
								)
							);
						}
					}
				}
			} elseif ($filter_args->data_source == 'relatedbyauthor') {
				$args = array(
					'post_status' => 'publish',
					'ignore_sticky_posts' => 1,
					'orderby' => 'post__in',
					'posts_per_page' => (int)$filter_args->show,
					'post_type' => $filter_args->post_type,
				);
				if (is_singular()) {
					global $post;
					$args['post__not_in'] = array($post->ID);
					$author_id = get_the_author_meta('ID');
					$args['author'] = $author_id;
				}
			}elseif ($filter_args->data_source == 'currentuser') {
				$args = array(
					'post_status' => 'publish',
					'ignore_sticky_posts' => 1,
					'posts_per_page' => (int)$filter_args->show,
					'post_type' => $filter_args->post_type,
				);
				$current_user_id = get_current_user_id();
				if($current_user_id){
					$args['author'] = $current_user_id;
				}else{
					$args['post_type'] = 'noexisted';
				}
			} elseif ($filter_args->data_source == 'currentchilds') {
				$args = array(
					'post_status' => 'publish',
					'ignore_sticky_posts' => 1,
					'orderby' => 'post__in',
					'posts_per_page' => (int)$filter_args->show,
					'post_type' => $filter_args->post_type,
				);
				global $post;
				$args['post_parent'] = $post->ID;
			}elseif ($filter_args->data_source == 'currentsisters') {
				$args = array(
					'post_status' => 'publish',
					'ignore_sticky_posts' => 1,
					'orderby' => 'post__in',
					'posts_per_page' => (int)$filter_args->show,
					'post_type' => $filter_args->post_type,
				);
				
				global $post;
				$args['post_parent'] = $post->post_parent;
				$args['post__not_in'] = array($post->ID);
			}elseif ($filter_args->data_source == 'currentparent') {
				$args = array(
					'post_status' => 'publish',
					'ignore_sticky_posts' => 1,
					'orderby' => 'post__in',
					'posts_per_page' => (int)$filter_args->show,
					'post_type' => $filter_args->post_type,
				);
				global $post;
				if ($post->post_parent) {
					$args['p'] = $post->post_parent; // Retrieve the parent post
				}
			}elseif ($filter_args->data_source == 'prevpost') {
				$args = array(
					'post_status' => 'publish',
					'ignore_sticky_posts' => 1,
					'orderby' => 'post__in',
					'posts_per_page' => 1,
					'post_type' => $filter_args->post_type,
				);
				if(is_singular() && !is_admin()){
					global $post;
					$prev_post = get_previous_post();
					if(is_object($prev_post) && is_object($post)){
						$prev_ID = $prev_post->ID;
						if($prev_ID != $post->ID){
							$args['post__in'] = array($prev_ID);
						}else{
							$args['post_type'] = 'noexisted';
						}
					}else{
						$args['post_type'] = 'noexisted';
					}
				}
			}elseif ($filter_args->data_source == 'nextpost') {
				$args = array(
					'post_status' => 'publish',
					'ignore_sticky_posts' => 1,
					'orderby' => 'post__in',
					'posts_per_page' => 1,
					'post_type' => $filter_args->post_type,
				);
				if(is_singular() && !is_admin()){
					global $post;
					$next_post = get_next_post();
					if(is_object($next_post) && is_object($post)){
						$next_ID = $next_post->ID;
						if($next_ID != $post->ID){
							$args['post__in'] = array($next_ID);
						}else{
							$args['post_type'] = 'noexisted';
						}
					}else{
						$args['post_type'] = 'noexisted';
					}
				}else{
					$args['offset'] = 1;
				}
			} elseif ($filter_args->data_source == 'itemrelationship') {
				$args = array(
					'post_status' => 'publish',
					'ignore_sticky_posts' => 1,
					'posts_per_page' => (int)$filter_args->show,
					'post_type' => $filter_args->post_type,

				);
				if(!is_admin()){
					global $post;
					if (is_object($post)){
						$postID = $post->ID;
					}else{
						$postID = 0;
					}
					$field = $filter_args->additional_field;
					if(is_tax() || is_category() || is_tag()){
						$term_id = get_queried_object_id();
						$idsfield = get_term_meta($term_id, $field, true);
						if(empty($idsfield)){
							$idsfield = get_post_meta($postID, $field, true);
						}
					}else{
						$idsfield = get_post_meta($postID, $field, true);
					}
					if(is_array($idsfield) && !empty($idsfield)){
						$args['post__in'] = $idsfield;
					}else{
						$idsfield = str_replace(' ', '', $idsfield);
						$ids = explode(',', $idsfield);
						$args['post__in'] = $ids;
					}
					$args['orderby'] = 'post__in';
				}
			} elseif ($filter_args->data_source == 'relatedbyfield') {
				$args = array(
					'post_status' => 'publish',
					'ignore_sticky_posts' => 1,
					'posts_per_page' => (int)$filter_args->show,
					'post_type' => $filter_args->post_type,

				);
				if(!is_admin()){
					global $post;
					if (is_object($post)){
						$postID = $post->ID;
					}else{
						$postID = 0;
					}
					$field = esc_attr($filter_args->additional_field);
					$value = get_post_meta($postID, $field, true);
					$args['meta_query'] = array(
						array(
							'key'     => $field,
							'value'   => $value,
							'compare' => '=',
						)
					);
					//$args = 

				}
			} elseif ($filter_args->data_source == 'metaboxrelationship') {
				$args = array(
					'post_status' => 'publish',
					'ignore_sticky_posts' => 1,
					'posts_per_page' => (int)$filter_args->show,
					'post_type' => $filter_args->post_type,

				);
				if(!is_admin()){
					global $post;
					if (is_object($post)){
						$postID = $post->ID;
					}else{
						$postID = 0;
					}
					$args['relationship'] = [
						'id'      => esc_attr($filter_args->additional_field),
					];
					if($filter_args->additional_type == 'to'){
						$args['relationship']['to'] = $postID;
					}else{
						$args['relationship']['from'] = $postID;
					}
				}
			} else {
				$args = array(
					'post_type' => 'post',
					'posts_per_page'   => (int)$filter_args->show,
					'order' => $filter_args->order,
					'post_status' => 'publish',
				);
				if ($filter_args->cat != '') {
					$args['cat'] = $filter_args->cat;
				}
				if ($filter_args->cat_name != '') {
					$args['category_name'] = $filter_args->cat_name;
				}
				if ($filter_args->tag != '') {
					$args['tag__in'] = array_map('trim', explode(",", $filter_args->tag));
				}
				if ($filter_args->cat_exclude != '') {
					$args['category__not_in'] = array_map('trim', explode(",", $filter_args->cat_exclude));
				}
				if ($filter_args->tag_exclude != '') {
					$args['tag__not_in'] = explode(',', $filter_args->tag_exclude);
				}
			}
			if ($filter_args->order != '') {
				$args['order'] = $filter_args->order;
			}
			if ($filter_args->offset != '') {
				$args['offset'] = (int)$filter_args->offset;
			}
			if (!empty($filter_args->searchtitle)) {
				$args['s'] = urlencode($filter_args->searchtitle);
				if ($filter_args->searchtitle == 'CURRENTPAGE') {
					$currenttitle = get_the_title();
					$args['s'] = urlencode($currenttitle);
				}
			}
			if (!empty($filter_args->tax_name) && !empty($filter_args->tax_slug)) {
				$tax_slugs = array_map('trim', explode(",", $filter_args->tax_slug));
				$args['tax_query'][] = array(
					'relation' => 'AND',
					array(
						'taxonomy' => $filter_args->tax_name,
						'field'    => 'slug',
						'terms'    => $tax_slugs,
					)
				);
			}
			if (!empty($filter_args->tax_name) && !empty($filter_args->tax_slug_exclude)) {
				$tax_slugs_exclude = array_map('trim', explode(",", $filter_args->tax_slug_exclude));
				$args['tax_query'][] = array(
					'relation' => 'AND',
					array(
						'taxonomy' => $filter_args->tax_name,
						'field'    => 'slug',
						'terms'    => $tax_slugs_exclude,
						'operator' => 'NOT IN',
					)
				);
			}
			if (!empty($filter_args->user_id)) {
				$users_arr = [];
				foreach (explode(',', $filter_args->user_id) as $key => $selected_user_id) {
					if ($selected_user_id === 'current_logged') {
						if (is_user_logged_in()) $users_arr[] = get_current_user_id();
						else $users_arr[] = 0;
					} else if ($selected_user_id !== 'current_logged') {
						$users_arr[] = (int) $selected_user_id;
					}
				}

				$users_arr = array_unique($users_arr);
				$args['author__in'] = $users_arr;
			}
			if (($filter_args->orderby == 'meta_value' || $filter_args->orderby == 'meta_value_num') && $filter_args->meta_key != '') {
				$args['meta_key'] = $filter_args->meta_key;
			}
			if (($filter_args->orderby == 'meta_value_date' || $filter_args->orderby == 'meta_value_datetime') && $filter_args->meta_key != '') {
				$args['meta_key'] = $filter_args->meta_key;
				if($filter_args->orderby == 'meta_value_datetime') {
					$args['orderby'] = 'meta_value';
					$args['meta_type'] = 'DATETIME';
				}else if($filter_args->orderby == 'meta_value_date') {
					$args['orderby'] = 'meta_value';
					$args['meta_type'] = 'DATE';
				}
			}
			if ($filter_args->orderby != '') {
				$args['orderby'] = $filter_args->orderby;
			}
			if ($filter_args->orderby == 'view' || $filter_args->orderby == 'thumb' || $filter_args->orderby == 'discount' || $filter_args->orderby == 'price') {
				$args['orderby'] = 'meta_value_num';
			}
			if ($filter_args->orderby == 'view') {
				$args['meta_key'] = 'rehub_views';
			}
			if ($filter_args->orderby == 'thumb') {
				$args['meta_key'] = 'post_hot_count';
			}
			if ($filter_args->orderby == 'wish') {
				$args['meta_key'] = 'post_wish_count';
			}
			if ($filter_args->orderby == 'discount') {
				$args['meta_key'] = '_rehub_offer_discount';
			}
			if ($filter_args->orderby == 'price') {
				if ($filter_args->post_type == 'product' || $args['post_type'] == 'product') {
					$args['meta_key'] = '_price';
				} else {
					$args['meta_key'] = 'rehub_main_product_price';
				}
			}
			if ($filter_args->orderby == 'hot') {
				$rehub_max_temp = (rehub_option('hot_max')) ? rehub_option('hot_max') : 50;
				$args['meta_query'] = array(
					array(
						'key'     => 'post_hot_count',
						'value'   => $rehub_max_temp,
						'type'    => 'numeric',
						'compare' => '>=',
					)
				);
				$args['orderby'] = 'date';
			}

			if ($filter_args->price_range != '') {
				if (!empty($args['meta_query'])) {
					$args['meta_query']['relation'] = 'AND';
				}
				$price_range_array = array_map('trim', explode("-", $filter_args->price_range));
				if ($filter_args->post_type == 'product' || $args['post_type'] == 'product') {
					$key = '_price';
				} else {
					$key = 'rehub_main_product_price';
				}

				$args['meta_query'][] = array(
					'key'     => $key,
					'value'   => $price_range_array,
					'type'    => 'numeric',
					'compare' => 'BETWEEN',
				);
			}

			if (isset($_GET['min_price']) || isset($_GET['max_price'])) {
				if (!empty($args['meta_query'])) {
					$args['meta_query']['relation'] = 'AND';
				}
				$minprice = isset($_GET['min_price']) ? $_GET['min_price'] : 0;
				$maxprice = isset($_GET['max_price']) ? $_GET['max_price'] : 1000000000;

				$price_range_array = array(floatval($minprice), floatval($maxprice));
				if ($filter_args->post_type == 'product' || $args['post_type'] == 'product') {
					$key = '_price';
				} else {
					$key = 'rehub_main_product_price';
				}

				$args['meta_query'][] = array(
					'key'     => $key,
					'value'   => $price_range_array,
					'type'    => 'numeric',
					'compare' => 'BETWEEN',
				);
			}

			if ($filter_args->show_date == 'day') {
				$args['date_query'][] = array(
					'after'  => '1 day ago',
				);
			}
			if ($filter_args->show_date == 'week') {
				$args['date_query'][] = array(
					'after'  => '7 days ago',
				);
			}
			if ($filter_args->show_date == 'month') {
				$args['date_query'][] = array(
					'after'  => '1 month ago',
				);
			}
			if ($filter_args->show_date == 'year') {
				$args['date_query'][] = array(
					'after'  => '1 year ago',
				);
			}

			if (get_query_var('paged')) {
				$paged = get_query_var('paged');
			} else if (get_query_var('page')) {
				$paged = get_query_var('page');
			} else {
				$paged = 1;
			}

			if ( $filter_args->enable_pagination != '' && $filter_args->enable_pagination != '0' ) {
				$args['paged'] = $paged;
			} else if ( $filter_args->filter_enable_pagination != '' && $filter_args->filter_enable_pagination != '0' ) {
				$args['paged'] = $paged;
			} else {
				$args['no_found_rows'] = 1;
			}

			if (!empty($filter_args->conditions_arr)) {
				$conditions_args = [];
				foreach ($filter_args->conditions_arr as $key => $condition) {
					$conditions_args[$key] = [];

					if ($condition['query_by'] === 'taxonomy') {

						if (empty($args['tax_query'])) $args['tax_query'] = [
							'relation' => 'AND'
						];

						if (empty($condition['tax_slug']) && empty($condition['tax_slug_exclude'])) {
							$args['tax_query'][] = [
								'taxonomy' => $condition['tax_name'],
								'operator' => 'EXISTS'
							];
						} else if (!empty($condition['tax_slug']) && !empty($condition['tax_slug_exclude'])) {
							$args['tax_query'][] = [
								'taxonomy' => $condition['tax_name'],
								'field' => 'id',
								'terms'    => array_column($condition['tax_slug'], 'value'),
							];

							$args['tax_query'][] = [
								'taxonomy' => $condition['tax_name'],
								'field' => 'id',
								'terms'    => array_column($condition['tax_slug_exclude'], 'value'),
								'operator'    => 'NOT IN',
							];
						} else {
							if (!empty($condition['tax_slug'])) {
								$args['tax_query'][] = [
									'taxonomy' => $condition['tax_name'],
									'field' => 'id',
									'terms'    => array_column($condition['tax_slug'], 'value'),
								];
							} else {
								$args['tax_query'][] = [
									'taxonomy' => $condition['tax_name'],
									'field' => 'id',
									'terms'    => array_column($condition['tax_slug_exclude'], 'value'),
									'operator'    => 'NOT IN',
								];
							}
						}
					} else if ($condition['query_by'] === 'custom_meta') {

						if (empty($args['meta_query'])) $args['meta_query'] = [
							'relation' => 'OR'
						];

						$value = $condition['custom_field_value'];
						switch ($condition['custom_field_compare']) {
							case 'exist':
								$compare = 'EXISTS';
								break;
							case 'noexist':
								$compare = 'NOT EXISTS';
								break;
							case 'less':
								$compare = '<';
								break;
							case 'more':
								$compare = '>';
								break;
							case 'equal':
								$compare = '=';
								break;
							case 'less_equal':
								$compare = '<=';
								break;
							case 'more_equal':
								$compare = '>=';
								break;
							case 'not_equal':
								$compare = '!=';
								break;
							default:
								$compare = $condition['custom_field_compare'];
								break;
						}

						if ($compare === 'EXISTS' || $compare === 'NOT EXISTS') {
							$args['meta_query'][] = [
								'key' => $condition['custom_field_key'],
								'compare' => $compare,
							];
						} else {
							$value = trim($value);
							if ($value == '{POST_ID}') {
								if(!is_admin()){
									global $post;
									if(is_object($post)){
										$post_id = $post->ID;
										$value = $post_id;
									}
									$args['meta_query'][] = [
										'key' => $condition['custom_field_key'],
										'value' => $value,
										'compare' => $compare,
									];
								}
							}
							else if ($value == '{AUTHOR_ID}') {
								if(!is_admin()){
									$author_id = get_the_author_meta('ID');
									if($author_id){
										$value = $author_id;
									}
									$args['meta_query'][] = [
										'key' => $condition['custom_field_key'],
										'value' => $value,
										'compare' => $compare,
									];
								}
							}
							else if ($value == '{QUERY_OBJ_ID}') {
								if(!is_admin()){
									$obj = get_queried_object();
									if (is_object($obj)) {
										if (!empty($obj->term_id)) {
											$value = $obj->term_id;
										} elseif (!empty($obj->ID)) {
											$value = $obj->ID;
										}
									}
									$args['meta_query'][] = [
										'key' => $condition['custom_field_key'],
										'value' => $value,
										'compare' => $compare,
									];
								}
							} else if ($value == '{CURRENT_USER_ID}') {
								if(!is_admin()){
									$value = get_current_user_id();
									$args['meta_query'][] = [
										'key' => $condition['custom_field_key'],
										'value' => $value,
										'compare' => $compare,
									];
								}
							}
							else if (strpos($value, "{CUSTOM:") !== false) {
								if(!is_admin()){
									global $post;
									if(is_object($post)){
										$post_id = $post->ID;
										$valueArr = explode(':', $value);
										$custom_field_key = str_replace('}', '', $valueArr[1]);
										$value = get_post_meta($post_id, $custom_field_key, true);
									}
									$args['meta_query'][] = [
										'key' => $condition['custom_field_key'],
										'value' => $value,
										'compare' => $compare,
									];
								}
							}else{
								$type = 'CHAR';
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
										if(strpos($value[$key], '{CURRENTDATE}') !== false){
											$value[$key] = str_replace('{CURRENTDATE}', date('Y-m-d'), $value[$key]);
											$type = 'DATE';
										}
										if(strpos($value[$key], '{CURRENTDATETIME}') !== false){
											$value[$key] = str_replace('{CURRENTDATETIME}', date('Y-m-d H:i:s'), $value[$key]);
											$type = 'DATETIME';
										}
									}
								}else{
									if(strpos($value, '{TIMESTRING:') !== false){
										$pattern = '/\{TIMESTRING:(.*?)\}/';
										preg_match($pattern, $value, $matches);
										$value = $matches[1];
										$value = strtotime($value);
									}
									if(strpos($value, '{CURRENTDATE}') !== false){
										$value = str_replace('{CURRENTDATE}', date('Y-m-d'), $value);
										$type = 'DATE';
									}
									if(strpos($value, '{CURRENTDATETIME}') !== false){
										$value = str_replace('{CURRENTDATETIME}', date('Y-m-d H:i:s'), $value);
										$type = 'DATETIME';
									}
								}
								$args['meta_query'][] = [
									'key' => $condition['custom_field_key'],
									'value' => $value,
									'compare' => $compare,
									'type'=> $type
								];
							}
							
						}
					} else if ($condition['query_by'] === 'by_title' && !empty($condition['by_title_field'])) {
						$args['s'] = $condition['by_title_field'];
					} else if ($condition['query_by'] === 'author' && !empty($condition['user_id'])) {
							$users_arr = [];
							foreach ($condition['user_id'] as $key => $value) {
								$selected_user_id = $value['value'];
								if ($selected_user_id === 'current_logged') {
									if (is_user_logged_in()) $users_arr[] = get_current_user_id();
									else $users_arr[] = 0;
								} else if ($selected_user_id !== 'current_logged') {
									$users_arr[] = (int) $selected_user_id;
								}
							}
			
							$users_arr = array_unique($users_arr);
							$args['author__in'] = $users_arr;
					}
				}
			}
			if(!empty($filter_args->enableSearchFilters) && !empty($filter_args->searchQueryId) ){
				$args['search_filter_query_id'] = (int)$filter_args->searchQueryId;
			}
			return $args;
		}
	}
}

//////////////////////////////////////////////////////////////////
// Filter panel render
//////////////////////////////////////////////////////////////////
function gspb_custom_taxonomy_dropdown($taxdrop, $limit = '40', $class = '', $taxdroplabel = '', $containerid = '', $taxdropids = '', $alldroplabel = '')
{
	$args = array(
		'taxonomy' => $taxdrop,
		'number' => $limit,
		'hide_empty' => true,
		'parent'        => 0,
	);
	if ($taxdropids) {
		$taxdropids = wp_parse_id_list($taxdropids);
		$args['include'] = $taxdropids;
		$args['parent'] = '';
		$args['orderby'] = 'include';
	}
	$terms = get_terms($args);
	$class = ($class) ? $class : 'gspb_tax_dropdown';
	$output = '';
	if ($terms && !is_wp_error($terms)) {
		$output .= '<ul class="' . $class . '">';
		if (empty($taxdroplabel)) {
			$taxdroplabel = esc_html__('Choose category', 'greenshiftquery');
		}
		if (empty($alldroplabel)) {
			$alldroplabel = esc_html__('All categories', 'greenshiftquery');
		}
		$output .= '<li class="label"><span class="gspb_tax_placeholder">' . $taxdroplabel . '</span><span class="gspb_choosed_tax"></span></li>';
		$output .= '<li class="gspb_drop_item"><span data-sorttype="" class="gspb_filtersort_btn" data-containerid="' . $containerid . '">' . $alldroplabel . '</span></li>';
		foreach ($terms as $term) {
			$term_link = get_term_link($term);
			if (is_wp_error($term_link)) {
				continue;
			}
			if (!empty($containerid)) {
				$sort_array = array();
				$sort_array['filtertype'] = 'tax';
				$sort_array['filtertaxkey'] = $taxdrop;
				$sort_array['filtertaxtermslug'] = $term->slug;
				$json_filteritem = json_encode($sort_array);
				$output .= '<li class="gspb_drop_item"><span data-sorttype=\'' . $json_filteritem . '\' class="gspb_filtersort_btn" data-containerid="' . $containerid . '">';
				$output .= $term->name;
				$output .= '</span></li>';
			} else {
				$output .= '<li class="gspb_drop_item"><span><a href="' . esc_url($term_link) . '">' . $term->name . '</a></span></li>';
			}
		}
		$output .= '</ul>';
	}
	return $output;
}

function gspb_vc_filterpanel_render($filterpanel = '', $containerid = '', $taxdrop = '', $taxdroplabel = '', $taxdropids = '', $filterheading = '', $alldroplabel = '')
{
	if (!$filterpanel) {
		return;
	}
	$filterpanel = (array) json_decode(urldecode($filterpanel), true);
	$output = '';
	if (!empty($filterpanel[0])) {
		$tax_enabled_div = (!empty($taxdrop)) ? ' tax_enabled_drop' : '';
		$heading_enabled_div = (!empty($filterheading)) ? ' heading_enabled' : '';
		$output .= '<div class="gspb_filter_panel' . $tax_enabled_div . $heading_enabled_div . '">';
		if ($filterheading) {
			$output .= '<div class="gspb_filter_heading">' . wp_kses_post($filterheading) . '</div>';
		}
		$output .= '<ul class="gspb_filter_ul">';
		foreach ($filterpanel as $k => $v) {
			$output .= '<li class="inlinestyle">';
			$label = '';
			if (!empty($v['filtertitle'])) {
				$label = $v['filtertitle'];
				unset($v['filtertitle']);
			}
			$json_filteritem = json_encode($v);
			$class = ($k == 0) ? ' class="active gspb_filtersort_btn resort_' . $k . '"' : ' class="gspb_filtersort_btn resort_' . $k . '"';
			$output .= '<span data-sorttype=\'' . $json_filteritem . '\'' . $class . ' data-containerid="' . $containerid . '">';
			$output .= $label;
			$output .= '</span>';
			$output .= '</li>';
		}
		$output .= '</ul>';

		if ($taxdrop && $taxdroplabel) {
			$output .= '<div class="gs-flex-right-align">';
			$output .= gspb_custom_taxonomy_dropdown($taxdrop, '40', 'gspb_tax_dropdown', $taxdroplabel, $containerid, $taxdropids, $alldroplabel);
			$output .= '</div>';
		}

		$output .= '</div>';
	}
	echo '' . $output;
}

function gspb_filter_empty_values($haystack)
{
	foreach ($haystack as $key => $value) {
		if (is_array($value)) {
			$haystack[$key] = gspb_filter_empty_values($haystack[$key]);
		}
		if (empty($haystack[$key])) {
			unset($haystack[$key]);
		}
	}
	return $haystack;
}

function gssal_multi_taxonomy_terms()
{
	$response_data = [
		'results' => []
	];

	if (empty($_POST['conditions'])) {
		wp_send_json_success($response_data);
	}

	foreach ($_POST['conditions'] as $key => $condition) {
		$taxonomy = sanitize_text_field($condition['taxonomy']);
		$selected = isset($condition['selected']) ? $condition['selected'] : '';

		if (empty($taxonomy)) {
			$response_data['results'][$key][] = '';
			continue;
		}

		$terms = get_terms([
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'number' => 15,
			'exclude' => $selected
		]);

		foreach ($terms as $term) {
			$response_data['results'][$key][] = [
				'id'        => $term->slug,
				'label'     => esc_html($term->name),
				'value'     => $term->term_id
			];
		}
	}

	wp_send_json_success($response_data);
}
