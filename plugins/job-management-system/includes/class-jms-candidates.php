<?php
/**
 * The class responsible for handling candidate-related functionality.
 *
 * @since      1.0.0
 * @package    Job_Management_System
 * @subpackage Job_Management_System/includes
 */

class JMS_Candidates {

    /**
     * Get all candidates with optional filtering.
     *
     * @since    1.0.0
     * @param    array    $args    Query arguments.
     * @return   array    Array of candidate objects.
     */
    public function get_candidates($args = array()) {
        $defaults = array(
            'job_id' => 0,
            'status' => '',
            'orderby' => 'application_date',
            'order' => 'DESC',
            'limit' => 0,
            'offset' => 0,
            'search' => ''
        );
        
        $args = wp_parse_args($args, $defaults);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'jms_candidates';
        
        $query = "SELECT * FROM $table_name WHERE 1=1";
        
        if (!empty($args['job_id'])) {
            $query .= $wpdb->prepare(" AND job_id = %d", $args['job_id']);
        }
        
        if (!empty($args['status'])) {
            $query .= $wpdb->prepare(" AND status = %s", $args['status']);
        }
        
        if (!empty($args['search'])) {
            $query .= $wpdb->prepare(
                " AND (name LIKE %s OR email LIKE %s OR phone LIKE %s OR experience LIKE %s OR education LIKE %s OR skills LIKE %s)",
                '%' . $wpdb->esc_like($args['search']) . '%',
                '%' . $wpdb->esc_like($args['search']) . '%',
                '%' . $wpdb->esc_like($args['search']) . '%',
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
     * Get a single candidate by ID.
     *
     * @since    1.0.0
     * @param    int      $candidate_id    Candidate ID.
     * @return   object   Candidate object or null if not found.
     */
    public function get_candidate($candidate_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'jms_candidates';
        
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $candidate_id));
    }

    /**
     * Get candidates by email.
     *
     * @since    1.0.0
     * @param    string   $email    Candidate email.
     * @return   array    Array of candidate objects.
     */
    public function get_candidates_by_email($email) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'jms_candidates';
        
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE email = %s ORDER BY application_date DESC", $email));
    }

    /**
     * Update candidate status.
     *
     * @since    1.0.0
     * @param    int      $candidate_id    Candidate ID.
     * @param    string   $status          New status.
     * @return   bool                      True on success, false on failure.
     */
    public function update_candidate_status($candidate_id, $status) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'jms_candidates';
        
        $updated = $wpdb->update(
            $table_name,
            array(
                'status' => $status,
                'last_updated' => current_time('mysql')
            ),
            array('id' => $candidate_id),
            array('%s', '%s'),
            array('%d')
        );
        
        return $updated !== false;
    }

    /**
     * Count candidates with optional filtering.
     *
     * @since    1.0.0
     * @param    array    $args    Query arguments.
     * @return   int      Number of candidates.
     */
    public function count_candidates($args = array()) {
        $defaults = array(
            'job_id' => 0,
            'status' => '',
            'search' => ''
        );
        
        $args = wp_parse_args($args, $defaults);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'jms_candidates';
        
        $query = "SELECT COUNT(*) FROM $table_name WHERE 1=1";
        
        if (!empty($args['job_id'])) {
            $query .= $wpdb->prepare(" AND job_id = %d", $args['job_id']);
        }
        
        if (!empty($args['status'])) {
            $query .= $wpdb->prepare(" AND status = %s", $args['status']);
        }
        
        if (!empty($args['search'])) {
            $query .= $wpdb->prepare(
                " AND (name LIKE %s OR email LIKE %s OR phone LIKE %s OR experience LIKE %s OR education LIKE %s OR skills LIKE %s)",
                '%' . $wpdb->esc_like($args['search']) . '%',
                '%' . $wpdb->esc_like($args['search']) . '%',
                '%' . $wpdb->esc_like($args['search']) . '%',
                '%' . $wpdb->esc_like($args['search']) . '%',
                '%' . $wpdb->esc_like($args['search']) . '%',
                '%' . $wpdb->esc_like($args['search']) . '%'
            );
        }
        
        return $wpdb->get_var($query);
    }

    /**
     * Get candidate statistics.
     *
     * @since    1.0.0
     * @return   array    Candidate statistics.
     */
    public function get_candidate_statistics() {
        global $wpdb;
        $candidates_table = $wpdb->prefix . 'jms_candidates';
        
        $stats = array(
            'total_candidates' => 0,
            'status_counts' => array(),
            'recent_applications' => array()
        );
        
        // Get total candidates
        $stats['total_candidates'] = $wpdb->get_var("SELECT COUNT(*) FROM $candidates_table");
        
        // Get status counts
        $status_counts = $wpdb->get_results("
            SELECT status, COUNT(*) as count
            FROM $candidates_table
            GROUP BY status
            ORDER BY count DESC
        ");
        
        foreach ($status_counts as $status) {
            $stats['status_counts'][$status->status] = $status->count;
        }
        
        // Get recent applications
        $stats['recent_applications'] = $wpdb->get_results("
            SELECT *
            FROM $candidates_table
            ORDER BY application_date DESC
            LIMIT 10
        ");
        
        return $stats;
    }

    /**
     * Delete a candidate.
     *
     * @since    1.0.0
     * @param    int      $candidate_id    Candidate ID.
     * @return   bool                      True on success, false on failure.
     */
    public function delete_candidate($candidate_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'jms_candidates';
        
        return $wpdb->delete($table_name, array('id' => $candidate_id), array('%d'));
    }
}
