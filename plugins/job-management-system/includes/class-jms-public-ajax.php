<?php

/**
 * Handle public AJAX requests for Job Management System
 *
 * @package Job_Management_System
 * @subpackage Job_Management_System/includes
 */

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Class JMS_Public_Ajax
 *
 * Handles all AJAX requests from the public frontend
 */
class JMS_Public_Ajax
{

  /**
   * Initialize the class and set its properties.
   */
  public function __construct()
  {
    add_action('wp_ajax_get_candidate_details', array($this, 'get_candidate_details'));
    add_action('wp_ajax_nopriv_get_candidate_details', array($this, 'get_candidate_details'));
    add_action('wp_ajax_schedule_interview', array($this, 'schedule_interview'));
    add_action('wp_ajax_nopriv_schedule_interview', array($this, 'schedule_interview'));
  }

  /**
   * Get candidate details via AJAX
   */
  public function get_candidate_details()
  {
    // Verify nonce
    if (!check_ajax_referer('jms_nonce', 'nonce', false)) {
      wp_send_json_error(array('message' => 'Invalid security token'));
    }

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

    // Format the resume URL if it exists
    if (!empty($candidate['resume_path'])) {
      $candidate['resume_url'] = wp_get_attachment_url($candidate['resume_path']);
    }

    // Format the applied date
    $candidate['applied_date'] = date('F j, Y', strtotime($candidate['applied_date']));

    wp_send_json_success(array(
      'candidate' => $candidate
    ));
  }

  /**
   * Schedule an interview via AJAX
   */
  public function schedule_interview()
  {
    // Verify nonce
    if (!check_ajax_referer('jms_nonce', 'nonce', false)) {
      wp_send_json_error(array('message' => 'Invalid security token'));
    }

    // Validate required fields
    $required_fields = array(
      'candidate_id' => 'Candidate ID',
      'interview_date' => 'Interview Date',
      'interview_time' => 'Interview Time',
      'interview_type' => 'Interview Type',
      'interviewer_email' => 'Interviewer Email'
    );

    $data = array();
    foreach ($required_fields as $field => $label) {
      if (!isset($_POST[$field]) || empty($_POST[$field])) {
        wp_send_json_error(array('message' => $label . ' is required'));
      }
      $data[$field] = sanitize_text_field($_POST[$field]);
    }

    // Validate email
    if (!is_email($data['interviewer_email'])) {
      wp_send_json_error(array('message' => 'Invalid interviewer email'));
    }

    // Additional notes (optional)
    $data['notes'] = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';

    // Insert interview into database
    global $wpdb;
    $table_name = $wpdb->prefix . 'jms_interviews';

    $result = $wpdb->insert(
      $table_name,
      array(
        'candidate_id' => $data['candidate_id'],
        'interview_date' => $data['interview_date'],
        'interview_time' => $data['interview_time'],
        'interview_type' => $data['interview_type'],
        'interviewer_email' => $data['interviewer_email'],
        'notes' => $data['notes'],
        'status' => 'scheduled',
        'created_at' => current_time('mysql')
      ),
      array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
    );

    if ($result === false) {
      wp_send_json_error(array('message' => 'Failed to schedule interview'));
    }

    // Send email notifications
    $this->send_interview_notifications($data, $wpdb->insert_id);

    wp_send_json_success(array(
      'message' => 'Interview scheduled successfully',
      'interview_id' => $wpdb->insert_id
    ));
  }

  /**
   * Send email notifications for scheduled interview
   *
   * @param array $data Interview data
   * @param int $interview_id Interview ID
   */
  private function send_interview_notifications($data, $interview_id)
  {
    global $wpdb;

    // Get candidate details
    $candidate = $wpdb->get_row(
      $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}jms_candidates WHERE id = %d",
        $data['candidate_id']
      )
    );

    if (!$candidate) {
      return;
    }

    // Email to interviewer
    $interviewer_subject = sprintf(
      'Interview Scheduled with %s - %s',
      $candidate->name,
      date('F j, Y', strtotime($data['interview_date']))
    );

    $interviewer_message = sprintf(
      "Hello,\n\nAn interview has been scheduled with the following details:\n\n" .
        "Candidate: %s\n" .
        "Date: %s\n" .
        "Time: %s\n" .
        "Type: %s\n" .
        "Notes: %s\n\n" .
        "Please confirm your availability.\n\n" .
        "Best regards,\n%s",
      $candidate->name,
      date('F j, Y', strtotime($data['interview_date'])),
      $data['interview_time'],
      $data['interview_type'],
      $data['notes'],
      get_bloginfo('name')
    );

    wp_mail($data['interviewer_email'], $interviewer_subject, $interviewer_message);

    // Email to candidate if email exists
    if (!empty($candidate->email)) {
      $candidate_subject = sprintf(
        'Interview Scheduled - %s',
        date('F j, Y', strtotime($data['interview_date']))
      );

      $candidate_message = sprintf(
        "Dear %s,\n\nYour interview has been scheduled with the following details:\n\n" .
          "Date: %s\n" .
          "Time: %s\n" .
          "Type: %s\n" .
          "Notes: %s\n\n" .
          "Please make sure to be available at the scheduled time.\n\n" .
          "Best regards,\n%s",
        $candidate->name,
        date('F j, Y', strtotime($data['interview_date'])),
        $data['interview_time'],
        $data['interview_type'],
        $data['notes'],
        get_bloginfo('name')
      );

      wp_mail($candidate->email, $candidate_subject, $candidate_message);
    }
  }
}

// Initialize the class
new JMS_Public_Ajax();
