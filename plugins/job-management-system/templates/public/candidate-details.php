<?php

/**
 * Public template for displaying candidate details and interview scheduling
 *
 * This template is used on the frontend to display candidate information
 * and provide interview scheduling functionality.
 *
 * @package JobManagementSystem
 * @subpackage Templates/Public
 */

defined('ABSPATH') || exit;

// Enqueue required scripts and styles
wp_enqueue_script('jms-candidate');
wp_enqueue_style('jms-public-styles');
?>

<!-- Candidate Details Modal -->
<div class="modal fade" id="candidate-details-modal" tabindex="-1" role="dialog" aria-labelledby="candidateDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="candidateDetailsModalLabel">Candidate Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="candidate-info">
          <p><strong>Name:</strong> <span id="candidate-name"></span></p>
          <p><strong>Email:</strong> <span id="candidate-email"></span></p>
          <p><strong>Phone:</strong> <span id="candidate-phone"></span></p>
          <p><strong>Applied Date:</strong> <span id="candidate-applied-date"></span></p>
          <p><strong>Resume:</strong> <a id="candidate-resume" href="#" target="_blank">View Resume</a></p>
        </div>
        <?php if (current_user_can('schedule_interviews')) : ?>
          <button type="button" class="btn btn-primary mt-3" data-toggle="modal" data-target="#schedule-interview-modal">
            Schedule Interview
          </button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Schedule Interview Modal -->
<div class="modal fade" id="schedule-interview-modal" tabindex="-1" role="dialog" aria-labelledby="scheduleInterviewModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="scheduleInterviewModalLabel">Schedule Interview</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="schedule-interview-form">
          <?php wp_nonce_field('jms_schedule_interview', 'jms_nonce'); ?>
          <input type="hidden" id="candidate-id" name="candidate_id">

          <div class="form-group">
            <label for="interview-date">Interview Date</label>
            <input type="date" class="form-control" id="interview-date" name="interview_date" required
              min="<?php echo date('Y-m-d'); ?>">
          </div>

          <div class="form-group">
            <label for="interview-time">Interview Time</label>
            <input type="time" class="form-control" id="interview-time" name="interview_time" required>
          </div>

          <div class="form-group">
            <label for="interview-type">Interview Type</label>
            <select class="form-control" id="interview-type" name="interview_type" required>
              <option value="">Select Interview Type</option>
              <?php
              $interview_types = apply_filters('jms_interview_types', array(
                'phone' => 'Phone Interview',
                'video' => 'Video Interview',
                'in-person' => 'In-Person Interview',
                'technical' => 'Technical Interview'
              ));

              foreach ($interview_types as $value => $label) {
                echo '<option value="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
              }
              ?>
            </select>
          </div>

          <div class="form-group">
            <label for="interviewer-email">Interviewer's Email</label>
            <input type="email" class="form-control" id="interviewer-email" name="interviewer_email" required>
          </div>

          <div class="form-group">
            <label for="additional-notes">Additional Notes</label>
            <textarea class="form-control" id="additional-notes" name="additional_notes" rows="3"></textarea>
          </div>

          <div class="form-actions">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Schedule Interview</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
