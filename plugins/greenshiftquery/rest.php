<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

//////////////////////////////////////////////////////////////////
// REST routes to save and get settings
//////////////////////////////////////////////////////////////////

add_action('rest_api_init', 'gspbquery_register_route');
function gspbquery_register_route()
{
    register_rest_route(
        'greenshift/v1',
        "/metaget/",
        array(
            'methods'  => WP_REST_Server::CREATABLE,
            'permission_callback' => function (WP_REST_Request $request) {
                return current_user_can('editor') || current_user_can('administrator');
            },
            'callback' => 'gspb_query_metagetapi',
        )
    );
    register_rest_route(
        'greenshift/v1',
        '/update_template_replace/',
        array(
            array(
                'methods'             => 'POST',
                'callback'            => 'gspb_update_template_replace',
                'permission_callback' => function (WP_REST_Request $request) {
                    return current_user_can('editor') || current_user_can('administrator');
                }
            ),
        )
    );
    register_rest_route(
        'greenshift/v1',
        "/getthumbelement/",
        array(
            'methods'  => WP_REST_Server::CREATABLE,
            'permission_callback' => function (WP_REST_Request $request) {
                return current_user_can('editor') || current_user_can('administrator');
            },
            'callback' => 'gspb_query_thumbelementapi',
        )
    );
    register_rest_route(
        'greenshift/v1',
        "/getwishlistelement/",
        array(
            'methods'  => WP_REST_Server::CREATABLE,
            'permission_callback' => function (WP_REST_Request $request) {
                return current_user_can('editor') || current_user_can('administrator');
            },
            'callback' => 'gspb_query_wishlistelementapi',
        )
    );

    register_rest_route('greenshift/v1', '/get-post-types/', [
        [
            'methods' => 'GET',
            'callback' => 'gspb_get_post_types',
            'permission_callback' => function (WP_REST_Request $request) {
                return current_user_can('editor') || current_user_can('administrator');
            }
        ]
    ]);

    register_rest_route('greenshift/v1', '/get-post-metas/', [
        [
            'methods' => 'GET',
            'callback' => 'gspb_get_post_metas',
            'permission_callback' => function (WP_REST_Request $request) {
                return current_user_can('editor') || current_user_can('administrator');
            },
            'args' => array(
                'post_type' => array(
                    'type' => 'string',
                    'required' => true,
                )
            ),
        ]
    ]);

    register_rest_route('greenshift/v1', '/get-taxonomies/', [
        [
            'methods' => 'GET',
            'callback' => 'gspb_get_taxonomies',
            'permission_callback' => function (WP_REST_Request $request) {
                return current_user_can('editor') || current_user_can('administrator');
            },
            'args' => array(
                'post_type' => array(
                    'type' => 'string',
                    'required' => true,
                )
            ),
        ]
    ]);
    register_rest_route('greenshift/v1', '/get-all-taxonomies/', [
        [
            'methods' => 'GET',
            'callback' => 'gspb_get_all_taxonomies',
            'permission_callback' => function (WP_REST_Request $request) {
                return current_user_can('editor') || current_user_can('administrator');
            }
        ]
    ]);

    register_rest_route('greenshift/v1', '/get-terms/', [
        [
            'methods' => 'GET',
            'callback' => 'gspb_get_terms',
            'permission_callback' => function (WP_REST_Request $request) {
                return current_user_can('editor') || current_user_can('administrator');
            },
            'args' => array(
                'taxonomy' => array(
                    'type' => 'string',
                    'required' => true,
                ),
                'show_empty' => array(
                    'type' => 'boolean',
                    'required' => true,
                ),
                'order_by' => array(
                    'type' => 'string',
                    'required' => true,
                ),
                'order' => array(
                    'type' => 'string',
                    'required' => true,
                ),
                'include' => array(
                    'type' => 'string',
                    'required' => false
                ),
                'exclude' => array(
                    'type' => 'string',
                    'required' => false
                ),
                'childof' => array(
                    'type' => 'string',
                    'required' => false
                ),
                'image_size' => array(
                    'type' => 'string',
                    'required' => false
                ),
                'hierarchy' => array(
                    'type' => 'boolean',
                    'required' => false
                ),
                'number' => array(
                    'type' => 'number',
                    'required' => false
                ),
            ),
        ]
    ]);

    register_rest_route('greenshift/v1', '/get-terms-search/', [
        [
            'methods' => 'GET',
            'callback' => 'gspb_get_taxonomy_terms_search',
            'permission_callback' => function (WP_REST_Request $request) {
                return current_user_can('editor') || current_user_can('administrator');
            },
            'args' => array(
                'taxonomy' => array(
                    'type' => 'string',
                    'required' => true,
                ),
                'search' => array(
                    'type' => 'string',
                    'required' => false
                ),
                'search-id' => array(
                    'type' => 'string',
                    'required' => false
                ),
            ),
        ]
    ]);

    register_rest_route('greenshift/v1', '/get-alpha-html/', [
        [
            'methods' => 'GET',
            'callback' => 'gspb_get_alpha_html',
            'permission_callback' => function (WP_REST_Request $request) {
                return current_user_can('editor') || current_user_can('administrator');
            },
            'args' => array(
                'taxonomy' => array(
                    'type' => 'string',
                    'required' => true,
                ),
                'show_empty' => array(
                    'type' => 'boolean',
                    'required' => true,
                ),
                'order_by' => array(
                    'type' => 'string',
                    'required' => true,
                ),
                'order' => array(
                    'type' => 'string',
                    'required' => true,
                ),
                'include' => array(
                    'type' => 'string',
                    'required' => false
                ),
                'exclude' => array(
                    'type' => 'string',
                    'required' => false
                ),
                'hierarchy' => array(
                    'type' => 'boolean',
                    'required' => false
                ),
                'show_count' => array(
                    'type' => 'boolean',
                    'required' => false
                ),
            ),
        ]
    ]);

    register_rest_route('greenshift/v1', '/get-user-roles/', [
        [
            'methods' => 'GET',
            'callback' => 'gspb_get_user_roles',
            'permission_callback' => function (WP_REST_Request $request) {
                return current_user_can('editor') || current_user_can('administrator');
            },
            'args' => array(
                'search' => array(
                    'type' => 'string',
                    'required' => false,
                )
            ),
        ]
    ]);

    register_rest_route('greenshift/v1', '/get-post-part/', [
        [
            'methods' => 'GET',
            'callback' => 'gspb_get_post_parts_callback',
            'permission_callback' => '__return_true',
            'args' => array(
                'post_id' => array(
                    'type' => 'int',
                    'required' => false,
                ),
                'part' => array(
                    'type' => 'string',
                    'required' => true,
                ),
                'post_type' => array(
                    'type' => 'string',
                    'required' => true,
                ),
                'image_size' => [
                    'type' => 'string',
                    'required' => false
                ]
            ),
        ]
    ]);

    register_rest_route('greenshift/v1', '/get-post-by/', [
        [
            'methods' => 'GET',
            'callback' => 'gspb_get_post_by',
            'permission_callback' => function (WP_REST_Request $request) {
                return current_user_can('editor') || current_user_can('administrator');
            },
            'args' => array(
                'post_id' => array(
                    'type' => 'int',
                    'required' => false,
                ),
                'post_type' => array(
                    'type' => 'string',
                    'required' => false,
                )
            ),
        ]
    ]);

    register_rest_route('greenshift/v1', '/get-post-search/', [
        [
            'methods' => 'GET',
            'callback' => 'gspb_get_post_search',
            'permission_callback' => function (WP_REST_Request $request) {
                return current_user_can('editor') || current_user_can('administrator');
            },
            'args' => array(
                'search' => array(
                    'type' => 'string',
                    'required' => true,
                ),
                'post_type' => array(
                    'type' => 'string',
                    'required' => true,
                )
            ),
        ]
    ]);

    register_rest_route('greenshift/v1', '/get-image-sizes/', [
        [
            'methods' => 'GET',
            'callback' => 'gspb_get_image_sizes',
            'permission_callback' => function (WP_REST_Request $request) {
                return current_user_can('editor') || current_user_can('administrator');
            },
        ]
    ]);

    register_rest_route('greenshift/v1', '/frontsearch/', array(
        'methods' => 'POST',
        'callback' => 'gspb_front_search_rest_callback',
        'permission_callback' => '__return_true'
    ));

    register_rest_route('greenshift/v1', '/api-connector/', array(
        'methods' => 'POST',
        'callback' => 'gspb_api_connector_callback',
        'permission_callback' => '__return_true'
    ));

    // regsiter route for filter block
    register_rest_route('greenshift/v1', '/get-gs-posts/', [
        'methods'  => 'POST',
        'callback' => 'gspb_filter_posts',
        'permission_callback' => '__return_true'
    ]);

    // posts indexing
    register_rest_route('greenshift/v1', '/gspb-indexing/', array(
        'methods' => 'POST',
        'callback' => 'gspb_handle_indexing_callback',
        'permission_callback' => '__return_true'
    ));

    // Fetch Count
    register_rest_route('greenshift/v1', '/indexer-count/', array(
        'methods' => 'POST',
        'callback' => 'gspb_indexer_count_callback',
        'permission_callback' => '__return_true'
    ));

    // Refresh filter chips
	register_rest_route('greenshift/v1', '/refresh-filterchips/', array(
		'methods' => 'POST',
		'callback' => 'gspb_refreshfilter_chips_callback',
		'permission_callback' => '__return_true'
	));

    // Get Rating
	register_rest_route('greenshift/v1', '/get-rating/', array(
		'methods' => 'GET',
		'callback' => 'gspb_get_wc_ratings',
		'permission_callback' => '__return_true'
	));

    register_rest_route('greenshift/v1', '/get-dynamic-part/', [
        [
            'methods' => 'GET',
            'callback' => 'gspb_get_dynamic_part_callback',
            'permission_callback' => '__return_true',
        ]
    ]);
}

//////////////////////////////////////////////////////////////////
// Frontend Search
//////////////////////////////////////////////////////////////////
function gspb_front_search_rest_callback(WP_REST_Request $request)
{
    $nonce = sanitize_text_field($request->get_param('nonce'));
    if (!wp_verify_nonce($nonce, 'wp_rest')) {
        return new WP_REST_Response('Invalid nonce', 403);
    }

    $query = sanitize_text_field($request->get_param('query'));
    $post_types = !empty($request->get_param('post_types')) ? sanitize_text_field($request->get_param('post_types')) : ['post'];
    $blockinstance = (!empty($request->get_param('blockinstance'))) ? $request->get_param('blockinstance') : '';
    $show = !empty($request->get_param('show')) ? intval($request->get_param('show')) : 5;
    $noresults = !empty($request->get_param('noresults')) ? sanitize_text_field($request->get_param('noresults')) : 'No results';

    if (empty($query)) {
        return new WP_REST_Response(array(), 200);
    }

    if (!is_array($post_types)) {
        $post_types = wp_parse_list($post_types);
    }

    $search_results = '';
    $args = array(
        'post_type' => $post_types,
        's' => $query,
        'ignore_sticky_posts' => 1,
        'posts_per_page'   => $show,
    );
    $wp_query = new WP_Query($args);
    if ($wp_query->have_posts()) {
        ob_start();
        while ($wp_query->have_posts()) {
            $wp_query->the_post();
            $block_instance = $blockinstance;
            include(GREENSHIFTQUERY_DIR_PATH . 'parts/search.php');
        }
        wp_reset_query();
        $search_results = ob_get_clean();
    }

    if (!empty($search_results)) {
        return new WP_REST_Response($search_results, 200);
    } else {
        return new WP_REST_Response($noresults, 404);
    }
}

//////////////////////////////////////////////////////////////////
// Get custom value shortcode
//////////////////////////////////////////////////////////////////

function gspb_data_post_processor($data, $postprocessor, $post_id, $replaceLabel, $icon){
    if ($postprocessor == 'textformat') {
        $data= wpautop(wptexturize($data));
    } else if ($postprocessor == 'ymd') {
        $date = DateTime::createFromFormat('Ymd', $data);
        $data= $date ? wp_date(get_option('date_format'), $date->format('U')) : $data;
    } else if ($postprocessor == 'ytmd') {
        $date = DateTime::createFromFormat('Y-m-d', $data);
        $data= $date ? wp_date(get_option('date_format'), $date->format('U')) : $data;
    } else if ($postprocessor == 'unixtowp') {
        $date = DateTime::createFromFormat('U', $data);
        $data= $date ? wp_date(get_option('date_format'), $date->format('U')) : $data;
    } else if ($postprocessor == 'ymdhis') {
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $data);
        $data= $date ? wp_date(get_option('date_format'), $date->format('U')) : $data;
    } else if ($postprocessor == 'ymdtodiff') {
        $date = strtotime($data);
        $data= $date ? human_time_diff($date, current_time('timestamp')) : $data;
    } else if ($postprocessor == 'numberformat') {
        if (is_numeric($data)) {
            $data = number_format($data);
        }
        $data= $data;
    }else if ($postprocessor == 'numeric') {
        $data = preg_replace('/[^0-9]/', '', $data);
    } else if ($postprocessor == 'mailto') {
        $mail = $data;
        if ($replaceLabel) {
            $mail = $replaceLabel;
        }
        $data= '<a href="mailto:' . $data . '">' . $mail . '</a>';
    } else if ($postprocessor == 'tel') {
        $tel = $data;
        if ($replaceLabel) {
            $tel = $replaceLabel;
        }
        $data= '<a href="tel:' . $data . '">' . $tel . '</a>';
    } else if ($postprocessor == 'postlink') {
        $postshow = $data;
        if ($replaceLabel) {
            $postshow = $replaceLabel;
        }
        $data= '<a href="' . get_permalink($post_id) . '">' . $postshow . '</a>';
    } else if ($postprocessor == 'extlink') {
        $link = $data;
        if ($replaceLabel) {
            $link = $replaceLabel;
        }
        $data= '<a href="' . esc_url($data) . '" rel="nofollow">' . $link . '</a>';
    }else if ($postprocessor == 'idtofile') {
        $data = wp_get_attachment_url((int)$data);
        $label = $data;
        if ($replaceLabel) {
            $label = $replaceLabel;
        }
        if ($data) {
            $data= '<a href="' . esc_url($data) . '">' . $label . '</a>';
        }
    }else if ($postprocessor == 'idtofileurl') {
        $data = wp_get_attachment_url((int)$data);
    }else if ($postprocessor == 'idtoimage') {
        $data = wp_get_attachment_image((int)$data, 'full');
    }else if ($postprocessor == 'idtoimageurl') {
        $data = wp_get_attachment_image_url((int)$data, 'full');
    }  else if ($postprocessor == 'commatolist') {
    }else if ($postprocessor == 'idtoimageurlthumb') {
        $data = wp_get_attachment_image_url((int)$data, 'thumbnail');
    }else if ($postprocessor == 'idtoimageurlthumbwoo') {
        $data = wp_get_attachment_image_url((int)$data, 'woocommerce_thumbnail');
    }   else if ($postprocessor == 'commatolist') {
        if (strpos($data, ', ') !== 'false') {
            $data = explode(', ', $data);
        } else if (strpos($data, ',') !== 'false') {
            $data = explode(',', $data);
        }
        if ($icon) {
            $icon = '<span class="gspb_meta_prefix_icon">' . greenshift_render_icon_module($icon) . '</span>';
        } else {
            $icon = '';
        }
        $data= '<ul>
<li>' . $icon . implode('</li>
<li>' . $icon, $data) . '</li>
</ul>';
    } else if ($postprocessor == 'commatotags') {
        if (strpos($data, ', ') !== 'false') {
            $data = explode(', ', $data);
        } else if (strpos($data, ',') !== 'false') {
            $data = explode(',', $data);
        } else {
            $data = [$data];
        }
        if ($icon) {
            $icon = '<span class="gspb_meta_prefix_icon">' . greenshift_render_icon_module($icon) . '</span>';
        } else {
            $icon = '';
        }
        $data= '<span class="gspb_meta_result_tags"><span>' . $icon . implode('</span><span>' . $icon, $data) .
            '</span></span>';
    } else if ($postprocessor == 'json') {
        $data = json_encode($data);
    }
    return $data;
}

