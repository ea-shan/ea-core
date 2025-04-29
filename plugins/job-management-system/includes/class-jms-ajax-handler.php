<?php

/**
 * Handle all AJAX requests for the Job Management System
 *
 * @package    Job_Management_System
 * @subpackage Job_Management_System/includes
 */

defined('WPINC') || exit;

/**
 * Class JMS_Ajax_Handler
 */
class JMS_Ajax_Handler
{

  /**
   * Google Integration instance
   *
   * @var JMS_Google_Integration
   */
  private $google_integration;

  /**
   * Initialize the class and set its properties.
   */
  public function __construct()
  {
    // Initialize Google Integration
    $this->google_integration = new JMS_Google_Integration();

    // Register AJAX actions for logged-in users
    add_action('wp_ajax_jms_get_candidate_details', array($this, 'get_candidate_details'));
    add_action('wp_ajax_jms_schedule_interview', array($this, 'schedule_interview'));

    // Register AJAX actions for non-logged-in users (if needed)
    add_action('wp_ajax_nopriv_jms_get_candidate_details', array($this, 'get_candidate_details'));
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
      wp_send_json_error(array('message' => __('Invalid candidate ID', 'job-management-system')));
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
      wp_send_json_error(array('message' => __('Candidate not found', 'job-management-system')));
    }

    // Format the response data
    $response = array(
      'name' => $candidate['name'],
      'email' => $candidate['email'],
      'phone' => $candidate['phone'],
      'resume_url' => wp_get_attachment_url($candidate['resume_id']),
      'applied_date' => date_i18n(get_option('date_format'), strtotime($candidate['created_at']))
    );

    wp_send_json_success($response);
  }

  /**
   * Schedule an interview
   */
  public function schedule_interview()
  {
    check_ajax_referer('jms_nonce', 'nonce');

    // Check if Google Calendar is configured
    if (!$this->google_integration->is_configured()) {
      wp_send_json_error(array(
        'message' => __('Google Calendar is not configured. Please configure it in the settings.', 'job-management-system')
      ));
    }

    // Validate required fields
    $required_fields = array(
      'candidate_id' => __('Candidate ID', 'job-management-system'),
      'interview_date' => __('Interview Date', 'job-management-system'),
      'interview_time' => __('Interview Time', 'job-management-system'),
      'interview_type' => __('Interview Type', 'job-management-system'),
      'interviewer_email' => __('Interviewer Email', 'job-management-system')
    );

    foreach ($required_fields as $field => $label) {
      if (empty($_POST[$field])) {
        wp_send_json_error(array(
          'message' => sprintf(__('%s is required', 'job-management-system'), $label)
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
      wp_send_json_error(array('message' => __('Invalid date format', 'job-management-system')));
    }

    // Validate email
    if (!is_email($interviewer_email)) {
      wp_send_json_error(array('message' => __('Invalid interviewer email', 'job-management-system')));
    }

    // Get candidate details
    global $wpdb;
    $candidate = $wpdb->get_row($wpdb->prepare(
      "SELECT * FROM {$wpdb->prefix}jms_candidates WHERE id = %d",
      $candidate_id
    ));

    if (!$candidate) {
      wp_send_json_error(array('message' => __('Candidate not found', 'job-management-system')));
    }

    // Get job details
    $job = get_post($candidate->job_id);
    if (!$job) {
      wp_send_json_error(array('message' => __('Job not found', 'job-management-system')));
    }

    // Prepare interview data
    $interview_data = array(
      'candidate_id' => $candidate_id,
      'job_id' => $candidate->job_id,
      'candidate_name' => $candidate->name,
      'candidate_email' => $candidate->email,
      'candidate_phone' => $candidate->phone,
      'job_title' => $job->post_title,
      'interview_date' => $interview_date,
      'interview_time' => $interview_time,
      'interview_type' => $interview_type,
      'interviewer_email' => $interviewer_email,
      'additional_notes' => $additional_notes
    );

    // Create calendar event with Meet link
    $event = $this->google_integration->create_interview_event($interview_data);

    if (!$event) {
      wp_send_json_error(array(
        'message' => __('Failed to schedule interview. Please try again.', 'job-management-system')
      ));
    }

    wp_send_json_success(array(
      'message' => __('Interview scheduled successfully', 'job-management-system'),
      'event' => $event
    ));
  }
}

// Initialize the AJAX handler
new JMS_Ajax_Handler();
