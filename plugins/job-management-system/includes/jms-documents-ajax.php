<?php
/**
 * AJAX handlers for document management.
 *
 * @since      1.0.0
 * @package    Job_Management_System
 * @subpackage Job_Management_System/includes
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * AJAX handler for document upload.
 */
function jms_upload_document() {
    // Check nonce for security
    check_ajax_referer('jms_public_nonce', 'nonce');

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('You must be logged in to upload documents.', 'job-management-system')));
        return;
    }

    $candidate_id = isset($_POST['candidate_id']) ? intval($_POST['candidate_id']) : 0;
    $document_type = isset($_POST['document_type']) ? sanitize_text_field($_POST['document_type']) : '';
    
    // Validate required fields
    if ($candidate_id <= 0 || empty($document_type)) {
        wp_send_json_error(array('message' => __('Invalid parameters.', 'job-management-system')));
        return;
    }
    
    // Verify candidate belongs to current user
    $current_user = wp_get_current_user();
    $email = $current_user->user_email;
    
    global $wpdb;
    $candidates_table = $wpdb->prefix . 'jms_candidates';
    
    $candidate = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $candidates_table WHERE id = %d AND email = %s",
        $candidate_id, $email
    ));
    
    if (!$candidate) {
        wp_send_json_error(array('message' => __('You do not have permission to upload documents for this candidate.', 'job-management-system')));
        return;
    }
    
    // Check if document already exists
    $jms_documents = new JMS_Documents();
    $existing_documents = $jms_documents->get_documents(array(
        'candidate_id' => $candidate_id,
        'document_type' => $document_type
    ));
    
    if (!empty($existing_documents)) {
        // If document exists and is rejected, allow re-upload
        $allow_reupload = false;
        
        foreach ($existing_documents as $doc) {
            if ($doc->status === 'rejected') {
                $allow_reupload = true;
                break;
            }
        }
        
        if (!$allow_reupload) {
            wp_send_json_error(array('message' => __('This document type has already been uploaded.', 'job-management-system')));
            return;
        }
    }
    
    // Handle document upload
    if (!isset($_FILES['document']) || empty($_FILES['document']['name'])) {
        wp_send_json_error(array('message' => __('Please select a document to upload.', 'job-management-system')));
        return;
    }
    
    $document_data = array(
        'candidate_id' => $candidate_id,
        'document_type' => $document_type
    );
    
    $document_id = $jms_documents->upload_document($document_data, $_FILES['document']);
    
    if ($document_id) {
        wp_send_json_success(array('message' => __('Document uploaded successfully.', 'job-management-system')));
    } else {
        wp_send_json_error(array('message' => __('Failed to upload document. Please try again.', 'job-management-system')));
    }
}
add_action('wp_ajax_jms_upload_document', 'jms_upload_document');

/**
 * AJAX handler for getting candidate documents.
 */
function jms_get_candidate_documents() {
    // Check nonce for security
    check_ajax_referer('jms_public_nonce', 'nonce');

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('You must be logged in to view documents.', 'job-management-system')));
        return;
    }

    $candidate_id = isset($_POST['candidate_id']) ? intval($_POST['candidate_id']) : 0;
    
    if ($candidate_id <= 0) {
        wp_send_json_error(array('message' => __('Invalid candidate ID.', 'job-management-system')));
        return;
    }
    
    // Verify candidate belongs to current user
    $current_user = wp_get_current_user();
    $email = $current_user->user_email;
    
    global $wpdb;
    $candidates_table = $wpdb->prefix . 'jms_candidates';
    
    $candidate = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $candidates_table WHERE id = %d AND email = %s",
        $candidate_id, $email
    ));
    
    if (!$candidate) {
        wp_send_json_error(array('message' => __('You do not have permission to view documents for this candidate.', 'job-management-system')));
        return;
    }
    
    // Get documents
    $jms_documents = new JMS_Documents();
    $documents = $jms_documents->get_documents(array('candidate_id' => $candidate_id));
    
    // Get document type labels
    $document_types = $jms_documents->get_document_types();
    
    foreach ($documents as &$document) {
        $document->document_type_label = isset($document_types[$document->document_type]) ? $document_types[$document->document_type] : $document->document_type;
        $document->upload_date = date_i18n(get_option('date_format'), strtotime($document->upload_date));
        
        if ($document->last_updated) {
            $document->last_updated = date_i18n(get_option('date_format'), strtotime($document->last_updated));
        }
    }
    
    wp_send_json_success(array('documents' => $documents));
}
add_action('wp_ajax_jms_get_candidate_documents', 'jms_get_candidate_documents');

/**
 * AJAX handler for deleting a document.
 */
function jms_delete_document() {
    // Check nonce for security
    check_ajax_referer('jms_public_nonce', 'nonce');

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('You must be logged in to delete documents.', 'job-management-system')));
        return;
    }

    $document_id = isset($_POST['document_id']) ? intval($_POST['document_id']) : 0;
    
    if ($document_id <= 0) {
        wp_send_json_error(array('message' => __('Invalid document ID.', 'job-management-system')));
        return;
    }
    
    // Get document
    $jms_documents = new JMS_Documents();
    $document = $jms_documents->get_document($document_id);
    
    if (!$document) {
        wp_send_json_error(array('message' => __('Document not found.', 'job-management-system')));
        return;
    }
    
    // Verify document belongs to current user
    $current_user = wp_get_current_user();
    $email = $current_user->user_email;
    
    global $wpdb;
    $candidates_table = $wpdb->prefix . 'jms_candidates';
    
    $candidate = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $candidates_table WHERE id = %d AND email = %s",
        $document->candidate_id, $email
    ));
    
    if (!$candidate) {
        wp_send_json_error(array('message' => __('You do not have permission to delete this document.', 'job-management-system')));
        return;
    }
    
    // Only allow deletion of rejected documents
    if ($document->status !== 'rejected') {
        wp_send_json_error(array('message' => __('Only rejected documents can be deleted.', 'job-management-system')));
        return;
    }
    
    // Delete document
    $deleted = $jms_documents->delete_document($document_id);
    
    if ($deleted) {
        wp_send_json_success(array('message' => __('Document deleted successfully.', 'job-management-system')));
    } else {
        wp_send_json_error(array('message' => __('Failed to delete document. Please try again.', 'job-management-system')));
    }
}
add_action('wp_ajax_jms_delete_document', 'jms_delete_document');
