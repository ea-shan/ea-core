<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Job_Management_System
 * @subpackage Job_Management_System/includes
 */

class JMS_Activator
{

    /**
     * Create necessary database tables and options during plugin activation.
     *
     * @since    1.0.0
     */
    public static function activate()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Create jobs table
        $table_name_jobs = $wpdb->prefix . 'jms_jobs';
        $sql_jobs = "CREATE TABLE $table_name_jobs (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            description longtext NOT NULL,
            requirements longtext NOT NULL,
            location varchar(255) NOT NULL,
            salary_range varchar(100) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'open',
            date_posted datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            date_modified datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // Create candidates table
        $table_name_candidates = $wpdb->prefix . 'jms_candidates';
        $sql_candidates = "CREATE TABLE $table_name_candidates (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            job_id mediumint(9) NOT NULL,
            name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(20) NOT NULL,
            experience text NOT NULL,
            education text NOT NULL,
            skills text NOT NULL,
            resume_path varchar(255) NOT NULL,
            status varchar(50) NOT NULL DEFAULT 'applied',
            application_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY job_id (job_id)
        ) $charset_collate;";

        // Create interviews table
        $table_name_interviews = $wpdb->prefix . 'jms_interviews';
        $sql_interviews = "CREATE TABLE $table_name_interviews (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            candidate_id mediumint(9) NOT NULL,
            job_id mediumint(9) NOT NULL,
            interview_date datetime NOT NULL,
            interview_type varchar(50) NOT NULL,
            google_meet_link varchar(255) DEFAULT NULL,
            notes text DEFAULT NULL,
            status varchar(50) NOT NULL DEFAULT 'scheduled',
            created_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY candidate_id (candidate_id),
            KEY job_id (job_id)
        ) $charset_collate;";

        // Create onboarding table
        $table_name_onboarding = $wpdb->prefix . 'jms_onboarding';
        $sql_onboarding = "CREATE TABLE $table_name_onboarding (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            candidate_id mediumint(9) NOT NULL,
            job_id mediumint(9) NOT NULL,
            start_date date NOT NULL,
            department varchar(100) NOT NULL,
            progress int(3) NOT NULL DEFAULT '0',
            status varchar(50) NOT NULL DEFAULT 'pending',
            notes text DEFAULT NULL,
            created_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY candidate_id (candidate_id),
            KEY job_id (job_id)
        ) $charset_collate;";

        // Create onboarding documents table
        $table_name_documents = $wpdb->prefix . 'jms_documents';
        $sql_documents = "CREATE TABLE $table_name_documents (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            candidate_id mediumint(9) NOT NULL,
            document_type varchar(100) NOT NULL,
            document_path varchar(255) NOT NULL,
            upload_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            status varchar(50) NOT NULL DEFAULT 'submitted',
            notes text DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY candidate_id (candidate_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_jobs);
        dbDelta($sql_candidates);
        dbDelta($sql_interviews);
        dbDelta($sql_onboarding);
        dbDelta($sql_documents);

        // Add version to options
        add_option('jms_version', JMS_VERSION);

        // Create upload directories if they don't exist
        $upload_dir = wp_upload_dir();
        $jms_upload_dir = $upload_dir['basedir'] . '/jms-uploads';

        if (!file_exists($jms_upload_dir)) {
            wp_mkdir_p($jms_upload_dir);
            wp_mkdir_p($jms_upload_dir . '/resumes');
            wp_mkdir_p($jms_upload_dir . '/documents');

            // Create index.php file to prevent directory listing
            $index_file = fopen($jms_upload_dir . '/index.php', 'w');
            fwrite($index_file, '<?php // Silence is golden');
            fclose($index_file);
        }

        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
