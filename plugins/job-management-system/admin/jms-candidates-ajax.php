<?php

/**
 * Admin AJAX handlers for candidate management.
 *
 * @since      1.0.0
 * @package    Job_Management_System
 * @subpackage Job_Management_System/admin
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * AJAX handler for getting candidates.
 */
function jms_admin_get_candidates()
{
    // Check nonce for security
    check_ajax_referer('jms_admin_nonce', 'nonce');

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions'));
        return;
    }

    $job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
    $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 10;

    // Calculate offset
    $offset = ($page - 1) * $per_page;

    // Get candidates
    $jms_candidates = new JMS_Candidates();
    $args = array(
        'job_id' => $job_id,
        'status' => $status,
        'search' => $search,
        'limit' => $per_page,
        'offset' => $offset
    );

    $candidates = $jms_candidates->get_candidates($args);

    // Get total count
    $total = $jms_candidates->count_candidates(array(
        'job_id' => $job_id,
        'status' => $status,
        'search' => $search
    ));

    // Calculate total pages
    $total_pages = ceil($total / $per_page);

    // Get job titles for each candidate
    $jms_jobs = new JMS_Jobs();

    foreach ($candidates as &$candidate) {
        $job = $jms_jobs->get_job($candidate->job_id);

        if ($job) {
            $candidate->job_title = $job->title;
        } else {
            $candidate->job_title = 'Unknown Job';
        }

        // Format date
        $candidate->application_date = date_i18n(get_option('date_format'), strtotime($candidate->application_date));

        // Add resume URL if resume exists
        if (!empty($candidate->resume_path)) {
            $candidate->resume_url = wp_get_attachment_url($candidate->resume_path);
        }
    }

    wp_send_json_success(array(
        'candidates' => $candidates,
        'total' => $total,
        'total_pages' => $total_pages
    ));
}
add_action('wp_ajax_jms_admin_get_candidates', 'jms_admin_get_candidates');

/**
 * AJAX handler for getting a single candidate.
 */
function jms_admin_get_candidate()
{
    // Check nonce for security
    check_ajax_referer('jms_admin_nonce', 'nonce');

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions'));
        return;
    }

    $candidate_id = isset($_POST['candidate_id']) ? intval($_POST['candidate_id']) : 0;

    if ($candidate_id <= 0) {
        wp_send_json_error(array('message' => 'Invalid candidate ID'));
        return;
    }

    // Get candidate
    $jms_candidates = new JMS_Candidates();
    $candidate = $jms_candidates->get_candidate($candidate_id);

    if (!$candidate) {
        wp_send_json_error(array('message' => 'Candidate not found'));
        return;
    }

    // Get job details
    $jms_jobs = new JMS_Jobs();
    $job = $jms_jobs->get_job($candidate->job_id);

    if ($job) {
        $candidate->job_title = $job->title;
    } else {
        $candidate->job_title = 'Unknown Job';
    }

    // Format date
    $candidate->application_date = date_i18n(get_option('date_format'), strtotime($candidate->application_date));

    // Add resume URL if resume exists
    if (!empty($candidate->resume_path)) {
        $candidate->resume_url = wp_get_attachment_url($candidate->resume_path);
    }

    // Get interviews
    global $wpdb;
    $interviews_table = $wpdb->prefix . 'jms_interviews';

    $interviews = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $interviews_table WHERE candidate_id = %d ORDER BY interview_date DESC",
        $candidate_id
    ));

    foreach ($interviews as &$interview) {
        $interview->interview_date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($interview->interview_date));
    }

    $candidate->interviews = $interviews;

    // Get documents
    $jms_documents = new JMS_Documents();
    $documents = $jms_documents->get_documents(array('candidate_id' => $candidate_id));

    $document_types = $jms_documents->get_document_types();

    foreach ($documents as &$document) {
        $document->document_type_label = isset($document_types[$document->document_type]) ? $document_types[$document->document_type] : $document->document_type;
        $document->upload_date = date_i18n(get_option('date_format'), strtotime($document->upload_date));
    }

    $candidate->documents = $documents;

    wp_send_json_success(array('candidate' => $candidate));
}
add_action('wp_ajax_jms_admin_get_candidate', 'jms_admin_get_candidate');

/**
 * AJAX handler for updating candidate status.
 */
function jms_admin_update_candidate_status()
{
    // Check nonce for security
    check_ajax_referer('jms_admin_nonce', 'nonce');

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions'));
        return;
    }

    $candidate_id = isset($_POST['candidate_id']) ? intval($_POST['candidate_id']) : 0;
    $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';

    if ($candidate_id <= 0 || empty($status)) {
        wp_send_json_error(array('message' => 'Invalid parameters'));
        return;
    }

    // Update candidate status
    $jms_candidates = new JMS_Candidates();
    $updated = $jms_candidates->update_candidate_status($candidate_id, $status);

    if ($updated) {
        // Get candidate email for notification
        $candidate = $jms_candidates->get_candidate($candidate_id);

        if ($candidate) {
            // Send email notification for status change
            $jms_emails = new JMS_Emails();
            $jms_emails->send_candidate_status_notification($candidate, $status);
        }

        wp_send_json_success(array('message' => 'Candidate status updated successfully'));
    } else {
        wp_send_json_error(array('message' => 'Failed to update candidate status'));
    }
}
add_action('wp_ajax_jms_admin_update_candidate_status', 'jms_admin_update_candidate_status');

/**
 * AJAX handler for scheduling interviews.
 */
