<?php

/**
 * AJAX Handler for Job Management System
 *
 * @package JobManagementSystem
 */

defined('ABSPATH') || exit;

/**
 * Class JMS_Ajax
 * Handles all AJAX requests for the Job Management System
 */
class JMS_Ajax
{
  /**
   * Constructor
   */
  public function __construct()
  {
    // Candidate details
    add_action('wp_ajax_jms_get_candidate_details', array($this, 'get_candidate_details'));
    add_action('wp_ajax_nopriv_jms_get_candidate_details', array($this, 'get_candidate_details'));

    // Interview scheduling
    add_action('wp_ajax_jms_schedule_interview', array($this, 'schedule_interview'));
    add_action('wp_ajax_nopriv_jms_schedule_interview', array($this, 'schedule_interview'));
  }

  /**
   * Get candidate details
   */
  public function get_candidate_details()
  {
    check_ajax_referer('jms_nonce', 'nonce');

    $candidate_id = isset($_POST['candidate_id']) ? intval($_POST['candidate_id']) : 0;

    if (!$candidate_id) {
      wp_send_json_error(array('message' => 'Invalid candidate ID'));
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'jms_candidates';

    $candidate = $wpdb->get_row(
      $wpdb->prepare(
        "SELECT * FROM {$table_name} WHERE id = %d",
        $candidate_id
      ),
      ARRAY_A
    );

    if (!$candidate) {
      wp_send_json_error(array('message' => 'Candidate not found'));
    }

    // Format the response data
    $response = array(
      'name' => $candidate['name'],
      'email' => $candidate['email'],
      'phone' => $candidate['phone'],
      'resume_url' => wp_get_attachment_url($candidate['resume_id']),
      'applied_date' => date('F j, Y', strtotime($candidate['created_at']))
    );

    wp_send_json_success($response);
  }

  /**
   * Schedule an interview
   */
  public function schedule_interview()
  {
    check_ajax_referer('jms_nonce', 'nonce');

    // Validate required fields
    $required_fields = array(
      'candidate_id',
      'interview_date',
      'interview_time',
      'interview_type',
      'interviewer_email'
    );

    foreach ($required_fields as $field) {
      if (empty($_POST[$field])) {
        wp_send_json_error(array(
          'message' => sprintf('Missing required field: %s', str_replace('_', ' ', $field))
        ));
      }
    }

    // Sanitize and validate input data
    $candidate_id = intval($_POST['candidate_id']);
    $interview_date = sanitize_text_field($_POST['interview_date']);
    $interview_time = sanitize_text_field($_POST['interview_time']);
    $interview_type = sanitize_text_field($_POST['interview_type']);
    $interviewer_email = sanitize_email($_POST['interviewer_email']);
    $additional_notes = isset($_POST['additional_notes']) ? sanitize_textarea_field($_POST['additional_notes']) : '';

    // Validate date format
    if (!strtotime($interview_date)) {
      wp_send_json_error(array('message' => 'Invalid date format'));
    }

    // Validate email
    if (!is_email($interviewer_email)) {
      wp_send_json_error(array('message' => 'Invalid email address'));
    }

    global $wpdb;
    $interviews_table = $wpdb->prefix . 'jms_interviews';

    // Insert interview record
    $result = $wpdb->insert(
      $interviews_table,
      array(
        'candidate_id' => $candidate_id,
        'interview_date' => $interview_date,
        'interview_time' => $interview_time,
        'interview_type' => $interview_type,
        'interviewer_email' => $interviewer_email,
        'additional_notes' => $additional_notes,
        'status' => 'scheduled',
        'created_at' => current_time('mysql')
      ),
      array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
    );

    if ($result === false) {
      wp_send_json_error(array('message' => 'Failed to schedule interview'));
    }

    // Send email notifications
    $this->send_interview_notifications($wpdb->insert_id);

    wp_send_json_success(array(
      'message' => 'Interview scheduled successfully',
      'interview_id' => $wpdb->insert_id
    ));
  }

  /**
   * Send interview notifications
   *
   * @param int $interview_id The interview ID
   */
  private function send_interview_notifications($interview_id)
  {
    global $wpdb;

    // Get interview details
    $interview = $wpdb->get_row(
      $wpdb->prepare(
        "SELECT i.*, c.name as candidate_name, c.email as candidate_email
        FROM {$wpdb->prefix}jms_interviews i
        JOIN {$wpdb->prefix}jms_candidates c ON i.candidate_id = c.id
        WHERE i.id = %d",
        $interview_id
      )
    );

    if (!$interview) {
      return;
    }

    // Send email to candidate
    $candidate_subject = 'Interview Scheduled - ' . get_bloginfo('name');
    $candidate_message = sprintf(
      "Dear %s,\n\nYour interview has been scheduled for %s at %s.\n\nInterview Type: %s\n\nBest regards,\n%s",
      $interview->candidate_name,
      date('F j, Y', strtotime($interview->interview_date)),
      $interview->interview_time,
      ucfirst($interview->interview_type),
      get_bloginfo('name')
    );

    wp_mail($interview->candidate_email, $candidate_subject, $candidate_message);

    // Send email to interviewer
    $interviewer_subject = 'Interview Schedule Notification - ' . get_bloginfo('name');
    $interviewer_message = sprintf(
      "Hello,\n\nAn interview has been scheduled with %s.\n\nDate: %s\nTime: %s\nType: %s\n\nCandidate Email: %s\n\nAdditional Notes: %s\n\nBest regards,\n%s",
      $interview->candidate_name,
      date('F j, Y', strtotime($interview->interview_date)),
      $interview->interview_time,
      ucfirst($interview->interview_type),
      $interview->candidate_email,
      $interview->additional_notes,
      get_bloginfo('name')
    );

    wp_mail($interview->interviewer_email, $interviewer_subject, $interviewer_message);
  }
}

// Initialize the AJAX handler
new JMS_Ajax();
