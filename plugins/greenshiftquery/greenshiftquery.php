<?php

/**
 * Plugin Name: Greenshift Query and Meta Addon
 * Description: Get any meta value, use better query block
 * Author: Wpsoul
 * Author URI: https://greenshiftwp.com
 * Text Domain: greenshiftquery
 * Version: 5.6.1
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Define Dir URL
define('GREENSHIFTQUERY_DIR_URL', plugin_dir_url(__FILE__));
define('GREENSHIFTQUERY_DIR_PATH', plugin_dir_path(__FILE__));
define('GREENSHIFTQUERY_PLUGIN_VER', '5.6.1');

function gspb_query_is_parent_active()
{
    $active_plugins = get_option('active_plugins', array());

    if (is_multisite()) {
        $network_active_plugins = get_site_option('active_sitewide_plugins', array());
        $active_plugins = array_merge($active_plugins, array_keys($network_active_plugins));
    }

    foreach ($active_plugins as $basename) {
        if (
            0 === strpos($basename, 'greenshift-animation-and-page-builder-blocks/')
        ) {
            return true;
        }
    }

    return false;
}

if (gspb_query_is_parent_active()) {
    if (!defined('EDD_CONSTANTS')) {
        require_once GREENSHIFT_DIR_PATH . 'edd/edd_constants.php';
    }

    add_filter('plugins_api', 'greenshiftquery_plugin_info', 20, 3);
    add_filter('site_transient_update_plugins', 'greenshiftquery_push_update');
    add_action('upgrader_process_complete', 'greenshiftquery_after_update', 10, 2);
    add_action('after_plugin_row_' . plugin_basename(__FILE__), 'greenshiftquery_after_plugin_row', 10, 3);

    // Hook: Editor assets.
    add_action('enqueue_block_editor_assets', 'greenShiftQuery_editor_assets');
    if(!function_exists('greenshift_check_cron_exec')){wp_die();}
} else {
    add_action('admin_notices', 'greenshiftquery_admin_notice_warning');
}


//////////////////////////////////////////////////////////////////
// Plugin updater
//////////////////////////////////////////////////////////////////

function greenshiftquery_after_plugin_row($plugin_file, $plugin_data, $status)
{
    $licenses = greenshift_edd_check_all_licenses();
    $is_active = ((!empty($licenses['all_in_one']) && $licenses['all_in_one'] == 'valid') || (!empty($licenses['query_addon']) && $licenses['query_addon'] == 'valid') || (!empty($licenses['all_in_one_design']) && $licenses['all_in_one_design'] == 'valid') || (!empty($licenses['all_in_one_woo']) && $licenses['all_in_one_woo'] == 'valid') || (!empty($licenses['all_in_one_seo']) && $licenses['all_in_one_seo'] == 'valid')) ? true : false;
    if (!$is_active) {
        echo sprintf('<tr class="active"><td colspan="4">%s <a href="%s">%s</a></td></tr>', 'Please enter a license to receive automatic updates', esc_url(admin_url('admin.php?page=' . EDD_GSPB_PLUGIN_LICENSE_PAGE)), 'Enter License.');
    }
}

function greenshiftquery_plugin_info($res, $action, $args)
{

    // do nothing if this is not about getting plugin information
    if ($action !== 'plugin_information') {
        return false;
    }

    // do nothing if it is not our plugin
    if (plugin_basename(__DIR__) !== $args->slug) {
        return $res;
    }

    // trying to get from cache first, to disable cache comment 23,33,34,35,36
    if (false == $remote = get_transient('greenshiftquery_upgrade_pluginslug')) {

        // info.json is the file with the actual information about plug-in on your server
        $remote = wp_remote_get(
            EDD_GSPB_STORE_URL_UPDATE . '/get-info.php?slug=' . plugin_basename(__DIR__) . '&action=info',
            array(
                'timeout' => 15,
                'headers' => array(
                    'Accept' => 'application/json'
                )
            )
        );

        if (!is_wp_error($remote) && isset($remote['response']['code']) && $remote['response']['code'] == 200 && !empty($remote['body'])) {
            set_transient('greenshiftquery_upgrade_pluginslug', $remote, 60000);
        }else{
            set_transient('greenshiftquery_upgrade_pluginslug', 'error', 60000);
        }
    }

    if ($remote && !is_wp_error($remote) && $remote != 'error' && isset($remote->version)) {

        $remote = json_decode(wp_remote_retrieve_body($remote));

        $res = new stdClass();
        $res->name = $remote->name;
        $res->slug = $remote->slug;
        $res->version = $remote->version;
        $res->tested = $remote->tested;
        $res->requires = $remote->requires;
        $res->author = $remote->author;
        $res->author_profile = $remote->author_homepage;
        $res->download_link = $remote->download_link;
        $res->trunk = $remote->download_link;
        $res->last_updated = $remote->last_updated;

        if (isset($remote->sections)) {
            $res->sections = array(
                'description' => $remote->sections->description, // description tab
                'installation' => $remote->sections->installation, // installation tab
                'changelog' => isset($remote->sections->changelog) ? $remote->sections->changelog : '',
            );
        }
        if (isset($remote->banners)) {
            $res->banners = array(
                'low' => $remote->banners->low,
                'high' => $remote->banners->high,
            );
        }

        return $res;
    }

    return false;
}

function greenshiftquery_push_update($transient)
{

    if (empty($transient->checked)) {
        return $transient;
    }

    // trying to get from cache first, to disable cache comment 11,20,21,22,23
    if (false == $remote = get_transient('greenshiftquery_upgrade_pluginslug')) {
        // info.json is the file with the actual plugin information on your server
        $remote = wp_remote_get(
            EDD_GSPB_STORE_URL_UPDATE . '/get-info.php?slug=' . plugin_basename(__DIR__) . '&action=info',
            array(
                'timeout' => 10,
                'headers' => array(
                    'Accept' => 'application/json'
                )
            )
        );

        if (!is_wp_error($remote) && isset($remote['response']['code']) && $remote['response']['code'] == 200 && !empty($remote['body'])) {
            set_transient('greenshiftquery_upgrade_pluginslug', $remote, 60000);
        }else{
            set_transient('greenshiftquery_upgrade_pluginslug', 'error', 60000);
        }
    }

    if (!is_wp_error($remote) && $remote && $remote != 'error') {

        $remote = json_decode($remote['body']);

        // your installed plugin version should be on the line below! You can obtain it dynamically of course
        if ($remote && isset($remote->version) && version_compare(GREENSHIFTQUERY_PLUGIN_VER, $remote->version, '<') && version_compare($remote->requires, get_bloginfo('version'), '<')) {
            $res = new stdClass();
            $res->slug = plugin_basename(__DIR__);
            $res->plugin = plugin_basename(__FILE__); // it could be just pluginslug.php if your plugin doesn't have its own directory
            $res->new_version = $remote->version;
            $res->tested = $remote->tested;
            $licenses = greenshift_edd_check_all_licenses();
            $is_active = ((!empty($licenses['all_in_one']) && $licenses['all_in_one'] == 'valid') || (!empty($licenses['query_addon']) && $licenses['query_addon'] == 'valid') || (!empty($licenses['all_in_one_woo']) && $licenses['all_in_one_woo'] == 'valid') || (!empty($licenses['all_in_one_design']) && $licenses['all_in_one_design'] == 'valid') || (!empty($licenses['all_in_one_seo']) && $licenses['all_in_one_seo'] == 'valid')) ? true : false;
            if ($is_active) {
                $res->package = $remote->download_link;
            }
            $transient->response[$res->plugin] = $res;
            //$transient->checked[$res->plugin] = $remote->version;
        }
    }
    return $transient;
}

function greenshiftquery_after_update($upgrader_object, $options)
{
    if ($options['action'] == 'update' && $options['type'] === 'plugin') {
        // just clean the cache when new plugin version is installed
        delete_transient('greenshiftquery_upgrade_pluginslug');
    }
}

function greenshiftquery_admin_notice_warning()
{
    ?>
    <div class="notice notice-warning">
        <p><?php printf(__('Please, activate %s plugin to use Query Addon'), '<a href="https://wordpress.org/plugins/greenshift-animation-and-page-builder-blocks" target="_blank">Greenshift</a>'); ?></p>
    </div>
    <?php
}

function greenshiftquery_change_action_links($links)
{

    $links = array_merge(array(
        '<a href="https://greenshiftwp.com/changelog" style="color:#93003c" target="_blank">' . __('What\'s New', 'greenshiftquery') . '</a>'
    ), $links);

    return $links;
}

add_action('plugin_action_links_' . plugin_basename(__FILE__), 'greenshiftquery_change_action_links');


/**
 * GreenShift Blocks Category
 */