if (!function_exists('gspb_query_get_custom_value')) {
    function gspb_query_get_custom_value($atts, $content = null)
    {
        extract(shortcode_atts(array(
            'post_id' => NULL,
            'field' => NULL,
            'subfield' => NULL,
            'subsubfield' => NULL,
            'attrfield' => '',
            'type' => 'custom',
            'show_empty' => '',
            'prefix' => '',
            'postfix' => '',
            'replaceLabel' => '',
            'icon' => '',
            'list' => '',
            'showtoggle' => '',
            'imageMapper' => '',
            'post_type' => '',
            'repeaternumber' => '',
            'acfrepeattype' => '',
            'postprocessor' => '',
            'repeaterArray' => '',

        ), $atts));
        if (!$field && !$attrfield && !$type) return;
        $field = trim($field);
        $attrfield = trim($attrfield);
        $result = $out = '';
        $field = esc_attr($field);
        $attrfield = esc_attr($attrfield);
        if($replaceLabel){
            $replaceLabel = esc_attr($replaceLabel);
        }

        if (!$post_id) {
            global $post;
            if (is_object($post)) {
                $post_id = $post->ID;
            }
        }

        $post_id = (int)$post_id;

        if ($type == 'custom') {
            if (!$field) return;
            $result = GSPB_get_custom_field_value($post_id, $field);
        } else if ($type == 'sitename') {
            $result = get_bloginfo('name');
        } else if ($type == 'sitedescription') {
            $result = get_bloginfo('description');
        } else if ($type == 'currentyear') {
            $result = date_i18n("Y");
        } else if ($type == 'currentmonth') {
            $result = date_i18n("F");
        } else if ($type == 'today') {
            $next_day = strtotime("today", current_time('timestamp'));
            $result = wp_date(get_option('date_format'), $next_day);
        } else if ($type == 'todayplus1') {
            $next_day = strtotime("+1 day", current_time('timestamp'));
            $result = wp_date(get_option('date_format'), $next_day);
        } else if ($type == 'todayplus2') {
            $next_day = strtotime("+2 days", current_time('timestamp'));
            $result = wp_date(get_option('date_format'), $next_day);
        } else if ($type == 'todayplus3') {
            $next_day = strtotime("+3 days", current_time('timestamp'));
            $result = wp_date(get_option('date_format'), $next_day);
        } else if ($type == 'todayplus7') {
            $next_day = strtotime("+7 days", current_time('timestamp'));
            $result = wp_date(get_option('date_format'), $next_day);
        } else if ($type == 'siteoption') {
            if (!$field) return;
            $field = esc_attr($field);
            $result = GSPB_get_custom_field_value($post_id, $field, ', ', 'option');
        }else if ($type == 'acfsiteoption') {
            if (!$field) return;
            $field = esc_attr($field);
            $result = GSPB_get_custom_field_value($post_id, $field, ', ', 'acf_option');
        }  else if ($type == 'querystring') {
            if (!$field) return;
            $field = esc_attr($field);
            $result = GSPB_get_custom_field_value($post_id, $field, ', ', 'querystring');
        } else if ($type == 'transient') {
            if (!$field) return;
            $field = esc_attr($field);
            $result = GSPB_get_custom_field_value($post_id, $field, ', ', 'transient');
        } else if (($type == 'attribute' || $type == 'local' || $type == 'swatch') && function_exists('wc_get_product')) {
            if ($post_id) {
                $post_id = trim($post_id);
                $post_id = (int)$post_id;
                $product = wc_get_product($post_id);
                if (!$product) return;
            } else {
                global $product;
                if (!is_object($product)) $product = wc_get_product(get_the_ID());
                if (!$product) return;
            }
            if ($attrfield) $field = $attrfield;
            if (!empty($product)) {
                if ($type == 'swatch' && function_exists('gspbwoo_show_swatch_show')) {
                    $attribute_id = wc_attribute_taxonomy_id_by_name($field);
                    if ($attribute_id) {
                        $att = wc_get_attribute($attribute_id);
                        $result = gspbwoo_show_swatch_show($att, $product, $field);
                    }
                } else {
                    $woo_attr = $product->get_attribute(esc_html($field));
                    if (!is_wp_error($woo_attr)) {
                        $result = $woo_attr;
                    }
                }
            }
        } else if ($type == 'checkattribute' && function_exists('wc_get_product')) {
            if ($post_id) {
                $post_id = trim($post_id);
                $post_id = (int)$post_id;
                $product = wc_get_product($post_id);
                if (!$product) return;
            } else {
                global $product;
                if (!is_object($product)) $product = wc_get_product(get_the_ID());
                if (!$product) return;
            }
            if ($attrfield) $field = $attrfield;
            if (!empty($product)) {
                $woo_attr = $product->get_attribute(esc_html($field));
                if (!is_wp_error($woo_attr)) {
                    $result = $woo_attr;
                }
            }
            if (!empty($result)) {
                $content = do_shortcode($content);
                $content = preg_replace('%<p>&nbsp;\s*</p>%', '', $content);
                $content = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $content);
                return $content;
            }
            return false;
        } else if ($type == 'vendor') {
            $vendor_id = get_query_var('author');
            if (!empty($vendor_id)) {
                $result = get_user_meta($vendor_id, $field, true);
            }
        } else if ($type == 'taxonomy') {
            $terms = get_the_terms($post_id, esc_html($field));
            if ($terms && !is_wp_error($terms)) {
                $term_slugs_arr = array();
                foreach ($terms as $term) {
                    $term_slugs_arr[] = '<span class="term-' . $term->term_id . '">' . $term->name . '</span>';
                }
                $terms_slug_str = join(", ", $term_slugs_arr);
                $result = $terms_slug_str;
            }
        } else if ($type == 'taxonomylink') {
            $terms = get_the_terms($post_id, esc_html($field));
            if ($terms && !is_wp_error($terms)) {
                $term_list = array();
                foreach ($terms as $term) {
                    $term_list[] = '<a class="term-' . $term->term_id . '" href="' . get_term_link($term) . '">' . esc_html($term->name) .
                        '</a>';
                }
                $term_list_output = implode(', ', $term_list);
                $result = $term_list_output;
            }
        } else if ($type == 'archivename') {
            if (is_archive()) {
                $taxonomy_title = single_term_title('', false);
                if (!$taxonomy_title) return;
                $result = $taxonomy_title;
            } else {
                return;
            }
        } else if ($type == 'archivedescription') {
            if (is_archive()) {
                $taxonomy_desc = term_description();
                if (!$taxonomy_desc) return;
                $result = $taxonomy_desc;
            } else {
                return;
            }
        } else if ($type == 'archivecount') {
            if (is_archive()) {
                $taxonomy = get_queried_object();
                if (!is_object($taxonomy)) return;
                $taxonomy_count = $taxonomy->count;
                if (!$taxonomy_count) return;
                $result = $taxonomy_count;
            } else {
                return;
            }
        } else if ($type == 'archivemeta') {
            if (is_archive()) {
                if (!$field) return;
                $field = esc_attr($field);
                $taxonomy = get_queried_object();
                $taxonomy_value = get_term_meta($taxonomy->term_id, $field, true);
                if (!$taxonomy_value) return;
                $result = $taxonomy_value;
            } else {
                return;
            }
        } else if ($type == 'author' || $type == 'author_meta') {
            $author_id = get_post_field('post_author', $post_id);
            if (!empty($author_id)) {
                $result = get_user_meta($author_id, $field, true);
            }
        } else if ($type == 'currentuser_meta') {
            $author_id = get_current_user_id();
            if (!empty($author_id)) {
                $result = get_user_meta($author_id, $field, true);
            }
        } else if ($type == 'author_name') {
            $author_id = get_post_field('post_author', $post_id);
            if (!empty($author_id)) {
                $result = get_the_author_meta('display_name', $author_id);
            }
        } else if ($type == 'author_name_link') {
            $author_id = get_post_field('post_author', $post_id);
            if (!empty($author_id)) {
                $link = get_author_posts_url($author_id);
                $name = get_the_author_meta('display_name', $author_id);
                if ($replaceLabel) {
                    $name = $replaceLabel;
                }
                $result = '<a href=' . $link . '>' . $name . '</a>';
            }
        } else if ($type == 'author_mail_link') {
            $author_id = get_post_field('post_author', $post_id);
            if (!empty($author_id)) {
                $mail = get_the_author_meta('user_email', $author_id);
                $mailshow = $mail;
                if ($replaceLabel) {
                    $mailshow = $replaceLabel;
                }
                $result = '<a href=mailto:' . $mail . '>' . $mailshow . '</a>';
            }
        } else if ($type == 'author_description') {
            $author_id = get_post_field('post_author', $post_id);
            if (!empty($author_id)) {
                $result = get_the_author_meta('user_description', $author_id);
            }
        } else if ($type == 'currentuser_name') {
            $author_id = get_current_user_id();
            if (!empty($author_id)) {
                $result = get_the_author_meta('display_name', $author_id);
            }
        } else if ($type == 'currentuser_link') {
            $author_id = get_current_user_id();
            if (!empty($author_id)) {
                $link = get_author_posts_url($author_id);
                $name = get_the_author_meta('display_name', $author_id);
                if ($replaceLabel) {
                    $name = $replaceLabel;
                }
                $result = '<a href=' . $link . '>' . $name . '</a>';
            }
        } else if ($type == 'currentuser_description') {
            $author_id = get_current_user_id();
            if (!empty($author_id)) {
                $result = get_the_author_meta('user_description', $author_id);
            }
        } else if ($type == 'post_date') {
            $result = get_the_date('', $post_id);
        } else if ($type == 'comment_count') {
            $result = get_post_field(
                'comment_count',
                $post_id
            );
        }else if ($type == 'permalink') {
            $result = get_the_permalink($post_id);
        }else if ($type == 'post_parent_title') {
            $parentId = wp_get_post_parent_id($post_id);
            if ($parentId) {
                $result = get_the_title($parentId);
            } else {
                $result = ''; // No parent post
            }
        } else if($type == 'post_parent_link'){
            $parentId = wp_get_post_parent_id($post_id);
            if ($parentId) {
                $result = get_the_permalink($parentId);
            } else {
                $result = ''; // No parent post
            }
        } else if ($type == 'post_modified') {
            $result = get_the_modified_date('', $post_id);
        } else if ($type == 'excerpt') {
            $result = get_the_excerpt($post_id);
        }else if ($type == 'post_title') {
            $result = get_the_title($post_id);
        }else if ($type == 'post_id') {
            $result = $post_id;
        } else if ($type == 'fullcontent') {
            $result = get_the_content(null, null, $post_id);
        } else if ($type == 'fullcontentfilters') {
            if (is_admin()) {
                $result = '<p>This is the Content block, it will display all the blocks in any single post or page.</p><p>That might be a simple arrangement like consecutive paragraphs in a blog post, or a more elaborate composition that includes image galleries, videos, tables, columns, and any other block types.</p><p>If there are any Custom Post Types registered at your site, the Content block can display the contents of those entries as well.</p>';
            } else if(is_singular()) {
                $result = apply_filters('the_content', get_the_content(null, false, $post_id));
            }
        } else if ($type == 'date') {
            if ($field == 'year') {
                return date_i18n("Y");
            } else if ($field == 'month') {
                return date_i18n("F");
            }
        } else if ($type == 'attributelink') {
            if ($attrfield) $field = $attrfield;
            if (function_exists('wc_get_product_terms')) {
                $attribute_values = wc_get_product_terms($post_id, $field, array('fields' => 'all'));
                $values = array();
                foreach ($attribute_values as $attribute_value) {
                    $value_name = esc_html($attribute_value->name);
                    if ($replaceLabel) {
                        $value_name = $replaceLabel;
                    }
                    $values[] = '<a href="' . esc_url(get_term_link($attribute_value->term_id, $field)) . '" rel="tag">' . $value_name .
                        '</a>';
                }
                $result = implode(', ', $values);
            }
        } else if ($type == 'checkmeta') {
            $result = get_post_meta($post_id, $field, true);
            if (!empty($result)) {
                $content = do_shortcode($content);
                $content = preg_replace('%<p>&nbsp;\s*</p>%', '', $content);
                $content = preg_replace('/^(?:<br\s*\ /?>\s*)+/', '', $content);
                return $content;
            }
            return false;
        } else if ($type == 'acfmulti' && function_exists('get_field')) {
            $result = get_field($field, $post_id);
            if (!empty($result) && is_array($result)) {
                $result = implode(', ', $result);
            }
        } else if ($type == 'acf_file' && function_exists('get_field')) {
            $result = get_field($field, $post_id);
            if (!empty($result) && is_array($result)) {
                $url = $result['url'];
                $title = $result['filename'];
                $result = '<a href="' . $url . '">' . $title . '</a>';
            }
        } else if ($type == 'acf_file_title' && function_exists('get_field')) {
            $result = get_field($field, $post_id);
            if (!empty($result) && is_array($result)) {
                $url = $result['url'];
                $title = $result['title'];
                $result = '<a href="' . $url . '">' . $title . '</a>';
            }
        } else if ($type == 'acfimage' && function_exists('get_field')) {
            if (!$field) return;
            $result = get_field($field, $post_id);
            if (!empty($result) && is_array($result)) {
                $id = $result['id'];
            } else {
                $id = $result;
            }
            if (is_numeric($id)) {
                $result = wp_get_attachment_image($id, 'full');
            } else {
                $result = '<img src=' . $id . ' />';
            }
            return $result;
        } else if ($type == 'acfrepeater' && function_exists('get_field')) {
            $result = get_field($field, $post_id);
            if (!empty($result) && !empty($subfield) && is_array($result)) {
                $rownumber = $repeaternumber ? intval($repeaternumber - 1) : 0;
                $result = $result[$rownumber][$subfield];
                if (!empty($subsubfield)) {
                    $result = $result[$rownumber][$subfield][$subsubfield];
                }
                if (!empty($result) && $acfrepeattype == 'multi') {
                    $result = implode(', ', $result);
                } else if (!empty($result) && $acfrepeattype == 'image') {
                    if (!empty($result) && is_array($result)) {
                        $id = $result['id'];
                    } else {
                        $id = $result;
                    }
                    if (is_numeric($id)) {
                        $result = wp_get_attachment_image($id, 'full');
                    } else {
                        $result = '<img src=' . $id . ' />';
                    }
                } else if (is_array($result)) {
                    $result = $result[0];
                }
            }
            return $result;
        } else if ($type == 'acfrepeatertable' && function_exists('get_field')) {
            $getrepeatable = get_field($field, $post_id);
            //print_r($getrepeatable);
            if (!empty($getrepeatable) && is_array($getrepeatable)) {
                $firstrow = $getrepeatable[0];
                $titlearray = array();
                $rowcount = 0;
                while (have_rows($field, $post_id)) : the_row();
                    $rowcount++;
                    if ($rowcount == 1) {
                        foreach ($firstrow as $rowkey => $rowvalue) {
                            $current = get_sub_field_object($rowkey);
                            $titlearray[] = $current['label'];
                        }
                    }
                endwhile;
                $result = '<table>';
                $result .= '<tr>';
                foreach ($titlearray as $title) {
                    $result .= '<th>' . $title . '</th>';
                }
                $result .= '</tr>';
                foreach ($getrepeatable as $item => $value) {
                    $result .= '<tr>';
                    foreach ($value as $field) {
                        $result .= '<td>';
                        if (is_array($field)) {
                            if (!empty($field['id'])) {
                                $result .= wp_get_attachment_image($field['id'], 'full');
                            } else {
                                $result .= implode(', ', $field);
                            }
                        } else {
                            $result .= $field;
                        }
                        $result .= '</td>';
                    }
                    $result .= '</tr>';
                }
                $result .= '</table>';
            }
            return $result;
        } else if ($type == 'repeater') {
            if (!empty($repeaterArray) && !empty($field)) {
                $result = GSPB_get_value_from_array_field($field, $repeaterArray);
                $result = GSPB_field_array_to_value($result, ', ');
            }
        } else {
            $result = GSPB_get_custom_field_value($post_id, $field);
        }
        if ($type != 'acfmulti' && $type != 'acfimage' && $type != 'acfrepeater' && $type != 'acfrepeatertable') {
            if (!empty($subfield) && !empty($subsubfield) && is_array($result)) {
                $result = $result[$subfield][$subsubfield];
            } else if (!empty($subfield) && is_array($result)) {
                $result = $result[$subfield];
            } else if (is_array($result) && !empty($result[0])) {
                $result = $result[0];
            }
        }
        if ($result || $result == '0') {
            if ($icon && $postprocessor != 'commatolist' && $postprocessor != 'commatotags') {
                $out .= '<span class="gspb_meta_prefix_icon">' . greenshift_render_icon_module($icon) . '</span>';
            }
            if ($prefix) {
                $out .= '<span class="gspb_meta_prefix">' . esc_attr($prefix) . '</span> ';
            }
            if ($showtoggle) {
                $out .= '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="30" height="30" fill="green">
            <path
                d="M504 256c0 136.967-111.033 248-248 248S8 392.967 8 256 119.033 8 256 8s248 111.033 248 248zM227.314 387.314l184-184c6.248-6.248 6.248-16.379 0-22.627l-22.627-22.627c-6.248-6.249-16.379-6.249-22.628 0L216 308.118l-70.059-70.059c-6.248-6.248-16.379-6.248-22.628 0l-22.627 22.627c-6.248 6.248-6.248 16.379 0 22.627l104 104c6.249 6.249 16.379 6.249 22.628.001z" />
        </svg>';
            } else {
                if($type == 'fullcontentfilters' || $type == 'fullcontent' || $type == 'excerpt'){
                    $out .= '<div class="gspb_meta_value">';
                }else{
                    $out .= '<span class="gspb_meta_value">';
                }
                $key = '';
                if (!empty($imageMapper)) {
                    $key = array_search($result, $imageMapper);
                    if ($key) {
                        $out .= wp_get_attachment_image((int)$key, 'full');
                    }
                }
                if (!$key) {
                    if($postprocessor){
                        if (is_array($result) && count($result) == count($result, COUNT_RECURSIVE)) {
                            $result = implode(', ', $result);
                        }
                        $result = gspb_data_post_processor($result, $postprocessor, $post_id, $replaceLabel, $icon);
                        $out .= $result;
                    }
                    else {
                        if (is_array($result) && count($result) == count($result, COUNT_RECURSIVE)) {
                            $result = implode(', ', $result);
                        }
                        if($replaceLabel){
                            $result = $replaceLabel;
                        }
                        $out .= $result;
                    }
                }
                if($type == 'fullcontentfilters' || $type == 'fullcontent' || $type == 'excerpt'){
                    $out .= '</div>';
                }else{
                    $out .= '</span>';
                }
            }

            if ($postfix) {
                $out .= '<span class="gspb_meta_postfix">' . esc_attr($postfix) . '</span> ';
            }
        } else {
            if ($show_empty) {
                if ($icon) {
                    $out .= '<span class="gspb_meta_prefix_icon">' . greenshift_render_icon_module($icon) . '</span>';
                }
                if ($prefix) {
                    $out .= '<span class="gspb_meta_prefix">' . esc_attr($prefix) . '</span> ';
                }
                if ($showtoggle) {
                    $out .= '<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 512 512" fill="red">
            <path
                d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 448c-110.5 0-200-89.5-200-200S145.5 56 256 56s200 89.5 200 200-89.5 200-200 200zm101.8-262.2L295.6 256l62.2 62.2c4.7 4.7 4.7 12.3 0 17l-22.6 22.6c-4.7 4.7-12.3 4.7-17 0L256 295.6l-62.2 62.2c-4.7 4.7-12.3 4.7-17 0l-22.6-22.6c-4.7-4.7-4.7-12.3 0-17l62.2-62.2-62.2-62.2c-4.7-4.7-4.7-12.3 0-17l22.6-22.6c4.7-4.7 12.3-4.7 17 0l62.2 62.2 62.2-62.2c4.7-4.7 12.3-4.7 17 0l22.6 22.6c4.7 4.7 4.7 12.3 0 17z" />
        </svg>';
                } else {
                    $out .= '-';
                }
            }
        }
        return $out;
    }
}

