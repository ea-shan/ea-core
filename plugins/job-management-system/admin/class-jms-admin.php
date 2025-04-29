<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Job_Management_System
 * @subpackage Job_Management_System/admin
 */

class JMS_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Register AJAX handlers
        add_action('wp_ajax_jms_admin_get_jobs', array($this, 'ajax_get_jobs'));
        add_action('wp_ajax_jms_admin_get_job', array($this, 'ajax_get_job'));
        add_action('wp_ajax_jms_admin_save_job', array($this, 'ajax_save_job'));
        add_action('wp_ajax_jms_admin_delete_job', array($this, 'ajax_delete_job'));
        add_action('wp_ajax_jms_admin_get_candidates', array($this, 'ajax_get_candidates'));
        add_action('wp_ajax_jms_admin_update_candidate_status', array($this, 'ajax_update_candidate_status'));
        add_action('wp_ajax_jms_admin_schedule_interview', array($this, 'ajax_schedule_interview'));
        add_action('wp_ajax_jms_admin_manage_documents', array($this, 'ajax_manage_documents'));
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, JMS_PLUGIN_URL . 'assets/css/jms-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_name, JMS_PLUGIN_URL . 'assets/js/jms-admin.js', array('jquery'), $this->version, false);

        // Localize the script with data for AJAX
        wp_localize_script($this->plugin_name, 'jms_admin_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('jms_admin_nonce')
        ));
    }

    /**
     * Add menu items to the admin dashboard.
     *
     * @since    1.0.0
     */
    public function add_admin_menu()
    {
        // Main menu
        add_menu_page(
            __('Job Management System', 'job-management-system'),
            __('Job Management', 'job-management-system'),
            'manage_options',
            'job-management-system',
            array($this, 'display_jobs_page'),
            'dashicons-id',
            25
        );

        // Jobs submenu
        add_submenu_page(
            'job-management-system',
            __('Jobs', 'job-management-system'),
            __('Jobs', 'job-management-system'),
            'manage_options',
            'job-management-system',
            array($this, 'display_jobs_page')
        );

        // Candidates submenu
        add_submenu_page(
            'job-management-system',
            __('Candidates', 'job-management-system'),
            __('Candidates', 'job-management-system'),
            'manage_options',
            'jms-candidates',
            array($this, 'display_candidates_page')
        );

        // Interviews submenu
        add_submenu_page(
            'job-management-system',
            __('Interviews', 'job-management-system'),
            __('Interviews', 'job-management-system'),
            'manage_options',
            'jms-interviews',
            array($this, 'display_interviews_page')
        );

        // Onboarding submenu
        add_submenu_page(
            'job-management-system',
            __('Onboarding', 'job-management-system'),
            __('Onboarding', 'job-management-system'),
            'manage_options',
            'jms-onboarding',
            array($this, 'display_onboarding_page')
        );

        // Settings submenu
        add_submenu_page(
            'job-management-system',
            __('Settings', 'job-management-system'),
            __('Settings', 'job-management-system'),
            'manage_options',
            'jms-settings',
            array($this, 'display_settings_page')
        );
    }

    /**
     * Display the jobs management page.
     *
     * @since    1.0.0
     */
    public function display_jobs_page()
    {
        require_once JMS_PLUGIN_DIR . 'templates/admin/jobs.php';
    }

    /**
     * Display the candidates management page.
     *
     * @since    1.0.0
     */
    public function display_candidates_page()
    {
        require_once JMS_PLUGIN_DIR . 'templates/admin/candidates.php';
    }

    /**
     * Display the interviews management page.
     *
     * @since    1.0.0
     */
    public function display_interviews_page()
    {
        require_once JMS_PLUGIN_DIR . 'templates/admin/interviews.php';
    }

    /**
     * Display the onboarding management page.
     *
     * @since    1.0.0
     */
    public function display_onboarding_page()
    {
        require_once JMS_PLUGIN_DIR . 'templates/admin/onboarding.php';
    }

    /**
     * Display the settings page.
     *
     * @since    1.0.0
     */
    public function display_settings_page()
    {
        require_once JMS_PLUGIN_DIR . 'templates/admin/settings.php';
    }

    /**
     * AJAX handler for getting candidates.
     *
     * @since    1.0.0
     */
    public function ajax_get_candidates()
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

        global $wpdb;
        $candidates_table = $wpdb->prefix . 'jms_candidates';
        $jobs_table = $wpdb->prefix . 'jms_jobs';

        $query = "SELECT c.*, j.title as job_title
                 FROM $candidates_table c
                 LEFT JOIN $jobs_table j ON c.job_id = j.id
                 WHERE 1=1";

        if ($job_id > 0) {
            $query .= $wpdb->prepare(" AND c.job_id = %d", $job_id);
        }

        if (!empty($status)) {
            $query .= $wpdb->prepare(" AND c.status = %s", $status);
        }

        if (!empty($search)) {
            $query .= $wpdb->prepare(
                " AND (c.name LIKE %s OR c.email LIKE %s OR c.phone LIKE %s)",
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            );
        }

        $query .= " ORDER BY c.application_date DESC";

        $candidates = $wpdb->get_results($query);

        // Format resume paths
        foreach ($candidates as &$candidate) {
            $candidate->resume_path = wp_get_attachment_url($candidate->resume_path);
        }

        wp_send_json_success(array('candidates' => $candidates));
    }

    /**
     * AJAX handler for updating candidate status.
     *
     * @since    1.0.0
     */
    public function ajax_update_candidate_status()
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

        global $wpdb;
        $candidates_table = $wpdb->prefix . 'jms_candidates';

        $updated = $wpdb->update(
            $candidates_table,
            array(
                'status' => $status,
                'last_updated' => current_time('mysql')
            ),
            array('id' => $candidate_id),
            array('%s', '%s'),
            array('%d')
        );

        if ($updated) {
            // Get candidate email for notification
            $candidate = $wpdb->get_row($wpdb->prepare("SELECT * FROM $candidates_table WHERE id = %d", $candidate_id));

            if ($candidate && $status == 'shortlisted') {
                // Send email notification for shortlisted candidates
                $jms_emails = new JMS_Emails();
                $jms_emails->send_candidate_status_notification($candidate, 'shortlisted');
            }

            wp_send_json_success(array('message' => 'Candidate status updated successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to update candidate status'));
        }
    }

    /**
     * AJAX handler for scheduling interviews.
     *
     * @since    1.0.0
     */
    public function ajax_schedule_interview()
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

        global $wpdb;
        $interviews_table = $wpdb->prefix . 'jms_interviews';

        // Format the interview date
        $interview_datetime = date('Y-m-d H:i:s', strtotime($interview_date));

        // Generate Google Meet link if needed
        $google_meet_link = '';
        if ($interview_type == 'google_meet') {
            $jms_google_meet = new JMS_Google_Meet();
            $google_meet_link = $jms_google_meet->create_meeting($candidate_id, $interview_datetime);
        }

        $inserted = $wpdb->insert(
            $interviews_table,
            array(
                'candidate_id' => $candidate_id,
                'job_id' => $job_id,
                'interview_date' => $interview_datetime,
                'interview_type' => $interview_type,
                'google_meet_link' => $google_meet_link,
                'notes' => $notes,
                'status' => 'scheduled',
                'created_date' => current_time('mysql')
            ),
            array('%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        if ($inserted) {
            // Update candidate status
            $candidates_table = $wpdb->prefix . 'jms_candidates';
            $wpdb->update(
                $candidates_table,
                array(
                    'status' => 'interview_scheduled',
                    'last_updated' => current_time('mysql')
                ),
                array('id' => $candidate_id),
                array('%s', '%s'),
                array('%d')
            );

            // Get candidate email for notification
            $candidate = $wpdb->get_row($wpdb->prepare("SELECT * FROM $candidates_table WHERE id = %d", $candidate_id));

            if ($candidate) {
                // Send interview notification email
                $jms_emails = new JMS_Emails();
                $interview_id = $wpdb->insert_id;
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

    /**
     * AJAX handler for managing onboarding documents.
     *
     * @since    1.0.0
     */
    public function ajax_manage_documents()
    {
        // Check nonce for security
        check_ajax_referer('jms_admin_nonce', 'nonce');

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }

        $action = isset($_POST['doc_action']) ? sanitize_text_field($_POST['doc_action']) : '';
        $document_id = isset($_POST['document_id']) ? intval($_POST['document_id']) : 0;

        if (empty($action) || $document_id <= 0) {
            wp_send_json_error(array('message' => 'Invalid parameters'));
            return;
        }

        global $wpdb;
        $documents_table = $wpdb->prefix . 'jms_documents';

        if ($action == 'approve') {
            $updated = $wpdb->update(
                $documents_table,
                array(
                    'status' => 'approved',
                    'notes' => isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : ''
                ),
                array('id' => $document_id),
                array('%s', '%s'),
                array('%d')
            );

            if ($updated) {
                wp_send_json_success(array('message' => 'Document approved successfully'));
            } else {
                wp_send_json_error(array('message' => 'Failed to approve document'));
            }
        } elseif ($action == 'reject') {
            $updated = $wpdb->update(
                $documents_table,
                array(
                    'status' => 'rejected',
                    'notes' => isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : ''
                ),
                array('id' => $document_id),
                array('%s', '%s'),
                array('%d')
            );

            if ($updated) {
                wp_send_json_success(array('message' => 'Document rejected successfully'));
            } else {
                wp_send_json_error(array('message' => 'Failed to reject document'));
            }
        } else {
            wp_send_json_error(array('message' => 'Invalid action'));
        }
    }

    /**
     * AJAX handler for getting jobs.
     *
     * @since    1.0.0
     */
    public function ajax_get_jobs()
    {
        // Check nonce for security
        check_ajax_referer('jms_admin_nonce', 'nonce');

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }

        $page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
        $per_page = isset($_POST['per_page']) ? max(1, intval($_POST['per_page'])) : 10;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        $location = isset($_POST['location']) ? sanitize_text_field($_POST['location']) : '';
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

        global $wpdb;
        $jobs_table = $wpdb->prefix . 'jms_jobs';

        // Build base query
        $query = "FROM $jobs_table WHERE 1=1";

        if (!empty($status)) {
            $query .= $wpdb->prepare(" AND status = %s", $status);
        }

        if (!empty($location)) {
            $query .= $wpdb->prepare(" AND location LIKE %s", '%' . $wpdb->esc_like($location) . '%');
        }

        if (!empty($search)) {
            $query .= $wpdb->prepare(
                " AND (title LIKE %s OR description LIKE %s OR requirements LIKE %s)",
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            );
        }

        // Get total count
        $total_query = "SELECT COUNT(*) " . $query;
        $total = $wpdb->get_var($total_query);

        // Get paginated results
        $offset = ($page - 1) * $per_page;
        $jobs_query = "SELECT * " . $query . " ORDER BY date_posted DESC LIMIT %d OFFSET %d";
        $jobs = $wpdb->get_results($wpdb->prepare($jobs_query, $per_page, $offset));

        // Get application count for each job
        foreach ($jobs as &$job) {
            $candidates_table = $wpdb->prefix . 'jms_candidates';
            $job->application_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $candidates_table WHERE job_id = %d",
                $job->id
            ));
        }

        wp_send_json_success(array(
            'jobs' => $jobs,
            'total' => intval($total),
            'total_pages' => ceil($total / $per_page)
        ));
    }

    /**
     * AJAX handler for saving jobs.
     *
     * @since    1.0.0
     */
    public function ajax_save_job()
    {
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

        if (empty($title) || empty($description) || empty($requirements) || empty($location) || empty($salary_range)) {
            wp_send_json_error(array('message' => 'Please fill in all required fields'));
            return;
        }

        global $wpdb;
        $jobs_table = $wpdb->prefix . 'jms_jobs';

        $data = array(
            'title' => $title,
            'description' => $description,
            'requirements' => $requirements,
            'location' => $location,
            'salary_range' => $salary_range,
            'status' => $status,
            'date_modified' => current_time('mysql')
        );

        $format = array('%s', '%s', '%s', '%s', '%s', '%s', '%s');

        if ($job_id > 0) {
            // Update existing job
            $updated = $wpdb->update(
                $jobs_table,
                $data,
                array('id' => $job_id),
                $format,
                array('%d')
            );

            if ($updated !== false) {
                wp_send_json_success(array(
                    'message' => 'Job updated successfully',
                    'job_id' => $job_id
                ));
            } else {
                wp_send_json_error(array('message' => 'Failed to update job'));
            }
        } else {
            // Add new job
            $data['date_posted'] = current_time('mysql');
            $format[] = '%s';

            $inserted = $wpdb->insert($jobs_table, $data, $format);

            if ($inserted) {
                wp_send_json_success(array(
                    'message' => 'Job added successfully',
                    'job_id' => $wpdb->insert_id
                ));
            } else {
                wp_send_json_error(array('message' => 'Failed to add job'));
            }
        }
    }

    /**
     * AJAX handler for deleting jobs.
     *
     * @since    1.0.0
     */
    public function ajax_delete_job()
    {
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

        global $wpdb;
        $jobs_table = $wpdb->prefix . 'jms_jobs';

        // Check if there are any candidates for this job
        $candidates_table = $wpdb->prefix . 'jms_candidates';
        $has_candidates = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $candidates_table WHERE job_id = %d",
            $job_id
        ));

        if ($has_candidates > 0) {
            wp_send_json_error(array('message' => 'Cannot delete job with existing applications'));
            return;
        }

        $deleted = $wpdb->delete(
            $jobs_table,
            array('id' => $job_id),
            array('%d')
        );

        if ($deleted) {
            wp_send_json_success(array('message' => 'Job deleted successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to delete job'));
        }
    }

    /**
     * AJAX handler for getting a single job.
     *
     * @since    1.0.0
     */
    public function ajax_get_job()
    {
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

        global $wpdb;
        $jobs_table = $wpdb->prefix . 'jms_jobs';

        $job = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $jobs_table WHERE id = %d",
            $job_id
        ));

        if ($job) {
            wp_send_json_success(array('job' => $job));
        } else {
            wp_send_json_error(array('message' => 'Job not found'));
        }
    }
}