if (!function_exists('gspb_greenShiftQuery_category')) {
    function gspb_greenShiftQuery_category($categories, $post)
    {
        return array_merge(
            array(
                array(
                    'slug' => 'greenShiftQuery',
                    'title' => __('GreenShift Query and Meta'),
                ),
            ),
            $categories
        );
    }
}
add_filter('block_categories_all', 'gspb_greenShiftQuery_category', 1, 2);

//////////////////////////////////////////////////////////////////
// Register server side
//////////////////////////////////////////////////////////////////
require_once GREENSHIFTQUERY_DIR_PATH . 'query.php';
require_once GREENSHIFTQUERY_DIR_PATH . 'blockrender/meta/block.php';
require_once GREENSHIFTQUERY_DIR_PATH . 'blockrender/thumbcounter/block.php';
require_once GREENSHIFTQUERY_DIR_PATH . 'blockrender/wishlist/block.php';
require_once GREENSHIFTQUERY_DIR_PATH . 'blockrender/advanced-listing/block.php';
require_once GREENSHIFTQUERY_DIR_PATH . 'blockrender/visibility-block/block.php';
require_once GREENSHIFTQUERY_DIR_PATH . 'blockrender/taxonomy/block.php';
require_once GREENSHIFTQUERY_DIR_PATH . 'blockrender/login-form/block.php';
require_once GREENSHIFTQUERY_DIR_PATH . 'blockrender/dynamic-post-title/block.php';
require_once GREENSHIFTQUERY_DIR_PATH . 'blockrender/dynamic-post-image/block.php';
require_once GREENSHIFTQUERY_DIR_PATH . 'blockrender/querygrid/block.php';
require_once GREENSHIFTQUERY_DIR_PATH . 'blockrender/pagelist/block.php';
require_once GREENSHIFTQUERY_DIR_PATH . 'blockrender/breadcrumbs/block.php';
require_once GREENSHIFTQUERY_DIR_PATH . 'blockrender/dynamicgallery/block.php';
require_once GREENSHIFTQUERY_DIR_PATH . 'blockrender/threesixty/block.php';
require_once GREENSHIFTQUERY_DIR_PATH . 'blockrender/repeater/block.php';
require_once GREENSHIFTQUERY_DIR_PATH . 'blockrender/filter-panel/block.php';
require_once GREENSHIFTQUERY_DIR_PATH . 'blockrender/filter-block/block.php';
require_once GREENSHIFTQUERY_DIR_PATH . 'blockrender/filter-results-count/block.php'; // New Code
require_once GREENSHIFTQUERY_DIR_PATH . 'blockrender/filter-sorting/block.php'; // New Code