function gspb_get_image_sizes()
{
    $res = [];
    foreach (wp_get_registered_image_subsizes() as $key => $val) {
        $res[] = ['label' => $key, 'value' => $key];
    }
    $res[] = ['label' => 'full', 'value' => 'full'];
    return $res;
}

function gspb_get_post_search(WP_REST_Request $request)
{
    $search = sanitize_text_field($request->get_param('search'));
    $post_type = sanitize_text_field($request->get_param('post_type'));

    $args = array(
        'post_type' => $post_type,
        's' => $search
    );

    $query = new WP_Query($args);

    $res = [];
    foreach ($query->posts as $_post) {
        $res[] = ['id' => $_post->ID, 'title' => ['rendered' => $_post->post_title]];
    }
    return $res;
}

function gspb_update_template_replace(WP_REST_Request $request)
{
    try {
        $type = sanitize_text_field($request->get_param('type'));
        $id = sanitize_text_field($request->get_param('id'));
        $gspb_taxonomy_value = sanitize_text_field($request->get_param('gspb_taxonomy_value'));
        $gspb_tax_slug = $request->get_param('gspb_tax_slug');
        $gspb_tax_slug_exclude = $request->get_param('gspb_tax_slug_exclude');
        $gspb_roles_list = $request->get_param('gspb_roles_list');
        $gspb_posttype_archive = sanitize_text_field($request->get_param('gspb_posttype_archive'));
        $gspb_singular = sanitize_text_field($request->get_param('gspb_singular'));
        $gspb_singular_filter_by = sanitize_text_field($request->get_param('gspb_singular_filter_by'));
        $gspb_singular_ids = $request->get_param('gspb_singular_ids');

        $defaults = get_option('gspb_template_replace');
        if ($defaults === false) {
            $defaults = [];
        }
        $defaults[$id]['type'] = $type;
        $defaults[$id]['gspb_taxonomy_value'] = $gspb_taxonomy_value;
        $defaults[$id]['gspb_tax_slug'] = $gspb_tax_slug;
        $defaults[$id]['gspb_tax_slug_exclude'] = $gspb_tax_slug_exclude;
        $defaults[$id]['gspb_roles_list'] = $gspb_roles_list;
        $defaults[$id]['gspb_posttype_archive'] = $gspb_posttype_archive;
        $defaults[$id]['gspb_singular'] = $gspb_singular;
        $defaults[$id]['gspb_singular_filter_by'] = $gspb_singular_filter_by;
        $defaults[$id]['gspb_singular_ids'] = $gspb_singular_ids;

        update_option('gspb_template_replace', $defaults);

        return json_encode(array(
            'success' => true,
            'message' => 'Template updated!',
        ));
    } catch (Exception $e) {
        return json_encode(array(
            'success' => false,
            'message' => $e->getMessage(),
        ));
    }
}

function gspb_get_post_by(WP_REST_Request $request)
{
    $postId = intval($request->get_param('post_id'));
    $post_type = sanitize_text_field($request->get_param('post_type'));
    $_post = gspb_get_post_object_by_id($postId, $post_type);
    return [['id' => $_post->ID, 'title' => ['rendered' => $_post->post_title]]];
}

