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
 * Defines the plugin name, version, and hooks for the public-facing side.
 *
 * @package    EA_Webinar
 * @subpackage EA_Webinar/public
 * @author     Express Analytics <info@expressanalytics.com>
 */
class EAW_Public
{

  /**
   * Initialize the class and set its properties.
   *
   * @since    1.0.0
   */
  public function __construct() {}

  /**
   * Register the stylesheets for the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function enqueue_styles()
  {
    wp_enqueue_style(
      'ea-webinar-public',
      plugin_dir_url(__FILE__) . 'css/ea-webinar-public.css',
      array(),
      EAW_VERSION,
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
      'ea-webinar-public',
      plugin_dir_url(__FILE__) . 'js/ea-webinar-public.js',
      array('jquery'),
      EAW_VERSION,
      true
    );

    wp_localize_script(
      'ea-webinar-public',
      'eawAjax',
      array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('eaw-registration-nonce')
      )
    );
  }

  /**
   * Register shortcodes
   *
   * @since    1.0.0
   */
  public function register_shortcodes()
  {
    add_shortcode('eaw_registration_form', array($this, 'render_registration_form'));
  }

  /**
   * Render the registration form
   *
   * @since    1.0.0
   * @param    array    $atts    Shortcode attributes
   * @return   string            Registration form HTML
   */
  public function render_registration_form($atts)
  {
    $atts = shortcode_atts(
      array(
        'webinar_id' => 0,
      ),
      $atts,
      'eaw_registration_form'
    );

    if (!$atts['webinar_id']) {
      return '<p class="eaw-error">' . esc_html__('Please specify a webinar ID.', 'ea-webinar') . '</p>';
    }

    $webinar = get_post($atts['webinar_id']);
    if (!$webinar || $webinar->post_type !== 'webinar') {
      return '<p class="eaw-error">' . esc_html__('Invalid webinar ID.', 'ea-webinar') . '</p>';
    }

    ob_start();
    include plugin_dir_path(__FILE__) . 'partials/registration-form.php';
    return ob_get_clean();
  }

  /**
   * Handle webinar registration
   *
   * @since    1.0.0
   */
  public function handle_registration()
  {
    check_ajax_referer('eaw-registration-nonce', 'nonce');

    $webinar_id = isset($_POST['webinar_id']) ? intval($_POST['webinar_id']) : 0;
    $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $company = isset($_POST['company']) ? sanitize_text_field($_POST['company']) : '';
    $job_title = isset($_POST['job_title']) ? sanitize_text_field($_POST['job_title']) : '';
    $questions = isset($_POST['questions']) ? sanitize_textarea_field($_POST['questions']) : '';

    if (!$webinar_id || !$name || !$email) {
      wp_send_json_error(array(
        'message' => __('Please fill in all required fields.', 'ea-webinar')
      ));
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'eaw_registrations';

    $result = $wpdb->insert(
      $table_name,
      array(
        'webinar_id' => $webinar_id,
        'name' => $name,
        'email' => $email,
        'company' => $company,
        'job_title' => $job_title,
        'questions' => $questions,
        'registration_date' => current_time('mysql')
      ),
      array('%d', '%s', '%s', '%s', '%s', '%s', '%s')
    );

    if ($result === false) {
      wp_send_json_error(array(
        'message' => __('Registration failed. Please try again.', 'ea-webinar')
      ));
    }

    // Send confirmation email
    $this->send_confirmation_email($webinar_id, $name, $email);

    wp_send_json_success(array(
      'message' => __('Registration successful! Check your email for confirmation.', 'ea-webinar')
    ));
  }

  /**
   * Send confirmation email to registrant
   *
   * @since    1.0.0
   * @param    int       $webinar_id    Webinar post ID
   * @param    string    $name          Registrant's name
   * @param    string    $email         Registrant's email
   */
  private function send_confirmation_email($webinar_id, $name, $email)
  {
    $webinar = get_post($webinar_id);
    $webinar_date = get_post_meta($webinar_id, '_eaw_date', true);
    $webinar_time = get_post_meta($webinar_id, '_eaw_time', true);

    $template = get_option('eaw_email_template');
    $site_name = get_bloginfo('name');

    $message = str_replace(
      array('{name}', '{webinar_title}', '{webinar_date}', '{webinar_time}', '{site_name}'),
      array($name, $webinar->post_title, $webinar_date, $webinar_time, $site_name),
      $template
    );

    $headers = array('Content-Type: text/html; charset=UTF-8');

    wp_mail(
      $email,
      sprintf(__('Registration Confirmation: %s', 'ea-webinar'), $webinar->post_title),
      $message,
      $headers
    );
  }
}
