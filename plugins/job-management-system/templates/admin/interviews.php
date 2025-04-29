<?php
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

// Get any existing interviews from the database
global $wpdb;
$interviews_table = $wpdb->prefix . 'jms_interviews';
$candidates_table = $wpdb->prefix . 'jms_candidates';
$jobs_table = $wpdb->prefix . 'jms_jobs';

$interviews = $wpdb->get_results("
    SELECT i.*, c.name as candidate_name, j.title as job_title
    FROM $interviews_table i
    LEFT JOIN $candidates_table c ON i.candidate_id = c.id
    LEFT JOIN $jobs_table j ON i.job_id = j.id
    ORDER BY i.interview_date DESC
");
?>

<div class="wrap">
  <h1><?php echo esc_html__('Interview Management', 'job-management-system'); ?></h1>

  <div class="notice notice-info">
    <p><?php echo esc_html__('Schedule and manage candidate interviews.', 'job-management-system'); ?></p>
  </div>

  <!-- Interview Scheduling Form -->
  <div class="jms-card">
    <h2><?php echo esc_html__('Schedule New Interview', 'job-management-system'); ?></h2>
    <form id="schedule-interview-form" class="jms-form">
      <?php wp_nonce_field('jms_schedule_interview', 'jms_interview_nonce'); ?>
      <div class="form-row">
        <label for="candidate_id"><?php echo esc_html__('Candidate:', 'job-management-system'); ?></label>
        <select name="candidate_id" id="candidate_id" required>
          <option value=""><?php echo esc_html__('Select Candidate', 'job-management-system'); ?></option>
          <?php
          $candidates = $wpdb->get_results("SELECT id, name FROM $candidates_table");
          foreach ($candidates as $candidate) {
            echo '<option value="' . esc_attr($candidate->id) . '">' . esc_html($candidate->name) . '</option>';
          }
          ?>
        </select>
      </div>
      <div class="form-row">
        <label for="interview_date"><?php echo esc_html__('Date & Time:', 'job-management-system'); ?></label>
        <input type="datetime-local" name="interview_date" id="interview_date" required>
      </div>
      <div class="form-row">
        <label for="interview_type"><?php echo esc_html__('Interview Type:', 'job-management-system'); ?></label>
        <select name="interview_type" id="interview_type" required>
          <option value="phone"><?php echo esc_html__('Phone', 'job-management-system'); ?></option>
          <option value="video"><?php echo esc_html__('Video', 'job-management-system'); ?></option>
          <option value="in-person"><?php echo esc_html__('In-Person', 'job-management-system'); ?></option>
        </select>
      </div>
      <div class="form-row">
        <label for="notes"><?php echo esc_html__('Notes:', 'job-management-system'); ?></label>
        <textarea name="notes" id="notes" rows="3"></textarea>
      </div>
      <div class="form-row">
        <button type="submit" class="button button-primary">
          <?php echo esc_html__('Schedule Interview', 'job-management-system'); ?>
        </button>
      </div>
    </form>
  </div>

  <!-- Interviews List -->
  <div class="jms-card">
    <h2><?php echo esc_html__('Scheduled Interviews', 'job-management-system'); ?></h2>
    <table class="wp-list-table widefat fixed striped">
      <thead>
        <tr>
          <th><?php echo esc_html__('Candidate', 'job-management-system'); ?></th>
          <th><?php echo esc_html__('Job', 'job-management-system'); ?></th>
          <th><?php echo esc_html__('Date & Time', 'job-management-system'); ?></th>
          <th><?php echo esc_html__('Type', 'job-management-system'); ?></th>
          <th><?php echo esc_html__('Status', 'job-management-system'); ?></th>
          <th><?php echo esc_html__('Actions', 'job-management-system'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($interviews)) : ?>
          <?php foreach ($interviews as $interview) : ?>
            <tr>
              <td><?php echo esc_html($interview->candidate_name); ?></td>
              <td><?php echo esc_html($interview->job_title); ?></td>
              <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($interview->interview_date))); ?></td>
              <td><?php echo esc_html(ucfirst($interview->interview_type)); ?></td>
              <td><?php echo esc_html(ucfirst($interview->status)); ?></td>
              <td>
                <button class="button button-small edit-interview" data-id="<?php echo esc_attr($interview->id); ?>">
                  <?php echo esc_html__('Edit', 'job-management-system'); ?>
                </button>
                <button class="button button-small delete-interview" data-id="<?php echo esc_attr($interview->id); ?>">
                  <?php echo esc_html__('Delete', 'job-management-system'); ?>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else : ?>
          <tr>
            <td colspan="6"><?php echo esc_html__('No interviews scheduled.', 'job-management-system'); ?></td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
