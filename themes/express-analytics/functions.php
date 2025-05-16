<?php

if (! function_exists('express_analytics_setup')) :

	// Include plugin.php for is_plugin_active function
	require_once ABSPATH . 'wp-admin/includes/plugin.php';

	function express_analytics_setup()
	{

		/*
	 * Define Theme Version
	 */
		define('EXPRESS_ANALYTICS_THEME_VERSION', '1.2');


		// Add default posts and comments RSS feed links to head.
		add_theme_support('automatic-feed-links');
		add_theme_support('wp-block-styles');
		add_theme_support('editor-styles');
		/*
	 * Let WordPress manage the document title.
	 */
		add_theme_support('title-tag');

		/*
	 * Enable support for Post Thumbnails on posts and pages.
	 */
		add_theme_support('post-thumbnails');

		/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
		add_theme_support('html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		));

		//Add selective refresh for sidebar widget
		add_theme_support('customize-selective-refresh-widgets');

		//Add custom logo support
		add_theme_support('custom-logo');

		remove_theme_support('widgets-block-editor');

		// Change text domain
		load_theme_textdomain('express-analytics', get_template_directory() . '/languages');
	}
endif;
add_action('after_setup_theme', 'express_analytics_setup');


/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 */
function express_analytics_content_width()
{
	$GLOBALS['content_width'] = apply_filters('express_analytics_content_width', 1170);
}
add_action('after_setup_theme', 'express_analytics_content_width', 0);


add_filter('wp_get_attachment_image_attributes', function ($attr) {
	if (isset($attr['class'])  && 'custom-logo' === $attr['class'])
		$attr['class'] = 'custom-logo navbar-brand';

	return $attr;
});


if (! function_exists('express_analytics_setup_style')) {
	function express_analytics_setup_style()
	{
		add_theme_support('wp-block-styles');
		add_editor_style('/assets/css/bootstrap.min.css');
		add_editor_style('/assets/css/all.min.css');
		add_editor_style('/assets/css/woo.css');
		add_editor_style('/assets/css/block-widgets.css');
		add_editor_style('style.css');
	}
}
add_action('admin_init', 'express_analytics_setup_style');

/**
 * Enqueue scripts and styles.
 */
function express_analytics_scripts()
{
	// Add Font Awesome preload
	add_action('wp_head', function () {
		echo '<link rel="preload" href="' . get_template_directory_uri() . '/assets/webfonts/fa-solid-900.woff2" as="font" type="font/woff2" crossorigin>';
	}, 1);

	wp_enqueue_style('bootstrap-min-css', get_template_directory_uri() . '/assets/css/bootstrap.min.css');
	wp_enqueue_style('owl-carousel-min-css', get_template_directory_uri() . '/assets/css/owl.carousel.min.css');
	wp_enqueue_style('font-awesome-all', get_template_directory_uri() . '/assets/css/all.min.css');
	wp_enqueue_style('animate-min-css', get_template_directory_uri() . '/assets/css/animate.min.css');
	wp_enqueue_style('block-style-css', get_template_directory_uri() . '/assets/css/block-widgets.css');
	wp_enqueue_style('legacy-style-css', get_template_directory_uri() . '/assets/css/lagecy-widgets.css');
	wp_enqueue_style('header-style-css', get_template_directory_uri() . '/assets/css/header.css');
	wp_enqueue_style('montserrat-font', get_template_directory_uri() . '/assets/css/montserrat.css');
	wp_enqueue_style('express-analytics-style', get_stylesheet_uri());
	wp_enqueue_style('mediaquery-css', get_template_directory_uri() . '/assets/css/mediaquery.css');

	// Scripts
	wp_enqueue_script('jquery');
	wp_enqueue_script('bootstrap-bundle', get_template_directory_uri() . '/assets/js/bootstrap.bundle.js', array('jquery'), '1.0.0', true);
	wp_enqueue_script('owl-carousel-min-js', get_template_directory_uri() . '/assets/js/owl.carousel.min.js', array('jquery'), '1.0.0', true);
	wp_enqueue_script('imagesloaded-js', get_template_directory_uri() . '/assets/js/imagesloaded.pkgd.min.js', array('jquery'), '1.0.0', true);
	wp_enqueue_script('isotope-js', get_template_directory_uri() . '/assets/js/isotope.pkgd.min.js', array('jquery'), '1.0.0', true);
	wp_enqueue_script('particles-js', get_template_directory_uri() . '/assets/js/lib/particles.min.js', array('jquery'), '1.0.0', true);
	wp_enqueue_script('show-more-js', get_template_directory_uri() . '/assets/js/show-more.js', array('jquery'), '1.0.0', true);
	wp_enqueue_script('script-js', get_template_directory_uri() . '/assets/js/script.js', array('jquery', 'particles-js'), '1.0.0', true);

	// Remove ea-webinar scripts if plugin is not active
	if (!is_plugin_active('ea-webinar/ea-webinar.php')) {
		wp_dequeue_style('ea-webinar-public');
		wp_dequeue_script('ea-webinar-public');
	}

	if (is_singular() && comments_open() && get_option('thread_comments')) {
		wp_enqueue_script('comment-reply');
	}
}
add_action('wp_enqueue_scripts', 'express_analytics_scripts');


/**
 * Register block patterns
 */
function express_analytics_register_patterns()
{
	// Register pattern category first
	if (function_exists('register_block_pattern_category')) {
		register_block_pattern_category('ea-patterns', array(
			'label' => __('EA-Patterns', 'express-analytics')
		));
	}

	// Register patterns
	if (function_exists('register_block_pattern')) {
		// Get the pattern content
		ob_start();
		include get_template_directory() . '/patterns/chaotic.php';
		$pattern_content = ob_get_clean();

		register_block_pattern(
			'express-analytics/chaotic',
			array(
				'title'       => __('Express Analytics Chaotic Services', 'express-analytics'),
				'description' => __('A chaotic scrolling section displaying analytics services with icons in a modern, animated layout.', 'express-analytics'),
				'content'     => $pattern_content,
				'categories'  => array('ea-patterns'),
				'keywords'    => array('services', 'analytics', 'scroll', 'cards'),
				'viewportWidth' => 1400,
			)
		);
	}
}
add_action('init', 'express_analytics_register_patterns');

function express_analytics_register_block_styles()
{
	if (function_exists('register_block_style')) {
		register_block_style(
			'core/button', // Button block
			array(
				'name'  => 'rounded-button',
				'label' => __('Rounded Button', 'express-analytics'),
				'inline_style' => '.wp-block-button.is-style-rounded-button a { border-radius: 50px; padding: 10px 20px; }',
			)
		);
	}
}
add_action('init', 'express_analytics_register_block_styles');

/**
 * Add custom post types.
 */
function expressanalytics_register_post_types()
{
	// Resources
	register_post_type(
		'resource',
		array(
			'labels' => array(
				'name'               => __('Resources', 'expressanalytics'),
				'singular_name'      => __('Resource', 'expressanalytics'),
				'add_new'           => __('Add New', 'expressanalytics'),
				'add_new_item'      => __('Add New Resource', 'expressanalytics'),
				'edit_item'         => __('Edit Resource', 'expressanalytics'),
				'new_item'          => __('New Resource', 'expressanalytics'),
				'view_item'         => __('View Resource', 'expressanalytics'),
				'search_items'      => __('Search Resources', 'expressanalytics'),
				'not_found'         => __('No resources found', 'expressanalytics'),
				'not_found_in_trash' => __('No resources found in trash', 'expressanalytics'),
			),
			'public'      => true,
			'has_archive' => true,
			'menu_icon'   => 'dashicons-portfolio',
			'supports'    => array('title', 'thumbnail', 'excerpt'),
			'rewrite'     => array('slug' => 'resources'),
			'show_in_rest' => true,
		)
	);

	// Services
	register_post_type(
		'service',
		array(
			'labels' => array(
				'name'               => __('Services', 'expressanalytics'),
				'singular_name'      => __('Service', 'expressanalytics'),
				'add_new'           => __('Add New', 'expressanalytics'),
				'add_new_item'      => __('Add New Service', 'expressanalytics'),
				'edit_item'         => __('Edit Service', 'expressanalytics'),
				'new_item'          => __('New Service', 'expressanalytics'),
				'view_item'         => __('View Service', 'expressanalytics'),
				'search_items'      => __('Search Services', 'expressanalytics'),
				'not_found'         => __('No services found', 'expressanalytics'),
				'not_found_in_trash' => __('No services found in trash', 'expressanalytics'),
			),
			'public'      => true,
			'has_archive' => true,
			'menu_icon'   => 'dashicons-analytics',
			'supports'    => array('title', 'thumbnail', 'excerpt'),
			'rewrite'     => array('slug' => 'services'),
			'show_in_rest' => true,
		)
	);

	// Solutions
	register_post_type(
		'solution',
		array(
			'labels' => array(
				'name'               => __('Solutions', 'expressanalytics'),
				'singular_name'      => __('Solution', 'expressanalytics'),
				'add_new'           => __('Add New', 'expressanalytics'),
				'add_new_item'      => __('Add New Solution', 'expressanalytics'),
				'edit_item'         => __('Edit Solution', 'expressanalytics'),
				'new_item'          => __('New Solution', 'expressanalytics'),
				'view_item'         => __('View Solution', 'expressanalytics'),
				'search_items'      => __('Search Solutions', 'expressanalytics'),
				'not_found'         => __('No solutions found', 'expressanalytics'),
				'not_found_in_trash' => __('No solutions found in trash', 'expressanalytics'),
			),
			'public'      => true,
			'has_archive' => true,
			'menu_icon'   => 'dashicons-chart-area',
			'supports'    => array('title', 'thumbnail', 'excerpt'),
			'rewrite'     => array('slug' => 'solutions'),
			'show_in_rest' => true,
		)
	);
}
add_action('init', 'expressanalytics_register_post_types');

/**
 * Add custom taxonomies.
 */
function expressanalytics_register_taxonomies()
{
	// Category taxonomy for Resources
	register_taxonomy(
		'resource-category',
		array('resource'),
		array(
			'labels' => array(
				'name'              => __('Resource Categories', 'expressanalytics'),
				'singular_name'     => __('Resource Category', 'expressanalytics'),
				'search_items'      => __('Search Resource Categories', 'expressanalytics'),
				'all_items'         => __('All Resource Categories', 'expressanalytics'),
				'edit_item'         => __('Edit Resource Category', 'expressanalytics'),
				'update_item'       => __('Update Resource Category', 'expressanalytics'),
				'add_new_item'      => __('Add New Resource Category', 'expressanalytics'),
				'new_item_name'     => __('New Resource Category Name', 'expressanalytics'),
				'menu_name'         => __('Resource Categories', 'expressanalytics'),
			),
			'hierarchical' => true,
			'show_ui' => true,
			'show_admin_column' => true,
			'query_var' => true,
			'rewrite' => array('slug' => 'resource-category'),
			'show_in_rest' => true
		)
	);

	// Service Category taxonomy
	register_taxonomy(
		'service-category',
		array('service'),
		array(
			'labels' => array(
				'name'              => __('Service Categories', 'expressanalytics'),
				'singular_name'     => __('Service Category', 'expressanalytics'),
				'search_items'      => __('Search Service Categories', 'expressanalytics'),
				'all_items'         => __('All Service Categories', 'expressanalytics'),
				'edit_item'         => __('Edit Service Category', 'expressanalytics'),
				'update_item'       => __('Update Service Category', 'expressanalytics'),
				'add_new_item'      => __('Add New Service Category', 'expressanalytics'),
				'new_item_name'     => __('New Service Category Name', 'expressanalytics'),
				'menu_name'         => __('Categories', 'expressanalytics'),
			),
			'hierarchical'      => true,
			'show_ui'          => true,
			'show_admin_column' => true,
			'query_var'        => true,
			'rewrite'          => array('slug' => 'service-category'),
			'show_in_rest'     => true,
		)
	);
	// Solutions Category taxonomy
	register_taxonomy(
		'solution-category',
		array('solution'),
		array(
			'labels' => array(
				'name'              => __('Solution Categories', 'expressanalytics'),
				'singular_name'     => __('Solution Category', 'expressanalytics'),
				'search_items'      => __('Search Solution Categories', 'expressanalytics'),
				'all_items'         => __('All Solution Categories', 'expressanalytics'),
				'edit_item'         => __('Edit Solution Category', 'expressanalytics'),
				'update_item'       => __('Update Solution Category', 'expressanalytics'),
				'add_new_item'      => __('Add New Solution Category', 'expressanalytics'),
				'new_item_name'     => __('New Solution Category Name', 'expressanalytics'),
				'menu_name'         => __('Categories', 'expressanalytics'),
			),
			'hierarchical'      => true,
			'show_ui'          => true,
			'show_admin_column' => true,
			'query_var'        => true,
			'rewrite'          => array('slug' => 'solution-category'),
			'show_in_rest'     => true,
		)
	);
}
add_action('init', 'expressanalytics_register_taxonomies');

// //Express Analytics Custom ACF Fields for Blocks.

// add_filter(
// 	'meta_field_block_get_acf_field',
// 	function ($block_content, $post_id, $field, $raw_value) {
// 		$field_name = $field['name'] ?? '';

// 		// Hero Button Field
// 		if ('hero_button_field' === $field_name) {
// 			$button_text = get_field('hero_button_text', $post_id);
// 			$button_url = get_field('hero_url', $post_id);

// 			// Validate both text and URL
// 			if ($button_text && $button_url) {
// 				$block_content = sprintf(
// 					'<a href="%s" class="wp-block-button__link wp-element-button">%s</a>',
// 					esc_url($button_url),
// 					esc_html($button_text)
// 				);
// 			}
// 		}

// 		return $block_content;
// 	},
// 	10,
// 	4
// );

// //Custom ACF Fields for Video Block
// add_filter(
// 	'meta_field_block_get_acf_field',
// 	function ($block_content, $post_id, $field, $raw_value) {
// 		$field_name = $field['name'] ?? '';

// 		if ('hero_sol_video' === $field_name) {
// 			$video_url = get_field('hero_sol_video', $post_id);

// 			if ($video_url && filter_var($video_url, FILTER_VALIDATE_URL)) {
// 				$block_content = sprintf(
// 					'<video src="%s" loop muted autoplay playsinline></video>',
// 					esc_url($video_url)
// 				);
// 			}
// 		}

// 		return $block_content;
// 	},
// 	10,
// 	4
// );
// add_action('acf/init', 'set_acf_settings');
// function set_acf_settings()
// {
// 	acf_update_setting('enable_shortcode', true);
// }

// //Custom ACF Fields for CTA Button Block
// add_filter(
// 	'meta_field_block_get_acf_field',
// 	function ($block_content, $post_id, $field, $raw_value) {
// 		$field_name = $field['name'] ?? '';

// 		// Hero Button Field
// 		if ('cta_button' === $field_name) {
// 			$button_text = get_field('cta_button_title', $post_id);
// 			$button_url = get_field('cta_button_url', $post_id);

// 			// Validate both text and URL
// 			if ($button_text && $button_url) {
// 				$block_content = sprintf(
// 					'<a href="%s" class="wp-block-button__link wp-element-button">%s</a>',
// 					esc_url($button_url),
// 					esc_html($button_text)
// 				);
// 			}
// 		}

// 		return $block_content;
// 	},
// 	10,
// 	4
// );

// Disable theme update checks
add_filter('site_transient_update_themes', function ($value) {
	if (isset($value) && is_object($value)) {
		unset($value->response['express-analytics']);
	}
	return $value;
});

// Remove theme from update checks
add_filter('http_request_args', function ($args, $url) {
	if (strpos($url, 'https://api.wordpress.org/themes/update-check') !== false) {
		$themes = json_decode($args['body']['themes']);
		unset($themes->themes->{'express-analytics'});
		$args['body']['themes'] = json_encode($themes);
	}
	return $args;
}, 10, 2);

add_filter('render_block', 'inject_category_into_stackable_heading', 10, 2);
function inject_category_into_stackable_heading($block_content, $block)
{
	// Ensure we are inside The Loop
	if (!in_the_loop()) {
		return $block_content;
	}

	// Target only Stackable Heading blocks
	if ($block['blockName'] === 'wp:stackable/heading') {
		// Look for a specific class (e.g., show-category)
		if (isset($block['attrs']['className']) && strpos($block['attrs']['className'], 'show-category') !== false) {

			// Get the category list
			$categories = get_the_category();
			if (!empty($categories)) {
				$cat_links = array_map(function ($cat) {
					return '<a href="' . esc_url(get_category_link($cat->term_id)) . '">' . esc_html($cat->name) . '</a>';
				}, $categories);
				$category_output = implode(', ', $cat_links);
			} else {
				$category_output = 'Uncategorized';
			}

			// Inject category links into <h4 class="stk-block-heading__text">...</h4>
			// This targets the heading precisely without altering other structure
			$block_content = preg_replace_callback(
				'/<h4([^>]*)class="([^"]*stk-block-heading__text[^"]*)"([^>]*)>(.*?)<\/h4>/is',
				function ($matches) use ($category_output) {
					return '<h4' . $matches[1] . 'class="' . $matches[2] . '"' . $matches[3] . '>' . $category_output . '</h4>';
				},
				$block_content
			);
		}
	}

	return $block_content;
}

/**
 * Add CORS headers for font files
 */
function express_analytics_add_cors_headers()
{
	if (strpos($_SERVER['REQUEST_URI'], '.woff2') !== false) {
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET');
		header('Access-Control-Allow-Headers: *');
	}
}
add_action('init', 'express_analytics_add_cors_headers');

/**
 * Allow REST API access
 */
function express_analytics_allow_rest_api()
{
	remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
	add_filter('rest_pre_serve_request', function ($value) {
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
		return $value;
	});
}
add_action('rest_api_init', 'express_analytics_allow_rest_api');

/**
 * Add proper block supports
 */
function express_analytics_block_supports()
{
	add_theme_support('core-block-patterns');

	// Add support for responsive embeds
	add_theme_support('responsive-embeds');

	// Add support for custom units
	add_theme_support('custom-units');

	// Add support for editor styles
	add_theme_support('editor-styles');

	// Add support for post thumbnails
	add_theme_support('post-thumbnails');

	// Add support for custom line heights
	add_theme_support('custom-line-height');

	// Add support for experimental link color control
	add_theme_support('experimental-link-color');

	// Add support for custom spacing
	add_theme_support('custom-spacing');
}
add_action('after_setup_theme', 'express_analytics_block_supports');

function register_solution_meta_fields()
{
	register_post_meta('solution', 'hero_title', [
		'type' => 'string',
		'single' => true,
		'show_in_rest' => true,
		'auth_callback' => '__return_true',
	]);
	register_post_meta('solution', 'hero_description', [
		'type'         => 'string',
		'single'       => true,
		'show_in_rest' => true,
		'auth_callback' => '__return_true',
	]);

	register_post_meta('solution', 'hero_url', [
		'type'         => 'string',
		'single'       => true,
		'show_in_rest' => true,
		'auth_callback' => '__return_true',
	]);

	register_post_meta('solution', 'hero_sol_video', [
		'type'         => 'string',
		'single'       => true,
		'show_in_rest' => true,
		'auth_callback' => '__return_true',
	]);

	register_post_meta('solution', 'hero_sol_image', [
		'type'         => 'string',
		'single'       => true,
		'show_in_rest' => true,
		'auth_callback' => '__return_true',
	]);

	register_post_meta('solution', 'sub_header_title', [
		'type'         => 'string',
		'single'       => true,
		'show_in_rest' => true,
		'auth_callback' => '__return_true',
	]);

	register_post_meta('solution', 'sub_header_description', [
		'type'         => 'string',
		'single'       => true,
		'show_in_rest' => true,
		'auth_callback' => '__return_true',
	]);
	register_post_meta('solution', 'button_url', [
		'type'         => 'string',
		'single'       => true,
		'show_in_rest' => true,
		'auth_callback' => '__return_true',
	]);
	register_post_meta('solution', 'video_title', [
		'type'         => 'string',
		'single'       => true,
		'show_in_rest' => true,
		'auth_callback' => '__return_true',
	]);
	register_post_meta('solution', 'video_description', [
		'type'         => 'string',
		'single'       => true,
		'show_in_rest' => true,
		'auth_callback' => '__return_true',
	]);
	register_post_meta('solution', 'video_url', [
		'type'         => 'string',
		'single'       => true,
		'show_in_rest' => true,
		'auth_callback' => '__return_true',
	]);
}
add_action('init', 'register_solution_meta_fields');

add_filter('block_editor_settings_all', function ($settings, $context) {
	$settings['hasResponsivePreview'] = true;
	return $settings;
}, 10, 2);
// Allow SVG upload
function shantanu_allow_svg_uploads($mimes)
{
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
}
add_filter('upload_mimes', 'shantanu_allow_svg_uploads');

// Fix SVG display in media library
function shantanu_fix_svg_display()
{
	echo '<style type="text/css">
        .attachment-266x266, .thumbnail img {
             width: 100% !important;
             height: auto !important;
        }
    </style>';
}
add_action('admin_head', 'shantanu_fix_svg_display');

// Optional: Extra check for admin users only
function shantanu_restrict_svg_uploads($file)
{
	if ($file['type'] === 'image/svg+xml' && !current_user_can('administrator')) {
		$file['error'] = 'Only administrators can upload SVG files.';
	}
	return $file;
}
add_filter('wp_handle_upload_prefilter', 'shantanu_restrict_svg_uploads');
function ea_estimated_reading_time($atts)
{
	global $post;
	$content = $post->post_content;
	$word_count = str_word_count(strip_tags($content));
	$read_time = ceil($word_count / 200); // 200 wpm average reading speed

	return $read_time . ' min read';
}
add_shortcode('read_time', 'ea_estimated_reading_time');
