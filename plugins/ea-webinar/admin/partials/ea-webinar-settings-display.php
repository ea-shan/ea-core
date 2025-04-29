<?php

/**
 * Admin settings page template
 *
 * @since      1.0.0
 * @package    EA_Webinar
 * @subpackage EA_Webinar/admin/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
  die;
}
?>

<div class="wrap">
  <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

  <?php
  // Show success/error messages
  if (isset($_GET['google_auth'])) {
    if ($_GET['google_auth'] === 'success') {
  ?>
      <div class="notice notice-success is-dismissible">
        <p><?php _e('Google authentication successful!', 'ea-webinar'); ?></p>
      </div>
    <?php
    } elseif ($_GET['google_auth'] === 'error') {
    ?>
      <div class="notice notice-error is-dismissible">
        <p><?php _e('Google authentication failed. Please try again.', 'ea-webinar'); ?></p>
      </div>
  <?php
    }
  }
  ?>

  <form method="post" action="options.php">
    <?php
    settings_fields('eaw_options');
    do_settings_sections('eaw_options');
    ?>

    <h2><?php _e('Google Integration Settings', 'ea-webinar'); ?></h2>
    <table class="form-table">
      <tr>
        <th scope="row">
          <label for="eaw_google_client_id"><?php _e('Google Client ID', 'ea-webinar'); ?></label>
        </th>
        <td>
          <input type="text" id="eaw_google_client_id" name="eaw_google_client_id"
            value="<?php echo esc_attr(get_option('eaw_google_client_id')); ?>" class="regular-text">
          <p class="description">
            <?php _e('Enter your Google Client ID from the Google Cloud Console.', 'ea-webinar'); ?>
          </p>
        </td>
      </tr>
      <tr>
        <th scope="row">
          <label for="eaw_google_client_secret"><?php _e('Google Client Secret', 'ea-webinar'); ?></label>
        </th>
        <td>
          <input type="password" id="eaw_google_client_secret" name="eaw_google_client_secret"
            value="<?php echo esc_attr(get_option('eaw_google_client_secret')); ?>" class="regular-text">
          <p class="description">
            <?php _e('Enter your Google Client Secret from the Google Cloud Console.', 'ea-webinar'); ?>
          </p>
        </td>
      </tr>
      <tr>
        <th scope="row"><?php _e('Google Authentication', 'ea-webinar'); ?></th>
        <td>
          <?php
          $google_integration = new EAW_Google_Integration();
          if ($google_integration->is_authenticated()) {
          ?>
            <p class="description" style="color: green;">
              <?php _e('Connected to Google Calendar', 'ea-webinar'); ?>
            </p>
          <?php
          } else {
            $auth_url = $google_integration->get_auth_url();
          ?>
            <a href="<?php echo esc_url($auth_url); ?>" class="button button-primary">
              <?php _e('Connect to Google Calendar', 'ea-webinar'); ?>
            </a>
          <?php
          }
          ?>
        </td>
      </tr>
    </table>

    <h2><?php _e('Email Settings', 'ea-webinar'); ?></h2>
    <table class="form-table">
      <tr>
        <th scope="row">
          <label for="eaw_email_template"><?php _e('Registration Confirmation Email', 'ea-webinar'); ?></label>
        </th>
        <td>
          <?php
          wp_editor(
            get_option('eaw_email_template', ''),
            'eaw_email_template',
            array(
              'textarea_name' => 'eaw_email_template',
              'textarea_rows' => 10,
              'media_buttons' => false,
              'teeny' => true,
              'quicktags' => true
            )
          );
          ?>
          <p class="description">
            <?php _e('Available placeholders: {webinar_title}, {webinar_date}, {webinar_time}, {meet_link}', 'ea-webinar'); ?>
          </p>
        </td>
      </tr>
      <tr>
        <th scope="row">
          <label for="eaw_reminder_time"><?php _e('Reminder Email Time', 'ea-webinar'); ?></label>
        </th>
        <td>
          <select id="eaw_reminder_time" name="eaw_reminder_time">
            <option value="24" <?php selected(get_option('eaw_reminder_time'), '24'); ?>>
              <?php _e('24 hours before', 'ea-webinar'); ?>
            </option>
            <option value="12" <?php selected(get_option('eaw_reminder_time'), '12'); ?>>
              <?php _e('12 hours before', 'ea-webinar'); ?>
            </option>
            <option value="6" <?php selected(get_option('eaw_reminder_time'), '6'); ?>>
              <?php _e('6 hours before', 'ea-webinar'); ?>
            </option>
            <option value="1" <?php selected(get_option('eaw_reminder_time'), '1'); ?>>
              <?php _e('1 hour before', 'ea-webinar'); ?>
            </option>
          </select>
        </td>
      </tr>
    </table>

    <?php submit_button(); ?>
  </form>
</div>
