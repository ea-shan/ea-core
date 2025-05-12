<?php

/**
 * Plugin Name: GreenShift Advanced Animation Addon
 * Description: Build most advanced animations with GSAP and Greenshift
 * Author: Wpsoul
 * Author URI: https://greenshiftwp.com
 * Version: 3.7.5
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

// Define Dir URL
define('GREENSHIFTGSAP_DIR_URL', plugin_dir_url(__FILE__));
define('GREENSHIFTGSAP_DIR_PATH', plugin_dir_path(__FILE__));
define('GREENSHIFTGSAP_PLUGIN_VER', '3.7.5');

function gspb_gsap_is_parent_active()
{
	$active_plugins = get_option('active_plugins', array());

	if (is_multisite()) {
		$network_active_plugins = get_site_option('active_sitewide_plugins', array());
		$active_plugins         = array_merge($active_plugins, array_keys($network_active_plugins));
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

if (gspb_gsap_is_parent_active()) {
	if (!defined('EDD_CONSTANTS')) {
		require_once GREENSHIFT_DIR_PATH . 'edd/edd_constants.php';
	}

	add_filter('plugins_api', 'greenshiftgsap_plugin_info', 20, 3);
	add_filter('site_transient_update_plugins', 'greenshiftgsap_push_update');
	add_action('upgrader_process_complete', 'greenshiftgsap_after_update', 10, 2);
	add_action('after_plugin_row_' . plugin_basename(__FILE__), 'greenshiftgsap_after_plugin_row', 10, 3);

	// Hook: Editor assets.
	add_action('enqueue_block_editor_assets', 'greenShiftGsap_editor_assets');
	//add_action('enqueue_block_assets', 'greenShiftGsap_block_assets');
} else {
	add_action('admin_notices', 'greenshiftgsap_admin_notice_warning');
}

//////////////////////////////////////////////////////////////////
// Plugin updater
//////////////////////////////////////////////////////////////////

function greenshiftgsap_after_plugin_row($plugin_file, $plugin_data, $status)
{
	$licenses = greenshift_edd_check_all_licenses();
	$is_active = ((!empty($licenses['all_in_one']) && $licenses['all_in_one'] == 'valid') || (!empty($licenses['gsap_addon']) && $licenses['gsap_addon'] == 'valid') || (!empty($licenses['all_in_one_design']) && $licenses['all_in_one_design'] == 'valid')) ? true : false;
	if (!$is_active) {
		echo sprintf('<tr class="active"><td colspan="4">%s <a href="%s">%s</a></td></tr>', 'Please enter a license to receive automatic updates', esc_url(admin_url('admin.php?page=' . EDD_GSPB_PLUGIN_LICENSE_PAGE)), 'Enter License.');
	}
}

function greenshiftgsap_plugin_info($res, $action, $args)
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
	if (false == $remote = get_transient('greenshiftgsap_upgrade_pluginslug')) {

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
			set_transient('greenshiftgsap_upgrade_pluginslug', $remote, 60000);
		}else{
			set_transient('greenshiftgsap_upgrade_pluginslug', 'error', 60000);
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

function greenshiftgsap_push_update($transient)
{

	if (empty($transient->checked)) {
		return $transient;
	}

	// trying to get from cache first, to disable cache comment 11,20,21,22,23
	if (false == $remote = get_transient('greenshiftgsap_upgrade_pluginslug')) {
		// info.json is the file with the actual plugin information on your server
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
			set_transient('greenshiftgsap_upgrade_pluginslug', $remote, 60000);
		}else{
			set_transient('greenshiftgsap_upgrade_pluginslug', 'error', 60000);
		}
	}

	if ($remote && !is_wp_error($remote) && $remote != 'error') {

		$remote = json_decode($remote['body']);

		// your installed plugin version should be on the line below! You can obtain it dynamically of course
		if ($remote && isset($remote->version) && version_compare(GREENSHIFTGSAP_PLUGIN_VER, $remote->version, '<') && version_compare($remote->requires, get_bloginfo('version'), '<')) {
			$res = new stdClass();
			$res->slug = plugin_basename(__DIR__);
			$res->plugin = plugin_basename(__FILE__); // it could be just pluginslug.php if your plugin doesn't have its own directory
			$res->new_version = $remote->version;
			$res->tested = $remote->tested;
			$licenses = greenshift_edd_check_all_licenses();
			$is_active = ((!empty($licenses['all_in_one']) && $licenses['all_in_one'] == 'valid') || (!empty($licenses['gsap_addon']) && $licenses['gsap_addon'] == 'valid') || (!empty($licenses['all_in_one_design']) && $licenses['all_in_one_design'] == 'valid')) ? true : false;
			if ($is_active) {
				$res->package = $remote->download_link;
			}
			$transient->response[$res->plugin] = $res;
			//$transient->checked[$res->plugin] = $remote->version;
		}
	}
	return $transient;
}

function greenshiftgsap_after_update($upgrader_object, $options)
{
	if ($options['action'] == 'update' && $options['type'] === 'plugin') {
		// just clean the cache when new plugin version is installed
		delete_transient('greenshiftgsap_upgrade_pluginslug');
	}
}

function greenshiftgsap_admin_notice_warning()
{
?>
	<div class="notice notice-warning">
		<p><?php printf(__('Please, activate %s plugin to use Animation Addon'), '<a href="https://wordpress.org/plugins/greenshift-animation-and-page-builder-blocks" target="_blank">Greenshift</a>'); ?></p>
	</div>
<?php
}

function greenshiftgsap_change_action_links($links)
{

	$links = array_merge(array(
		'<a href="https://greenshiftwp.com/changelog" style="color:#93003c" target="_blank">' . __('What\'s New', 'greenshiftgsap') . '</a>'
	), $links);

	return $links;
}
add_action('plugin_action_links_' . plugin_basename(__FILE__), 'greenshiftgsap_change_action_links');


/**
 * GreenShift Blocks Category
 */