//REST functions
require_once GREENSHIFTQUERY_DIR_PATH . 'rest.php';
require_once GREENSHIFTQUERY_DIR_PATH . 'hotcounter.php';
require_once GREENSHIFTQUERY_DIR_PATH . 'wishlist.php';


//require_once GREENSHIFTQUERY_DIR_PATH . 'querypatterns.php';
require_once GREENSHIFTQUERY_DIR_PATH . 'filterindexer.php';
require_once GREENSHIFTQUERY_DIR_PATH . 'integrations.php';


//////////////////////////////////////////////////////////////////
// Functions to render conditional scripts
//////////////////////////////////////////////////////////////////

// Hook: Frontend assets.
add_action('init', 'greenShiftQuery_register_init');

if (!function_exists('greenShiftQuery_register_init')) {
    function greenShiftQuery_register_init()
    {

        //Non render function
        register_block_type(__DIR__ . '/blockrender/searchbox');

        wp_register_script(
            'gs-thumbscounter',
            GREENSHIFTQUERY_DIR_URL . 'assets/thumbs.min.js',
            array(),
            '1.6',
            true
        );
        wp_register_script(
            'gspb-apiconnector',
            GREENSHIFTQUERY_DIR_URL . 'libs/api-connector/index.js',
            array(),
            '1.2',
            true
        );
        wp_localize_script(
            'gs-thumbscounter',
            'gsthumbvars',
            array(
                'ajax_url' => admin_url('admin-ajax.php', 'relative'),
                'hotnonce' => wp_create_nonce('hotnonce'),
            )
        );

        wp_register_script(
            'gs-wishcounter',
            GREENSHIFTQUERY_DIR_URL . 'assets/wishlist.min.js',
            array(),
            '1.3',
            true
        );
        wp_localize_script(
            'gs-wishcounter',
            'gswishvars',
            array(
                'ajax_url' => admin_url('admin-ajax.php', 'relative'),
                'wishnonce' => wp_create_nonce('wishnonce'),
            )
        );

        wp_register_script(
            'gsquerytoggler',
            GREENSHIFTQUERY_DIR_URL . 'libs/toggle/toggle.js',
            array(),
            '1.0',
            true
        );

        //filter panel
        wp_register_script(
            'gspbfilterpanel',
            GREENSHIFTQUERY_DIR_URL . 'libs/filterpanel/index.js',
            array(),
            '2.1',
            true
        );
        wp_register_script(
            'gspbajaxpagination',
            GREENSHIFTQUERY_DIR_URL . 'libs/filterpanel/ajaxpagination.js',
            array(),
            '2.4',
            true
        );

        wp_register_style(
            'gsquerytooltip',
            GREENSHIFTQUERY_DIR_URL . 'libs/tooltip.css',
            array(),
            '1.0',
            true
        );

        // login form
        wp_register_script(
            'gspbloginform',
            GREENSHIFTQUERY_DIR_URL . 'libs/loginform/index.js',
            array(),
            '1.1',
            true
        );

        wp_register_script(
            'gsdynamicgallery',
            GREENSHIFTQUERY_DIR_URL . 'libs/dynamicgallery/index.js',
            array(),
            '1.1',
            true
        );
        wp_register_script(
            'gsthreesixty',
            GREENSHIFTQUERY_DIR_URL . 'libs/threesixty/circl.js',
            array(),
            '1.3',
            true
        );
        wp_register_script(
            'gssearchbox',
            GREENSHIFTQUERY_DIR_URL . 'libs/search/index.js',
            array(),
            '1.6',
            true
        );
        wp_localize_script(
            'gssearchbox',
            'ajax_search_params',
            array(
                'nonce' => wp_create_nonce('wp_rest'),
                'rest_url' => esc_url_raw(rest_url('greenshift/v1/frontsearch/'))
            )
        );
        wp_register_script( 'greenshiftloop', '', [], '', true );

        // Filter Panel New
        wp_register_script(
        'gspb-filterblock',
        GREENSHIFTQUERY_DIR_URL . 'libs/filter-block/filter-index.js',
        array(),
        '1.8',
        true
    );
        
    }
}


//Polyfill for str_contains
if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle)
    {
        return empty($needle) || strpos($haystack, $needle) !== false;
    }
}

