<?php
/**
 * Template for displaying job details on the frontend.
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
$job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : (isset($atts['id']) ? intval($atts['id']) : 0);

if ($job_id <= 0) {
    echo '<div class="jms-error">' . __('Job not found.', 'job-management-system') . '</div>';
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

<div class="jms-job-details">
    <div class="jms-job-header">
        <h1 class="jms-job-title"><?php echo esc_html($job->title); ?></h1>
        <div class="jms-job-meta">
            <div class="jms-job-location">
                <span class="jms-meta-label"><?php _e('Location:', 'job-management-system'); ?></span>
                <span class="jms-meta-value"><?php echo esc_html($job->location); ?></span>
            </div>
            <div class="jms-job-salary">
                <span class="jms-meta-label"><?php _e('Salary Range:', 'job-management-system'); ?></span>
                <span class="jms-meta-value"><?php echo esc_html($job->salary_range); ?></span>
            </div>
            <div class="jms-job-date">
                <span class="jms-meta-label"><?php _e('Posted:', 'job-management-system'); ?></span>
                <span class="jms-meta-value"><?php echo date_i18n(get_option('date_format'), strtotime($job->date_posted)); ?></span>
            </div>
        </div>
    </div>

    <div class="jms-job-content">
        <div class="jms-job-section">
            <h2 class="jms-section-title"><?php _e('Job Description', 'job-management-system'); ?></h2>
            <div class="jms-section-content">
                <?php echo wpautop($job->description); ?>
            </div>
        </div>

        <div class="jms-job-section">
            <h2 class="jms-section-title"><?php _e('Requirements', 'job-management-system'); ?></h2>
            <div class="jms-section-content">
                <?php echo wpautop($job->requirements); ?>
            </div>
        </div>
    </div>

    <div class="jms-job-actions">
        <a href="#jms-application-form" class="jms-button jms-apply-button"><?php _e('Apply Now', 'job-management-system'); ?></a>
        <a href="javascript:history.back();" class="jms-button jms-back-button"><?php _e('Back to Jobs', 'job-management-system'); ?></a>
    </div>

    <div id="jms-application-form" class="jms-application-form-container">
        <h2 class="jms-form-title"><?php _e('Apply for this Position', 'job-management-system'); ?></h2>
        <?php echo do_shortcode('[jms_job_application_form job_id="' . $job_id . '"]'); ?>
    </div>
</div>