if (!function_exists('gspb_greenShiftGsap_category')) {
	function gspb_greenShiftGsap_category($categories, $post)
	{
		return array_merge(
			array(
				array(
					'slug'  => 'Greenshiftpro',
					'title' => __('GreenShift Animations'),
				),
			),
			$categories
		);
	}
}
add_filter('block_categories_all', 'gspb_greenShiftGsap_category', 1, 2);

//////////////////////////////////////////////////////////////////
// Functions to render conditional scripts
//////////////////////////////////////////////////////////////////

// Hook: Frontend assets.
add_action('init', 'greenShiftGsap_register_scripts_blocks');
add_filter('render_block', 'greenShiftGsap_render_block', 10, 2);

if (!function_exists('greenShiftGsap_register_scripts_blocks')) {
	function greenShiftGsap_register_scripts_blocks()
	{

		wp_register_style(
			'greenShiftGsap-block-css', // Handle.
			GREENSHIFTGSAP_DIR_URL . 'build/index.css', // Block editor CSS.
			array('greenShift-library-editor', 'wp-edit-blocks'),
			'3.2'
		);
		wp_register_script(
			'gsap-animation',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/gsap.min.js',
			array(),
			'3.12.2',
			true
		);
		// scroll trigger
		wp_register_script(
			'gsap-scrolltrigger',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/ScrollTrigger.min.js',
			array('gsap-animation'),
			'3.12.2',
			true
		);
		wp_register_script(
			'gsapflip',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/Flip.min.js',
			array('gsap-animation'),
			'3.12.2',
			true
		);
		wp_register_script(
			'gsapsplittext',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/SplitText.min.js',
			array('gsap-animation'),
			'3.12.2',
			true
		);
		wp_register_script(
			'gsapsmoothscroll',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/ScrollSmoother.min.js',
			array('gsap-animation', 'gsap-scrolltrigger', 'gsapsplittext'),
			'3.12.2',
			true
		);
		wp_register_script(
			'gsapsmoothscroll-init',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/gsap-smoothscroll-init.js',
			array('gsapsmoothscroll'),
			'3.12.4',
			true
		);
		wp_register_script(
			'gsapsvgdraw',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/DrawSVGPlugin.min.js',
			array('gsap-animation'),
			'3.12.2',
			true
		);
		wp_register_script(
			'gsapsvgmorph',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/MorphSVGPlugin.min.js',
			array('gsap-animation'),
			'3.12.2',
			true
		);
		wp_register_script(
			'gsapsvgpath',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/MotionPathPlugin.min.js',
			array('gsap-animation'),
			'3.12.2',
			true
		);
		wp_register_script(
			'gsapcustomease',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/CustomEase.min.js',
			array(),
			'3.12.2',
			true
		);

		wp_register_script(
			'gsap-scrollx',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/gsap-scrollx.js',
			array('gsap-animation'),
			'1.1',
			true
		);

		wp_register_script(
			'gsap-scroll-editor',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/scrolleditor.js',
			array('gsap-scrolltrigger'),
			'1.0',
			true
		);

		// gsap init
		wp_register_script(
			'gsap-animation-init',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/gsap-init.js',
			array('gsap-animation'),
			'4.9',
			true
		);
		//gsap reveal init
		wp_register_script(
			'gsap-reveal-init',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/gsap-reveal-init.js',
			array('gsap-animation'),
			'3.5',
			true
		);
		wp_register_script(
			'gsap-mousemove-init',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/gsap-mousemove-init.js',
			array('gsap-animation'),
			'3.9.4',
			true
		);
		wp_register_script(
			'gsap-scrollparallax-init',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/gsap-scrollparallax-init.js',
			array('gsap-animation'),
			'3.9.3',
			true
		);
		wp_register_script(
			'gsap-scrollbg',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/gsap-parallax-bg.js',
			array('gsap-animation'),
			'1.0',
			true
		);

		// flip init
		wp_register_script(
			'gsap-flip-init',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/gsap-flip-init.js',
			array('gsap-animation', 'gsap-scrolltrigger', 'gsapflip'),
			'4.6',
			true
		);

		// flip init
		wp_register_script(
			'gsap-filter-init',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/gsap-filter-init.js',
			array('gsap-animation', 'gsapflip'),
			'3.9.2',
			true
		);

		// sequencer init
		wp_register_script(
			'gsap-seq-init',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/gsap-seq-init.js',
			array('gsap-animation', 'gsap-scrolltrigger'),
			'4.1',
			true
		);

		//gsap mousefollow init
		wp_register_script(
			'gsap-mousefollow-init',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/gsap-mousefollow.js',
			array('gsap-animation'),
			'3.12.2',
			true
		);

		// blob animate init
		wp_register_script(
			'gs-blob-init',
			GREENSHIFTGSAP_DIR_URL . 'libs/blob/index.js',
			array('gsap-animation'),
			'1.0',
			true
		);

		// page navigation
		wp_register_script(
			'gs-pagenav',
			GREENSHIFTGSAP_DIR_URL . 'libs/pagenav/index.js',
			array(),
			'1.4',
			true
		);

		// blob animate init
		wp_register_script(
			'gs-videoscroller',
			GREENSHIFTGSAP_DIR_URL . 'libs/videoscroll/index.js',
			array(),
			'1.0',
			true
		);

		wp_register_script(
			'gs-3d-animation',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/gsap-3d-init.js',
			array(),
			'1.0',
			true,
		);

		wp_register_script(
			'gs-spline-init',
			GREENSHIFTGSAP_DIR_URL . 'libs/splinetool/spline-init.js',
			array(),
			'1.0',
			true,
		);

		wp_localize_script('gs-spline-init', 'gssplineRuntime', array(
			'url' => GREENSHIFTGSAP_DIR_URL
		));

		//Lottie interactive loader
		wp_register_script('gs-lottieloader', GREENSHIFTGSAP_DIR_URL . 'libs/lottie/index.js', array(), '1.1', true);
		wp_register_script('gs-dotlottieloader', GREENSHIFTGSAP_DIR_URL . 'libs/dotlottie/index.js', array(), '1.0', true);
		wp_register_script('gs-rive', GREENSHIFTGSAP_DIR_URL . 'libs/rive/rive.js', array(), '2.23.11', true);
		wp_register_script('gs-riveloader', GREENSHIFTGSAP_DIR_URL . 'libs/rive/index.js', array(), '1.1', true);
		wp_register_script('gs-riveloadernow', GREENSHIFTGSAP_DIR_URL . 'libs/rive/riveindex.js', array('gs-rive'), '1.0', true);

		//register blocks on server side with block.json
		register_block_type(__DIR__ . '/blockrender/animation-container');
		register_block_type(__DIR__ . '/blockrender/blob');
		register_block_type(__DIR__ . '/blockrender/flipstate');
		register_block_type(__DIR__ . '/blockrender/sequencer');
		register_block_type(__DIR__ . '/blockrender/pinscroll');
		register_block_type(__DIR__ . '/blockrender/scrollbg');
		register_block_type(__DIR__ . '/blockrender/pagescroll');
		register_block_type(__DIR__ . '/blockrender/smoothscroll');
		register_block_type(__DIR__ . '/blockrender/pagenav');
		register_block_type(__DIR__ . '/blockrender/lottie');
		register_block_type(__DIR__ . '/blockrender/dotlottie');
		register_block_type(__DIR__ . '/blockrender/rive');
		register_block_type(__DIR__ . '/blockrender/flipfilter');
		register_block_type(__DIR__ . '/blockrender/videoscroll');
		register_block_type(__DIR__ . '/blockrender/dynamic-3d');
	}
}

