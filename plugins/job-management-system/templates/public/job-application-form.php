<?php
/**
 * Template for displaying job application form on the frontend.
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

// Get job ID
$job_id = isset($atts['job_id']) ? intval($atts['job_id']) : 0;

if ($job_id <= 0) {
    echo '<div class="jms-error">' . __('Invalid job ID.', 'job-management-system') . '</div>';
    return;
}

// Get job details
$jms_jobs = new JMS_Jobs();
$job = $jms_jobs->get_job($job_id);

if (!$job || $job->status !== 'open') {
    echo '<div class="jms-error">' . __('Job not found or no longer available.', 'job-management-system') . '</div>';
    return;
}
?>

<div class="jms-application-form">
    <div class="jms-form-notices"></div>
    
    <form id="jms-apply-form" enctype="multipart/form-data">
        <input type="hidden" name="job_id" value="<?php echo esc_attr($job_id); ?>">
        
        <div class="jms-form-row">
            <label for="jms-applicant-name"><?php _e('Full Name', 'job-management-system'); ?> <span class="required">*</span></label>
            <input type="text" id="jms-applicant-name" name="name" required>
        </div>
        
        <div class="jms-form-row">
            <label for="jms-applicant-email"><?php _e('Email Address', 'job-management-system'); ?> <span class="required">*</span></label>
            <input type="email" id="jms-applicant-email" name="email" required>
        </div>
        
        <div class="jms-form-row">
            <label for="jms-applicant-phone"><?php _e('Phone Number', 'job-management-system'); ?> <span class="required">*</span></label>
            <input type="tel" id="jms-applicant-phone" name="phone" required>
        </div>
        
        <div class="jms-form-row">
            <label for="jms-applicant-experience"><?php _e('Work Experience', 'job-management-system'); ?> <span class="required">*</span></label>
            <textarea id="jms-applicant-experience" name="experience" rows="4" required></textarea>
            <p class="jms-field-hint"><?php _e('Please provide details of your relevant work experience.', 'job-management-system'); ?></p>
        </div>
        
        <div class="jms-form-row">
            <label for="jms-applicant-education"><?php _e('Education', 'job-management-system'); ?> <span class="required">*</span></label>
            <textarea id="jms-applicant-education" name="education" rows="4" required></textarea>
            <p class="jms-field-hint"><?php _e('Please provide details of your educational background.', 'job-management-system'); ?></p>
        </div>
        
        <div class="jms-form-row">
            <label for="jms-applicant-skills"><?php _e('Skills', 'job-management-system'); ?> <span class="required">*</span></label>
            <textarea id="jms-applicant-skills" name="skills" rows="4" required></textarea>
            <p class="jms-field-hint"><?php _e('Please list your relevant skills for this position.', 'job-management-system'); ?></p>
        </div>
        
        <div class="jms-form-row">
            <label for="jms-applicant-resume"><?php _e('Resume/CV', 'job-management-system'); ?> <span class="required">*</span></label>
            <input type="file" id="jms-applicant-resume" name="resume" required accept=".pdf,.doc,.docx">
            <p class="jms-field-hint"><?php _e('Please upload your resume/CV in PDF, DOC, or DOCX format.', 'job-management-system'); ?></p>
        </div>
        
        <div class="jms-form-actions">
            <button type="submit" class="jms-button jms-submit-button"><?php _e('Submit Application', 'job-management-system'); ?></button>
        </div>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    $('#jms-apply-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('action', 'jms_submit_application');
        formData.append('nonce', jms_public_ajax.nonce);
        
        // Show loading message
        $('.jms-form-notices').html('<div class="jms-notice jms-notice-info"><?php _e('Submitting your application, please wait...', 'job-management-system'); ?></div>');
        
        $.ajax({
            url: jms_public_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Show success message
                    $('.jms-form-notices').html('<div class="jms-notice jms-notice-success">' + response.data.message + '</div>');
                    
                    // Reset form
                    $('#jms-apply-form')[0].reset();
                } else {
                    // Show error message
                    $('.jms-form-notices').html('<div class="jms-notice jms-notice-error">' + response.data.message + '</div>');
                }
            },
            error: function() {
                // Show error message
                $('.jms-form-notices').html('<div class="jms-notice jms-notice-error"><?php _e('An error occurred while submitting your application. Please try again.', 'job-management-system'); ?></div>');
            }
        });
    });
});
</script>
