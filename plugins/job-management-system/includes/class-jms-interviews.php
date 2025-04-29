<?php
/**
 * The class responsible for handling interview scheduling.
 *
 * @since      1.0.0
 * @package    Job_Management_System
 * @subpackage Job_Management_System/includes
 */

class JMS_Interviews {

    /**
     * Get all interviews with optional filtering.
     *
     * @since    1.0.0
     * @param    array    $args    Query arguments.
     * @return   array    Array of interview objects.
     */
    public function get_interviews($args = array()) {
        $defaults = array(
            'candidate_id' => 0,
            'job_id' => 0,
            'status' => '',
            'orderby' => 'interview_date',
            'order' => 'ASC',
            'limit' => 0,
            'offset' => 0
        );
        
        $args = wp_parse_args($args, $defaults);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'jms_interviews';
        
        $query = "SELECT * FROM $table_name WHERE 1=1";
        
        if (!empty($args['candidate_id'])) {
            $query .= $wpdb->prepare(" AND candidate_id = %d", $args['candidate_id']);
        }
        
        if (!empty($args['job_id'])) {
            $query .= $wpdb->prepare(" AND job_id = %d", $args['job_id']);
        }
        
        if (!empty($args['status'])) {
            $query .= $wpdb->prepare(" AND status = %s", $args['status']);
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
     * Get a single interview by ID.
     *
     * @since    1.0.0
     * @param    int      $interview_id    Interview ID.
     * @return   object   Interview object or null if not found.
     */
    public function get_interview($interview_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'jms_interviews';
        
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $interview_id));
    }

    /**
     * Schedule a new interview.
     *
     * @since    1.0.0
     * @param    array    $interview_data    Interview data.
     * @return   int|false                   Interview ID on success, false on failure.
     */
    public function schedule_interview($interview_data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'jms_interviews';
        
        $defaults = array(
            'candidate_id' => 0,
            'job_id' => 0,
            'interview_date' => '',
            'interview_type' => '',
            'google_meet_link' => '',
            'notes' => '',
            'status' => 'scheduled',
            'created_date' => current_time('mysql')
        );
        
        $interview_data = wp_parse_args($interview_data, $defaults);
        
        $inserted = $wpdb->insert(
            $table_name,
            array(
                'candidate_id' => $interview_data['candidate_id'],
                'job_id' => $interview_data['job_id'],
                'interview_date' => $interview_data['interview_date'],
                'interview_type' => $interview_data['interview_type'],
                'google_meet_link' => $interview_data['google_meet_link'],
                'notes' => $interview_data['notes'],
                'status' => $interview_data['status'],
                'created_date' => $interview_data['created_date']
            ),
            array('%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($inserted) {
            return $wpdb->insert_id;
        }
        
        return false;
    }

    /**
     * Update interview status.
     *
     * @since    1.0.0
     * @param    int      $interview_id    Interview ID.
     * @param    string   $status          New status.
     * @param    string   $notes           Optional notes.
     * @return   bool                      True on success, false on failure.
     */
    public function update_interview_status($interview_id, $status, $notes = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'jms_interviews';
        
        $data = array('status' => $status);
        $format = array('%s');
        
        if (!empty($notes)) {
            $data['notes'] = $notes;
            $format[] = '%s';
        }
        
        $updated = $wpdb->update(
            $table_name,
            $data,
            array('id' => $interview_id),
            $format,
            array('%d')
        );
        
        return $updated !== false;
    }

    /**
     * Delete an interview.
     *
     * @since    1.0.0
     * @param    int      $interview_id    Interview ID.
     * @return   bool                      True on success, false on failure.
     */
    public function delete_interview($interview_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'jms_interviews';
        
        return $wpdb->delete($table_name, array('id' => $interview_id), array('%d'));
    }

    /**
     * Get upcoming interviews.
     *
     * @since    1.0.0
     * @param    int      $limit    Maximum number of interviews to return.
     * @return   array    Array of upcoming interview objects.
     */
    public function get_upcoming_interviews($limit = 10) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'jms_interviews';
        
        $current_time = current_time('mysql');
        
        $query = $wpdb->prepare("
            SELECT i.*, c.name as candidate_name, c.email as candidate_email, j.title as job_title
            FROM $table_name i
            JOIN {$wpdb->prefix}jms_candidates c ON i.candidate_id = c.id
            JOIN {$wpdb->prefix}jms_jobs j ON i.job_id = j.id
            WHERE i.interview_date > %s AND i.status = 'scheduled'
            ORDER BY i.interview_date ASC
            LIMIT %d
        ", $current_time, $limit);
        
        return $wpdb->get_results($query);
    }

    /**
     * Get interviews for today.
     *
     * @since    1.0.0
     * @return   array    Array of today's interview objects.
     */
    public function get_todays_interviews() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'jms_interviews';
        
        $today_start = date('Y-m-d 00:00:00', current_time('timestamp'));
        $today_end = date('Y-m-d 23:59:59', current_time('timestamp'));
        
        $query = $wpdb->prepare("
            SELECT i.*, c.name as candidate_name, c.email as candidate_email, j.title as job_title
            FROM $table_name i
            JOIN {$wpdb->prefix}jms_candidates c ON i.candidate_id = c.id
            JOIN {$wpdb->prefix}jms_jobs j ON i.job_id = j.id
            WHERE i.interview_date BETWEEN %s AND %s
            ORDER BY i.interview_date ASC
        ", $today_start, $today_end);
        
        return $wpdb->get_results($query);
    }

    /**
     * Count interviews with optional filtering.
     *
     * @since    1.0.0
     * @param    array    $args    Query arguments.
     * @return   int      Number of interviews.
     */
    public function count_interviews($args = array()) {
        $defaults = array(
            'candidate_id' => 0,
            'job_id' => 0,
            'status' => ''
        );
        
        $args = wp_parse_args($args, $defaults);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'jms_interviews';
        
        $query = "SELECT COUNT(*) FROM $table_name WHERE 1=1";
        
        if (!empty($args['candidate_id'])) {
            $query .= $wpdb->prepare(" AND candidate_id = %d", $args['candidate_id']);
        }
        
        if (!empty($args['job_id'])) {
            $query .= $wpdb->prepare(" AND job_id = %d", $args['job_id']);
        }
        
        if (!empty($args['status'])) {
            $query .= $wpdb->prepare(" AND status = %s", $args['status']);
        }
        
        return $wpdb->get_var($query);
    }
}