if (!function_exists('greenShiftGsap_render_block')) {
	function greenShiftGsap_render_block($html, $block)
	{
		// phpcs:ignore

		//Main styles for blocks are loaded via Redux. Can be found in src/customJS/editor/store/index.js and src/gspb-library/helpers/reusable_block_css/index.js

		if (!is_admin()) {

			$blockname = $block['blockName'];
			// looking for gsap animation.
			if ($blockname === 'greenshift-blocks/animation-container') {
				wp_enqueue_script('gsap-animation');
				wp_enqueue_script('gsap-scrolltrigger');

				// looking for gsap libraries 
				$initscript = false;

				if (!empty($block['attrs'])) {
					if (isset($block['attrs']['animation_type'])) {

						if ($block['attrs']['animation_type'] === 'text_transformations') {
							wp_enqueue_script('gsapsplittext');
							$initscript = true;
						}
						if ($block['attrs']['animation_type'] === 'svg_line_draw') {
							wp_enqueue_script('gsapsvgdraw');
							$initscript = true;
						}
						if ($block['attrs']['animation_type'] === 'svg_motion_path') {
							wp_enqueue_script('gsapsvgpath');
							$initscript = true;
						}
						if ($block['attrs']['animation_type'] === 'svg_morph') {
							wp_enqueue_script('gsapsvgmorph');
							$initscript = true;
						}
					}
					if (!empty($block['attrs']['reveal_enabled'])) {
						wp_enqueue_script('gsap-reveal-init');
					}
					if (!empty($block['attrs']['easecustom'])) {
						wp_enqueue_script('gsapcustomease');
					}
					if (!empty($block['attrs']['scroll_parallax_enabled'])) {
						wp_enqueue_script('gsap-scrollparallax-init');
					}
					if (!empty($block['attrs']['mouse_move_enabled'])) {
						wp_enqueue_script('gsap-mousemove-init');
					}
					if (!empty($block['attrs']['triggertype']) && $block['attrs']['triggertype'] == 'mousefollow') {
						$initscript = true;
					}
				}

				$attributearray = array(
					"x",
					"y",
					"z",
					"xo",
					"yo",
					"r",
					"rx",
					"ry",
					"s",
					"sx",
					"sy",
					"o",
					"xM",
					"yM",
					"zM",
					"xoM",
					"yoM",
					"rM",
					"rxM",
					"ryM",
					"sM",
					"sxM",
					"syM",
					"oM",
					"multiple_animation",
					"pinned",
					"pinspace",
					"variable1",
					"variable2",
					"variable3",
					"variable1value",
					"variable2value",
					"variable3value",
					"background",
					"videoplay"
				);

				if ($initscript) {
					wp_enqueue_script('gsap-animation-init');
				} else {
					foreach ($attributearray as $attributeitem) {
						if (!empty($block['attrs'][$attributeitem])) {
							wp_enqueue_script('gsap-animation-init');
							break;
						}
					}
				}

				// gsap init
			}
			// looking for gsap Flip
			else if ($blockname === 'greenshift-blocks/flipstate') {
				wp_enqueue_script('gsap-flip-init');
			} else if ($blockname === 'greenshift-blocks/flipfilter') {
				wp_enqueue_script('gsap-filter-init');
			}
			// looking for gsap sequencer
			else if ($blockname === 'greenshift-blocks/sequencer') {
				wp_enqueue_script('gsap-seq-init');
				$html = str_replace('img', 'img decoding="auto"', $html);
			}
			// looking for pin scroll
			else if ($blockname === 'greenshift-blocks/pinscroll') {
				wp_enqueue_script('gsap-animation');
				wp_enqueue_script('gsap-scrolltrigger');
				wp_enqueue_script('gsap-animation-init');
			} else if ($blockname === 'greenshift-blocks/smoothscroll') {
				if(isset($block['attrs']['type']) && $block['attrs']['type'] == 'lenis'){
					wp_enqueue_script('gs-smooth-scroll');
				}else{
					wp_enqueue_script('gsapsmoothscroll-init');
				}

			} else if ($blockname === 'greenshift-blocks/parallaxbg') {
				wp_enqueue_script('gsap-animation');
				wp_enqueue_script('gsap-scrolltrigger');
				wp_enqueue_script('gsap-scrollbg');
				if (function_exists('GSPB_make_dynamic_image') && !empty($block['attrs']['parallaximageDynamic']['dynamicEnable'])) {
					$html = $html . '<style scoped>#gspb-gsap-parbg-' . $block['attrs']['id'] . '{background-image:url(' . $block['attrs']['parallaximageurl'] . ');}</style>';
					$html = GSPB_make_dynamic_image($html, $block['attrs'], $block, $block['attrs']['parallaximageDynamic'], $block['attrs']['parallaximageurl']);
				}
			} else if ($blockname === 'greenshift-blocks/pagescroll') {
				wp_enqueue_script('gsap-animation');
				wp_enqueue_script('gsap-scrolltrigger');
				wp_enqueue_script('gsap-scrollx');
			}

			// looking for blob animation
			else if ($blockname === 'greenshift-blocks/blob') {
				wp_enqueue_script('gsap-animation');
				wp_enqueue_script('gs-blob-init');
				$html = str_replace('stopcolor', 'stop-color', $html);
			} else if ($blockname == 'greenshift-blocks/lottie') {
				wp_enqueue_script('gs-lottieloader');
			} else if ($blockname == 'greenshift-blocks/dotlottie') {
				wp_enqueue_script('gs-dotlottieloader');
			} else if ($blockname == 'greenshift-blocks/videoscroll') {
				wp_enqueue_script('gs-videoscroller');
			} else if ($blockname == 'greenshift-blocks/rive') {
				if (!empty($block['attrs']['loadnow'])) {
					wp_enqueue_script('gs-riveloadernow');
				} else {
					wp_enqueue_script('gs-riveloader');
				}
			}

			if (($blockname == 'greenshift-blocks/heading' || $blockname == 'greenshift-blocks/text') && !empty($block['attrs']['highlightanimate'])) {
				wp_enqueue_script('greenshift-inview');
			}

			if (!empty($block['attrs']['animatesvg'])) {
				wp_enqueue_script('gsap-animation');
				wp_enqueue_script('gsap-scrolltrigger');
				wp_enqueue_script('gsapsvgdraw');
				wp_enqueue_script('gsap-animation-init');
			}
			if (!empty($block['attrs']['animation']['usegsap'])) {
				wp_enqueue_script('gsap-animation');
				wp_enqueue_script('gsap-scrolltrigger');
				if (!empty($block['attrs']['animation']['type']) && $block['attrs']['animation']['type'] == 'text_transformations') {
					wp_enqueue_script('gsapsplittext');
				}
				if (!empty($block['attrs']['animation']['type']) && $block['attrs']['animation']['type'] == 'svg' && !empty($block['attrs']['animation']['svg_draw']) && $block['attrs']['animation']['svg_draw'] == true) {
					wp_enqueue_script('gsapsvgdraw');
				}
				if (!empty($block['attrs']['animation']['type']) && $block['attrs']['animation']['type'] == 'svg' && !empty($block['attrs']['animation']['path'])) {
					wp_enqueue_script('gsapsvgpath');
				}
				wp_enqueue_script('gsap-animation-init');
			} else if ($blockname == 'greenshift-blocks/pagenav') {
				wp_enqueue_script('gs-pagenav');
			}
			if ($block['blockName'] == 'greenshift-blocks/dynamic-3d') {
				wp_enqueue_script('gs-spline-init');
				if(!empty($block['attrs']['model_animations'])){
					wp_enqueue_script('gsap-animation');
					wp_enqueue_script('gsap-scrolltrigger');
					wp_enqueue_script('gs-3d-animation');
				}
				if(!empty($block['attrs']['variables'])){
					$variables = [];
					$dynamic = false;
					foreach($block['attrs']['variables'] as $index=>$variable){
						$variables[$index] = $variable;
						if(!empty($variable['dynamicEnable']) && function_exists('GSPB_make_dynamic_text')){
							$dynamic = true;
							$variables[$index]['value'] = GSPB_make_dynamic_text($variables[$index]['value'], $block['attrs'], $block, $variable);
						}
					}
					if($dynamic){
						$p = new WP_HTML_Tag_Processor( $html );
						if ( $p->next_tag( 'div' )) {
							$p->set_attribute( 'data-variables', json_encode($variables));
						}
						$html = $p->get_updated_html();
					}
				}
			}
		}


		return $html;
	}
}

