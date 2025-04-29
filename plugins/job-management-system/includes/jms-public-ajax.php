<?php
/**
 * Public AJAX handlers for candidate applications.
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
 * AJAX handler for job application submission.
 */
function jms_submit_application() {
    // Check nonce for security
    check_ajax_referer('jms_public_nonce', 'nonce');

    $job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
    $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    $experience = isset($_POST['experience']) ? sanitize_textarea_field($_POST['experience']) : '';
    $education = isset($_POST['education']) ? sanitize_textarea_field($_POST['education']) : '';
    $skills = isset($_POST['skills']) ? sanitize_textarea_field($_POST['skills']) : '';
    
    // Validate required fields
    if ($job_id <= 0 || empty($name) || empty($email) || empty($phone)) {
        wp_send_json_error(array('message' => __('Please fill in all required fields.', 'job-management-system')));
        return;
    }
    
    // Check if job exists and is open
    $jms_jobs = new JMS_Jobs();
    $job = $jms_jobs->get_job($job_id);
    
    if (!$job || $job->status !== 'open') {
        wp_send_json_error(array('message' => __('This job is no longer available.', 'job-management-system')));
        return;
    }
    
    // Handle resume upload
    $resume_path = '';
    if (!empty($_FILES['resume'])) {
        $upload_dir = wp_upload_dir();
        $jms_upload_dir = $upload_dir['basedir'] . '/jms-uploads/resumes';
        
        // Create directory if it doesn't exist
        if (!file_exists($jms_upload_dir)) {
            wp_mkdir_p($jms_upload_dir);
            
            // Create index.php file to prevent directory listing
            $index_file = fopen($jms_upload_dir . '/index.php', 'w');
            fwrite($index_file, '<?php // Silence is golden');
            fclose($index_file);
        }
        
        $file_name = sanitize_file_name($_FILES['resume']['name']);
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        
        // Check file extension
        $allowed_types = array('pdf', 'doc', 'docx');
        if (!in_array(strtolower($file_ext), $allowed_types)) {
            wp_send_json_error(array('message' => __('Invalid file type. Please upload PDF, DOC, or DOCX files.', 'job-management-system')));
            return;
        }
        
        // Generate unique filename
        $unique_filename = uniqid() . '-' . $file_name;
        $upload_path = $jms_upload_dir . '/' . $unique_filename;
        
        // Move uploaded file
        if (move_uploaded_file($_FILES['resume']['tmp_name'], $upload_path)) {
            $resume_path = $upload_dir['baseurl'] . '/jms-uploads/resumes/' . $unique_filename;
        } else {
            wp_send_json_error(array('message' => __('Failed to upload resume. Please try again.', 'job-management-system')));
            return;
        }
    } else {
        wp_send_json_error(array('message' => __('Please upload your resume.', 'job-management-system')));
        return;
    }
    
    // Save application to database
    global $wpdb;
    $candidates_table = $wpdb->prefix . 'jms_candidates';
    
    $inserted = $wpdb->insert(
        $candidates_table,
        array(
            'job_id' => $job_id,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'experience' => $experience,
            'education' => $education,
            'skills' => $skills,
            'resume_path' => $resume_path,
            'status' => 'applied',
            'application_date' => current_time('mysql'),
            'last_updated' => current_time('mysql')
        ),
        array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
    );
    
    if ($inserted) {
        // Send confirmation email to candidate
        $jms_emails = new JMS_Emails();
        $candidate_id = $wpdb->insert_id;
        $jms_emails->send_application_confirmation($candidate_id);
        
        wp_send_json_success(array('message' => __('Your application has been submitted successfully. We will contact you soon.', 'job-management-system')));
    } else {
        wp_send_json_error(array('message' => __('Failed to submit application. Please try again.', 'job-management-system')));
    }
}
add_action('wp_ajax_jms_submit_application', 'jms_submit_application');
add_action('wp_ajax_nopriv_jms_submit_application', 'jms_submit_application');

/**
 * AJAX handler for getting candidate applications.
 */
function jms_get_candidate_applications() {
    // Check nonce for security
    check_ajax_referer('jms_public_nonce', 'nonce');

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('You must be logged in to view your applications.', 'job-management-system')));
        return;
    }

    $current_user = wp_get_current_user();
    $email = $current_user->user_email;
    
    // Get applications for this email
    $jms_candidates = new JMS_Candidates();
    $applications = $jms_candidates->get_candidates_by_email($email);
    
    if (empty($applications)) {
        wp_send_json_success(array('applications' => array()));
        return;
    }
    
    // Get job details for each application
    $jms_jobs = new JMS_Jobs();
    
    foreach ($applications as &$application) {
        $job = $jms_jobs->get_job($application->job_id);
        
        if ($job) {
            $application->job_title = $job->title;
            $application->job_location = $job->location;
        } else {
            $application->job_title = __('Job no longer available', 'job-management-system');
            $application->job_location = '';
        }
        
        // Format dates
        $application->application_date = date_i18n(get_option('date_format'), strtotime($application->application_date));
        $application->last_updated = date_i18n(get_option('date_format'), strtotime($application->last_updated));
        
        // Get interview details if any
        global $wpdb;
        $interviews_table = $wpdb->prefix . 'jms_interviews';
        
        $interview = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $interviews_table WHERE candidate_id = %d ORDER BY interview_date DESC LIMIT 1",
            $application->id
        ));
        
        if ($interview) {
            $application->has_interview = true;
            $application->interview_date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($interview->interview_date));
            $application->interview_type = $interview->interview_type;
            $application->google_meet_link = $interview->google_meet_link;
        } else {
            $application->has_interview = false;
        }
    }
    
    wp_send_json_success(array('applications' => $applications));
}
add_action('wp_ajax_jms_get_candidate_applications', 'jms_get_candidate_applications');
