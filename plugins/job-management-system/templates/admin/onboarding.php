<?php
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

// Get any existing onboarding records from the database
global $wpdb;
$onboarding_table = $wpdb->prefix . 'jms_onboarding';
$candidates_table = $wpdb->prefix . 'jms_candidates';
$jobs_table = $wpdb->prefix . 'jms_jobs';

$onboarding_records = $wpdb->get_results("
    SELECT o.*, c.name as candidate_name, j.title as job_title
    FROM $onboarding_table o
    LEFT JOIN $candidates_table c ON o.candidate_id = c.id
    LEFT JOIN $jobs_table j ON o.job_id = j.id
    ORDER BY o.start_date DESC
");
?>

<div class="wrap">
  <h1><?php echo esc_html__('Onboarding Management', 'job-management-system'); ?></h1>

  <div class="notice notice-info">
    <p><?php echo esc_html__('Manage employee onboarding process and track documentation.', 'job-management-system'); ?></p>
  </div>

  <!-- New Onboarding Form -->
  <div class="jms-card">
    <h2><?php echo esc_html__('Start New Onboarding', 'job-management-system'); ?></h2>
    <form id="start-onboarding-form" class="jms-form">
      <?php wp_nonce_field('jms_start_onboarding', 'jms_onboarding_nonce'); ?>
      <div class="form-row">
        <label for="candidate_id"><?php echo esc_html__('Candidate:', 'job-management-system'); ?></label>
        <select name="candidate_id" id="candidate_id" required>
          <option value=""><?php echo esc_html__('Select Candidate', 'job-management-system'); ?></option>
          <?php
          $candidates = $wpdb->get_results("SELECT id, name FROM $candidates_table WHERE status = 'hired'");
          foreach ($candidates as $candidate) {
            echo '<option value="' . esc_attr($candidate->id) . '">' . esc_html($candidate->name) . '</option>';
          }
          ?>
        </select>
      </div>
      <div class="form-row">
        <label for="start_date"><?php echo esc_html__('Start Date:', 'job-management-system'); ?></label>
        <input type="date" name="start_date" id="start_date" required>
      </div>
      <div class="form-row">
        <label for="department"><?php echo esc_html__('Department:', 'job-management-system'); ?></label>
        <input type="text" name="department" id="department" required>
      </div>
      <div class="form-row">
        <label for="notes"><?php echo esc_html__('Notes:', 'job-management-system'); ?></label>
        <textarea name="notes" id="notes" rows="3"></textarea>
      </div>
      <div class="form-row">
        <button type="submit" class="button button-primary">
          <?php echo esc_html__('Start Onboarding', 'job-management-system'); ?>
        </button>
      </div>
    </form>
  </div>

  <!-- Onboarding Checklist -->
  <div class="jms-card">
    <h2><?php echo esc_html__('Onboarding Progress', 'job-management-system'); ?></h2>
    <table class="wp-list-table widefat fixed striped">
      <thead>
        <tr>
          <th><?php echo esc_html__('Employee', 'job-management-system'); ?></th>
          <th><?php echo esc_html__('Position', 'job-management-system'); ?></th>
          <th><?php echo esc_html__('Start Date', 'job-management-system'); ?></th>
          <th><?php echo esc_html__('Department', 'job-management-system'); ?></th>
          <th><?php echo esc_html__('Progress', 'job-management-system'); ?></th>
          <th><?php echo esc_html__('Actions', 'job-management-system'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($onboarding_records)) : ?>
          <?php foreach ($onboarding_records as $record) : ?>
            <tr>
              <td><?php echo esc_html($record->candidate_name); ?></td>
              <td><?php echo esc_html($record->job_title); ?></td>
              <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($record->start_date))); ?></td>
              <td><?php echo esc_html($record->department); ?></td>
              <td>
                <div class="progress-bar">
                  <div class="progress" style="width: <?php echo esc_attr($record->progress); ?>%;">
                    <?php echo esc_html($record->progress); ?>%
                  </div>
                </div>
              </td>
              <td>
                <button class="button button-small view-checklist" data-id="<?php echo esc_attr($record->id); ?>">
                  <?php echo esc_html__('View Checklist', 'job-management-system'); ?>
                </button>
                <button class="button button-small edit-onboarding" data-id="<?php echo esc_attr($record->id); ?>">
                  <?php echo esc_html__('Edit', 'job-management-system'); ?>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else : ?>
          <tr>
            <td colspan="6"><?php echo esc_html__('No onboarding records found.', 'job-management-system'); ?></td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
