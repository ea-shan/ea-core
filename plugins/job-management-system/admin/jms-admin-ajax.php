<?php
/**
 * Add AJAX handlers for job management.
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
 * AJAX handler for getting jobs.
 */
function jms_admin_get_jobs() {
    // Check nonce for security
    check_ajax_referer('jms_admin_nonce', 'nonce');

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions'));
        return;
    }

    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 10;
    $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
    $location = isset($_POST['location']) ? sanitize_text_field($_POST['location']) : '';
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    
    // Calculate offset
    $offset = ($page - 1) * $per_page;
    
    // Get jobs
    $jms_jobs = new JMS_Jobs();
    $args = array(
        'limit' => $per_page,
        'offset' => $offset,
        'status' => $status,
        'location' => $location,
        'search' => $search
    );
    
    $jobs = $jms_jobs->get_jobs($args);
    
    // Get total count
    $total = $jms_jobs->count_jobs(array(
        'status' => $status,
        'location' => $location,
        'search' => $search
    ));
    
    // Calculate total pages
    $total_pages = ceil($total / $per_page);
    
    // Get application counts for each job
    global $wpdb;
    $candidates_table = $wpdb->prefix . 'jms_candidates';
    
    foreach ($jobs as &$job) {
        $job->application_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $candidates_table WHERE job_id = %d",
            $job->id
        ));
        
        // Format date
        $job->date_posted = date_i18n(get_option('date_format'), strtotime($job->date_posted));
    }
    
    wp_send_json_success(array(
        'jobs' => $jobs,
        'total' => $total,
        'total_pages' => $total_pages
    ));
}
add_action('wp_ajax_jms_admin_get_jobs', 'jms_admin_get_jobs');

/**
 * AJAX handler for getting a single job.
 */
function jms_admin_get_job() {
    // Check nonce for security
    check_ajax_referer('jms_admin_nonce', 'nonce');

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions'));
        return;
    }

    $job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
    
    if ($job_id <= 0) {
        wp_send_json_error(array('message' => 'Invalid job ID'));
        return;
    }
    
    // Get job
    $jms_jobs = new JMS_Jobs();
    $job = $jms_jobs->get_job($job_id);
    
    if (!$job) {
        wp_send_json_error(array('message' => 'Job not found'));
        return;
    }
    
    wp_send_json_success(array('job' => $job));
}
add_action('wp_ajax_jms_admin_get_job', 'jms_admin_get_job');

/**
 * AJAX handler for saving a job.
 */
function jms_admin_save_job() {
    // Check nonce for security
    check_ajax_referer('jms_admin_nonce', 'nonce');

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions'));
        return;
    }

    $job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
    $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
    $description = isset($_POST['description']) ? wp_kses_post($_POST['description']) : '';
    $requirements = isset($_POST['requirements']) ? wp_kses_post($_POST['requirements']) : '';
    $location = isset($_POST['location']) ? sanitize_text_field($_POST['location']) : '';
    $salary_range = isset($_POST['salary_range']) ? sanitize_text_field($_POST['salary_range']) : '';
    $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'open';
    
    // Validate required fields
    if (empty($title) || empty($description) || empty($requirements) || empty($location) || empty($salary_range)) {
        wp_send_json_error(array('message' => 'Please fill in all required fields'));
        return;
    }
    
    // Prepare job data
    $job_data = array(
        'title' => $title,
        'description' => $description,
        'requirements' => $requirements,
        'location' => $location,
        'salary_range' => $salary_range,
        'status' => $status,
        'date_modified' => current_time('mysql')
    );
    
    // Save job
    $jms_jobs = new JMS_Jobs();
    
    if ($job_id > 0) {
        // Update existing job
        $updated = $jms_jobs->update_job($job_id, $job_data);
        
        if ($updated) {
            wp_send_json_success(array('message' => 'Job updated successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to update job'));
        }
    } else {
        // Create new job
        $job_id = $jms_jobs->create_job($job_data);
        
        if ($job_id) {
            wp_send_json_success(array('message' => 'Job created successfully', 'job_id' => $job_id));
        } else {
            wp_send_json_error(array('message' => 'Failed to create job'));
        }
    }
}
add_action('wp_ajax_jms_admin_save_job', 'jms_admin_save_job');

/**
 * AJAX handler for deleting a job.
 */
function jms_admin_delete_job() {
    // Check nonce for security
    check_ajax_referer('jms_admin_nonce', 'nonce');

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions'));
        return;
    }

    $job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
    
    if ($job_id <= 0) {
        wp_send_json_error(array('message' => 'Invalid job ID'));
        return;
    }
    
    // Check if job has applications
    global $wpdb;
    $candidates_table = $wpdb->prefix . 'jms_candidates';
    
    $application_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $candidates_table WHERE job_id = %d",
        $job_id
    ));
    
    if ($application_count > 0) {
        wp_send_json_error(array('message' => 'Cannot delete job with existing applications'));
        return;
    }
    
    // Delete job
    $jms_jobs = new JMS_Jobs();
    $deleted = $jms_jobs->delete_job($job_id);
    
    if ($deleted) {
        wp_send_json_success(array('message' => 'Job deleted successfully'));
    } else {
        wp_send_json_error(array('message' => 'Failed to delete job'));
    }
}
add_action('wp_ajax_jms_admin_delete_job', 'jms_admin_delete_job');