add_filter('render_block', 'greenShiftQuery_block_script_assets', 10, 2);
if (!function_exists('greenShiftQuery_block_script_assets')) {
    function greenShiftQuery_block_script_assets($html, $block)
    {
        // phpcs:ignore

        //Main styles for blocks are loaded via Redux. Can be found in src/customJS/editor/store/index.js and src/gspb-library/helpers/reusable_block_css/index.js

        if (!is_admin()) {
            $blockname = $block['blockName'];
            if ($blockname == 'greenshift-blocks/thumbcounter') {
                wp_enqueue_script('gs-thumbscounter');
            } else if ($blockname === 'greenshift-blocks/login-form') {
                wp_enqueue_script('gspbloginform');
                $scriptvars = array(
                    'ajax_url' => admin_url('admin-ajax.php', 'relative'),
                );
                wp_localize_script('gspbloginform', 'gspbloginvars', $scriptvars);
            } else if ($blockname == 'greenshift-blocks/dynamicgallery') {
                if (isset($block['attrs']['lightbox']) && $block['attrs']['lightbox'] == true) {
                    wp_enqueue_style('gslightbox');
                    wp_enqueue_script('gslightbox');
                    wp_enqueue_script('gsdynamicgallery');
                }
            } else if ($blockname == 'greenshift-blocks/threesixty') {
                if (!empty($block['attrs']['lightbox'])) {
                    wp_enqueue_script('gslightbox');
                    wp_enqueue_style('gslightbox');
                }
                wp_enqueue_script('gsthreesixty');
            } else if($blockname == 'greenshift-blocks/searchbox'){
                $block_instance = (is_array($block)) ? $block : $block->parsed_block;
                $json_block = rawurlencode(json_encode($block_instance['innerBlocks']));
                $html = str_replace('action=""', 'action="'.home_url().'"', $html);
                wp_enqueue_script('gssearchbox');
                $blockid = 'gspb-search-'.$block['attrs']['id'];
                $blockid = str_replace('-','_', $blockid);
                wp_add_inline_script('gssearchbox', 'var '.$blockid.'="'.$json_block.'"', 'before');
            }            
            else if ($blockname == 'greenshift-blocks/filter-block') {
                // Store pre-select parameters for filter block
                $pre_select = array();

                if ( is_tax() || is_tag() || is_category()) {
                    $category = get_queried_object();
                    $pre_select['currentTaxonomoy'] = $category->slug;
                }

                $rest_vars = array(
                    'rest_url' => esc_url_raw(rest_url('greenshift/v1/get-gs-posts/')),
                    'indexing_rest_url' => esc_url_raw(rest_url('greenshift/v1/indexer-count/')),
                    'refresh_filterchips' => esc_url_raw(rest_url('greenshift/v1/refresh-filterchips/')),
                    'preSelect' => $pre_select,
                );

                wp_localize_script('gspb-filterblock', 'RESTVARS', $rest_vars);
                wp_enqueue_script('gspb-filterblock');
            }
        }

        return $html;
    }
}

//////////////////////////////////////////////////////////////////
// Enqueue Gutenberg block assets for backend editor.
//////////////////////////////////////////////////////////////////

if (!function_exists('greenShiftQuery_editor_assets')) {
    function greenShiftQuery_editor_assets()
    {
        // phpcs:ignor

        $index_asset_file = include(GREENSHIFTQUERY_DIR_PATH . 'build/index.asset.php');


        // Blocks Assets Scripts
        wp_enqueue_script(
            'greenShiftQuery-block-js', // Handle.
            GREENSHIFTQUERY_DIR_URL . 'build/index.js',
            array('greenShift-editor-js', 'greenShift-library-script', 'wp-block-editor', 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-data'),
            $index_asset_file['version'],
            true
        );

        $licenses = greenshift_edd_check_all_licenses();
        $is_active = (((!empty($licenses['all_in_one']) && $licenses['all_in_one'] == 'valid') || (!empty($licenses['query_addon']) && $licenses['query_addon'] == 'valid') || (!empty($licenses['all_in_one_woo']) && $licenses['all_in_one_woo'] == 'valid') || (!empty($licenses['all_in_one_design']) && $licenses['all_in_one_design'] == 'valid') || (!empty($licenses['all_in_one_seo']) && $licenses['all_in_one_seo'] == 'valid'))) ? true : false;

        $check = '';
        if ($is_active) {
            $check = 1;
        }
        $lc = array('can_use_premium_code' => $check);
        if(defined('\ContentEgg\PLUGIN_PATH') && class_exists('\ContentEgg\application\components\GreenshiftIntegrator')){
            $lc['ce_enabled'] = 1;
            $filters = \ContentEgg\application\components\GreenshiftIntegrator::getAllowedFilters();
            if(!empty($filters['modules']['allowed_values'])){
                $lc['ce_modules'] = $filters['modules']['allowed_values'];
            }
        }
        wp_localize_script('greenShiftQuery-block-js', 'greenshiftQUERY', $lc);


        // Styles.
        wp_enqueue_style(
            'greenShiftQuery-block-css', // Handle.
            GREENSHIFTQUERY_DIR_URL . 'build/index.css', // Block editor CSS.
            array('greenShift-library-editor', 'wp-edit-blocks'),
            $index_asset_file['version']
        );
    }
}


add_action('init', 'wp_block_custom_meta');
function wp_block_custom_meta()
{
    if (post_type_exists('wp_block')) {
        add_post_type_support('wp_block', 'custom-fields'); // enable custom fields for post type

        //register custom meta fields
        register_post_meta('wp_block', 'gspb_overwrite_archive_by_this_template', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'boolean',
            'default' => false,
        ));

        register_post_meta('wp_block', 'gspb_overwrite_filter_by', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string',
            'default' => '',
        ));

        register_post_meta('wp_block', 'gspb_taxonomy_value', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string',
            'default' => '',
        ));

        $tax_args = array(
            'show_in_rest' => array(
                'schema' => array(
                    'type' => 'array',
                    'items' => array(
                        'type' => 'object',
                        'properties' => array(
                            'id' => array(
                                'type' => 'string',
                            ),
                            'label' => array(
                                'type' => 'string',
                            ),
                            'value' => array(
                                'type' => 'number',
                            ),
                        ),
                    ),
                ),
            ),
            'single' => true,
            'type' => 'array',
        );

        register_post_meta('wp_block', 'gspb_tax_slug', $tax_args);

        register_post_meta('wp_block', 'gspb_tax_slug_exclude', $tax_args);

        register_post_meta('wp_block', 'gspb_roles_list', array(
            'show_in_rest' => array(
                'schema' => array(
                    'type' => 'array',
                    'items' => array(
                        'type' => 'object',
                        'properties' => array(
                            'id' => array(
                                'type' => 'string',
                            ),
                            'label' => array(
                                'type' => 'string',
                            ),
                            'value' => array(
                                'type' => 'string',
                            ),
                        ),
                    ),
                ),
            ),
            'single' => true,
            'type' => 'array',
        ));

        register_post_meta('wp_block', 'gspb_posttype_archive', array(
            'show_in_rest' => true,
            'single' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'type' => 'string',
        ));

        register_post_meta('wp_block', 'gspb_singular', array(
            'show_in_rest' => true,
            'single' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'type' => 'string',
        ));

        register_post_meta('wp_block', 'gspb_singular_filter_by', array(
            'show_in_rest' => true,
            'single' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'type' => 'string',
            'default' => 'all_items',
        ));

        register_post_meta('wp_block', 'gspb_singular_ids', array(
            'show_in_rest' => array(
                'schema' => array(
                    'type' => 'array',
                    'items' => array(
                        'type' => 'object',
                        'properties' => array(
                            'id' => array(
                                'type' => 'string',
                            ),
                            'label' => array(
                                'type' => 'string',
                            ),
                            'value' => array(
                                'type' => 'string',
                            ),
                        ),
                    ),
                ),
            ),
            'single' => true,
            'type' => 'array',
        ));
    }
}

