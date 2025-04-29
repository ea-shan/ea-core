<?php

/**
 * Google API Settings for Job Management System
 *
 * @package    Job_Management_System
 * @subpackage Job_Management_System/admin
 */

defined('WPINC') || exit;

/**
 * Class JMS_Google_Settings
 */
class JMS_Google_Settings
{

  /**
   * Initialize the class and set its properties.
   */
  public function __construct()
  {
    add_action('admin_menu', array($this, 'add_settings_page'));
    add_action('admin_init', array($this, 'register_settings'));
  }

  /**
   * Add settings page to admin menu
   */
  public function add_settings_page()
  {
    add_submenu_page(
      'edit.php?post_type=jms_job',
      __('Google API Settings', 'job-management-system'),
      __('Google API Settings', 'job-management-system'),
      'manage_options',
      'jms-google-settings',
      array($this, 'render_settings_page')
    );
  }

  /**
   * Register settings
   */
  public function register_settings()
  {
    register_setting('jms_google_settings', 'jms_google_credentials');
    register_setting('jms_google_settings', 'jms_google_token');

    add_settings_section(
      'jms_google_api_section',
      __('Google API Configuration', 'job-management-system'),
      array($this, 'render_section_description'),
      'jms_google_settings'
    );

    add_settings_field(
      'jms_google_credentials',
      __('Google API Credentials', 'job-management-system'),
      array($this, 'render_credentials_field'),
      'jms_google_settings',
      'jms_google_api_section'
    );

    add_settings_field(
      'jms_google_token',
      __('Google API Token', 'job-management-system'),
      array($this, 'render_token_field'),
      'jms_google_settings',
      'jms_google_api_section'
    );
  }

  /**
   * Render the settings page
   */
  public function render_settings_page()
  {
    if (!current_user_can('manage_options')) {
      return;
    }

    // Check if OAuth flow is complete
    if (isset($_GET['code'])) {
      $this->handle_oauth_callback();
    }

?>
    <div class="wrap">
      <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

      <?php
      $credentials = get_option('jms_google_credentials');
      $token = get_option('jms_google_token');

      if (!empty($credentials) && !empty($token)) {
        echo '<div class="notice notice-success"><p>' .
          esc_html__('Google API is configured and connected.', 'job-management-system') .
          '</p></div>';
      }
      ?>

      <form action="options.php" method="post">
        <?php
        settings_fields('jms_google_settings');
        do_settings_sections('jms_google_settings');
        submit_button(__('Save Settings', 'job-management-system'));
        ?>
      </form>

      <?php if (!empty($credentials)) : ?>
        <hr>
        <h2><?php esc_html_e('Google OAuth Authorization', 'job-management-system'); ?></h2>
        <p><?php esc_html_e('Click the button below to authorize the application with Google Calendar:', 'job-management-system'); ?></p>
        <a href="<?php echo esc_url($this->get_oauth_url()); ?>" class="button button-primary">
          <?php esc_html_e('Authorize with Google', 'job-management-system'); ?>
        </a>
      <?php endif; ?>
    </div>
  <?php
  }

  /**
   * Render section description
   */
  public function render_section_description()
  {
  ?>
    <p>
      <?php esc_html_e('Configure your Google API credentials to enable Calendar and Meet integration.', 'job-management-system'); ?>
      <a href="https://console.cloud.google.com/apis/credentials" target="_blank">
        <?php esc_html_e('Get your credentials from Google Cloud Console', 'job-management-system'); ?>
      </a>
    </p>
  <?php
  }

  /**
   * Render credentials field
   */
  public function render_credentials_field()
  {
    $credentials = get_option('jms_google_credentials');
  ?>
    <textarea name="jms_google_credentials" rows="10" cols="50" class="large-text code"><?php
                                                                                        echo esc_textarea($credentials);
                                                                                        ?></textarea>
    <p class="description">
      <?php esc_html_e('Paste your Google API credentials JSON here.', 'job-management-system'); ?>
    </p>
  <?php
  }

  /**
   * Render token field
   */
  public function render_token_field()
  {
    $token = get_option('jms_google_token');
  ?>
    <textarea name="jms_google_token" rows="5" cols="50" class="large-text code" readonly><?php
                                                                                          echo esc_textarea($token);
                                                                                          ?></textarea>
    <p class="description">
      <?php esc_html_e('This field is automatically populated when you authorize with Google.', 'job-management-system'); ?>
    </p>
<?php
  }

  /**
   * Get OAuth URL
   *
   * @return string
   */
  private function get_oauth_url()
  {
    $credentials = json_decode(get_option('jms_google_credentials'), true);

    if (empty($credentials['web']['client_id'])) {
      return '#';
    }

    $client_id = $credentials['web']['client_id'];
    $redirect_uri = admin_url('edit.php?post_type=jms_job&page=jms-google-settings');
    $scope = urlencode('https://www.googleapis.com/auth/calendar');

    return sprintf(
      'https://accounts.google.com/o/oauth2/v2/auth?client_id=%s&redirect_uri=%s&response_type=code&scope=%s&access_type=offline&prompt=consent',
      $client_id,
      urlencode($redirect_uri),
      $scope
    );
  }

  /**
   * Handle OAuth callback
   */
  private function handle_oauth_callback()
  {
    $code = sanitize_text_field($_GET['code']);
    $credentials = json_decode(get_option('jms_google_credentials'), true);

    if (empty($code) || empty($credentials)) {
      add_settings_error(
        'jms_google_settings',
        'oauth_error',
        __('Invalid OAuth response or missing credentials.', 'job-management-system'),
        'error'
      );
      return;
    }

    try {
      require_once JMS_PLUGIN_DIR . 'vendor/autoload.php';

      $client = new Google_Client();
      $client->setAuthConfig($credentials);
      $client->setRedirectUri(admin_url('edit.php?post_type=jms_job&page=jms-google-settings'));

      // Exchange authorization code for access token
      $token = $client->fetchAccessTokenWithAuthCode($code);

      if (!empty($token) && !isset($token['error'])) {
        update_option('jms_google_token', json_encode($token));
        add_settings_error(
          'jms_google_settings',
          'oauth_success',
          __('Successfully connected to Google Calendar!', 'job-management-system'),
          'success'
        );
      } else {
        add_settings_error(
          'jms_google_settings',
          'oauth_error',
          __('Failed to obtain access token from Google.', 'job-management-system'),
          'error'
        );
      }
    } catch (Exception $e) {
      add_settings_error(
        'jms_google_settings',
        'oauth_error',
        sprintf(__('Error: %s', 'job-management-system'), $e->getMessage()),
        'error'
      );
    }
  }
}

// Initialize the settings
new JMS_Google_Settings();
