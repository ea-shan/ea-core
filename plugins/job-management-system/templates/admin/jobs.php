<?php
/**
 * Admin template for managing jobs.
 *
 * @link       https://expressanalytics.net
 * @since      1.0.0
 *
 * @package    Job_Management_System
 * @subpackage Job_Management_System/templates/admin
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap jms-admin">
    <h1 class="wp-heading-inline"><?php _e('Job Management', 'job-management-system'); ?></h1>
    <a href="javascript:void(0);" class="page-title-action add-new-job"><?php _e('Add New Job', 'job-management-system'); ?></a>
    <hr class="wp-header-end">

    <div class="jms-admin-notices"></div>

    <!-- Job Filters -->
    <div class="tablenav top">
        <div class="alignleft actions">
            <select id="jms-filter-status">
                <option value=""><?php _e('All Statuses', 'job-management-system'); ?></option>
                <option value="open"><?php _e('Open', 'job-management-system'); ?></option>
                <option value="closed"><?php _e('Closed', 'job-management-system'); ?></option>
            </select>
            <input type="text" id="jms-filter-location" placeholder="<?php _e('Filter by location', 'job-management-system'); ?>">
            <input type="text" id="jms-search-jobs" placeholder="<?php _e('Search jobs', 'job-management-system'); ?>">
            <button class="button" id="jms-filter-jobs"><?php _e('Filter', 'job-management-system'); ?></button>
        </div>
        <div class="tablenav-pages">
            <span class="displaying-num"><span id="jms-jobs-count">0</span> <?php _e('items', 'job-management-system'); ?></span>
            <span class="pagination-links">
                <a class="first-page button" href="javascript:void(0);"><span class="screen-reader-text"><?php _e('First page', 'job-management-system'); ?></span><span aria-hidden="true">«</span></a>
                <a class="prev-page button" href="javascript:void(0);"><span class="screen-reader-text"><?php _e('Previous page', 'job-management-system'); ?></span><span aria-hidden="true">‹</span></a>
                <span class="paging-input">
                    <span class="tablenav-paging-text"><span id="jms-current-page">1</span> <?php _e('of', 'job-management-system'); ?> <span id="jms-total-pages">1</span></span>
                </span>
                <a class="next-page button" href="javascript:void(0);"><span class="screen-reader-text"><?php _e('Next page', 'job-management-system'); ?></span><span aria-hidden="true">›</span></a>
                <a class="last-page button" href="javascript:void(0);"><span class="screen-reader-text"><?php _e('Last page', 'job-management-system'); ?></span><span aria-hidden="true">»</span></a>
            </span>
        </div>
        <br class="clear">
    </div>

    <!-- Jobs Table -->
    <table class="wp-list-table widefat fixed striped jms-jobs-table">
        <thead>
            <tr>
                <th class="column-title"><?php _e('Title', 'job-management-system'); ?></th>
                <th class="column-location"><?php _e('Location', 'job-management-system'); ?></th>
                <th class="column-salary"><?php _e('Salary Range', 'job-management-system'); ?></th>
                <th class="column-status"><?php _e('Status', 'job-management-system'); ?></th>
                <th class="column-applications"><?php _e('Applications', 'job-management-system'); ?></th>
                <th class="column-date"><?php _e('Date Posted', 'job-management-system'); ?></th>
                <th class="column-actions"><?php _e('Actions', 'job-management-system'); ?></th>
            </tr>
        </thead>
        <tbody id="jms-jobs-list">
            <tr>
                <td colspan="7"><?php _e('Loading jobs...', 'job-management-system'); ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Job Form Modal -->
    <div id="jms-job-modal" class="jms-modal">
        <div class="jms-modal-content">
            <span class="jms-modal-close">&times;</span>
            <h2 id="jms-job-modal-title"><?php _e('Add New Job', 'job-management-system'); ?></h2>
            <div class="jms-modal-notices"></div>
            <form id="jms-job-form">
                <input type="hidden" id="jms-job-id" value="0">
                <div class="jms-form-row">
                    <label for="jms-job-title"><?php _e('Job Title', 'job-management-system'); ?> <span class="required">*</span></label>
                    <input type="text" id="jms-job-title" name="title" required>
                </div>
                <div class="jms-form-row">
                    <label for="jms-job-description"><?php _e('Job Description', 'job-management-system'); ?> <span class="required">*</span></label>
                    <textarea id="jms-job-description" name="description" rows="5" required></textarea>
                </div>
                <div class="jms-form-row">
                    <label for="jms-job-requirements"><?php _e('Job Requirements', 'job-management-system'); ?> <span class="required">*</span></label>
                    <textarea id="jms-job-requirements" name="requirements" rows="5" required></textarea>
                </div>
                <div class="jms-form-row">
                    <label for="jms-job-location"><?php _e('Location', 'job-management-system'); ?> <span class="required">*</span></label>
                    <input type="text" id="jms-job-location" name="location" required>
                </div>
                <div class="jms-form-row">
                    <label for="jms-job-salary"><?php _e('Salary Range', 'job-management-system'); ?> <span class="required">*</span></label>
                    <input type="text" id="jms-job-salary" name="salary_range" required>
                </div>
                <div class="jms-form-row">
                    <label for="jms-job-status"><?php _e('Status', 'job-management-system'); ?></label>
                    <select id="jms-job-status" name="status">
                        <option value="open"><?php _e('Open', 'job-management-system'); ?></option>
                        <option value="closed"><?php _e('Closed', 'job-management-system'); ?></option>
                    </select>
                </div>
                <div class="jms-form-actions">
                    <button type="submit" class="button button-primary"><?php _e('Save Job', 'job-management-system'); ?></button>
                    <button type="button" class="button jms-modal-cancel"><?php _e('Cancel', 'job-management-system'); ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="jms-delete-modal" class="jms-modal">
        <div class="jms-modal-content">
            <span class="jms-modal-close">&times;</span>
            <h2><?php _e('Delete Job', 'job-management-system'); ?></h2>
            <p><?php _e('Are you sure you want to delete this job? This action cannot be undone.', 'job-management-system'); ?></p>
            <div class="jms-form-actions">
                <button type="button" class="button button-primary" id="jms-confirm-delete"><?php _e('Delete', 'job-management-system'); ?></button>
                <button type="button" class="button jms-modal-cancel"><?php _e('Cancel', 'job-management-system'); ?></button>
            </div>
        </div>
    </div>
</div>
