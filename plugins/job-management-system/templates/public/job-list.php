<?php

/**
 * Template for displaying job listings on the frontend.
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

// Get jobs
$jms_jobs = new JMS_Jobs();
$limit = isset($atts['limit']) ? intval($atts['limit']) : 10;
$category = isset($atts['category']) ? sanitize_text_field($atts['category']) : '';
$location = isset($atts['location']) ? sanitize_text_field($atts['location']) : '';

$args = array(
    'limit' => $limit,
    'status' => 'open',
    'location' => $location
);

$jobs = $jms_jobs->get_jobs($args);
?>

<div class="jms-job-list">
    <?php if (empty($jobs)) : ?>
        <div class="jms-no-jobs">
            <p><?php _e('No job openings available at this time. Please check back later.', 'job-management-system'); ?></p>
        </div>
    <?php else : ?>
        <div class="jms-jobs-filter">
            <form method="get" action="">
                <div class="jms-filter-row">
                    <div class="jms-filter-field">
                        <label for="jms-filter-location"><?php _e('Location', 'job-management-system'); ?></label>
                        <input type="text" id="jms-filter-location" name="location" placeholder="<?php _e('Filter by location', 'job-management-system'); ?>" value="<?php echo esc_attr($location); ?>">
                    </div>
                    <div class="jms-filter-field">
                        <label for="jms-filter-search"><?php _e('Search', 'job-management-system'); ?></label>
                        <input type="text" id="jms-filter-search" name="search" placeholder="<?php _e('Search jobs', 'job-management-system'); ?>" value="<?php echo isset($_GET['search']) ? esc_attr($_GET['search']) : ''; ?>">
                    </div>
                    <div class="jms-filter-submit">
                        <button type="submit" class="jms-button"><?php _e('Filter', 'job-management-system'); ?></button>
                    </div>
                </div>
            </form>
        </div>

        <div class="jms-jobs-grid">
            <?php foreach ($jobs as $job) : ?>
                <div class="jms-job-card">
                    <h3 class="jms-job-title">
                        <a href="<?php echo esc_url(add_query_arg('job_id', $job->id, home_url('/job-details/'))); ?>"><?php echo esc_html($job->title); ?></a>
                    </h3>
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
                    <div class="jms-job-excerpt">
                        <?php echo wp_trim_words(wp_strip_all_tags($job->description), 30, '...'); ?>
                    </div>
                    <div class="jms-job-actions">
                        <a href="<?php echo esc_url(add_query_arg('job_id', $job->id, home_url('/job-details/'))); ?>" class="jms-button"><?php _e('View Details', 'job-management-system'); ?></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
