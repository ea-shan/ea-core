<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Job_Management_System
 * @subpackage Job_Management_System/includes
 */

class JMS_Public
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
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, JMS_PLUGIN_URL . 'assets/css/jms-public.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_name, JMS_PLUGIN_URL . 'assets/js/jms-public.js', array('jquery'), $this->version, false);

        // Localize the script with data for AJAX
        wp_localize_script($this->plugin_name, 'jms_public_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('jms_public_nonce')
        ));
    }

    /**
     * Register custom post type for jobs.
     *
     * @since    1.0.0
     */
    public function register_job_post_type()
    {
        $labels = array(
            'name'                  => _x('Jobs', 'Post Type General Name', 'job-management-system'),
            'singular_name'         => _x('Job', 'Post Type Singular Name', 'job-management-system'),
            'menu_name'             => __('Jobs', 'job-management-system'),
            'name_admin_bar'        => __('Job', 'job-management-system'),
            'archives'              => __('Job Archives', 'job-management-system'),
            'attributes'            => __('Job Attributes', 'job-management-system'),
            'parent_item_colon'     => __('Parent Job:', 'job-management-system'),
            'all_items'             => __('All Jobs', 'job-management-system'),
            'add_new_item'          => __('Add New Job', 'job-management-system'),
            'add_new'               => __('Add New', 'job-management-system'),
            'new_item'              => __('New Job', 'job-management-system'),
            'edit_item'             => __('Edit Job', 'job-management-system'),
            'update_item'           => __('Update Job', 'job-management-system'),
            'view_item'             => __('View Job', 'job-management-system'),
            'view_items'            => __('View Jobs', 'job-management-system'),
            'search_items'          => __('Search Job', 'job-management-system'),
            'not_found'             => __('Not found', 'job-management-system'),
            'not_found_in_trash'    => __('Not found in Trash', 'job-management-system'),
            'featured_image'        => __('Featured Image', 'job-management-system'),
            'set_featured_image'    => __('Set featured image', 'job-management-system'),
            'remove_featured_image' => __('Remove featured image', 'job-management-system'),
            'use_featured_image'    => __('Use as featured image', 'job-management-system'),
            'insert_into_item'      => __('Insert into job', 'job-management-system'),
            'uploaded_to_this_item' => __('Uploaded to this job', 'job-management-system'),
            'items_list'            => __('Jobs list', 'job-management-system'),
            'items_list_navigation' => __('Jobs list navigation', 'job-management-system'),
            'filter_items_list'     => __('Filter jobs list', 'job-management-system'),
        );

        $args = array(
            'label'                 => __('Job', 'job-management-system'),
            'description'           => __('Job listings', 'job-management-system'),
            'labels'                => $labels,
            'supports'              => array('title', 'editor', 'thumbnail', 'custom-fields'),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => false, // We'll handle this in our custom admin menu
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-id',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
            'rewrite'               => array('slug' => 'jobs'),
        );

        register_post_type('jms_job', $args);
    }

    /**
     * Register shortcodes.
     *
     * @since    1.0.0
     */
    public function register_shortcodes()
    {
        add_shortcode('jms_job_list', array($this, 'job_list_shortcode'));
        add_shortcode('jms_job_details', array($this, 'job_details_shortcode'));
        add_shortcode('jms_job_application_form', array($this, 'job_application_form_shortcode'));
        add_shortcode('jms_candidate_dashboard', array($this, 'candidate_dashboard_shortcode'));
    }

    /**
     * Shortcode for displaying job listings.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string   Rendered shortcode output.
     */
    public function job_list_shortcode($atts)
    {
        $atts = shortcode_atts(array(
            'limit' => 10,
            'category' => '',
            'location' => '',
        ), $atts, 'jms_job_list');

        ob_start();
        include JMS_PLUGIN_DIR . 'templates/public/job-list.php';
        return ob_get_clean();
    }

    /**
     * Shortcode for displaying job details.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string   Rendered shortcode output.
     */
    public function job_details_shortcode($atts)
    {
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts, 'jms_job_details');

        ob_start();
        include JMS_PLUGIN_DIR . 'templates/public/job-details.php';
        return ob_get_clean();
    }

    /**
     * Shortcode for displaying job application form.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string   Rendered shortcode output.
     */
    public function job_application_form_shortcode($atts)
    {
        $atts = shortcode_atts(array(
            'job_id' => 0,
        ), $atts, 'jms_job_application_form');

        ob_start();
        include JMS_PLUGIN_DIR . 'templates/public/job-application-form.php';
        return ob_get_clean();
    }

    /**
     * Shortcode for displaying candidate dashboard.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string   Rendered shortcode output.
     */
    public function candidate_dashboard_shortcode($atts)
    {
        $atts = shortcode_atts(array(), $atts, 'jms_candidate_dashboard');

        // Only show dashboard for logged-in users
        if (!is_user_logged_in()) {
            return '<p>' . __('Please log in to view your application status.', 'job-management-system') . '</p>';
        }

        ob_start();
        include JMS_PLUGIN_DIR . 'templates/public/candidate-dashboard.php';
        return ob_get_clean();
    }

    /**
     * AJAX handler for job application submission.
     *
     * @since    1.0.0
     */
    public function ajax_submit_application()
    {
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
            wp_send_json_error(array('message' => 'Please fill in all required fields.'));
            return;
        }

        // Handle resume upload
        $resume_id = 0;
        if (!empty($_FILES['resume'])) {
            // Check file extension
            $file_name = sanitize_file_name($_FILES['resume']['name']);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_types = array('pdf', 'doc', 'docx');
            if (!in_array($file_ext, $allowed_types)) {
                wp_send_json_error(array('message' => 'Invalid file type. Please upload PDF, DOC, or DOCX files.'));
                return;
            }

            // Include required files for media handling
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            // Upload file to WordPress media library
            $upload = wp_handle_upload($_FILES['resume'], array('test_form' => false));

            if (isset($upload['error'])) {
                wp_send_json_error(array('message' => 'Failed to upload resume: ' . $upload['error']));
                return;
            }

            // Create attachment in media library
            $attachment = array(
                'post_mime_type' => $upload['type'],
                'post_title' => preg_replace('/\.[^.]+$/', '', $file_name),
                'post_content' => '',
                'post_status' => 'inherit'
            );

            $resume_id = wp_insert_attachment($attachment, $upload['file']);

            if (is_wp_error($resume_id)) {
                wp_send_json_error(array('message' => 'Failed to create resume attachment.'));
                return;
            }

            // Generate metadata for the attachment
            $attach_data = wp_generate_attachment_metadata($resume_id, $upload['file']);
            wp_update_attachment_metadata($resume_id, $attach_data);
        } else {
            wp_send_json_error(array('message' => 'Please upload your resume.'));
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
                'resume_path' => $resume_id, // Store attachment ID instead of file path
                'status' => 'applied',
                'application_date' => current_time('mysql'),
                'last_updated' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s')
        );

        if ($inserted) {
            // Send confirmation email to candidate
            $jms_emails = new JMS_Emails();
            $candidate_id = $wpdb->insert_id;
            $jms_emails->send_application_confirmation($candidate_id);

            wp_send_json_success(array('message' => 'Your application has been submitted successfully.'));
        } else {
            // Delete the uploaded attachment if candidate insertion fails
            wp_delete_attachment($resume_id, true);
            wp_send_json_error(array('message' => 'Failed to submit application. Please try again.'));
        }
    }

    /**
     * AJAX handler for document upload.
     *
     * @since    1.0.0
     */
    public function ajax_upload_document()
    {
        // Check nonce for security
        check_ajax_referer('jms_public_nonce', 'nonce');

        $candidate_id = isset($_POST['candidate_id']) ? intval($_POST['candidate_id']) : 0;
        $document_type = isset($_POST['document_type']) ? sanitize_text_field($_POST['document_type']) : '';

        // Validate required fields
        if ($candidate_id <= 0 || empty($document_type)) {
            wp_send_json_error(array('message' => 'Invalid parameters.'));
            return;
        }

        // Handle document upload
        $document_id = 0;
        if (!empty($_FILES['document'])) {
            // Check file extension
            $file_name = sanitize_file_name($_FILES['document']['name']);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            // Check file extension
            $allowed_types = array('pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png');
            if (!in_array($file_ext, $allowed_types)) {
                wp_send_json_error(array('message' => 'Invalid file type. Please upload PDF, DOC, DOCX, JPG, JPEG, or PNG files.'));
                return;
            }

            // Include required files for media handling
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            // Upload file to WordPress media library
            $upload = wp_handle_upload($_FILES['document'], array('test_form' => false));

            if (isset($upload['error'])) {
                wp_send_json_error(array('message' => 'Failed to upload document: ' . $upload['error']));
                return;
            }

            // Create attachment in media library
            $attachment = array(
                'post_mime_type' => $upload['type'],
                'post_title' => preg_replace('/\.[^.]+$/', '', $file_name),
                'post_content' => '',
                'post_status' => 'inherit'
            );

            $document_id = wp_insert_attachment($attachment, $upload['file']);

            if (is_wp_error($document_id)) {
                wp_send_json_error(array('message' => 'Failed to create document attachment.'));
                return;
            }

            // Generate metadata for the attachment
            $attach_data = wp_generate_attachment_metadata($document_id, $upload['file']);
            wp_update_attachment_metadata($document_id, $attach_data);
        } else {
            wp_send_json_error(array('message' => 'Please upload a document.'));
            return;
        }

        // Save document to database
        global $wpdb;
        $documents_table = $wpdb->prefix . 'jms_documents';

        $inserted = $wpdb->insert(
            $documents_table,
            array(
                'candidate_id' => $candidate_id,
                'document_type' => $document_type,
                'document_path' => $document_id, // Store attachment ID instead of file path
                'upload_date' => current_time('mysql'),
                'status' => 'submitted'
            ),
            array('%d', '%s', '%d', '%s', '%s')
        );

        if ($inserted) {
            wp_send_json_success(array('message' => 'Document uploaded successfully.'));
        } else {
            // Delete the uploaded attachment if document insertion fails
            wp_delete_attachment($document_id, true);
            wp_send_json_error(array('message' => 'Failed to save document. Please try again.'));
        }
    }
}