add_action('template_redirect', 'gspb_reusable_block_change_template');

function gspb_reusable_block_change_template()
{
    $templates = get_option('gspb_template_replace');
    if (!empty($templates)) {
        foreach ($templates as $key => $template) {
            $type = !empty($template['type']) ? $template['type'] : '';
            $gspb_taxonomy_value = !empty($template['gspb_taxonomy_value']) ? $template['gspb_taxonomy_value'] : '';
            $gspb_tax_slug = !empty($template['gspb_tax_slug']) ? $template['gspb_tax_slug'] : '';
            $gspb_tax_slug_exclude = !empty($template['gspb_tax_slug_exclude']) ? $template['gspb_tax_slug_exclude'] : '';
            $gspb_roles_list = !empty($template['gspb_roles_list']) ? $template['gspb_roles_list'] : '';
            $gspb_posttype_archive = !empty($template['gspb_posttype_archive']) ? $template['gspb_posttype_archive'] : '';
            $gspb_singular = !empty($template['gspb_singular']) ? $template['gspb_singular'] : '';
            $gspb_singular_filter_by = !empty($template['gspb_singular_filter_by']) ? $template['gspb_singular_filter_by'] : '';
            $gspb_singular_ids = !empty($template['gspb_singular_ids']) ? $template['gspb_singular_ids'] : '';

            if ($type) {
                $flag = false;
                if ($type == 'errorpage' && is_404()) {
                    $flag = true;
                }if ($type == 'search' && is_search()) {
                    $flag = true;
                } else if ($type == 'singular' && $gspb_singular && is_singular($gspb_singular)) {
                    if ($gspb_singular_filter_by === 'manual_select') {
                        global $post;
                        $posts_include = (!empty($gspb_singular_ids)) ? array_column($gspb_singular_ids, 'id') : '';
                        if (!empty($posts_include) && in_array($post->ID, $posts_include)) $flag = true;
                    } else if ($gspb_singular_filter_by === 'by_taxonomy') {
                        global $post;
                        $post_terms = get_the_terms($post, $gspb_taxonomy_value);
                        if (!$post_terms) continue;

                        $post_terms = array_column($post_terms, 'term_id');

                        $tax_include = (!empty($gspb_tax_slug)) ? array_column($gspb_tax_slug, 'value') : [];
                        $tax_exclude = (!empty($gspb_tax_slug_exclude)) ? array_column($gspb_tax_slug_exclude, 'value') : [];

                        $array_intersect_include = array_values(array_intersect($tax_include, $post_terms));
                        $array_intersect_exclude = array_values(array_intersect($tax_exclude, $post_terms));

                        if (
                            (!empty($tax_exclude) && !empty($array_intersect_exclude)) ||
                            (!empty($tax_include) && empty($array_intersect_include))
                        ) continue;

                        $flag = true;
                    } else {
                        $flag = true;
                    }
                } else if ($type == 'taxonomy' && $gspb_taxonomy_value && (is_tax() || is_category() || is_tag())) {
                    $term = get_queried_object();
                    if ($term->taxonomy != $gspb_taxonomy_value) {
                        continue;
                    }
                    $tax_include = (!empty($gspb_tax_slug)) ? array_column($gspb_tax_slug, 'value') : '';
                    $tax_exclude = (!empty($gspb_tax_slug_exclude)) ? array_column($gspb_tax_slug_exclude, 'value') : '';
                    if (
                        (!empty($tax_include) && !in_array($term->term_id, $tax_include)) ||
                        (!empty($tax_exclude) && in_array($term->term_id, $tax_exclude))
                    ) continue;
                    $flag = true;
                } else if ($type == 'user' && is_author()) {
                    $user_roles = get_queried_object()->roles;
                    $roles_include = (!empty($gspb_roles_list)) ? array_column($gspb_roles_list, 'id') : '';
                    if (!empty($roles_include) && !empty(array_diff($user_roles, $roles_include))) continue;
                    $flag = true;
                } else if ($type == 'posttype' && $gspb_posttype_archive && is_post_type_archive($gspb_posttype_archive)) {
                    $flag = true;
                }
                if ($flag) {
                    $postReusable = get_post($key);
                    if (!$postReusable) {
                        unset($templates[$key]);
                        update_option('gspb_template_replace', $templates);
                        continue;
                    }
                    if ($postReusable->post_status != 'publish') {
                        unset($templates[$key]);
                        update_option('gspb_template_replace', $templates);
                        continue;
                    };
                    $enabled = get_post_meta($key, 'gspb_overwrite_archive_by_this_template', true);
                    if (!$enabled) {
                        unset($templates[$key]);
                        update_option('gspb_template_replace', $templates);
                        continue;
                    }
                    if(wp_is_block_theme()){
                        ?>
                        <!DOCTYPE html>
                        <html <?php language_attributes(); ?>>
                            <head>
                                <meta charset="<?php bloginfo( 'charset' ); ?>">
                                <meta name="viewport" content="width=device-width, initial-scale=1" />
                                <link rel="profile" href="https://gmpg.org/xfn/11" />
                                <?php if ( ! current_theme_supports( 'title-tag' ) ) : ?>
                                    <title><?php echo wp_get_document_title(); ?></title>
                                <?php endif; ?>
                                <?php wp_head(); ?>
                                <style>
                                    <?php 
                                    $post_css = get_post_meta((int)$postReusable->ID, '_gspb_post_css', true);
                                    if(!empty($post_css)){
                                        $dynamic_style = wp_kses_post($post_css);
                                        $dynamic_style = gspb_get_final_css($dynamic_style);
                                        $dynamic_style = gspb_quick_minify_css($dynamic_style);
                                        $dynamic_style = htmlspecialchars_decode($dynamic_style);
                                        echo $dynamic_style;
                                    }
                                    ?>
                                </style>
                            </head>
                            <body <?php body_class(); ?>>
                                <?php wp_body_open(); ?>
                                <?php while ( have_posts() ) : the_post(); ?>
                                    <?php 
                                        $content = $postReusable->post_content;
                                        $content = do_blocks($content);
                                        $content = do_shortcode($content);
                                        $content = preg_replace('%<p>&nbsp;\s*</p>%', '', $content);
                                        $content = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $content);
                                        echo ''.$content;
                                    ?>
                                <?php endwhile; ?>
                                <?php wp_footer(); ?>
                            </body>
                        </html>
                        <?php
                    }else{
                        get_header();
                        echo '<main id="site-content">';
                        $post_css = get_post_meta((int)$postReusable->ID, '_gspb_post_css', true);
                        if(!empty($post_css)){
                            $dynamic_style = '<style>' . wp_kses_post($post_css) . '</style>';
                            $dynamic_style = gspb_get_final_css($dynamic_style);
                            $dynamic_style = gspb_quick_minify_css($dynamic_style);
                            $dynamic_style = htmlspecialchars_decode($dynamic_style);
                            echo $dynamic_style;
                        }
                        $content = $postReusable->post_content;
                        $content = do_blocks($content);
                        $content = do_shortcode($content);
                        $content = preg_replace('%<p>&nbsp;\s*</p>%', '', $content);
                        $content = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $content);
                        echo ''.$content;
    
                        echo '</main>';
                        get_footer();
                    }
                    exit;
                }
            }
        }
    }
}

