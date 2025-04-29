<?php

/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    EA_Webinar
 * @subpackage EA_Webinar/includes
 */

class EA_Webinar
{

  /**
   * The loader that's responsible for maintaining and registering all hooks that power
   * the plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      EAW_Loader    $loader    Maintains and registers all hooks for the plugin.
   */
  protected $loader;

  /**
   * Define the core functionality of the plugin.
   *
   * @since    1.0.0
   */
  public function __construct()
  {
    $this->load_dependencies();
    $this->set_locale();
    $this->define_admin_hooks();
    $this->define_public_hooks();
  }

  /**
   * Load the required dependencies for this plugin.
   *
   * @since    1.0.0
   * @access   private
   */
  private function load_dependencies()
  {
    require_once EAW_PLUGIN_DIR . 'includes/class-eaw-loader.php';
    require_once EAW_PLUGIN_DIR . 'includes/class-eaw-i18n.php';
    require_once EAW_PLUGIN_DIR . 'admin/class-eaw-admin.php';
    require_once EAW_PLUGIN_DIR . 'public/class-eaw-public.php';

    $this->loader = new EAW_Loader();
  }

  /**
   * Define the locale for this plugin for internationalization.
   *
   * @since    1.0.0
   * @access   private
   */
  private function set_locale()
  {
    $plugin_i18n = new EAW_i18n();
    $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
  }

  /**
   * Register all of the hooks related to the admin area functionality
   * of the plugin.
   *
   * @since    1.0.0
   * @access   private
   */
  private function define_admin_hooks()
  {
    $plugin_admin = new EAW_Admin();

    // Admin menu and settings
    $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
    $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');

    // Webinar management
    $this->loader->add_action('init', $plugin_admin, 'register_webinar_post_type');
    $this->loader->add_action('add_meta_boxes', $plugin_admin, 'add_webinar_meta_boxes');
    $this->loader->add_action('save_post', $plugin_admin, 'save_webinar_meta');

    // Admin assets
    $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
    $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
  }

  /**
   * Register all of the hooks related to the public-facing functionality
   * of the plugin.
   *
   * @since    1.0.0
   * @access   private
   */
  private function define_public_hooks()
  {
    $plugin_public = new EAW_Public();

    // Public assets
    $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
    $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

    // Registration form shortcode
    $this->loader->add_action('init', $plugin_public, 'register_shortcodes');

    // AJAX handlers
    $this->loader->add_action('wp_ajax_eaw_register_webinar', $plugin_public, 'handle_registration');
    $this->loader->add_action('wp_ajax_nopriv_eaw_register_webinar', $plugin_public, 'handle_registration');
  }

  /**
   * Run the loader to execute all of the hooks with WordPress.
   *
   * @since    1.0.0
   */
  public function run()
  {
    $this->loader->run();
  }
}
