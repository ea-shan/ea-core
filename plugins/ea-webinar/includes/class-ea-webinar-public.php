<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://expressanalytics.com
 * @since      1.0.0
 *
 * @package    EA_Webinar
 * @subpackage EA_Webinar/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for
 * enqueuing the public-facing stylesheet and JavaScript.
 *
 * @package    EA_Webinar
 * @subpackage EA_Webinar/public
 * @author     Express Analytics <info@expressanalytics.com>
 */
class EA_Webinar_Public
{

  /**
   * The ID of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $plugin_name    The ID of this plugin.
   */
  private $plugin_name;

  /**
   * The version of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $version    The current version of this plugin.
   */
  private $version;

  /**
   * Initialize the class and set its properties.
   *
   * @since    1.0.0
   * @param    string    $plugin_name    The name of the plugin.
   * @param    string    $version        The version of this plugin.
   */
  public function __construct($plugin_name, $version)
  {
    $this->plugin_name = $plugin_name;
    $this->version = $version;

    add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
    add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
  }

  /**
   * Register the stylesheets for the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function enqueue_styles()
  {
    wp_enqueue_style(
      $this->plugin_name,
      plugin_dir_url(__FILE__) . '../assets/css/ea-webinar-public.css',
      array(),
      $this->version,
      'all'
    );
  }

  /**
   * Register the JavaScript for the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function enqueue_scripts()
  {
    wp_enqueue_script(
      $this->plugin_name,
      plugin_dir_url(__FILE__) . '../assets/js/ea-webinar-public.js',
      array('jquery'),
      $this->version,
      true
    );

    // Localize the script with data needed for AJAX
    wp_localize_script(
      $this->plugin_name,
      'ea_webinar_obj',
      array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ea_webinar_nonce'),
        'messages' => array(
          'success' => __('Registration successful! Check your email for details.', 'ea-webinar'),
          'error' => __('Registration failed. Please try again.', 'ea-webinar'),
          'required' => __('Please fill in all required fields.', 'ea-webinar'),
          'low_spots' => __('Only {spots} spots remaining!', 'ea-webinar')
        )
      )
    );
  }
}
