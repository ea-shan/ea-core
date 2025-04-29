<?php
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

// Get plugin settings
$settings = get_option('jms_settings', array(
  'company_name' => '',
  'company_email' => '',
  'notification_emails' => '',
  'resume_file_types' => 'pdf,doc,docx',
  'max_file_size' => '5',
  'job_categories' => '',
  'departments' => '',
  'interview_locations' => '',
));

// Save settings if form is submitted
if (isset($_POST['jms_save_settings']) && check_admin_referer('jms_settings_nonce')) {
  $settings['company_name'] = sanitize_text_field($_POST['company_name']);
  $settings['company_email'] = sanitize_email($_POST['company_email']);
  $settings['notification_emails'] = sanitize_textarea_field($_POST['notification_emails']);
  $settings['resume_file_types'] = sanitize_text_field($_POST['resume_file_types']);
  $settings['max_file_size'] = absint($_POST['max_file_size']);
  $settings['job_categories'] = sanitize_textarea_field($_POST['job_categories']);
  $settings['departments'] = sanitize_textarea_field($_POST['departments']);
  $settings['interview_locations'] = sanitize_textarea_field($_POST['interview_locations']);

  update_option('jms_settings', $settings);
  echo '<div class="notice notice-success"><p>' . esc_html__('Settings saved successfully.', 'job-management-system') . '</p></div>';
}
?>

<div class="wrap">
  <h1><?php echo esc_html__('Job Management System Settings', 'job-management-system'); ?></h1>

  <form method="post" action="" class="jms-settings-form">
    <?php wp_nonce_field('jms_settings_nonce'); ?>

    <!-- General Settings -->
    <div class="jms-card">
      <h2><?php echo esc_html__('General Settings', 'job-management-system'); ?></h2>
      <table class="form-table">
        <tr>
          <th scope="row">
            <label for="company_name"><?php echo esc_html__('Company Name', 'job-management-system'); ?></label>
          </th>
          <td>
            <input type="text" name="company_name" id="company_name"
              value="<?php echo esc_attr($settings['company_name']); ?>" class="regular-text">
          </td>
        </tr>
        <tr>
          <th scope="row">
            <label for="company_email"><?php echo esc_html__('Company Email', 'job-management-system'); ?></label>
          </th>
          <td>
            <input type="email" name="company_email" id="company_email"
              value="<?php echo esc_attr($settings['company_email']); ?>" class="regular-text">
          </td>
        </tr>
        <tr>
          <th scope="row">
            <label for="notification_emails"><?php echo esc_html__('Notification Emails', 'job-management-system'); ?></label>
          </th>
          <td>
            <textarea name="notification_emails" id="notification_emails" rows="3" class="large-text"><?php
                                                                                                      echo esc_textarea($settings['notification_emails']);
                                                                                                      ?></textarea>
            <p class="description"><?php echo esc_html__('Enter email addresses (one per line) that should receive notifications.', 'job-management-system'); ?></p>
          </td>
        </tr>
      </table>
    </div>

    <!-- File Upload Settings -->
    <div class="jms-card">
      <h2><?php echo esc_html__('File Upload Settings', 'job-management-system'); ?></h2>
      <table class="form-table">
        <tr>
          <th scope="row">
            <label for="resume_file_types"><?php echo esc_html__('Allowed Resume File Types', 'job-management-system'); ?></label>
          </th>
          <td>
            <input type="text" name="resume_file_types" id="resume_file_types"
              value="<?php echo esc_attr($settings['resume_file_types']); ?>" class="regular-text">
            <p class="description"><?php echo esc_html__('Enter file extensions separated by commas (e.g., pdf,doc,docx)', 'job-management-system'); ?></p>
          </td>
        </tr>
        <tr>
          <th scope="row">
            <label for="max_file_size"><?php echo esc_html__('Maximum File Size (MB)', 'job-management-system'); ?></label>
          </th>
          <td>
            <input type="number" name="max_file_size" id="max_file_size"
              value="<?php echo esc_attr($settings['max_file_size']); ?>" class="small-text" min="1" max="50">
            <p class="description"><?php echo esc_html__('Maximum allowed file size for uploads in megabytes.', 'job-management-system'); ?></p>
          </td>
        </tr>
      </table>
    </div>

    <!-- Job Categories and Departments -->
    <div class="jms-card">
      <h2><?php echo esc_html__('Categories & Departments', 'job-management-system'); ?></h2>
      <table class="form-table">
        <tr>
          <th scope="row">
            <label for="job_categories"><?php echo esc_html__('Job Categories', 'job-management-system'); ?></label>
          </th>
          <td>
            <textarea name="job_categories" id="job_categories" rows="4" class="large-text"><?php
                                                                                            echo esc_textarea($settings['job_categories']);
                                                                                            ?></textarea>
            <p class="description"><?php echo esc_html__('Enter job categories (one per line) that will be available when creating jobs.', 'job-management-system'); ?></p>
          </td>
        </tr>
        <tr>
          <th scope="row">
            <label for="departments"><?php echo esc_html__('Departments', 'job-management-system'); ?></label>
          </th>
          <td>
            <textarea name="departments" id="departments" rows="4" class="large-text"><?php
                                                                                      echo esc_textarea($settings['departments']);
                                                                                      ?></textarea>
            <p class="description"><?php echo esc_html__('Enter departments (one per line) that will be available for job assignments.', 'job-management-system'); ?></p>
          </td>
        </tr>
        <tr>
          <th scope="row">
            <label for="interview_locations"><?php echo esc_html__('Interview Locations', 'job-management-system'); ?></label>
          </th>
          <td>
            <textarea name="interview_locations" id="interview_locations" rows="4" class="large-text"><?php
                                                                                                      echo esc_textarea($settings['interview_locations']);
                                                                                                      ?></textarea>
            <p class="description"><?php echo esc_html__('Enter interview locations (one per line) that will be available when scheduling interviews.', 'job-management-system'); ?></p>
          </td>
        </tr>
      </table>
    </div>

    <p class="submit">
      <input type="submit" name="jms_save_settings" class="button button-primary"
        value="<?php echo esc_attr__('Save Settings', 'job-management-system'); ?>">
    </p>
  </form>
</div>
