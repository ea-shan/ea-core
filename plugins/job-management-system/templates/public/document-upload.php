<?php
/**
 * Template for document upload in candidate dashboard.
 *
 * @link       https://expressanalytics.net
 * @since      1.0.0
 *
 * @package    Job_Management_System
 * @subpackage Job_Management_System/templates/public
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Check if user is logged in
if (!is_user_logged_in()) {
    return;
}

// Get candidate ID from email
$current_user = wp_get_current_user();
$email = $current_user->user_email;

global $wpdb;
$candidates_table = $wpdb->prefix . 'jms_candidates';
$candidate = $wpdb->get_row($wpdb->prepare("SELECT * FROM $candidates_table WHERE email = %s ORDER BY id DESC LIMIT 1", $email));

if (!$candidate) {
    return;
}

// Get document types
$jms_documents = new JMS_Documents();
$document_types = $jms_documents->get_document_types();

// Get existing documents
$documents = $jms_documents->get_documents(array('candidate_id' => $candidate->id));
?>

<div class="jms-document-upload">
    <h3><?php _e('Onboarding Documents', 'job-management-system'); ?></h3>
    
    <p><?php _e('Please upload the required documents for your application process.', 'job-management-system'); ?></p>
    
    <div class="jms-document-notices"></div>
    
    <?php if (!empty($documents)) : ?>
        <div class="jms-document-list">
            <h4><?php _e('Uploaded Documents', 'job-management-system'); ?></h4>
            
            <?php foreach ($documents as $document) : ?>
                <div class="jms-document-item">
                    <div class="jms-document-name">
                        <?php echo isset($document_types[$document->document_type]) ? esc_html($document_types[$document->document_type]) : esc_html($document->document_type); ?>
                    </div>
                    <div class="jms-document-status jms-status-<?php echo esc_attr($document->status); ?>">
                        <?php
                        switch ($document->status) {
                            case 'submitted':
                                _e('Submitted', 'job-management-system');
                                break;
                            case 'approved':
                                _e('Approved', 'job-management-system');
                                break;
                            case 'rejected':
                                _e('Rejected', 'job-management-system');
                                break;
                            default:
                                echo esc_html($document->status);
                        }
                        ?>
                    </div>
                    <div class="jms-document-actions">
                        <a href="<?php echo esc_url($document->document_path); ?>" target="_blank" class="jms-button jms-view-button"><?php _e('View', 'job-management-system'); ?></a>
                        <?php if ($document->status === 'rejected') : ?>
                            <a href="#" class="jms-button jms-reupload-button" data-type="<?php echo esc_attr($document->document_type); ?>"><?php _e('Re-upload', 'job-management-system'); ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div class="jms-upload-form">
        <h4><?php _e('Upload New Document', 'job-management-system'); ?></h4>
        
        <form id="jms-document-upload-form" enctype="multipart/form-data">
            <input type="hidden" name="candidate_id" value="<?php echo esc_attr($candidate->id); ?>">
            
            <div class="jms-form-row">
                <label for="jms-document-type"><?php _e('Document Type', 'job-management-system'); ?> <span class="required">*</span></label>
                <select id="jms-document-type" name="document_type" required>
                    <option value=""><?php _e('Select Document Type', 'job-management-system'); ?></option>
                    <?php foreach ($document_types as $type => $label) : ?>
                        <option value="<?php echo esc_attr($type); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="jms-form-row">
                <label for="jms-document-file"><?php _e('Document File', 'job-management-system'); ?> <span class="required">*</span></label>
                <input type="file" id="jms-document-file" name="document" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                <p class="jms-field-hint"><?php _e('Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG', 'job-management-system'); ?></p>
            </div>
            
            <div class="jms-form-actions">
                <button type="submit" class="jms-button jms-upload-button"><?php _e('Upload Document', 'job-management-system'); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Document upload form submission
    $('#jms-document-upload-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('action', 'jms_upload_document');
        formData.append('nonce', jms_public_ajax.nonce);
        
        // Show loading message
        $('.jms-document-notices').html('<div class="jms-notice jms-notice-info"><?php _e('Uploading document, please wait...', 'job-management-system'); ?></div>');
        
        $.ajax({
            url: jms_public_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Show success message
                    $('.jms-document-notices').html('<div class="jms-notice jms-notice-success">' + response.data.message + '</div>');
                    
                    // Reset form
                    $('#jms-document-upload-form')[0].reset();
                    
                    // Reload page after a delay to show the new document
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    // Show error message
                    $('.jms-document-notices').html('<div class="jms-notice jms-notice-error">' + response.data.message + '</div>');
                }
            },
            error: function() {
                // Show error message
                $('.jms-document-notices').html('<div class="jms-notice jms-notice-error"><?php _e('An error occurred while uploading the document. Please try again.', 'job-management-system'); ?></div>');
            }
        });
    });
    
    // Re-upload button click
    $('.jms-reupload-button').on('click', function(e) {
        e.preventDefault();
        
        var documentType = $(this).data('type');
        
        // Set the document type in the form
        $('#jms-document-type').val(documentType);
        
        // Scroll to the upload form
        $('html, body').animate({
            scrollTop: $('.jms-upload-form').offset().top - 50
        }, 500);
    });
});
</script>