function gspb_get_post_object_by_id($post_id, $post_type)
{
    if ((int)$post_id > 0) $post = get_post($post_id);
    else {
        $args = array(
            'posts_per_page' => 1,
            'post_status' => 'published',
            'orderby' => 'ID',
            'order' => 'DESC',
            'post_type' => $post_type
        );

        $loop = new WP_Query($args);

        if (empty($loop->posts)) return NULL;

        $post = get_post($loop->posts[0]->ID);
        wp_reset_query();
    }

    return $post;
}

function filter_by_custom_meta($compare, $key = '', $value = '', $not_show_for_selected = false, $type = 'post')
{
    if (empty($key)) return true;

    if($type == 'post'){
        global $post;
        if(empty($post)) return false;
        $post_meta = GSPB_get_custom_field_value($post->ID, $key);
    }else if($type == 'user'){
        $user_id = get_current_user_id();
        if(empty($user_id)) return false;
        $post_meta = GSPB_get_custom_field_value(0, $key, ', ', 'currentusermeta');
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
        case 'less_equal':
            $result = $post_meta <= $value;
            break;
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

function filter_by_taxonomy_meta($compare, $key = '', $value = '', $not_show_for_selected = false)
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

function filter_by_cpt($post_type, $tax_name, $tax_slug = [], $tax_slug_exclude = [], $cat = [], $cat_exclude = [], $tag = [], $tag_exclude = [], $price_range = '', $type = 'all', $not_show_for_selected = false, $hasParent = false, $hasChildren = false)
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
            $_product_price = (int)$_product->get_price();

            if (!$not_show_for_selected && ($_product_price < (int)$price_range_array[0] || $_product_price > (int)$price_range_array[1])) $result = false;
            if ($not_show_for_selected && !($_product_price < (int)$price_range_array[0] || $_product_price > (int)$price_range_array[1])) $result = false;
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
            $viewed_products = !empty($_COOKIE['woocommerce_recently_viewed']) ? (array)explode('|', $_COOKIE['woocommerce_recently_viewed']) : array();
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

function filter_by_post_cat_or_tag($cat = [], $cat_exclude = [], $tag = [], $tag_exclude = [], $not_show_for_selected = false)
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

function filter_by_post_id($ids = [], $not_show_for_selected = false)
{
    if (empty($ids)) return true;

    foreach ($ids as $id) {
        if ($not_show_for_selected && (is_single($id['id']) || is_page($id['id']))) return false;
        if (is_single($id['id']) || is_page($id['id'])) return true;
    }

    return $not_show_for_selected;
}

function filter_by_user($user_must_logged, $allowed_roles = [], $user_id = [], $not_show_for_selected = false)
{
    $userid = get_current_user_id();

    $user = get_userdata($userid);

    if (count($allowed_roles) > 0) {
        if (!empty($user)) {
            $user_has_role = false;
            foreach ($allowed_roles as $allowed_role) {
                if (in_array($allowed_role['value'], (array)$user->roles)) $user_has_role = true;
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


function visibility_conditions_check($block_content, $block)
{
    if ( isset($block['attrs']['is_visibility_set']) && $block['attrs']['is_visibility_set'] === true) {
        $conditions_arr_visibility = $block['attrs']['conditions_arr_visibility'] ?? [];
        $settings = !empty($block['attrs']['blockVisibility']) ? $block['attrs']['blockVisibility'] : [];
        if(empty($settings) || is_admin()) return $block_content;
        $post_type = $settings['post_type'] ?? 'post';
        $dataSource = $settings['data_source'] ?? 'cat';
        $cat = $settings['cat'] ?? [];
        $tag = $settings['tag'] ?? [];
        $cat_exclude = $settings['cat_exclude'] ?? [];
        $tag_exclude = $settings['tag_exclude'] ?? [];
        $tax_name = $settings['tax_name'] ?? '';
        $tax_slug = $settings['tax_slug'] ?? [];
        $tax_slug_exclude = $settings['tax_slug_exclude'] ?? [];
        $price_range = $settings['price_range'] ?? '';
        $user_id = $settings['user_id'] ?? [];
        $type = $settings['type'] ?? 'all';
        $ids = $settings['ids'] ?? [];
        $user_logged_in = $settings['user_logged_in'] ?? false;
        $user_roles = $settings['user_roles'] ?? [];
        $query_by = $settings['query_by'] ?? 'post_type';
        $not_show_for_selected = $settings['not_show_for_selected'] ?? false;
        $custom_field_key = $settings['custom_field_key'] ?? '';
        $custom_field_value = $settings['custom_field_value'] ?? '';
        $custom_field_compare = $settings['custom_field_compare'] ?? 'equal';
        $url_path_field = $settings['url_path_field'] ?? '';
        $is_pagination = $settings['is_pagination'] ?? false;
        $referal_source_field = $settings['referal_source_field'] ?? '';
        $date_time_from_field = $settings['date_time_from_field'] ?? '';
        $date_time_to_field = $settings['date_time_to_field'] ?? '';
        $recursive_date_from = $settings['recursive_date_from'] ?? '';
        $recursive_date_to = $settings['recursive_date_to'] ?? '';
        $type_of_condition = $settings['type_of_condition'] ?? 'and';
        $name_of_cookie = $settings['name_of_cookie'] ?? '';
        $compare_type_cookie = $settings['compare_type_cookie'] ?? '';
        $equal_cookie = $settings['equal_cookie'] ?? '';
        $woocommerce_type = $settings['woocommerce_type'] ?? '';
        $woocommerce_field = $settings['woocommerce_field'] ?? 0;
        $comment_type = $settings['comment_type'] ?? '';
        $comment_field = $settings['comment_field'] ?? 0;
        $hasParent = $settings['hasParent'] ?? false;
        $hasChildren = $settings['hasChildren'] ?? false;

        $isVisibility = true;

        if ($query_by === 'post_type') {
            switch ($dataSource) {
                case 'ids':
                    $isVisibility = filter_by_post_id($ids, $not_show_for_selected);
                    break;
                case 'cat':
                    $isVisibility = filter_by_post_cat_or_tag($cat, $cat_exclude, $tag, $tag_exclude, $not_show_for_selected);
                    break;
                case 'cpt':
                    $isVisibility = filter_by_cpt($post_type, $tax_name, $tax_slug, $tax_slug_exclude, $cat, $cat_exclude, $tag, $tag_exclude, $price_range, $type, $not_show_for_selected, $hasParent, $hasChildren);
                    break;
                default:
                    break;
            }
        } else if ($query_by === 'taxonomy' && !empty($tax_name)) {
            if (isset(get_queried_object()->term_id) && get_queried_object()->taxonomy == $tax_name) {
                $terms_id = array(get_queried_object()->term_id);
            } else {
                global $post;
                if(is_object($post) && is_array(wp_get_post_terms($post->ID, $tax_name))){
                    $terms_id = array_column(wp_get_post_terms($post->ID, $tax_name), 'term_id');
                }else{
                    $terms_id = [];
                }
            }
            if(empty($terms_id)) {
                $isVisibility = false;
            }else{
                $tax_include_ids = !empty($tax_slug) ? array_column($tax_slug, 'value') : [];
                $tax_exclude_ids = !empty($tax_slug_exclude) ? array_column($tax_slug_exclude, 'value') : [];
    
                if (!$not_show_for_selected) {
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
            if (!filter_by_user($user_logged_in, $user_roles, $user_id, $not_show_for_selected)) $isVisibility = false;
            if($custom_field_key){
                if (!filter_by_custom_meta($custom_field_compare, $custom_field_key, $custom_field_value, $not_show_for_selected, $type = 'user')) $isVisibility = false;
            }
        } else if ($query_by === 'custom_meta') {
            if (!filter_by_custom_meta($custom_field_compare, $custom_field_key, $custom_field_value, $not_show_for_selected)) $isVisibility = false;
        } else if ($query_by === 'taxonomy_meta') {
            if (!filter_by_taxonomy_meta($custom_field_compare, $custom_field_key, $custom_field_value, $not_show_for_selected)) $isVisibility = false;
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
        } else if ($query_by === 'referal_source') {
            if (!empty($referal_source_field) && (empty($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER'] !== $referal_source_field)) {
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
        } else if ($query_by === 'date_time') {
            $timestampCurrent = current_time('timestamp', 0);
            $timestampFrom = strtotime($date_time_from_field);
            $timestampTo = strtotime($date_time_to_field);

            if ($timestampFrom && $timestampTo && !($timestampCurrent > $timestampFrom && $timestampCurrent < $timestampTo)) {
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
		} else if ($query_by === 'post_date') {
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
		
			if ($not_show_for_selected) {
				$isVisibility = !$isVisibility;
			}
		} else if ($query_by === 'mobile_view') {
            if ($not_show_for_selected) {
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
        } else if ($query_by === 'by_cookie' && !empty($name_of_cookie)) {
            if (
                ($compare_type_cookie === 'exist' && !empty($_COOKIE[$name_of_cookie])) ||
                $compare_type_cookie === 'equal' && !empty($_COOKIE[$name_of_cookie]) && $_COOKIE[$name_of_cookie] === $equal_cookie
            ) {
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
        else if ($query_by === 'woocommerce') {
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
				if(is_object($post) && get_comments_number($post->ID) && !empty($comment_field) && get_comments_number($post->ID) > $comment_field){
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


        if (!empty($conditions_arr_visibility)) {
            $isVisibilityConditions = [];
            foreach ($conditions_arr_visibility as $key => $condition) {
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
    
                        if (!$not_show_for_selected) {
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

                    if (!filter_by_custom_meta($condition['custom_field_compare'], $condition['custom_field_key'], $condition['custom_field_value'], $not_show_for_selected)) $isVisibilityConditions[$key] = false;
                } else if ($condition['query_by'] === 'taxonomy_meta') {

                    if (!filter_by_taxonomy_meta($condition['custom_field_compare'], $condition['custom_field_key'], $condition['custom_field_value'], $not_show_for_selected)) $isVisibilityConditions[$key] = false;
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
                        if ($not_show_for_selected) {
                            $isVisibilityConditions[$key] = true;
                        } else {
                            $isVisibilityConditions[$key] = false;
                        }
                    } else {
                        if ($not_show_for_selected) {
                            $isVisibilityConditions[$key] = false;
                        } else {
                            $isVisibilityConditions[$key] = true;
                        }
                    }
                } else if ($condition['query_by'] === 'referal_source') {
                    if (!empty($condition['referal_source_field']) && (empty($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER'] !== $condition['referal_source_field'])) {
                        if ($not_show_for_selected) {
                            $isVisibilityConditions[$key] = true;
                        } else {
                            $isVisibilityConditions[$key] = false;
                        }
                    } else {
                        if ($not_show_for_selected) {
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
                        if ($not_show_for_selected) {
                            $isVisibilityConditions[$key] = false;
                        } else {
                            $isVisibilityConditions[$key] = true;
                        }
                    } else {
                        if ($not_show_for_selected) {
                            $isVisibilityConditions[$key] = true;
                        } else {
                            $isVisibilityConditions[$key] = false;
                        }
                    }
                }
            }

            if (!$not_show_for_selected) {
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

        if (!$result) return '';
    }

    return $block_content;
}

add_filter('render_block', 'visibility_conditions_check', 10, 2);

//////////////////////////////////////////////////////////////////
// Localization
//////////////////////////////////////////////////////////////////
function greenshiftquery_plugin_load_textdomain() {
    load_plugin_textdomain('greenshiftquery', false, GREENSHIFTQUERY_DIR_URL . 'languages');
}
add_action('plugins_loaded', 'greenshiftquery_plugin_load_textdomain');