//////////////////////////////////////////////////////////////////
// Enqueue Gutenberg block assets for backend editor.
//////////////////////////////////////////////////////////////////

if (!function_exists('greenShiftGsap_editor_assets')) {
	function greenShiftGsap_editor_assets()
	{
		// phpcs:ignor

		$index_asset_file = include(GREENSHIFTGSAP_DIR_PATH . 'build/index.asset.php');


		// Blocks Assets Scripts
		wp_enqueue_script(
			'greenShiftGsap-block-js', // Handle.
			GREENSHIFTGSAP_DIR_URL . 'build/index.js',
			array('greenShift-editor-js', 'greenShift-library-script', 'wp-block-editor', 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-data'),
			$index_asset_file['version'],
			true
		);

		$licenses = greenshift_edd_check_all_licenses();
		$is_premium = ((!empty($licenses['all_in_one']) && $licenses['all_in_one'] == 'valid') || (!empty($licenses['gsap_addon']) && $licenses['gsap_addon'] == 'valid') || (!empty($licenses['all_in_one_design']) && $licenses['all_in_one_design'] == 'valid')) ? true : false;

		$check = '';
		if (defined('REHUB_ADMIN_DIR') || $is_premium) {
			$check = 1;
		}
		$lc = array(
			'can_use_premium_code' => $check,
			'pluginURL' => GREENSHIFTGSAP_DIR_URL,
		);
		wp_localize_script('greenShiftGsap-block-js', 'greenshiftGSAP', $lc);

		// gsap animation
		wp_enqueue_script('gsap-animation');
		wp_enqueue_script('gsap-scrolltrigger');
		wp_enqueue_script('gsapcustomease');
		wp_enqueue_script('gsapsplittext');
		wp_enqueue_script('gsapsvgdraw');
		wp_enqueue_script('gsapsvgpath');
		wp_enqueue_script('gsapsvgmorph');
		wp_enqueue_script('gsapflip');
		wp_enqueue_script('gsap-flip-init');
		wp_enqueue_script('gsap-seq-init');

		// gsap init
		wp_enqueue_script('gsap-animation-init');
		wp_enqueue_script('gs-3d-animation');
		wp_enqueue_script('gsap-reveal-init');
		wp_enqueue_script('gsap-scrollparallax-init');
		wp_enqueue_script('gsap-scrollbg');
		wp_enqueue_script('gsap-scrollx');
		wp_enqueue_script('gsap-mousemove-init');
	}
}

if (!function_exists('greenShiftGsap_block_assets')) {
	function greenShiftGsap_block_assets()
	{
		if (is_admin()) {
		}
	}
}

function greenshiftgsap_mime_types($mimes)
{
	$mimes['riv'] = 'application/octet-stream';
	$mimes['lottie'] = 'application/octet-stream';
	return $mimes;
}
add_filter('upload_mimes', 'greenshiftgsap_mime_types');

//////////////////////////////////////////////////////////////////
// Localization
//////////////////////////////////////////////////////////////////
//function greenshiftgsap_plugin_load_textdomain()
//{
	//load_plugin_textdomain('greenshiftgsap', false, GREENSHIFTGSAP_DIR_URL . 'languages');
//}
//add_action('plugins_loaded', 'greenshiftgsap_plugin_load_textdomain');