function gspb_get_post_parts_callback(WP_REST_Request $request)
{
    $postId = intval($request->get_param('post_id'));
    $part = sanitize_text_field($request->get_param('part'));
    $post_type = sanitize_text_field($request->get_param('post_type'));
    $image_size = sanitize_text_field($request->get_param('image_size'));
    $additional = sanitize_text_field($request->get_param('additional'));
    $additional2 = sanitize_text_field($request->get_param('additional2'));
    $field = sanitize_text_field($request->get_param('field'));

    if ($postId == 0 && $post_type && $part != 'site_data' && $part != 'acfsiteoption') {
        if ($part == 'taxonomy_field_get_text' || $part == 'taxonomy_field_get_image') {
            $taxonomies = get_terms(array('taxonomy' => $post_type, 'hide_empty' => false, 'number' => 1));
            $postId = $taxonomies[0]->term_id;
        } else {
            $args = array(
                'post_type' => $post_type,
                'posts_per_page' => 1,
                'post_status' => 'publish',
                'ignore_sticky_posts' => true,
                'orderby' => 'date',
                'order' => 'DESC',
                'no_found_rows' => true,
            );
            $latest_cpt = get_posts($args);
            if (!empty($latest_cpt)) {
                $postId = $latest_cpt[0]->ID;
            }
        }
    }

    $result = '';
    switch ($part) {
        case 'title':
            $result = !empty(get_the_title($postId)) ? get_the_title($postId) : __(
                'Post don\'t has a title.',
                'greenshiftquery'
            );
            break;
        case 'featured_image':
            $result = get_the_post_thumbnail($postId, $image_size);
            $result = !empty($result) ? $result : '<svg width="500" height="500" class="gspb_svg_placeholder"
            viewBox="0 0 500 500" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="500" height="500" fill="transparent" />
            <g clip-path="url(#clip0_1521_865)">
                <path d="M196 262.573L226.452 231.96L285.38 291" stroke="black" />
                <path
                    d="M271.8 230.867C271.8 236.63 267.142 241.3 261.4 241.3C255.658 241.3 251 236.63 251 230.867C251 225.103 255.658 220.433 261.4 220.433C267.142 220.433 271.8 225.103 271.8 230.867Z"
                    stroke="black" />
                <path d="M259.22 264.76L282.11 241.8L305 265.853" stroke="black" />
            </g>
            <rect x="196.5" y="209.5" width="108" height="81" stroke="black" />
            <defs>
                <clipPath id="clip0_1521_865">
                    <rect x="196" y="209" width="109" height="82" fill="white" />
                </clipPath>
            </defs>
        </svg>';
            break;
        case 'meta_field_image':
            $result = GSPB_get_custom_field_value($postId, $field, 'no');
            if (is_numeric($result)) {
                $result = wp_get_attachment_image($result, $image_size);
                return json_encode($result);
            }
            $result = !empty($result) ? '<img src="' . esc_url($result) . '" />' : '<svg width="500" height="500"
            class="gspb_svg_placeholder" viewBox="0 0 500 500" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="500" height="500" fill="transparent" />
            <g clip-path="url(#clip0_1521_865)">
                <path d="M196 262.573L226.452 231.96L285.38 291" stroke="black" />
                <path
                    d="M271.8 230.867C271.8 236.63 267.142 241.3 261.4 241.3C255.658 241.3 251 236.63 251 230.867C251 225.103 255.658 220.433 261.4 220.433C267.142 220.433 271.8 225.103 271.8 230.867Z"
                    stroke="black" />
                <path d="M259.22 264.76L282.11 241.8L305 265.853" stroke="black" />
            </g>
            <rect x="196.5" y="209.5" width="108" height="81" stroke="black" />
            <defs>
                <clipPath id="clip0_1521_865">
                    <rect x="196" y="209" width="109" height="82" fill="white" />
                </clipPath>
            </defs>
        </svg>';
            break;
        case 'meta_field_image_by_id':
            if (is_numeric($postId)) {
                $result = wp_get_attachment_image($postId, $image_size);
                return json_encode($result);
            }
            break;
        case 'featured_image_get':
            $result = get_the_post_thumbnail_url($postId, $image_size);
            $result = !empty($result) ? $result : '';
            break;
        case 'meta_field_gallery':
            if ($additional == 'array_ids') {
                $result = [];
                $galleryIdArray = get_post_meta($postId, $field, true);
                if (!is_array($galleryIdArray) && strpos($galleryIdArray, ',') !== false) $galleryIdArray =
                    wp_parse_list($galleryIdArray);
                if (!is_array($galleryIdArray)) $galleryIdArray = get_post_meta($postId, $field, false);
                if (!empty($galleryIdArray)) {
                    foreach ($galleryIdArray as $image_id) {
                        $imgsrc = wp_get_attachment_url($image_id);
                        $result[] = $imgsrc;
                    }
                }
            } else if ($additional == 'array_urls') {
                $galleryData = get_post_meta($postId, $field, true);
                if (is_array($galleryData) && !empty($galleryData)) {
                    $result = $galleryData;
                } else if (!is_array($galleryData) && !empty($galleryData)) {
                    $result = wp_parse_list($galleryData);
                }
            } else if ($additional == 'acf_gallery') {
                if (function_exists('get_field')) {
                    $result = [];
                    $galleryIdArray = get_field($field, $postId);
                    if (!empty($galleryIdArray)) {
                        foreach ($galleryIdArray as $image_id) {
                            if (is_array($image_id)) {
                                $imgsrc = $image_id['url'];
                            } else if (is_numeric($image_id)) {
                                $imgsrc = wp_get_attachment_url($image_id);
                            } else {
                                $imgsrc = $image_id;
                            }
                            $result[] = $imgsrc;
                        }
                    }
                }
            } else if ($additional == 'post_images') {
                $result = [];
                $galleryIdArray = get_attached_media('image', $postId);
                if (!empty($galleryIdArray)) {
                    foreach ($galleryIdArray as $image_id) {
                        $result[] = $image_id['guid'];
                    }
                }
            }
            if ($additional2 === 'true') {
                $im = get_the_post_thumbnail_url($postId, 'full');
                if ($im) {
                    array_unshift($result, get_the_post_thumbnail_url($postId, 'full'));
                }
            }
            $result = !empty($result) ? $result : '';
            break;
        case 'meta_field_gallery_repeater':
            $result = [];
            $galleryIdArray = $field;
            if (!empty($galleryIdArray)) {
                if (!is_array($galleryIdArray)) {
                    $galleryIdArray = wp_parse_list($galleryIdArray);
                }
                if (is_array($galleryIdArray)) {
                    foreach ($galleryIdArray as $image_id) {
                        if (is_array($image_id)) {
                            $imgsrc = $image_id['url'];
                        } else if (is_numeric($image_id)) {
                            $imgsrc = wp_get_attachment_url($image_id);
                        } else {
                            $imgsrc = $image_id;
                        }
                        $result[] = $imgsrc;
                    }
                }
            }
            if ($additional2 === 'true') {
                $im = get_the_post_thumbnail_url($postId, 'full');
                if ($im) {
                    array_unshift($result, get_the_post_thumbnail_url($postId, 'full'));
                }
            }
            $result = !empty($result) ? $result : '';
            break;
        case 'meta_repeater':
            $result = [];
            if ($additional == 'acf' && function_exists('get_field')) {
                $getrepeatable = get_field($field, $postId);
            } else if ($additional == 'relationpostobj' || $additional == 'relationpostids') {
                if (function_exists('get_field')) {
                    $getrepeatable = get_field($field, $postId);
                } else {
                    $getrepeatable = GSPB_get_custom_field_value($postId, $field, 'flatarray');
                }
                if ($additional == 'relationpostids' || !is_array($getrepeatable)) {
                    if (!empty($getrepeatable) && !is_array($getrepeatable)) {
                        $ids = wp_parse_id_list($getrepeatable);
                    } else {
                        $ids = $getrepeatable;
                    }
                    if (!empty($ids)) {
                        $args = array(
                            'post__in' => $ids,
                            'numberposts' => '-1',
                            'orderby' => 'post__in',
                            'ignore_sticky_posts' => 1,
                            'post_type' => 'any',
                        );
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
            } else if ($additional == 'taxonomy') {
                $filters = json_decode($field, true);
                $taxonomy = !empty($filters['taxonomy']) ? esc_attr($filters['taxonomy']) : 'category';
                if (!empty($filters['show_current'])) {
                    if (is_tax() || is_category() || is_tag()) {
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
                    } else {
                        $getrepeatable = get_the_terms($postId, $taxonomy);
                    }
                } else {
                    $args = ['parent' => 0];
                    if (!empty($filters['include'])) {
                        $args['include'] = sanitize_text_field($filters['include']);
                    }
                    if (!empty($filters['exclude'])) {
                        $args['exclude'] = sanitize_text_field($filters['exclude']);
                    }
                    if (!empty($filters['orderby'])) {
                        $args['orderby'] = sanitize_text_field($filters['orderby']);
                    }
                    if (!empty($filters['show_empty'])) {
                        $args['hide_empty'] = false;
                    }
                    if (!empty($filters['order']) && $filters['order'] == 'DESC') {
                        $args['order'] = sanitize_text_field($filters['order']);
                    }
                    if (!empty($filters['meta_key'])) {
                        $args['meta_key'] = sanitize_text_field($filters['meta_key']);
                        if (!empty($filters['meta_value'])) {
                            $args['meta_value'] = sanitize_text_field($filters['meta_value']);
                        }
                        if (!empty($filters['meta_compare'])) {
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
                    if (!empty($filters['cross_enabled'])) {
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
                            if (!empty($posts_by_term) && !empty($posts_by_term[$value->term_id])) {
                                $terms[$key]['crossposts'] = $posts_by_term[$value->term_id];
                            }
                        }
                    }
                    $getrepeatable = $terms;
                }
            } else if($additional == 'taxonomymeta'){
                $filters = json_decode($field, true);
				$taxonomy = !empty($filters['taxonomy']) ? $filters['taxonomy'] : 'category';
				$dynamicField = !empty($filters['taxonomy_meta_field']) ? $filters['taxonomy_meta_field'] : '';
                $taxonomies = get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => false, 'number' => 1));
                if(!empty($taxonomies) && is_array($taxonomies)){
                    $termId = $taxonomies[0]->term_id;
                }else{
                    $termId = 0;
                }
				if($termId){
					$getrepeatable = GSPB_get_custom_field_value($termId, $dynamicField, 'flatarray', 'taxonomymetabyid');
				}else{
					$getrepeatable = [];
				}
            } else if ($additional == 'users') {
                $filters = json_decode($field, true);
                $source = !empty($filters['source']) ? $filters['source'] : 'ids';
                $args = [];
                if ($source == 'ids') {
                    if (!empty($filters['ids'])) {
                        $ids = wp_parse_id_list($filters['ids']);
                        if (!empty($ids)) {
                            $args['include'] = $ids;
                        }
                    }
                } else if ($source == 'role') {
                    if (!empty($filters['role'])) {
                        $args['role'] = trim($filters['role']);
                    }
                } else if ($source == 'field') {
                    if (!empty($filters['field'])) {
                        $field = trim($filters['field']);
                        $field = GSPB_make_dynamic_from_metas($filters['field'], $postId);
                        $ids = wp_parse_id_list($field);
                        if (!empty($ids)) {
                            $args['include'] = $ids;
                        }
                    }
                } else if ($source == 'current') {
                    $args['include'] = get_current_user_id();
                } else if ($source == 'author') {
                    $args['include'] = get_post_field('post_author', $postId);
                }
                if (!empty($filters['orderby'])) {
                    $args['orderby'] = sanitize_text_field($filters['orderby']);
                }
                if (!empty($filters['order']) && $filters['order'] == 'DESC') {
                    $args['order'] = sanitize_text_field($filters['order']);
                }
                if (!empty($filters['meta_key'])) {
                    $args['meta_key'] = sanitize_text_field($filters['meta_key']);
                    if (!empty($filters['meta_value'])) {
                        $args['meta_value'] = sanitize_text_field($filters['meta_value']);
                    }
                    if (!empty($filters['meta_compare'])) {
                        $args['meta_compare'] = sanitize_text_field($filters['meta_compare']);
                    }
                }
                if (!empty($filters['number'])) {
                    $args['number'] = (int)$filters['number'];
                } else {
                    $args['number'] = 12;
                }
                if (!empty($args)) {
                    $users = get_users($args);
                    if (!empty($users)) {
                        $items = [];
                        $posts_by_user = [];
                        if (!empty($filters['cross_enabled'])) {
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
                            $items[$key]['avatar'] = get_avatar_url($value->ID, ['size' => !empty($filters['size']) ? (int)$filters['size']
                                : 96]);
                            $items[$key]['display_name'] = $value->display_name;
                            $items[$key]['user_email'] = $value->user_email;
                            $items[$key]['user_url'] = $value->user_url;
                            $items[$key]['user_registered'] = $value->user_registered;
                            $items[$key]['roles'] = GSPB_field_array_to_value($value->roles, ', ');
                            $items[$key]['description'] = $value->description;
                            $items[$key]['user_nicename'] = $value->user_nicename;

                            $custom_fields = get_user_meta($value->ID);
                            foreach ($custom_fields as $fieldindex => $fieldvalue) {
                                if (
                                    $fieldindex == 'rich_editing' || $fieldindex == 'syntax_highlighting' || $fieldindex == 'comment_shortcuts'
                                    || $fieldindex == 'admin_color' || $fieldindex == 'show_admin_bar_front' || $fieldindex == 'use_ssl' ||
                                    $fieldindex == 'show_welcome_panel' || $fieldindex == 'locale' || $fieldindex == 'wp_capabilities' ||
                                    $fieldindex == 'wp_user_level' || $fieldindex == 'dismissed_wp_pointers' || $fieldindex == 'session_tokens' ||
                                    $fieldindex == 'wp_dashboard_quick_press_last_post_id' || $fieldindex == 'wp_user-settings-time' || $fieldindex
                                    == 'wp_capabilities' || $fieldindex == 'wp_user-settings' || $fieldindex == 'wp_user-settings-time'
                                ) {
                                    continue;
                                }
                                if (is_serialized($fieldvalue[0])) {
                                    $fieldvalue[0] = maybe_unserialize($fieldvalue[0]);
                                }
                                if (!empty($fieldvalue[0])) {
                                    $items[$key][$fieldindex] = GSPB_field_array_to_value($fieldvalue[0], 'no');
                                }
                            }
                            if (!empty($posts_by_user) && !empty($posts_by_user[$value->ID])) {
                                $items[$key]['crossposts'] = $posts_by_user[$value->ID];
                            }
                        }
                    } else {
                        $items = [];
                    }
                } else {
                    $items = [];
                }
                $getrepeatable = $items;
            } else if ($additional == 'comments') {
                $filters = json_decode($field, true);
                $source = !empty($filters['source']) ? $filters['source'] : '';
                $args = [];
                if ($source == 'ids') {
                    if (!empty($filters['ids'])) {
                        $ids = wp_parse_id_list($filters['ids']);
                        if (!empty($ids)) {
                            $args['comment__in'] = $ids;
                        }
                    }
                } else if ($source == 'post_ids') {
                    if (!empty($filters['ids'])) {
                        $ids = wp_parse_id_list($filters['ids']);
                        if (!empty($ids)) {
                            $args['post__in'] = $ids;
                        }
                    }
                } else if ($source == 'author_ids') {
                    if (!empty($filters['ids'])) {
                        $ids = wp_parse_id_list($filters['ids']);
                        if (!empty($ids)) {
                            $args['author__in'] = $ids;
                        }
                    }
                }
                if (!empty($filters['orderby'])) {
                    $args['orderby'] = sanitize_text_field($filters['orderby']);
                }
                if (!empty($filters['order']) && $filters['order'] == 'DESC') {
                    $args['order'] = sanitize_text_field($filters['order']);
                }
                if (!empty($filters['meta_key'])) {
                    $args['meta_key'] = sanitize_text_field($filters['meta_key']);
                    if (!empty($filters['meta_value'])) {
                        $args['meta_value'] = sanitize_text_field($filters['meta_value']);
                    }
                    if (!empty($filters['meta_compare'])) {
                        $args['meta_compare'] = sanitize_text_field($filters['meta_compare']);
                    }
                }
                if (!empty($filters['number'])) {
                    $args['number'] = (int)$filters['number'];
                } else {
                    $args['number'] = 12;
                }
                if (!empty($filters['post_type'])) {
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
                        $items[$key]['date_formatted'] = date_i18n(get_option('date_format'), strtotime($value->comment_date));;
                        $items[$key]['post_permalink'] = get_the_permalink($post_id);
                        $items[$key]['post_title'] = get_the_title($post_id);

                        $items[$key]['author'] = $value->comment_author;
                        $items[$key]['author_email'] = $value->comment_author_email;
                        $items[$key]['author_url'] = $value->comment_author_url;
                        $items[$key]['author_IP'] = $value->comment_author_IP;

                        $items[$key]['avatar'] = get_avatar_url($author_id, ['size' => !empty($filters['size']) ? (int)$filters['size']
                            : 96]);

                        $custom_fields = get_comment_meta($value->comment_ID);
                        foreach ($custom_fields as $fieldindex => $fieldvalue) {
                            if (
                                $fieldindex == 'rich_editing' || $fieldindex == 'syntax_highlighting' || $fieldindex == 'comment_shortcuts'
                                || $fieldindex == 'admin_color' || $fieldindex == 'show_admin_bar_front' || $fieldindex == 'use_ssl' ||
                                $fieldindex == 'show_welcome_panel' || $fieldindex == 'locale' || $fieldindex == 'wp_capabilities' ||
                                $fieldindex == 'wp_user_level' || $fieldindex == 'dismissed_wp_pointers' || $fieldindex == 'session_tokens' ||
                                $fieldindex == 'wp_dashboard_quick_press_last_post_id' || $fieldindex == 'wp_user-settings-time' || $fieldindex
                                == 'wp_capabilities' || $fieldindex == 'wp_user-settings' || $fieldindex == 'wp_user-settings-time'
                            ) {
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
                } else {
                    $items = [];
                }
                $getrepeatable = $items;
            } else if ($additional == 'ce') {
                if (
                    defined('\ContentEgg\PLUGIN_PATH') &&
                    class_exists('\ContentEgg\application\components\GreenshiftIntegrator')
                ) {
                    $filters = json_decode($field, true);
                    $getrepeatable = \ContentEgg\application\components\GreenshiftIntegrator::getProductData($postId, $filters);
                    if (!empty($getrepeatable)) {
                        if (!is_array($getrepeatable)) {
                            $getrepeatable = [$getrepeatable];
                        }
                        $items = [];
                        $allowed_fields = \ContentEgg\application\components\GreenshiftIntegrator::getAllowedProductFields();
                        foreach ($getrepeatable as $key => $value) {
                            if (!empty($value) && is_array($value) && is_array($allowed_fields)) {
                                $filtered = wp_array_slice_assoc($value, $allowed_fields);
                                if (!empty($filtered)) {
                                    $items[$key] = $filtered;
                                }
                            }
                        }
                        $getrepeatable = $items;
                    }
                } else {
                    $getrepeatable = [];
                }
            } else if ($additional == 'direct') {
                $getrepeatable = $field;
            } else if ($additional == 'acpt') {
                $getrepeatable = GSPB_get_custom_field_value($postId, $field, 'flatarray');
                if (!empty($getrepeatable) && is_array($getrepeatable)) {
                    $getrepeatable = gspb_acptConvertArray($getrepeatable);
                }
            } else if ($additional == 'site_data') {
                $filters = json_decode($field, true);
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
            } else if ($additional == 'acfsiteoption' && function_exists('get_field')) {
                $filters = json_decode($field, true);
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
            } else if ($additional == 'transient') {
                $filters = json_decode($field, true);
                $option_name = !empty($filters['option_name']) ? $filters['option_name'] : '';
                $getrepeatable = get_transient($option_name);
                if (!is_array($getrepeatable)) {
                    $getrepeatable = [];
                }
            } else if ($additional == 'metajson') {
                $getrepeatable = GSPB_get_custom_field_value($postId, $field, 'no');
                if (!empty($getrepeatable)) {
                    $getrepeatable = json_decode($getrepeatable, true);
                }
                if (!is_array($getrepeatable)) {
                    $getrepeatable = [];
                }
            } else if ($additional == 'api_request') {
                $cache = false;
                $field = greenshift_dynamic_placeholders($field, [], 0);
                $filters = json_decode(urldecode($field), true);
                $apiUrl = !empty($filters['apiUrl']) ? $filters['apiUrl'] : '';
				$apiUrlcache = !empty($filters['apiUrlcache']) ? $filters['apiUrlcache'] : '';
				$apiUrltransient = !empty($filters['apiUrltransient']) ? $filters['apiUrltransient'] : '';
				if(!empty($apiUrlcache) && !empty($apiUrltransient)){
					$transient_name = trim($apiUrltransient);
					$transient_value = get_transient($transient_name);
					if (false === $transient_value) {
						$getrepeatable = \gspb_api_connector_run($filters);
					}else{
						$getrepeatable = $transient_value;
						$cache = true;
					}
				}else{
					if(!empty($apiUrl)){
						$getrepeatable = \gspb_api_connector_run($filters);
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
							'excerpt',
                            'featured_media'
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
            } else if ($additional == 'query_args') {
				$getrepeatable = [];
				$filters = json_decode(urldecode($field), true);
				$filters = greenshiftquery_sanitize_multi_array($filters);
				$query_args = !empty($filters) ? $filters : [];
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
            } else if ($additional == 'idstourl') {
                $getrepeatable = GSPB_get_custom_field_value($postId, $field, 'flatarray');
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
            } else {
                $getrepeatable = GSPB_get_custom_field_value($postId, $field, 'flatarray');
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
            $result = !empty($result) ? $result : [
                [
                    'title' => 'Select a field',
                    'content' => 'Test content 1',
                    'image' => GREENSHIFTQUERY_DIR_URL.'assets/placeholder.svg',
                    'link' => '#'
                ],
                [
                    'title' => 'Select a field',
                    'content' => 'Test content 2',
                    'image' => GREENSHIFTQUERY_DIR_URL.'assets/placeholder.svg',
                    'link' => '#'
                ],
                [
                    'title' => 'Select a field',
                    'content' => 'Test content 3',
                    'image' => GREENSHIFTQUERY_DIR_URL.'assets/placeholder.svg',
                    'link' => '#'
                ]
            ];
            $result = apply_filters('gspb_repeater_args_id', $result, $postId);
            break;
        case 'meta_repeater_inner':
            $result = [];
            $getrepeatable = $field;
            if ($additional == 'relationpostobj' || $additional == 'relationpostids') {
                if ($additional == 'relationpostids') {
                    if (!empty($getrepeatable) && !is_array($getrepeatable)) {
                        $ids = wp_parse_id_list($getrepeatable);
                    } else {
                        $ids = $getrepeatable;
                    }
                    if (!empty($ids)) {
                        $args = array(
                            'post__in' => $ids,
                            'numberposts' => '-1',
                            'orderby' => 'post__in',
                            'ignore_sticky_posts' => 1,
                            'post_type' => 'any'
                        );
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
            if (!empty($getrepeatable) && is_array($getrepeatable)) {
                $result = $getrepeatable;
            }
            $result = !empty($result) ? $result : '';
            break;
        case 'site_data':
            if ($field == 'name' || $field == 'description') {
                $result = get_bloginfo($field);
            } else if ($field == 'year') {
                $result = date_i18n("Y");
            } else if ($field == 'month') {
                $result = date_i18n("F");
            } else if ($field == 'today') {
                $next_day = strtotime("today", current_time('timestamp'));
                $result = wp_date(get_option('date_format'), $next_day);
            } else if ($field == 'todayplus1') {
                $next_day = strtotime("+1 day", current_time('timestamp'));
                $result = wp_date(get_option('date_format'), $next_day);;
            } else if ($field == 'todayplus2') {
                $next_day = strtotime("+2 days", current_time('timestamp'));
                $result = wp_date(get_option('date_format'), $next_day);;
            } else if ($field == 'todayplus3') {
                $next_day = strtotime("+3 days", current_time('timestamp'));
                $result = wp_date(get_option('date_format'), $next_day);;
            } else if ($field == 'todayplus7') {
                $next_day = strtotime("+7 days", current_time('timestamp'));
                $result = wp_date(get_option('date_format'), $next_day);;
            } else if ($field == 'siteoption') {
                $field = esc_attr($field);
                $result = GSPB_get_custom_field_value(0, $field, ', ', 'option');
            } else if ($field == 'acfsiteoption') {
                $field = esc_attr($field);
                $result = GSPB_get_custom_field_value(0, $field, ', ', 'acf_option');
            }
            $result = !empty($result) ? $result : '';
            break;
        case 'post_data':
            if($field == 'post_image'){
                $size = 'full';
                if(!empty($additional2)){
                    $size = esc_attr($additional2);
                }
                $result = get_the_post_thumbnail_url($postId, $size);
            }else if($field == 'post_author'){
                $author_id = get_post_field('post_author', $postId); 
                $author_name = get_the_author_meta('display_name', $author_id);
                $result = $author_name;
            } else if ($field == 'post_date') {
                $result = get_the_date('', $postId);
            } else if ($field == 'post_modified') {
                $result = get_the_modified_date('', $postId);
            }else {
                $resultObj = get_post($postId);
                if (is_object($resultObj)) {
                    $result = $resultObj->$field;
                }
            }
            $result = !empty($result) ? $result : '';
            break;
        case 'author_data':
        case 'authordata':
            $resultObj = get_post($postId);
            if (is_object($resultObj)) $result = $resultObj->post_author;
            if ($result) {
                $result = get_the_author_meta($field, $result);
            }
            $result = !empty($result) ? $result : '';
            break;
        case 'author_meta':
            $resultObj = get_post($postId);
            if (is_object($resultObj)) $resultId = $resultObj->post_author;
            if ($resultId) {
                $result = get_user_meta($resultId, $field, true);
            }
            $result = !empty($result) ? $result : '';
            break;
        case 'user_data':
            $resultId = get_current_user_id();
            if ($resultId && $field && $resultId > 0) {
                $result = get_the_author_meta($field, $resultId);
            }
            $result = !empty($result) ? $result : '';
            break;
        case 'user_meta':
            $resultId = get_current_user_id();
            if ($resultId && $field && $resultId > 0) {
                $result = get_user_meta($resultId, $field, true);
            }
            $result = !empty($result) ? $result : '';
            break;
        case 'meta_field_get_image':
            $result = GSPB_get_custom_field_value($postId, $field, 'no');
            if (is_numeric($result)) $result = wp_get_attachment_url($result);
            $result = !empty($result) ? $result : '';
            break;
        case 'taxonomy_field_get_image':
            $result = get_term_meta($postId, $field, true);
            if (is_array($result)) $result = $result[0];
            if (is_numeric($result)) $result = wp_get_attachment_url($result);
            $result = !empty($result) ? $result : '';
            break;
        case 'taxonomy_value':
            $terms = get_the_terms($postId, $field);
            $divider = ', ';
            if ($additional) $divider = '<span class="gspb_tax_spacer">' . $additional . '</span>';
            if ($terms && !is_wp_error($terms)) {
                $term_slugs_arr = array();
                foreach ($terms as $term) {
                    $term_slugs_arr[] = '' . $term->name . '';
                }
                $terms_slug_str = join($divider, $term_slugs_arr);
                $result = $terms_slug_str;
            }
            $result = !empty($result) ? $result : '';
            break;
        case 'taxonomy_link':
            $divider = ', ';
            if ($additional) $divider = '<span class="gspb_tax_spacer">' . $additional . '</span>';
            $term_list = get_the_term_list($postId, $field, '', $divider, '');
            if (!is_wp_error($term_list)) {
                $result = $term_list;
            }
            $result = !empty($result) ? $result : '';
            break;
        case 'meta_field_get_text':
            $result = GSPB_get_custom_field_value($postId, $field);
            $result = !empty($result) ? $result : '';
            break;
        case 'meta_field_get_map':
            $result = GSPB_get_custom_field_value($postId, $field, 'flat');
            if (is_array($result)) {
                $map = [];
                if (isset($result['location']) && isset($result['location']['lat']) && isset($result['location']['lng'])) {
                    $map['lat'] = $result['location']['lat'];
                    $map['lng'] = $result['location']['lng'];
                    $map['lang'] = $result['location']['lng'];
                    $map['title'] = !empty($result['title']) ? $result['title'] : '';
                    $map['description'] = !empty($result['description']) ? $result['description'] : '';
                } else if (isset($result['lat']) && isset($result['lng'])) {
                    $map['lat'] = $result['lat'];
                    $map['lng'] = $result['lng'];
                    $map['lang'] = $result['lng'];
                } else if (isset($result['latitude']) && isset($result['longitude'])) {
                    $map['lat'] = $result['latitude'];
                    $map['lng'] = $result['longitude'];
                    $map['lang'] = $result['longitude'];
                }
                $result = $map;
            }
            $result = !empty($result) ? $result : '';
            break;
        case 'taxonomy_field_get_text':
            if ($field == 'name') {
                $taxobj = get_term($postId);
                if (!is_wp_error($taxobj)) {
                    $result = $taxobj->name;
                }
            } else if ($field == 'description') {
                $taxobj = get_term($postId);
                if (!is_wp_error($taxobj)) {
                    $result = $taxobj->description;
                }
            } else {
                $result = get_term_meta($postId, $field, true);
            }
            if (is_array($result)) $result = $result[0];
            $result = !empty($result) ? $result : '';
            break;
        default:
            break;
    }

    return json_encode($result);
}

function gspb_get_user_roles(WP_REST_Request $request)
{
    $search = sanitize_text_field($request->get_param('search'));

    global $wp_roles;

    $res = [];

    foreach ($wp_roles->roles as $key => $role) {
        if (empty($search) || strpos(strtolower($role['name']), strtolower($search)) !== false) {
            $res[] = ['label' => $role['name'], 'id' => $key, 'value' => $key];
        }
    }

    return json_encode($res);
}

function gspb_get_alpha_html(WP_REST_Request $request)
{

    $taxonomy = sanitize_text_field($request->get_param('taxonomy'));
    $show_empty = boolval(sanitize_text_field($request->get_param('show_empty')));
    $order_by = sanitize_text_field($request->get_param('order_by'));
    $order = sanitize_text_field($request->get_param('order'));
    $include = sanitize_text_field($request->get_param('include'));
    $exclude = sanitize_text_field($request->get_param('exclude'));
    $hierarchy = boolval(sanitize_text_field($request->get_param('hierarchy')));
    $show_count = sanitize_text_field($request->get_param('show_count'));

    $terms = \Greenshift\Blocks\ProductTaxonomy::get_terms(
        $taxonomy,
        $show_empty,
        $order_by,
        $order,
        $include,
        $exclude,
        $hierarchy
    );
    $alpha_html = \Greenshift\Blocks\ProductTaxonomy::alphabetical_view($terms, $taxonomy, $show_count, '');
    $json = json_encode($alpha_html);

    return $json;
}

function gspb_get_taxonomy_terms_search(WP_REST_Request $request)
{
    $taxonomy = sanitize_text_field($request->get_param('taxonomy'));
    $search = sanitize_text_field($request->get_param('search'));
    $search_id = sanitize_text_field($request->get_param('search-id'));

    global $wpdb;
    if (empty($search_id)) {
        $query = [
            "select" => "SELECT SQL_CALC_FOUND_ROWS a.term_id AS id, b.name as name, b.slug AS slug
        FROM {$wpdb->term_taxonomy} AS a
        INNER JOIN {$wpdb->terms} AS b ON b.term_id = a.term_id",
            "where" => "WHERE a.taxonomy = '{$taxonomy}'",
            "like" => "AND (b.slug LIKE '%s' OR b.name LIKE '%s' )",
            "offset" => "LIMIT %d, %d"
        ];

        $search_term = '%' . $wpdb->esc_like($search) . '%';
        $offset = 0;
        $search_limit = 100;

        $final_query = $wpdb->prepare(implode(' ', $query), $search_term, $search_term, $offset, $search_limit);
    } else {
        $search_id = rtrim($search_id, ',');
        $query = [
            "select" => "SELECT SQL_CALC_FOUND_ROWS a.term_id AS id, b.name as name, b.slug AS slug
        FROM {$wpdb->term_taxonomy} AS a
        INNER JOIN {$wpdb->terms} AS b ON b.term_id = a.term_id",
            "where" => "WHERE a.taxonomy = '{$taxonomy}'",
            "like" => "AND a.term_id IN({$search_id})",
            "offset" => "LIMIT %d, %d"
        ];

        $offset = 0;
        $search_limit = 100;

        $final_query = $wpdb->prepare(implode(' ', $query), $offset, $search_limit);
    }
    // Return saved values

    $results = $wpdb->get_results($final_query);

    $total_results = $wpdb->get_row("SELECT FOUND_ROWS() as total_rows;");
    $response_data = [];

    if ($results) {
        foreach ($results as $result) {
            $response_data[] = [
                'slug' => esc_html($result->slug),
                'name' => esc_html($result->name),
                'id' => (int)$result->id
            ];
        }
    }

    return json_encode($response_data);
}

function gspb_get_terms(WP_REST_Request $request)
{
    $taxonomy = sanitize_text_field($request->get_param('taxonomy'));
    $show_empty = boolval(sanitize_text_field($request->get_param('show_empty')));
    $order_by = sanitize_text_field($request->get_param('order_by'));
    $order = sanitize_text_field($request->get_param('order'));
    $include = sanitize_text_field($request->get_param('include'));
    $exclude = sanitize_text_field($request->get_param('exclude'));
    $hierarchy = boolval(sanitize_text_field($request->get_param('hierarchy')));
    $image_meta = sanitize_text_field($request->get_param('image_meta'));
    $number = intval($request->get_param('number'));
    $childOf = sanitize_text_field($request->get_param('childof'));
    $image_size = sanitize_text_field($request->get_param('image_size'));

    $terms = \Greenshift\Blocks\ProductTaxonomy::get_terms(
        $taxonomy,
        $show_empty,
        $order_by,
        $order,
        $include,
        $exclude,
        $hierarchy,
        false,
        false,
        $image_meta,
        $number,
        $image_size,
        $childOf
    );
    $json = json_encode($terms, JSON_FORCE_OBJECT);

    return $json;
}

function gspb_get_taxonomies(WP_REST_Request $request)
{
    $post_type = sanitize_text_field($request->get_param('post_type'));

    $result = \Greenshift\Blocks\ProductTaxonomy::get_taxonomies($post_type);

    return json_encode($result);
}

function gspb_get_all_taxonomies()
{
    $exclude_list = array_flip([
        'nav_menu',
        'link_category',
        'post_format',
        'elementor_library_type',
        'elementor_library_category',
        'action-group'
    ]);
    $response_data = [];
    $args = [];
    foreach (get_taxonomies($args, 'objects') as $taxonomy => $object) {
        if (isset($exclude_list[$taxonomy])) {
            continue;
        }

        $taxonomy = esc_html($taxonomy);
        $response_data[] = [
            'value' => $taxonomy,
            'label' => esc_html($object->label),
        ];
    }
    return json_encode($response_data);
}

function gspb_get_post_types()
{
    $post_types = get_post_types(['public' => true], 'objects');
    $result = [];

    foreach ($post_types as $post_type) {
        if (empty(get_object_taxonomies($post_type->name, 'objects'))) continue;
        $result[] = ['value' => $post_type->name, 'label' => $post_type->label];
    }

    return json_encode($result);
}

function gspb_get_post_metas(WP_REST_Request $request)
{
    $post_type = sanitize_text_field($request->get_param('post_type'));
    if (empty($post_type)) $post_type = 'post';
    $exclude_empty = false;
    $exclude_hidden = false;
    global $wpdb;
    $query = "
        SELECT DISTINCT($wpdb->postmeta.meta_key)
        FROM $wpdb->posts
        LEFT JOIN $wpdb->postmeta
        ON $wpdb->posts.ID = $wpdb->postmeta.post_id
        WHERE $wpdb->posts.post_type = '%s'
        ";
    if ($exclude_empty)
        $query .= " AND $wpdb->postmeta.meta_key != ''";
    if ($exclude_hidden)
        $query .= " AND $wpdb->postmeta.meta_key NOT RegExp '(^[_0-9].+$)' ";

    $meta_keys = $wpdb->get_col($wpdb->prepare($query, $post_type));

    $result = [];
    foreach ($meta_keys as $meta_key) {
        $result[] = ['value' => $meta_key, 'label' => $meta_key];
    }

    return json_encode($result);
}

function gspb_query_metagetapi(WP_REST_Request $request)
{
    $field = sanitize_text_field($request->get_param('field'));
    $subfield = sanitize_text_field($request->get_param('subfield'));
    $subsubfield = sanitize_text_field($request->get_param('subsubfield'));
    $postId = (int)$request->get_param('postId');
    $type = sanitize_text_field($request->get_param('type'));
    $post_type = sanitize_text_field($request->get_param('post_type'));
    $show_empty = sanitize_text_field($request->get_param('show_empty'));
    $prefix = sanitize_text_field($request->get_param('prefix'));
    $postfix = sanitize_text_field($request->get_param('postfix'));
    $replaceLabel = sanitize_text_field($request->get_param('replaceLabel'));
    $showtoggle = sanitize_text_field($request->get_param('showtoggle'));
    $repeaternumber = sanitize_text_field($request->get_param('repeaternumber'));
    $acfrepeattype = sanitize_text_field($request->get_param('acfrepeattype'));
    $postprocessor = sanitize_text_field($request->get_param('postprocessor'));
    $repeaterArray = $request->get_param('repeaterArray');
    $icon = $request->get_param('icon');

    if ($post_type && $postId == 0) {
        $latest_cpt = get_posts("post_type='.$post_type.'&numberposts=1&no_found_rows=1");
        if (!is_array($latest_cpt) || (!empty($latest_cpt) && !is_object($latest_cpt[0]))) return '';
        $postId = $latest_cpt[0]->ID;
    }

    $value = gspb_query_get_custom_value(array(
        'field' => $field,
        'subfield' => $subfield,
        'subsubfield' =>
        $subsubfield,
        'post_id' => $postId,
        'type' => $type,
        'show_empty' => $show_empty,
        'prefix' => $prefix,
        'postfix'
        => $postfix,
        'showtoggle' => $showtoggle,
        'post_type' => $post_type,
        'repeaternumber' => $repeaternumber,
        'acfrepeattype' => $acfrepeattype,
        'replaceLabel' => $replaceLabel,
        'icon' => $icon,
        'postprocessor' =>
        $postprocessor,
        'repeaterArray' => $repeaterArray
    ));

    return json_encode($value);
}

function gspb_query_thumbelementapi(WP_REST_Request $request)
{
    $type = sanitize_text_field($request->get_param('type'));
    $postfix = sanitize_text_field($request->get_param('postfix'));
    $postId = (int)$request->get_param('postId');
    $maxtemp = intval($request->get_param('maxtemp'));
    $tempscale = sanitize_text_field($request->get_param('tempscale'));

    $value = gspb_query_thumb_counter(array('type' => $type, 'postfix' => $postfix, 'post_id' => $postId, 'maxtemp'
    => $maxtemp, 'tempscale' => $tempscale));
    return json_encode($value);
}

function gspb_query_wishlistelementapi(WP_REST_Request $request)
{
    $type = sanitize_text_field($request->get_param('type'));
    $icontype = sanitize_text_field($request->get_param('icontype'));
    $postId = (int)$request->get_param('postId');
    $wishlistadd = sanitize_text_field($request->get_param('wishlistadd'));
    $wishlistadded = sanitize_text_field($request->get_param('wishlistadded'));
    $wishlistpage = sanitize_text_field($request->get_param('wishlistpage'));
    $loginpage = sanitize_text_field($request->get_param('loginpage'));
    $noitemstext = sanitize_text_field($request->get_param('noitemstext'));

    $value = gspb_query_wishlist(array(
        'type' => $type,
        'icontype' => $icontype,
        'post_id' => $postId,
        'wishlistadd' => $wishlistadd,
        'wishlistadded' => $wishlistadded,
        'wishlistpage' => $wishlistpage,
        'loginpage'
        => $loginpage,
        'noitemstext' => $noitemstext
    ));
    return json_encode($value);
}

if (!function_exists('gspb_get_user_ip')) {
    function gspb_get_user_ip()
    {
        foreach (
            array(
                'HTTP_CLIENT_IP',
                'HTTP_X_FORWARDED_FOR',
                'HTTP_X_FORWARDED',
                'HTTP_X_CLUSTER_CLIENT_IP',
                'HTTP_FORWARDED_FOR',
                'HTTP_FORWARDED',
                'REMOTE_ADDR'
            ) as $key
        ) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip);
                    $ip = $ip[0];
                }
                if ($ip) {
                    substr_replace($ip, 0, -1);
                } //GDRP
                return esc_attr($ip);
            }
        }
        return '127.0.0.3';
    }
}

function GSPB_make_dynamic_image($dynamic_style, $attrs, $block, $attribute, $attributeimage)
{
    $imageoriginal = $attributeimage;
    $image_url = $postid = '';

    if(empty($attribute['dynamicType'])) {
        $attribute['dynamicType'] = 'featured';
    }

    if (!empty($attribute['dynamicSource']) && $attribute['dynamicSource'] == 'definite_item') {
        if ($attribute['dynamicType'] == 'taxonomy') {
            $postid = !empty($attribute['dynamicTaxonomyId']) ? $attribute['dynamicTaxonomyId'] : '';
        } else {
            if (!empty($attribute['dynamicPostId'])) {
                $postid = (int)$attribute['dynamicPostId'];
            }
        }
    } else {
        if (!empty($attribute['dynamicType']) && $attribute['dynamicType'] == 'taxonomy') {
            if (is_tax() || is_category() || is_tag()) {
                $postid = get_queried_object()->term_id;
            } else {
                global $post;
                if (is_object($post)) {
                    $postid = $post->ID;
                    $fieldslug = esc_attr($attribute['dynamicTaxonomy']);
                    if ($fieldslug) {
                        $term_ids = wp_get_post_terms($postid, $fieldslug, array("fields" => "ids"));
                        if (!empty($term_ids) && !is_wp_error($term_ids)) {
                            $term_id = $term_ids[0];
                            $postid = $term_id;
                        }
                    }
                }
            }
        } else {
            global $post;
            if (is_object($post)) {
                $postid = $post->ID;
            }
        }
    }
    if (!$postid) return $dynamic_style;
    if ($attribute['dynamicType'] === 'custom') {
        if (!empty($attribute['dynamicField'])) {
            $field = esc_attr($attribute['dynamicField']);
            $fieldvalue = GSPB_get_custom_field_value($postid, $field, 'no');
            if (is_numeric($fieldvalue)) $fieldvalue = wp_get_attachment_url($fieldvalue);
            $image_url = esc_url($fieldvalue);
        }
    } else if ($attribute['dynamicType'] == 'featured') {
        $image_url = get_the_post_thumbnail_url($postid, 'full');
    } else if ($attribute['dynamicType'] == 'taxonomy') {
        if (!empty($attribute['dynamicTaxonomyField'])) {
            $field = esc_attr($attribute['dynamicTaxonomyField']);
            $fieldvalue = get_term_meta($postid, $field, true);
            if (is_array($fieldvalue)) $fieldvalue = $fieldvalue[0];
            if (is_numeric($fieldvalue)) $fieldvalue = wp_get_attachment_url($fieldvalue);
            $image_url = esc_url($fieldvalue);
        }
    }
    if ($image_url && $imageoriginal) {
        return str_replace($imageoriginal, $image_url, $dynamic_style);
    } else {
        return '';
    }
    return $dynamic_style;
}

function gspb_get_dynamic_part_callback(WP_REST_Request $request){
    $dynamic_text = !empty($request->get_param('dynamic_text')) ? sanitize_text_field($request->get_param('dynamic_text')) : '';
    $attributetext = !empty($request->get_param('attributetext')) ? sanitize_text_field($request->get_param('attributetext')) : '';
    $postID = !empty($request->get_param('postID')) ? sanitize_text_field($request->get_param('postID')) : '';
    $attribute = !empty($request->get_param('attribute')) ? json_decode(sanitize_text_field($request->get_param('attribute')), true) : '';
    if(!empty($attribute) && is_array($attribute) && $postID){
        $attribute['dynamicPostId'] = $postID;
        $attribute['dynamicSource'] = 'definite_item';
    }

    $result = GSPB_make_dynamic_text($dynamic_text, '', '', $attribute, $attributetext);
    return json_encode($result);
}

function GSPB_make_dynamic_text($dynamic_text, $attrs, $block, $attribute, $attributetext = '')
{

    $textoriginal = $attributetext;
    $text_replace = '';
    $postid = '';

    $repeaterField = !empty($attribute['repeaterField']) ? $attribute['repeaterField'] : '';
    if ($repeaterField && !empty($attribute['repeaterArray'])) {
        $text_replace = GSPB_get_value_from_array_field($repeaterField, $attribute['repeaterArray']);
        if(!empty($text_replace)){
            if(!empty($attribute['postprocessor']) && $attribute['postprocessor'] == 'json'){
                //do nothing. Add json postprocessor below
            }else{
                $text_replace = GSPB_field_array_to_value($text_replace, ', ');
            }
        }
    } else {
        if (!empty($attribute['dynamicSource']) && $attribute['dynamicSource'] == 'definite_item') {
            if ($attribute['dynamicType'] == 'taxonomy') {
                $postid = $attribute['dynamicTaxonomyId'];
            } else {
                if (!empty($attribute['dynamicPostId'])) {
                    $postid = (int)$attribute['dynamicPostId'];
                }
            }
        } else {
            if (empty($attribute['dynamicType'])) return $dynamic_text;
            if (!empty($attribute['dynamicType']) && $attribute['dynamicType'] == 'taxonomy') {
                if (is_tax() || is_category() || is_tag()) {
                    $postid = get_queried_object()->term_id;
                }
            } else {
                global $post;
                if (is_object($post)) {
                    $postid = $post->ID;
                }
            }
        }
        if ($attribute['dynamicType'] === 'custom') {
            if (!$postid) return $dynamic_text;
            $fieldvalue = '';
            if (!empty($attribute['dynamicField'])) {
                $field = esc_attr($attribute['dynamicField']);
                $fieldvalue = GSPB_get_custom_field_value($postid, $field);
                $text_replace = $fieldvalue;
            }
        } else if ($attribute['dynamicType'] == 'sitedata' && !empty($attribute['dynamicSiteData'])) {
            $field = esc_attr($attribute['dynamicSiteData']);

            if ($field == 'name' || $field == 'description') {
                $text_replace = get_bloginfo($field);
            } else if ($field == 'year') {
                $text_replace = date_i18n("Y");
            } else if ($field == 'month') {
                $text_replace = date_i18n("F");
            } else if ($field == 'today') {
                $next_day = strtotime("today", current_time('timestamp'));
                $text_replace = wp_date(get_option('date_format'), $next_day);
            } else if ($field == 'todayplus1') {
                $next_day = strtotime("+1 day", current_time('timestamp'));
                $text_replace = wp_date(get_option('date_format'), $next_day);
            } else if ($field == 'todayplus2') {
                $next_day = strtotime("+2 days", current_time('timestamp'));
                $text_replace = wp_date(get_option('date_format'), $next_day);
            } else if ($field == 'todayplus3') {
                $next_day = strtotime("+3 days", current_time('timestamp'));
                $text_replace = wp_date(get_option('date_format'), $next_day);
            } else if ($field == 'todayplus7') {
                $next_day = strtotime("+7 days", current_time('timestamp'));
                $text_replace = wp_date(get_option('date_format'), $next_day);
            } else if ($field == 'transient') {
                $field = esc_attr($attribute['dynamicField']);
                if (!$field) return;
                $text_replace = GSPB_get_custom_field_value(0, $field, ', ', 'transient');
            } else if ($field == 'querystring') {
                $field = esc_attr($attribute['dynamicField']);
                if (!$field) return;
                $text_replace = GSPB_get_custom_field_value(0, $field, ', ', 'querystring');
            } else if ($field == 'siteoption') {
                $field = esc_attr($attribute['dynamicField']);
                if (!$field) return;
                $text_replace = GSPB_get_custom_field_value(0, $field, ', ', 'option');
            } else if ($field == 'acfsiteoption') {
                $field = esc_attr($attribute['dynamicField']);
                if (!$field) return;
                $text_replace = GSPB_get_custom_field_value(0, $field, ', ', 'acf_option');
            }
        } else if ($attribute['dynamicType'] == 'authordata' && !empty($attribute['dynamicAuthorData'])) {
            if (!$postid) return $dynamic_text;
            $field = esc_attr($attribute['dynamicAuthorData']);
            $resultObj = get_post($postid);
            if (is_object($resultObj)) {
                $authorID = $resultObj->post_author;
                if ($field == 'meta' && !empty($attribute['dynamicAuthorField'])) {
                    $text_replace = get_user_meta($authorID, $attribute['dynamicAuthorField'], true);
                } else if($field == 'user_avatar_url'){
                    $text_replace = get_avatar_url($authorID, ['size' => !empty($attribute['avatarSize']) ? (int)$attribute['avatarSize'] : 96]);
                } else {
                    $text_replace = get_the_author_meta($field, $authorID);
                }
            }
        }else if ($attribute['dynamicType'] == 'user_data' && !empty($attribute['dynamicAuthorData'])) {
            $userId = get_current_user_id();
            $field = esc_attr($attribute['dynamicAuthorData']);
            if ($userId && $field) {
                if ($field == 'meta' && !empty($attribute['dynamicAuthorField'])) {
                    $text_replace = get_user_meta($userId, $attribute['dynamicAuthorField'], true);
                }else if($field == 'user_avatar_url'){
                    $text_replace = get_avatar_url($userId, ['size' => !empty($attribute['avatarSize']) ? (int)$attribute['avatarSize'] : 96]);
                } else {
                    $text_replace = get_the_author_meta($field, $userId);
                }
            }
        } else if ($attribute['dynamicType'] == 'taxonomyvalue' && !empty($attribute['dynamicTaxonomyValue'])) {
            if (!isset($postid) || !$postid) return $dynamic_text;
            $field = esc_attr($attribute['dynamicTaxonomyValue']);
            $divider = !empty($attribute['dynamicTaxonomyDivider']) ? '<span class="gspb_tax_spacer">' .
                $attribute['dynamicTaxonomyDivider'] . '</span>' : ', ';
            $linkrender = !empty($attribute['dynamicTaxonomyLink']) ? $attribute['dynamicTaxonomyLink'] : false;
            if ($linkrender) {
                $term_list = get_the_term_list($postid, $field, '', $divider, '');
                if (!is_wp_error($term_list)) {
                    $text_replace = $term_list;
                }
            } else {
                $terms = get_the_terms($postid, $field);
                if ($terms && !is_wp_error($terms)) {
                    $term_slugs_arr = array();
                    foreach ($terms as $term) {
                        $term_slugs_arr[] = '' . $term->name . '';
                    }
                    $terms_slug_str = join($divider, $term_slugs_arr);
                    $text_replace = $terms_slug_str;
                }
            }
        } else if ($attribute['dynamicType'] == 'postdata' && !empty($attribute['dynamicPostData'])) {
            if (empty($postid)) return $dynamic_text;
            $field = esc_attr($attribute['dynamicPostData']);

            if($field == 'post_image'){
                $size = 'full';
                if(!empty($attribute['dynamicPostImageSize'])){
                    $size = esc_attr($attribute['dynamicPostImageSize']);
                }
                $text_replace = get_the_post_thumbnail_url($postid, $size);
            } else if($field == 'post_author'){
                $author_id = get_post_field('post_author', $postid); 
                $author_name = get_the_author_meta('display_name', $author_id);
                $text_replace = $author_name;
            } else if ($field == 'post_date') {
                $text_replace = get_the_date('', $postid);
            } else if ($field == 'post_modified') {
                $text_replace = get_the_modified_date('', $postid);
            } else if ($field == 'permalink') {
                $text_replace = get_the_permalink($postid);
            }else if ($field == 'post_parent_title') {
                $parentId = wp_get_post_parent_id($postid);
                if ($parentId) {
                    $text_replace = get_the_title($parentId);
                } else {
                    $text_replace = ''; // No parent post
                }
            } else if($field == 'post_parent_link'){
                $parentId = wp_get_post_parent_id($postid);
                if ($parentId) {
                    $text_replace = get_the_permalink($parentId);
                } else {
                    $text_replace = ''; // No parent post
                }
            }else {
                $resultObj = get_post($postid);
                if (is_object($resultObj)) {
                    $text_replace = $resultObj->$field;
                }
            }
        } else if ($attribute['dynamicType'] == 'taxonomy' && !empty($attribute['dynamicTaxonomyField'])) {
            if (!isset($postid) || !$postid) return $dynamic_text;
            $field = esc_attr($attribute['dynamicTaxonomyField']);

            if ($field == 'name') {
                $taxobj = get_term($postid);
                if (!is_wp_error($taxobj)) {
                    $fieldvalue = $taxobj->name;
                }
            } else if ($field == 'description') {
                $taxobj = get_term($postid);
                if (!is_wp_error($taxobj)) {
                    $fieldvalue = $taxobj->description;
                }
            } else {
                $fieldvalue = get_term_meta($postid, $field, true);
            }
            if (is_array($fieldvalue)) $fieldvalue = $fieldvalue[0];
            $text_replace = $fieldvalue;
        }
    }

    if (!empty($attribute['postprocessor'])) {
        $text_replace = gspb_data_post_processor($text_replace, $attribute['postprocessor'], $postid, $replaceLabel='', $icon='');
        if($attribute['postprocessor'] == 'datecustom' && !empty($attribute['dateformat'])){
            $dateformat = $attribute['dateformat'];
            $text_replace = date(
                $dateformat,
                is_numeric($text_replace) ? $text_replace : strtotime($text_replace)
            );
        }
    }

    if ($text_replace && $textoriginal) {
        $dynamic_text = str_replace('$', '\$', $dynamic_text);
        // Use preg_replace_callback to handle the replacement safely
		$pattern = '/<dynamictext>.*?<\/dynamictext>/s';  // Added 's' modifier for multiline
        return preg_replace_callback($pattern, 
            function($matches) use ($text_replace) {
                return $text_replace;
            }, 
            $dynamic_text
        );
    } else if ($text_replace) {
        if ($dynamic_text && strpos($dynamic_text, "{DYNAMIC}") !== false) {
            $pattern = '/{DYNAMIC}/';
            $text_replace = preg_replace($pattern,$text_replace, $dynamic_text);
        }
        return $text_replace;
    }else if(!$text_replace && !$textoriginal){
        if(!empty($attribute['fallbackValue'])){
            return $attribute['fallbackValue'];
        }
        return '';
    }
    if(!empty($attribute['fallbackValue'])){
        return $attribute['fallbackValue'];
    } else {
        return $dynamic_text;
    }
}

function GSPB_make_dynamic_video($html, $attrs, $block, $attribute, $attributevideo, $return = false)
{
    $videooriginal = $attributevideo;
    $video_url = '';

    global $post;
    if (is_object($post)) {
        $postid = $post->ID;
    }
    if (!empty($attribute) && $postid) {
        $field = esc_attr($attribute);
        $fieldvalue = GSPB_get_custom_field_value($postid, $field, 'no');
        if (is_numeric($fieldvalue)) $fieldvalue = wp_get_attachment_url($fieldvalue);
        $video_url = esc_url($fieldvalue);
    }
    if ($video_url) {
        if ($return) return $video_url;
        if ($videooriginal) {
            return str_replace($videooriginal, $video_url, $html);
        } else {
            return $video_url;
        }
    } else {
        return '';
    }
    return $html;
}
function GSPB_make_dynamic_link($html, $attrs, $block, $attribute, $attributelink)
{
    $link_url = '';

    global $post;
    if (is_object($post)) {
        $postid = $post->ID;
    } else {
        return $html;
    }
    if (!empty($attrs['dynamicType']) && $attrs['dynamicType'] == 'permalink') {
        $link_url = get_the_permalink($postid);
    } else if (!empty($attrs['dynamicType']) && $attrs['dynamicType'] == 'option') {
        $field = esc_attr($attribute);
        $link_url = esc_url(get_option($field));
    } else {
        if (!empty($attribute) && $postid) {
            $field = esc_attr($attribute);
            $fieldvalue = GSPB_get_custom_field_value($postid, $field, 'no');
            if (is_numeric($fieldvalue)) $fieldvalue = wp_get_attachment_url($fieldvalue);
            $link_url = esc_url($fieldvalue);
            $link_url = apply_filters('greenshiftseo_url_filter', $link_url);
        }
    }
    if ($link_url) {
        return preg_replace('/href\s*=\s*"([^"]*)"/i', 'href="' . $link_url . '"', $html);
    } else {
        return '';
    }
    return $html;
}

function GSPB_make_dynamic_flatvalue($html, $attrs, $block, $attribute, $attributereplace, $return =
false)
{
    $textoriginal = $attributereplace;
    $value = '';

    global $post;
    if (is_object($post)) {
        $postid = $post->ID;
    }
    if (!empty($attrs['dynamicType']) && $attrs['dynamicType'] == 'permatext') {
    } else {
        if (!empty($attribute) && $postid) {
            $field = esc_attr($attribute);
            $value = GSPB_get_custom_field_value($postid, $field);
        }
    }
    if (!$value && $return) {
        return '';
    }
    if ($return) {
        return $value;
    }
    if ($value && $textoriginal) {
        return str_replace($textoriginal, $value, $html);
    }
    return $html;
}

function GSPB_get_custom_field_value($postid, $field, $divider = ', ', $type = 'custom')
{
    if (!$field) return;
    if (strpos($field, '[') !== false) {
        $fieldarray = explode('[', $field);
        $cleanfield = $fieldarray[0];
        $pattern = '/\[(\'[^\']*\'|"[^"]*"|[^\]]*)\]/';
        preg_match_all($pattern, $field, $matches);
        $matches = $matches[1];
        if (!empty($matches) && is_array($matches)) {
            if ($type == 'option') {
                $fieldvalue = get_option($cleanfield);
            } else if ($type == 'acf_option' && function_exists('get_field')) {
                $fieldvalue = get_field($cleanfield, 'option');
            } else if ($type == 'transient') {
                $fieldvalue = get_transient($cleanfield);
            } else if ($type == 'querystring') {
                $fieldvalue = !empty($_GET[$cleanfield]) ? esc_attr($_GET[$cleanfield]) : '';
            } else if ($type == 'authormeta') {
                $author_id = get_post_field('post_author', $postid);
                $fieldvalue = get_user_meta($author_id, $cleanfield, true);
            } else if ($type == 'currentusermeta') {
                $author_id = get_current_user_id();
                if ($author_id) {
                    $fieldvalue = get_user_meta($author_id, $cleanfield, true);
                } else {
                    $fieldvalue = '';
                }
            } else if ($type == 'taxonomymeta') {
                $taxonomy_id = get_queried_object_id();
                if ($taxonomy_id) {
                    $fieldvalue = get_term_meta($taxonomy_id, $cleanfield, true);
                    if(function_exists('get_field') && $fieldvalue){
                        $fieldvalue = get_field($cleanfield, 'term_'.$taxonomy_id);
                    }
                } else {
                    $fieldvalue = '';
                }
            }else if ($type == 'taxonomymetabyid') {
                $taxonomy_id = $postid;
                if ($taxonomy_id) {
                    $fieldvalue = get_term_meta($taxonomy_id, $cleanfield, true);
                    if(function_exists('get_field') && $fieldvalue){
                        $fieldvalue = get_field($cleanfield, 'term_'.$taxonomy_id);
                    }
                } else {
                    $fieldvalue = '';
                }
            } else {
                $fieldvalue = get_post_meta($postid, $cleanfield, true);
            }
            foreach ($matches as $match) {
                $match = str_replace("'", '', $match);
                $match = str_replace('"', '', $match);
                if (!is_array($fieldvalue) && strpos($fieldvalue, ',') !== false) {
                    $fieldvalue = explode(',', $fieldvalue);
                }
                if (is_array($fieldvalue) && isset($fieldvalue[$match])) {
                    $fieldvalue = $fieldvalue[$match];
                } else {
                    $fieldvalue = '';
                }
            }
        }
    } else {
        if ($type == 'option') {
            $fieldvalue = get_option($field);
        } else if ($type == 'transient') {
            $fieldvalue = get_transient($field);
        } else if ($type == 'acf_option' && function_exists('get_field')) {
            $fieldvalue = get_field($field, 'option');
        } else if ($type == 'querystring') {
            $fieldvalue = !empty($_GET[$field]) ? esc_attr($_GET[$field]) : '';
        } else if ($type == 'authormeta') {
            $author_id = get_post_field('post_author', $postid);
            $fieldvalue = get_user_meta($author_id, $field, true);
        } else if ($type == 'currentusermeta') {
            $author_id = get_current_user_id();
            if ($author_id) {
                $fieldvalue = get_user_meta($author_id, $field, true);
            } else {
                $fieldvalue = '';
            }
        } else if ($type == 'taxonomymeta') {
            $taxonomy_id = get_queried_object_id();
            if ($taxonomy_id) {
                $fieldvalue = get_term_meta($taxonomy_id, $field, true);
                if(function_exists('get_field') && $fieldvalue){
                    $fieldvalue = get_field($field, 'term_'.$taxonomy_id);
                }
            } else {
                $fieldvalue = '';
            }
        } else if ($type == 'taxonomymetabyid') {
            $taxonomy_id = $postid;
            if ($taxonomy_id) {
                $fieldvalue = get_term_meta($taxonomy_id, $field, true);
                if(function_exists('get_field') && $fieldvalue){
                    $fieldvalue = get_field($field, 'term_'.$taxonomy_id);
                }
            } else {
                $fieldvalue = '';
            }
        } else {
            $fieldvalue = get_post_meta($postid, $field, true);
        }
    }
    if ($divider != 'flatarray') {
        $fieldvalue = GSPB_field_array_to_value($fieldvalue, $divider);
    }
    return apply_filters('greenshift_dynamic_field_output', $fieldvalue);
}

function GSPB_get_value_from_array_field($field, $value)
{
    if (strpos($field, '[') !== false) {
        $fieldarray = explode('[', $field);
        $cleanfield = $fieldarray[0];
        $pattern = '/\[(\'[^\']*\'|"[^"]*"|[^\]]*)\]/';
        preg_match_all($pattern, $field, $matches);
        $matches = $matches[1];
        if (!empty($matches) && is_array($matches)) {
            $fieldvalue = $value[$cleanfield];
            foreach ($matches as $match) {
                $match = str_replace("'", '', $match);
                $match = str_replace('"', '', $match);
                if (isset($fieldvalue[$match])) {
                    $fieldvalue = $fieldvalue[$match];
                }
            }
        }
    } else {
        $fieldvalue = isset($value[$field]) ? $value[$field] : '';
    }
    return apply_filters('greenshift_dynamic_field_output', $fieldvalue);
}

function GSPB_field_array_to_value($fieldvalue, $divider)
{
    if (is_array($fieldvalue)) {
        $output = '';
        foreach ($fieldvalue as $key => $value) {
            if (is_array($value)) {
                // recursively call this function to handle multi-dimensional arrays
                $value = GSPB_field_array_to_value($value, $divider);
            }
            if ($divider == 'list') {
                $output .= '<li>' . $value . '</li>';
            } else if ($divider == 'no') {
                return $value;
            } else if ($divider != 'flat') {
                $output .= $value . $divider;
            } else {
                // nothing to do
            }
        }
        if ($divider == 'list') {
            return '<ul>' . $output . '</ul>';
        } else if ($divider == 'flat' && count($fieldvalue) > 0) {
            return $fieldvalue[0];
        } else {
            return rtrim($output, $divider);
        }
    }
    return $fieldvalue;
}

function GSPB_make_dynamic_from_metas($field, $post_id = null)
{
    $value = '';
    $postid = null;

    if ($post_id) {
        $postid = $post_id;
    } else {
        global $post;
        if (is_object($post)) {
            $postid = $post->ID;
        }
    }
    if (!empty($field) && $postid) {
        $field = esc_attr($field);
        $value = GSPB_get_custom_field_value($postid, $field);
    }
    return apply_filters('greenshift_make_dynamic_from_metas', $value);
}

//////////////////////////////////////////////////////////////////
// Gallery Video field
//////////////////////////////////////////////////////////////////

function gspb_woo_add_custom_video_field_to_attachment_fields_to_edit($form_fields, $post)
{
    $video_field = get_post_meta($post->ID, 'gs_video_field', true);
    $form_fields['gs_video_field'] = array(
        'label' => 'Add video or 3d file url',
        'input' => 'text', // you may alos use 'textarea' field
        'value' => $video_field,
        'helps' => 'Place video, youtube, .glb, .gltf, .splinecode url for GreenShift Gallery block'
    );
    return $form_fields;
}
add_filter(
    'attachment_fields_to_edit',
    'gspb_woo_add_custom_video_field_to_attachment_fields_to_edit',
    null,
    2
);

// Save custom text/textarea attachment field
function save_custom_text_attachment_field($post, $attachment)
{
    if (isset($attachment['gs_video_field'])) {
        update_post_meta($post['ID'], 'gs_video_field', sanitize_text_field($attachment['gs_video_field']));
    } else {
        delete_post_meta($post['ID'], 'gs_video_field');
    }
    return $post;
}
add_filter('attachment_fields_to_save', 'save_custom_text_attachment_field', null, 2);

//////////////////////////////////////////////////////////////////
// Quick get and show video thumbnail and embed by url
//////////////////////////////////////////////////////////////////
if (!function_exists('gs_parse_video_url')) {
    function gs_parse_video_url($url, $return = 'embed', $width = '', $height = '', $rel = 0)
    {
        $urls = parse_url($url);

        //url is http://vimeo.com/xxxx
        if ($urls['host'] == 'vimeo.com') {
            $vid = ltrim($urls['path'], '/');
        }
        //url is http://youtu.be/xxxx
        else if ($urls['host'] == 'youtu.be') {
            $yid = ltrim($urls['path'], '/');
        }
        //url is http://www.youtube.com/embed/xxxx
        else if (strpos($urls['path'], 'embed') == 1) {
            $yid = end(explode('/', $urls['path']));
        }
        //url is xxxx only
        else if (strpos($url, '/') === false) {
            $yid = $url;
        }
        //http://www.youtube.com/watch?feature=player_embedded&v=m-t4pcO99gI
        //url is http://www.youtube.com/watch?v=xxxx
        else {
            parse_str($urls['query'], $i);
            $yid = $i['v'];
            if (!empty($feature)) {
                $yid = end(explode('v=', $urls['query']));
                $arr = explode('&', $yid);
                $yid = $arr[0];
            }
        }
        if (isset($yid)) {

            //return embed iframe
            if ($return == 'embed') {
                return '<iframe width="' . ($width ? $width : 765) . '" height="' . ($height ? $height : 430) . '"
                    src="https://www.youtube.com/embed/' . $yid . '?rel=' . $rel . '&enablejsapi=1" frameborder="0"
                    ebkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
            }
            //return normal thumb
            else if ($return == 'thumb' || $return == 'thumbmed') {
                return '//i1.ytimg.com/vi/' . $yid . '/default.jpg';
            } else if ($return == 'embedurl') {
                return 'https://www.youtube.com/embed/' . $yid;
            }
            //return hqthumb
            else if ($return == 'hqthumb') {
                return '//i1.ytimg.com/vi/' . $yid . '/hqdefault.jpg';
            } else if ($return == 'maxthumb') {
                $vtrid = 'ymaxthumb_' . $yid;
                $ymaxthumblink = get_transient($vtrid);

                if ($ymaxthumblink) {
                    $image = $ymaxthumblink;
                } else {
                    $maxurl = "https://i.ytimg.com/vi/" . $yid . "/maxresdefault.jpg";
                    $max = wp_safe_remote_head($maxurl);

                    if (!is_wp_error($max) && wp_remote_retrieve_response_code($max) != '404') {
                        $image = $maxurl;
                        set_transient($vtrid, $image, 30 * DAY_IN_SECONDS);
                    } else {
                        $image = '//i1.ytimg.com/vi/' . $yid . '/hqdefault.jpg';
                        set_transient($vtrid, $image, 30 * DAY_IN_SECONDS);
                    }
                }

                return $image;
            } else if ($return == 'hoster') {
                return 'youtube';
            } else if ($return == 'data') {
                $vtrid = 'ymaxthumb_' . $yid;
                $ymaxthumblink = get_transient($vtrid);

                if ($ymaxthumblink) {
                    $image = $ymaxthumblink;
                } else {
                    $maxurl = "https://i.ytimg.com/vi/" . $yid . "/maxresdefault.jpg";
                    $max = wp_safe_remote_head($maxurl);

                    if (!is_wp_error($max) && wp_remote_retrieve_response_code($max) != '404') {
                        $image = $maxurl;
                        set_transient($vtrid, $image, 30 * DAY_IN_SECONDS);
                    } else {
                        $image = '//i1.ytimg.com/vi/' . $yid . '/hqdefault.jpg';
                        set_transient($vtrid, $image, 30 * DAY_IN_SECONDS);
                    }
                }
                return array('hoster' => 'youtube', 'image' => $image, 'id' => $yid, 'embed' =>
                'https://www.youtube.com/embed/' . $yid);
            }
            // else return id
            else {
                return $yid;
            }
        } else if ($vid) {
            $oembed_endpoint = 'https://vimeo.com/api/oembed';
            $json_url = $oembed_endpoint . '.json?url=' . rawurlencode($url) . '&width=765';
            $response = wp_remote_get($json_url);
            if (!is_wp_error($response) && $response['response']['code'] == 200) {
                $vimeoObject = json_decode($response['body']);
            }
            if (!empty($vimeoObject) && $vimeoObject !== FALSE) {
                //return embed iframe
                if ($return == 'embed') {
                    return '<iframe width="' . ($width ? $width : $vimeoObject['width']) . '"
                    height="' . ($height ? $height : $vimeoObject['height']) . '"
                    src="//player.vimeo.com/video/' . $vid . '?title=0&byline=0&portrait=0" frameborder="0"
                    webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
                }
                //return normal thumb
                else if ($return == 'thumb' || $return == 'maxthumb') {
                    if (is_array($vimeoObject)) {
                        return $vimeoObject['thumbnail_url'];
                    }
                } else if ($return == 'embedurl') {
                    return '//player.vimeo.com/video/' . $vid;
                }
                //return medium thumb
                else if ($return == 'thumbmed') {
                    return str_replace('_640', '_340', $vimeoObject['thumbnail_url']);
                }
                //return hqthumb
                else if ($return == 'hqthumb') {
                    return $vimeoObject['thumbnail_url'];
                } else if ($return == 'hoster') {
                    return 'vimeo';
                } else if ($return == 'data') {
                    return array('hoster' => 'vimeo', 'image' => $vimeoObject['thumbnail_url'], 'id' => $vid, 'embed' =>
                    '//player.vimeo.com/video/' . $vid);
                }
                // else return id
                else {
                    return $vid;
                }
            }
        }
    }
}
if (!function_exists('gs_video_thumbnail_html')) {
    function gs_video_thumbnail_html($video, $image_id, $image_alt, $size = 60, $imagesize =
    'woocommerce_single')
    {
        ob_start();
?>
        <?php
        if (preg_match("/^(http(s)?:\/\/)?((w){3}.)?(m\.)?youtu(be|.be)?(\.com)?\/.+$/", $video) || strpos($video, 'vimeo.com') !== false) : ?>
            <a href="<?php echo esc_url(gs_parse_video_url($video, 'embedurl')); ?>"
                title="<?php echo esc_attr($image_alt); ?>" class="imagelink gspb-gallery-video">
                <?php echo wp_get_attachment_image($image_id, $imagesize) ?>
                <div class="gs-gallery-icon-play"
                    style="position: absolute;top: 50%;transform: translate(-50%, -50%);left: 50%;">
                    <svg class="play" width="<?php echo (int)$size; ?>px" height="<?php echo (int)$size; ?>px"
                        viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 0h48v48H0z" fill="none"></path>
                        <path
                            d="m20 33 12-9-12-9v18zm4-29C12.95 4 4 12.95 4 24s8.95 20 20 20 20-8.95 20-20S35.05 4 24 4zm0 36c-8.82 0-16-7.18-16-16S15.18 8 24 8s16 7.18 16 16-7.18 16-16 16z"
                            fill="#ffffff" class="fill-000000"></path>
                    </svg>
                </div>
            </a>
        <?php elseif (strpos($video, '.glb') !== false || strpos($video, '.gltf') !== false) : ?>
            <?php echo do_blocks('<!-- wp:greenshift-blocks/modelviewer {"id":"gsbp-92b193e9-b2ec","inlineCssStyles":".gs-t-model{position:relative;}.gs-t-model :not(:defined)\u003e:not(.poster){display:none}.gs-t-model :defined\u003e.poster\u003e.pre-prompt{display:none}.gs-t-model .poster{display:flex;justify-content:center;align-items:center;height:100%;top:0;left:0;background-size:contain;background-repeat:no-repeat;background-position:center}.gs-t-model .pre-prompt{pointer-events:none;animation-name:lefttoright;animation-duration:5s;animation-iteration-count:infinite;animation-timing-function:ease-in-out}.gs-t-model .ar-button{position:absolute;left:50%;transform:translateX(-50%);white-space:nowrap;bottom:16px;font-size:14px;border-radius:18px;border:1px solid #dadce0;color:#6495ed;display:flex;visibility:visible !important; gap:10px;}.progress-bar{display:block;width:33%;height:10%;max-height:2%;position:absolute;left:50%;top:50%;transform:translate3d(-50%,-50%,0);border-radius:25px}.progress-bar.hide{visibility:hidden;transition:visibility .3s}.update-bar{background-image:linear-gradient(45deg,#b2a2cd 25%,#5c5269 25%,#5c5269 50%,#b2a2cd 50%,#b2a2cd 75%,#5c5269 75%,#5c5269 100%);background-size:28.28px 28.28px;width:0%;height:100%;border-radius:25px;float:left;transition:width .3s}.gs-t-model .progress-bar:not(.hide) + .ar-button{display:none !important;}#gspb_modelBox-id-gsbp-92b193e9-b2ec .gsmodelviewer{\u002d\u002dposter-color: transparent;background-color:transparent;\u002d\u002dprogress-mask:transparent;\u002d\u002dprogress-bar-color: #00ab1985}","td_url":"' . esc_url($video) . '","imageurl":"' . wp_get_attachment_image_url($image_id, $imagesize) . '","td_load_iter":true} -->
				<div id="gspb_modelBox-id-gsbp-92b193e9-b2ec" class="gspb_modelBox gs-t-model gspb_modelBox-id-gsbp-92b193e9-b2ec wp-block-greenshift-blocks-modelviewer" style="display:flex"><model-viewer id="gs_three_gsbp-92b193e9-b2ec" class="gsmodelviewer" src="' . esc_url($video) . '" data-loaditer="true" auto-rotate="true" camera-controls="true" data-camera="yes" ar="true"><div class="poster" slot="poster" style="background-image:url(' . wp_get_attachment_image_url($image_id, $imagesize) . ')"><div class="pre-prompt"><svg xmlns="http://www.w3.org/2000/svg" width="25" height="36"><defs><path id="gsbp-92b193e9-b2ecA" d="M.001.232h24.997V36H.001z"></path></defs><g transform="translate(-11 -4)" fill="none" fill-rule="evenodd"><path fill-opacity="0" fill="#fff" d="M0 0h44v44H0z"></path><g transform="translate(11 3)"><path d="M8.733 11.165c.04-1.108.766-2.027 1.743-2.307a2.54 2.54 0 0 1 .628-.089c.16 0 .314.017.463.044 1.088.2 1.9 1.092 1.9 2.16v8.88h1.26c2.943-1.39 5-4.45 5-8.025a9.01 9.01 0 0 0-1.9-5.56l-.43-.5c-.765-.838-1.683-1.522-2.712-2-1.057-.49-2.226-.77-3.46-.77s-2.4.278-3.46.77c-1.03.478-1.947 1.162-2.71 2l-.43.5a9.01 9.01 0 0 0-1.9 5.56 9.04 9.04 0 0 0 .094 1.305c.03.21.088.41.13.617l.136.624c.083.286.196.56.305.832l.124.333a8.78 8.78 0 0 0 .509.953l.065.122a8.69 8.69 0 0 0 3.521 3.191l1.11.537v-9.178z" fill-opacity=".5" fill="#e4e4e4"></path><path d="M22.94 26.218l-2.76 7.74c-.172.485-.676.8-1.253.8H12.24c-1.606 0-3.092-.68-3.98-1.82-1.592-2.048-3.647-3.822-6.11-5.27-.095-.055-.15-.137-.152-.23-.004-.1.046-.196.193-.297.56-.393 1.234-.6 1.926-.6a3.43 3.43 0 0 1 .691.069l4.922.994V10.972c0-.663.615-1.203 1.37-1.203s1.373.54 1.373 1.203v9.882h2.953c.273 0 .533.073.757.21l6.257 3.874c.027.017.045.042.07.06.41.296.586.77.426 1.22M4.1 16.614c-.024-.04-.042-.083-.065-.122a8.69 8.69 0 0 1-.509-.953c-.048-.107-.08-.223-.124-.333l-.305-.832c-.058-.202-.09-.416-.136-.624l-.13-.617a9.03 9.03 0 0 1-.094-1.305c0-2.107.714-4.04 1.9-5.56l.43-.5c.764-.84 1.682-1.523 2.71-2 1.058-.49 2.226-.77 3.46-.77s2.402.28 3.46.77c1.03.477 1.947 1.16 2.712 2l.428.5a9 9 0 0 1 1.901 5.559c0 3.577-2.056 6.636-5 8.026h-1.26v-8.882c0-1.067-.822-1.96-1.9-2.16-.15-.028-.304-.044-.463-.044-.22 0-.427.037-.628.09-.977.28-1.703 1.198-1.743 2.306v9.178l-1.11-.537C6.18 19.098 4.96 18 4.1 16.614M22.97 24.09l-6.256-3.874c-.102-.063-.218-.098-.33-.144 2.683-1.8 4.354-4.855 4.354-8.243 0-.486-.037-.964-.104-1.43a9.97 9.97 0 0 0-1.57-4.128l-.295-.408-.066-.092a10.05 10.05 0 0 0-.949-1.078c-.342-.334-.708-.643-1.094-.922-1.155-.834-2.492-1.412-3.94-1.65l-.732-.088-.748-.03a9.29 9.29 0 0 0-1.482.119c-1.447.238-2.786.816-3.94 1.65a9.33 9.33 0 0 0-.813.686 9.59 9.59 0 0 0-.845.877l-.385.437-.36.5-.288.468-.418.778-.04.09c-.593 1.28-.93 2.71-.93 4.222 0 3.832 2.182 7.342 5.56 8.938l1.437.68v4.946L5 25.64a4.44 4.44 0 0 0-.888-.086c-.017 0-.034.003-.05.003-.252.004-.503.033-.75.08a5.08 5.08 0 0 0-.237.056c-.193.046-.382.107-.568.18-.075.03-.15.057-.225.1-.25.114-.494.244-.723.405a1.31 1.31 0 0 0-.566 1.122 1.28 1.28 0 0 0 .645 1.051C4 29.925 5.96 31.614 7.473 33.563a5.06 5.06 0 0 0 .434.491c1.086 1.082 2.656 1.713 4.326 1.715h6.697c.748-.001 1.43-.333 1.858-.872.142-.18.256-.38.336-.602l2.757-7.74c.094-.26.13-.53.112-.794s-.088-.52-.203-.76a2.19 2.19 0 0 0-.821-.91" fill-opacity=".6" fill="#000"></path><path d="M22.444 24.94l-6.257-3.874a1.45 1.45 0 0 0-.757-.211h-2.953v-9.88c0-.663-.616-1.203-1.373-1.203s-1.37.54-1.37 1.203v16.643l-4.922-.994a3.44 3.44 0 0 0-.692-.069 3.35 3.35 0 0 0-1.925.598c-.147.102-.198.198-.194.298.004.094.058.176.153.23 2.462 1.448 4.517 3.22 6.11 5.27.887 1.14 2.373 1.82 3.98 1.82h6.686c.577 0 1.08-.326 1.253-.8l2.76-7.74c.16-.448-.017-.923-.426-1.22-.025-.02-.043-.043-.07-.06z" fill="#fff"></path><g transform="translate(0 .769)"><mask id="gsbp-92b193e9-b2ecB" fill="#fff"><use xlink:href="#gsbp-92b193e9-b2ecA"></use></mask><path d="M23.993 24.992a1.96 1.96 0 0 1-.111.794l-2.758 7.74c-.08.22-.194.423-.336.602-.427.54-1.11.87-1.857.872h-6.698c-1.67-.002-3.24-.633-4.326-1.715-.154-.154-.3-.318-.434-.49C5.96 30.846 4 29.157 1.646 27.773c-.385-.225-.626-.618-.645-1.05a1.31 1.31 0 0 1 .566-1.122 4.56 4.56 0 0 1 .723-.405l.225-.1a4.3 4.3 0 0 1 .568-.18l.237-.056c.248-.046.5-.075.75-.08.018 0 .034-.003.05-.003.303-.001.597.027.89.086l3.722.752V20.68l-1.436-.68c-3.377-1.596-5.56-5.106-5.56-8.938 0-1.51.336-2.94.93-4.222.015-.03.025-.06.04-.09.127-.267.268-.525.418-.778.093-.16.186-.316.288-.468.063-.095.133-.186.2-.277L3.773 5c.118-.155.26-.29.385-.437.266-.3.544-.604.845-.877a9.33 9.33 0 0 1 .813-.686C6.97 2.167 8.31 1.59 9.757 1.35a9.27 9.27 0 0 1 1.481-.119 8.82 8.82 0 0 1 .748.031c.247.02.49.05.733.088 1.448.238 2.786.816 3.94 1.65.387.28.752.588 1.094.922a9.94 9.94 0 0 1 .949 1.078l.066.092c.102.133.203.268.295.408a9.97 9.97 0 0 1 1.571 4.128c.066.467.103.945.103 1.43 0 3.388-1.67 6.453-4.353 8.243.11.046.227.08.33.144l6.256 3.874c.37.23.645.55.82.9.115.24.185.498.203.76m.697-1.195c-.265-.55-.677-1.007-1.194-1.326l-5.323-3.297c2.255-2.037 3.564-4.97 3.564-8.114 0-2.19-.637-4.304-1.84-6.114-.126-.188-.26-.37-.4-.552-.645-.848-1.402-1.6-2.252-2.204C15.472.91 13.393.232 11.238.232A10.21 10.21 0 0 0 5.23 2.19c-.848.614-1.606 1.356-2.253 2.205-.136.18-.272.363-.398.55C1.374 6.756.737 8.87.737 11.06c0 4.218 2.407 8.08 6.133 9.842l.863.41v3.092l-2.525-.51c-.356-.07-.717-.106-1.076-.106a5.45 5.45 0 0 0-3.14.996c-.653.46-1.022 1.202-.99 1.983a2.28 2.28 0 0 0 1.138 1.872c2.24 1.318 4.106 2.923 5.543 4.772 1.26 1.62 3.333 2.59 5.55 2.592h6.698c1.42-.001 2.68-.86 3.134-2.138l2.76-7.74c.272-.757.224-1.584-.134-2.325" fill-opacity=".05" fill="#000" mask="url(#B)"></path></g></g></g></svg></div></div><div class="progress-bar" slot="progress-bar"><div class="update-bar"></div></div><button slot="ar-button" class="ar-button" style="display:flex;justify-content:center;align-items:center;background-color:white;padding:5px 15px 5px 15px;visibility:hidden"><svg height="25" viewBox="0 0 60 54" width="25" class="mr10"><g fill="none" fill-rule="evenodd"><g fill="rgb(0,0,0)" fill-rule="nonzero"><path d="m53 0h-46c-3.86416566.00440864-6.99559136 3.13583434-7 7v40c.00440864 3.8641657 3.13583434 6.9955914 7 7h46c3.8641657-.0044086 6.9955914-3.1358343 7-7v-40c-.0044086-3.86416566-3.1358343-6.99559136-7-7zm5 47c-.0033061 2.7600532-2.2399468 4.9966939-5 5h-46c-2.76005315-.0033061-4.99669388-2.2399468-5-5v-40c.00330612-2.76005315 2.23994685-4.99669388 5-5h46c2.7600532.00330612 4.9966939 2.23994685 5 5z"></path><path d="m53 8h-46c-1.65685425 0-3 1.34314575-3 3v36c0 1.6568542 1.34314575 3 3 3h46c1.6568542 0 3-1.3431458 3-3v-36c0-1.65685425-1.3431458-3-3-3zm-23 19.864-10.891-5.864 10.891-5.864 10.891 5.864zm12-4.19v11.726l-11 5.926v-11.726zm-13 5.926v11.726l-11-5.926v-11.726zm-23-18.6c0-.5522847.44771525-1 1-1h22v4.4l-12.474 6.72c-.013.007-.028.01-.041.018-.3023938.1816727-.4866943.5092336-.485.862v8.382l-10 5zm48 36c0 .5522847-.4477153 1-1 1h-46c-.55228475 0-1-.4477153-1-1v-9.382l10-5v3.382c.000193.3677348.2022003.7056937.526.88l13 7c.2959236.1593002.6520764.1593002.948 0l13-7c.3237997-.1743063.525807-.5122652.526-.88v-3.382l10 5zm0-11.618-10-5v-8.382c-.0001367-.3517458-.1850653-.6775544-.487-.858-.013-.008-.028-.011-.041-.018l-12.472-6.724v-4.4h22c.5522847 0 1 .4477153 1 1z"></path><circle cx="6" cy="5" r="1"></circle><circle cx="10" cy="5" r="1"></circle><circle cx="14" cy="5" r="1"></circle><path d="m39 6h14c.5522847 0 1-.44771525 1-1s-.4477153-1-1-1h-14c-.5522847 0-1 .44771525-1 1s.4477153 1 1 1z"></path></g></g></svg>  View in your space</button></model-viewer></div>
			<!-- /wp:greenshift-blocks/modelviewer -->'); ?>

        <?php elseif (strpos($video, '.splinecode') !== false) : ?>
            <?php echo do_blocks('<!-- wp:greenshift-blocks/spline3d {"loadnow":false,"id":"gsbp-7c097221-b9b7","inlineCssStyles":".gspb-bodyfront .gspb_id-gsbp-7c097221-b9b7 img{transition: opacity 0.3s ease-in; position:absolute}.gspb-bodyfront .gspb_id-gsbp-7c097221-b9b7.gs-splineloaded img{opacity:0}.gspb_id-gsbp-7c097221-b9b7 img, .gspb_id-gsbp-7c097221-b9b7 spline-viewer{max-width:100%}.gspb_id-gsbp-7c097221-b9b7 img, .gspb_id-gsbp-7c097221-b9b7 spline-viewer{width:300px;}.gspb_id-gsbp-7c097221-b9b7 img, .gspb_id-gsbp-7c097221-b9b7 spline-viewer{height:300px;}.gspb_id-gsbp-7c097221-b9b7{display:flex;justify-content:center;position:relative;}","hint":true,"enableSmartLoading":true} -->
			<div class="wp-block-greenshift-blocks-spline3d gs-splineloader gspb_id-gsbp-7c097221-b9b7"><spline-viewer url="' . esc_url($video) . '"></spline-viewer><img src="' . wp_get_attachment_image_url($image_id, $imagesize) . '" alt="" width="500px" height="500px" style="pointer-events:none"/></div><!-- /wp:greenshift-blocks/spline3d -->'); ?>

        <?php else : ?>
            <a href="<?php echo esc_url($video); ?>" title="<?php echo esc_attr($image_alt); ?>"
                class="imagelink gspb-gallery-video">
                <video class="gs-video-element-gallery" loading="lazy" src="<?php echo esc_url($video); ?>"
                    poster="<?php echo esc_url(wp_get_attachment_url($image_id)); ?>" autoplay playsinline loop
                    muted></video>
            </a>
        <?php endif; ?>
<?php
        $res = ob_get_contents();
        ob_get_clean();
        return $res;
    }
}

//////////////////////////////////////////////////////////////////
// ACPT Repeater
//////////////////////////////////////////////////////////////////
function gspb_acptConvertArray($inputArray)
{
    $outputArray = [];

    // Get the keys from the first item in the input array
    $keys = array_keys($inputArray);

    // Loop through the values of the first item in the input array
    for ($i = 0; $i < count($inputArray[$keys[0]]); $i++) {
        $item = [];

        // Loop through the keys and add the corresponding value to the item array
        foreach ($keys as $key) {
            $value = $inputArray[$key][$i]['value'];
            $item[$key] = $value;
        }

        // Add the item to the output array
        $outputArray[] = $item;
    }

    return $outputArray;
}

//////////////////////////////////////////////////////////////////
// API Connector
//////////////////////////////////////////////////////////////////
function gspb_api_connector_callback(WP_REST_Request $request)
{
    $nonce = sanitize_text_field($request->get_param('nonce'));
    if (!wp_verify_nonce($nonce, 'wp_rest')) {
        return new WP_REST_Response('Invalid nonce', 403);
    }

    $api_id = sanitize_text_field($request->get_param('api_id'));
    $runindex = sanitize_text_field($request->get_param('runindex'));
    $formdata = !empty($request->get_param('formdata')) ? greenshiftquery_sanitize_multi_array($request->get_param('formdata')) : [];
    if(!$api_id) return new WP_REST_Response(array(), 200);
    $block = get_transient($api_id);
    if(empty($block)) return new WP_REST_Response(array(), 200);

    $generated_content = GSPB_generate_dynamic_repeater('repeater_generate', $block, $formdata, $runindex);

    if (!empty($generated_content)) {
        return new WP_REST_Response($generated_content, 200);
    } else {
        return new WP_REST_Response(array(), 200);
    }
}
function gspb_api_connector_run( $filters, $runindex = null ) {
    extract($filters);
	$response_data = [];
    $apiMethod = ! empty ( $apiMethod ) ? $apiMethod : 'GET';
    $args = [
		'method'     => $apiMethod,
        'timeout'     => ! empty( $apiTimeout ) ? (int) $apiTimeout : 30,
        'redirection' => 5,
        'sslverify'   => false,
		'headers'     => [
			'Content-Type' => 'application/json',
		],
    ];

    if(!empty($pagination_selector) && isset($runindex)){
        $apiUrl = add_query_arg( [ 'page' => $runindex + 1 ], $apiUrl );
    }

    // Prepare headers
    if ( ! empty( $apiHeaders ) && is_array( $apiHeaders ) ) {
        $args['headers'] = array_reduce( $apiHeaders, function ( $headers, $header ) {
			$header_name  = ( isset( $header['name'] ) ) ? sanitize_text_field( $header['name'] ) : '';
			$header_value = ( isset( $header['value'] ) ) ? sanitize_text_field( $header['value'] ) : '';

            if ( ! empty( $header_name ) && ! empty( $header_value ) ) {
                $headers[ $header_name ] = $header_value;
            }
            return $headers;
        }, []);
    }

    // Process query or body parameters
    if ( ! empty( $apiBody ) ) {
        // If apiBody is a simple text string, just use it directly.
        if ( 'GET' === $apiMethod ) {
            // For GET requests, add the encoded body as a query parameter.
            $encodedBody = gspb_ensure_json_encoded($apiBody);
            $apiUrl = add_query_arg( [ 'body' => $encodedBody ], $apiUrl );
        } else {
            // For non-GET (POST, etc.), set the body, making sure it's properly encoded.
            $args['body'] = gspb_ensure_json_encoded($apiBody);
        }
    }

    // Execute the API request
    $response = ( 'GET' === $apiMethod ) ? wp_safe_remote_get( $apiUrl, $args ) : wp_safe_remote_post( $apiUrl, $args );

    // Handle response
    if ( is_wp_error( $response ) ) {
        error_log( 'API Request Error: ' . $response->get_error_message() );
    } else {
        $body          = wp_remote_retrieve_body( $response );
        if(!empty($body)){
            $body = json_decode( $body, true );
        }
        if(!empty($responseField) && !empty($body)){
            // Split the string "field[0][message]" into an array like ["field", "0", "message"]
            $keys = preg_split('/[\[\]]+/', $responseField, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($keys as $key) {
                if (isset($body[$key])) {
                    $body = $body[$key];
                } else {
                    // If some key doesn't exist, set $body to null (or handle it as you need).
                    $body = null;
                    break;
                }
            }
        }

        if (is_null($body)) {
            $body = '';
        }
        $response_data = $body;

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            error_log( 'JSON Decode Error: ' . json_last_error_msg() );
        }
    }

    return $response_data;
}

/**
 * Ensure that the passed data is JSON-encoded.
 *
 * @param mixed $data The data to be checked/encoded.
 * @return string JSON-encoded string.
 */
function gspb_ensure_json_encoded($data) {
    // If the data is a string, try to check if it is already valid JSON.
    if (is_string($data)) {
        $trimmed = trim($data);
        // A valid JSON string usually starts with '{' (object) or '[' (array)
        if ((substr($trimmed, 0, 1) === '{' || substr($trimmed, 0, 1) === '[')) {
            json_decode($data);
            if (json_last_error() === JSON_ERROR_NONE) {
                // Already valid JSON.
                return $data;
            }
        }
    }
    // Otherwise, encode the data (works if $data is an array or any non-string data).
    return json_encode($data);
}