function jms_admin_schedule_interview()
{
    // Check nonce for security
    check_ajax_referer('jms_admin_nonce', 'nonce');

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions'));
        return;
    }

    $candidate_id = isset($_POST['candidate_id']) ? intval($_POST['candidate_id']) : 0;
    $job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
    $interview_date = isset($_POST['interview_date']) ? sanitize_text_field($_POST['interview_date']) : '';
    $interview_type = isset($_POST['interview_type']) ? sanitize_text_field($_POST['interview_type']) : '';
    $notes = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';

    if ($candidate_id <= 0 || $job_id <= 0 || empty($interview_date) || empty($interview_type)) {
        wp_send_json_error(array('message' => 'Invalid parameters'));
        return;
    }

    // Format the interview date
    $interview_datetime = date('Y-m-d H:i:s', strtotime($interview_date));

    // Generate Google Meet link if needed
    $google_meet_link = '';
    if ($interview_type == 'google_meet') {
        $jms_google_meet = new JMS_Google_Meet();
        $google_meet_link = $jms_google_meet->create_meeting($candidate_id, $interview_datetime);
    }

    // Schedule interview
    $jms_interviews = new JMS_Interviews();
    $interview_data = array(
        'candidate_id' => $candidate_id,
        'job_id' => $job_id,
        'interview_date' => $interview_datetime,
        'interview_type' => $interview_type,
        'google_meet_link' => $google_meet_link,
        'notes' => $notes,
        'status' => 'scheduled'
    );

    $interview_id = $jms_interviews->schedule_interview($interview_data);

    if ($interview_id) {
        // Update candidate status
        $jms_candidates = new JMS_Candidates();
        $jms_candidates->update_candidate_status($candidate_id, 'interview_scheduled');

        // Get candidate for notification
        $candidate = $jms_candidates->get_candidate($candidate_id);

        if ($candidate) {
            // Send interview notification email
            $jms_emails = new JMS_Emails();
            $jms_emails->send_interview_notification($candidate, $interview_id, $google_meet_link);
        }

        wp_send_json_success(array(
            'message' => 'Interview scheduled successfully',
            'google_meet_link' => $google_meet_link
        ));
    } else {
        wp_send_json_error(array('message' => 'Failed to schedule interview'));
    }
}
add_action('wp_ajax_jms_admin_schedule_interview', 'jms_admin_schedule_interview');

/**
 * AJAX handler for requesting documents.
 */
function jms_admin_request_documents()
{
    // Check nonce for security
    check_ajax_referer('jms_admin_nonce', 'nonce');

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions'));
        return;
    }

    $candidate_id = isset($_POST['candidate_id']) ? intval($_POST['candidate_id']) : 0;
    $document_types = isset($_POST['document_types']) ? (array) $_POST['document_types'] : array();

    if ($candidate_id <= 0 || empty($document_types)) {
        wp_send_json_error(array('message' => 'Invalid parameters'));
        return;
    }

    // Sanitize document types
    foreach ($document_types as $key => $type) {
        $document_types[$key] = sanitize_text_field($type);
    }

    // Get candidate
    $jms_candidates = new JMS_Candidates();
    $candidate = $jms_candidates->get_candidate($candidate_id);

    if (!$candidate) {
        wp_send_json_error(array('message' => 'Candidate not found'));
        return;
    }

    // Send document request email
    $jms_emails = new JMS_Emails();
    $sent = $jms_emails->send_document_request($candidate, $document_types);

    if ($sent) {
        wp_send_json_success(array('message' => 'Document request sent successfully'));
    } else {
        wp_send_json_error(array('message' => 'Failed to send document request'));
    }
}
add_action('wp_ajax_jms_admin_request_documents', 'jms_admin_request_documents');

/**
 * AJAX handler for managing documents.
 */
function jms_admin_manage_documents()
{
    // Check nonce for security
    check_ajax_referer('jms_admin_nonce', 'nonce');

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions'));
        return;
    }

    $doc_action = isset($_POST['doc_action']) ? sanitize_text_field($_POST['doc_action']) : '';
    $document_id = isset($_POST['document_id']) ? intval($_POST['document_id']) : 0;
    $notes = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';

    if (empty($doc_action) || $document_id <= 0) {
        wp_send_json_error(array('message' => 'Invalid parameters'));
        return;
    }

    // Get document
    $jms_documents = new JMS_Documents();
    $document = $jms_documents->get_document($document_id);

    if (!$document) {
        wp_send_json_error(array('message' => 'Document not found'));
        return;
    }

    // Get candidate
    $jms_candidates = new JMS_Candidates();
    $candidate = $jms_candidates->get_candidate($document->candidate_id);

    if (!$candidate) {
        wp_send_json_error(array('message' => 'Candidate not found'));
        return;
    }

    if ($doc_action == 'approve') {
        // Approve document
        $updated = $jms_documents->update_document_status($document_id, 'approved', $notes);

        if ($updated) {
            // Send notification email
            $jms_emails = new JMS_Emails();
            $jms_emails->send_document_status_notification($candidate, $document, 'approved');

            wp_send_json_success(array('message' => 'Document approved successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to approve document'));
        }
    } elseif ($doc_action == 'reject') {
        // Reject document
        $updated = $jms_documents->update_document_status($document_id, 'rejected', $notes);

        if ($updated) {
            // Send notification email
            $jms_emails = new JMS_Emails();
            $jms_emails->send_document_status_notification($candidate, $document, 'rejected');

            wp_send_json_success(array('message' => 'Document rejected successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to reject document'));
        }
    } else {
        wp_send_json_error(array('message' => 'Invalid action'));
    }
}
add_action('wp_ajax_jms_admin_manage_documents', 'jms_admin_manage_documents');
