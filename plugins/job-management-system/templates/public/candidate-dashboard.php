<?php
/**
 * Template for displaying candidate dashboard on the frontend.
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
    echo '<div class="jms-login-required">';
    echo '<p>' . __('Please log in to view your application status.', 'job-management-system') . '</p>';
    echo '<p><a href="' . wp_login_url(get_permalink()) . '" class="jms-button">' . __('Log In', 'job-management-system') . '</a></p>';
    echo '</div>';
    return;
}
?>

<div class="jms-candidate-dashboard">
    <h2><?php _e('My Applications', 'job-management-system'); ?></h2>
    
    <div class="jms-dashboard-notices"></div>
    
    <div class="jms-applications-container">
        <div class="jms-loading"><?php _e('Loading your applications...', 'job-management-system'); ?></div>
        <div class="jms-applications-list" style="display: none;"></div>
        <div class="jms-no-applications" style="display: none;">
            <p><?php _e('You have not submitted any job applications yet.', 'job-management-system'); ?></p>
            <p><a href="<?php echo esc_url(home_url('/jobs/')); ?>" class="jms-button"><?php _e('Browse Jobs', 'job-management-system'); ?></a></p>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Load applications
    $.ajax({
        url: jms_public_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'jms_get_candidate_applications',
            nonce: jms_public_ajax.nonce
        },
        success: function(response) {
            $('.jms-loading').hide();
            
            if (response.success) {
                var applications = response.data.applications;
                
                if (applications.length > 0) {
                    displayApplications(applications);
                    $('.jms-applications-list').show();
                } else {
                    $('.jms-no-applications').show();
                }
            } else {
                showNotice('error', response.data.message);
            }
        },
        error: function() {
            $('.jms-loading').hide();
            showNotice('error', '<?php _e('An error occurred while loading your applications.', 'job-management-system'); ?>');
        }
    });
    
    function displayApplications(applications) {
        var html = '<div class="jms-applications-grid">';
        
        $.each(applications, function(index, application) {
            html += '<div class="jms-application-card">';
            html += '<div class="jms-application-header">';
            html += '<h3 class="jms-application-job-title">' + application.job_title + '</h3>';
            html += '<span class="jms-application-status jms-status-' + application.status + '">' + getStatusLabel(application.status) + '</span>';
            html += '</div>';
            
            html += '<div class="jms-application-meta">';
            html += '<div class="jms-application-location"><span class="jms-meta-label"><?php _e('Location:', 'job-management-system'); ?></span> ' + application.job_location + '</div>';
            html += '<div class="jms-application-date"><span class="jms-meta-label"><?php _e('Applied:', 'job-management-system'); ?></span> ' + application.application_date + '</div>';
            html += '<div class="jms-application-updated"><span class="jms-meta-label"><?php _e('Last Updated:', 'job-management-system'); ?></span> ' + application.last_updated + '</div>';
            html += '</div>';
            
            if (application.has_interview) {
                html += '<div class="jms-application-interview">';
                html += '<h4><?php _e('Interview Details', 'job-management-system'); ?></h4>';
                html += '<div class="jms-interview-date"><span class="jms-meta-label"><?php _e('Date & Time:', 'job-management-system'); ?></span> ' + application.interview_date + '</div>';
                html += '<div class="jms-interview-type"><span class="jms-meta-label"><?php _e('Type:', 'job-management-system'); ?></span> ' + getInterviewTypeLabel(application.interview_type) + '</div>';
                
                if (application.interview_type === 'google_meet' && application.google_meet_link) {
                    html += '<div class="jms-interview-link"><a href="' + application.google_meet_link + '" target="_blank" class="jms-button jms-meet-button"><?php _e('Join Google Meet', 'job-management-system'); ?></a></div>';
                }
                
                html += '</div>';
            }
            
            html += '<div class="jms-application-actions">';
            html += '<a href="' + application.resume_path + '" target="_blank" class="jms-button jms-resume-button"><?php _e('View Resume', 'job-management-system'); ?></a>';
            html += '</div>';
            
            html += '</div>';
        });
        
        html += '</div>';
        
        $('.jms-applications-list').html(html);
    }
    
    function getStatusLabel(status) {
        switch (status) {
            case 'applied':
                return '<?php _e('Applied', 'job-management-system'); ?>';
            case 'shortlisted':
                return '<?php _e('Shortlisted', 'job-management-system'); ?>';
            case 'interview_scheduled':
                return '<?php _e('Interview Scheduled', 'job-management-system'); ?>';
            case 'interviewed':
                return '<?php _e('Interviewed', 'job-management-system'); ?>';
            case 'offered':
                return '<?php _e('Offer Extended', 'job-management-system'); ?>';
            case 'hired':
                return '<?php _e('Hired', 'job-management-system'); ?>';
            case 'rejected':
                return '<?php _e('Not Selected', 'job-management-system'); ?>';
            default:
                return status;
        }
    }
    
    function getInterviewTypeLabel(type) {
        switch (type) {
            case 'in_person':
                return '<?php _e('In Person', 'job-management-system'); ?>';
            case 'phone':
                return '<?php _e('Phone', 'job-management-system'); ?>';
            case 'google_meet':
                return '<?php _e('Google Meet', 'job-management-system'); ?>';
            default:
                return type;
        }
    }
    
    function showNotice(type, message) {
        var notice = $('<div class="jms-notice jms-notice-' + type + '">' + message + '</div>');
        $('.jms-dashboard-notices').html(notice);
    }
});
</script>
