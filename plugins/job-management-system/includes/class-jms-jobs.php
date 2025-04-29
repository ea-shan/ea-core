<?php
/**
 * The class responsible for handling job-related functionality.
 *
 * @since      1.0.0
 * @package    Job_Management_System
 * @subpackage Job_Management_System/includes
 */

class JMS_Jobs {

    /**
     * Get all jobs with optional filtering.
     *
     * @since    1.0.0
     * @param    array    $args    Query arguments.
     * @return   array    Array of job objects.
     */
    public function get_jobs($args = array()) {
        $defaults = array(
            'limit' => 10,
            'offset' => 0,
            'status' => 'open',
            'orderby' => 'date_posted',
            'order' => 'DESC',
            'location' => '',
            'search' => ''
        );
        
        $args = wp_parse_args($args, $defaults);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'jms_jobs';
        
        $query = "SELECT * FROM $table_name WHERE 1=1";
        
        if (!empty($args['status'])) {
            $query .= $wpdb->prepare(" AND status = %s", $args['status']);
        }
        
        if (!empty($args['location'])) {
            $query .= $wpdb->prepare(" AND location LIKE %s", '%' . $wpdb->esc_like($args['location']) . '%');
        }
        
        if (!empty($args['search'])) {
            $query .= $wpdb->prepare(
                " AND (title LIKE %s OR description LIKE %s OR requirements LIKE %s)",
                '%' . $wpdb->esc_like($args['search']) . '%',
                '%' . $wpdb->esc_like($args['search']) . '%',
                '%' . $wpdb->esc_like($args['search']) . '%'
            );
        }
        
        // Order
        $query .= " ORDER BY " . sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);
        
        // Limit
        if ($args['limit'] > 0) {
            $query .= $wpdb->prepare(" LIMIT %d, %d", $args['offset'], $args['limit']);
        }
        
        return $wpdb->get_results($query);
    }

    /**
     * Get a single job by ID.
     *
     * @since    1.0.0
     * @param    int      $job_id    Job ID.
     * @return   object   Job object or null if not found.
     */
    public function get_job($job_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'jms_jobs';
        
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $job_id));
    }

    /**
     * Create a new job.
     *
     * @since    1.0.0
     * @param    array    $job_data    Job data.
     * @return   int|false             Job ID on success, false on failure.
     */
    public function create_job($job_data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'jms_jobs';
        
        $defaults = array(
            'title' => '',
            'description' => '',
            'requirements' => '',
            'location' => '',
            'salary_range' => '',
            'status' => 'open',
            'date_posted' => current_time('mysql'),
            'date_modified' => current_time('mysql')
        );
        
        $job_data = wp_parse_args($job_data, $defaults);
        
        $inserted = $wpdb->insert(
            $table_name,
            array(
                'title' => $job_data['title'],
                'description' => $job_data['description'],
                'requirements' => $job_data['requirements'],
                'location' => $job_data['location'],
                'salary_range' => $job_data['salary_range'],
                'status' => $job_data['status'],
                'date_posted' => $job_data['date_posted'],
                'date_modified' => $job_data['date_modified']
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($inserted) {
            return $wpdb->insert_id;
        }
        
        return false;
    }

    /**
     * Update an existing job.
     *
     * @since    1.0.0
     * @param    int      $job_id      Job ID.
     * @param    array    $job_data    Job data.
     * @return   bool                  True on success, false on failure.
     */
    public function update_job($job_id, $job_data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'jms_jobs';
        
        $job_data['date_modified'] = current_time('mysql');
        
        $updated = $wpdb->update(
            $table_name,
            $job_data,
            array('id' => $job_id),
            null,
            array('%d')
        );
        
        return $updated !== false;
    }

    /**
     * Delete a job.
     *
     * @since    1.0.0
     * @param    int      $job_id    Job ID.
     * @return   bool                True on success, false on failure.
     */
    public function delete_job($job_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'jms_jobs';
        
        return $wpdb->delete($table_name, array('id' => $job_id), array('%d'));
    }

    /**
     * Count jobs with optional filtering.
     *
     * @since    1.0.0
     * @param    array    $args    Query arguments.
     * @return   int      Number of jobs.
     */
    public function count_jobs($args = array()) {
        $defaults = array(
            'status' => '',
            'location' => '',
            'search' => ''
        );
        
        $args = wp_parse_args($args, $defaults);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'jms_jobs';
        
        $query = "SELECT COUNT(*) FROM $table_name WHERE 1=1";
        
        if (!empty($args['status'])) {
            $query .= $wpdb->prepare(" AND status = %s", $args['status']);
        }
        
        if (!empty($args['location'])) {
            $query .= $wpdb->prepare(" AND location LIKE %s", '%' . $wpdb->esc_like($args['location']) . '%');
        }
        
        if (!empty($args['search'])) {
            $query .= $wpdb->prepare(
                " AND (title LIKE %s OR description LIKE %s OR requirements LIKE %s)",
                '%' . $wpdb->esc_like($args['search']) . '%',
                '%' . $wpdb->esc_like($args['search']) . '%',
                '%' . $wpdb->esc_like($args['search']) . '%'
            );
        }
        
        return $wpdb->get_var($query);
    }

    /**
     * Get job statistics.
     *
     * @since    1.0.0
     * @return   array    Job statistics.
     */
    public function get_job_statistics() {
        global $wpdb;
        $jobs_table = $wpdb->prefix . 'jms_jobs';
        $candidates_table = $wpdb->prefix . 'jms_candidates';
        
        $stats = array(
            'total_jobs' => 0,
            'open_jobs' => 0,
            'closed_jobs' => 0,
            'total_applications' => 0,
            'applications_by_job' => array()
        );
        
        // Get job counts
        $stats['total_jobs'] = $wpdb->get_var("SELECT COUNT(*) FROM $jobs_table");
        $stats['open_jobs'] = $wpdb->get_var("SELECT COUNT(*) FROM $jobs_table WHERE status = 'open'");
        $stats['closed_jobs'] = $wpdb->get_var("SELECT COUNT(*) FROM $jobs_table WHERE status = 'closed'");
        
        // Get application counts
        $stats['total_applications'] = $wpdb->get_var("SELECT COUNT(*) FROM $candidates_table");
        
        // Get applications by job
        $applications_by_job = $wpdb->get_results("
            SELECT j.id, j.title, COUNT(c.id) as application_count
            FROM $jobs_table j
            LEFT JOIN $candidates_table c ON j.id = c.job_id
            GROUP BY j.id
            ORDER BY application_count DESC
        ");
        
        $stats['applications_by_job'] = $applications_by_job;
        
        return $stats;
    }
}
