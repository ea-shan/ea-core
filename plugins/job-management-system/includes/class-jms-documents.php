<?php
/**
 * Document management functionality for the Job Management System.
 *
 * @link       https://expressanalytics.net
 * @since      1.0.0
 *
 * @package    Job_Management_System
 * @subpackage Job_Management_System/includes
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Document management class.
 */
class JMS_Documents {

    /**
     * Initialize the class.
     */
    public function __construct() {
        // Nothing to initialize
    }

    /**
     * Get document types.
     *
     * @return array The document types.
     */
    public function get_document_types() {
        $document_types = array(
            'id_proof' => 'ID Proof',
            'aadhar_card' => 'Aadhar Card',
            'pan_card' => 'PAN Card',
            'passport' => 'Passport',
            'address_proof' => 'Address Proof',
            'salary_slip' => 'Salary Slip',
            'offer_letter' => 'Offer Letter',
            'experience_certificate' => 'Experience Certificate',
            'degree_certificate' => 'Degree Certificate',
            'employee_form' => 'Employee Form',
            'current_offer' => 'Current Offer Letter',
            'other' => 'Other Document'
        );
        
        return apply_filters('jms_document_types', $document_types);
    }

    /**
     * Upload document.
     *
     * @param array $document_data The document data.
     * @param array $file The uploaded file.
     * @return int|false The document ID or false on failure.
     */
    public function upload_document($document_data, $file) {
        // Validate document data
        if (!isset($document_data['candidate_id']) || !isset($document_data['document_type'])) {
            return false;
        }
        
        // Validate file
        if (!isset($file['name']) || !isset($file['tmp_name']) || empty($file['name']) || empty($file['tmp_name'])) {
            return false;
        }
        
        // Get upload directory
        $upload_dir = wp_upload_dir();
        $jms_upload_dir = $upload_dir['basedir'] . '/jms-uploads/documents';
        
        // Create directory if it doesn't exist
        if (!file_exists($jms_upload_dir)) {
            wp_mkdir_p($jms_upload_dir);
            
            // Create index.php file to prevent directory listing
            $index_file = fopen($jms_upload_dir . '/index.php', 'w');
            fwrite($index_file, '<?php // Silence is golden');
            fclose($index_file);
        }
        
        // Get file extension
        $file_name = sanitize_file_name($file['name']);
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        
        // Check file extension
        $allowed_types = array('pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png');
        if (!in_array(strtolower($file_ext), $allowed_types)) {
            return false;
        }
        
        // Generate unique filename
        $unique_filename = uniqid() . '-' . $file_name;
        $upload_path = $jms_upload_dir . '/' . $unique_filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
            return false;
        }
        
        // Get document path
        $document_path = $upload_dir['baseurl'] . '/jms-uploads/documents/' . $unique_filename;
        
        // Prepare document data
        $document_data = array_merge($document_data, array(
            'document_path' => $document_path,
            'status' => 'submitted',
            'upload_date' => current_time('mysql')
        ));
        
        // Insert document into database
        global $wpdb;
        $documents_table = $wpdb->prefix . 'jms_documents';
        
        $inserted = $wpdb->insert(
            $documents_table,
            array(
                'candidate_id' => $document_data['candidate_id'],
                'document_type' => $document_data['document_type'],
                'document_path' => $document_data['document_path'],
                'status' => $document_data['status'],
                'upload_date' => $document_data['upload_date'],
                'notes' => isset($document_data['notes']) ? $document_data['notes'] : ''
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($inserted) {
            return $wpdb->insert_id;
        }
        
        return false;
    }

    /**
     * Get documents.
     *
     * @param array $args The query arguments.
     * @return array The documents.
     */
    public function get_documents($args = array()) {
        global $wpdb;
        $documents_table = $wpdb->prefix . 'jms_documents';
        
        $defaults = array(
            'candidate_id' => 0,
            'document_type' => '',
            'status' => '',
            'limit' => 0,
            'offset' => 0,
            'orderby' => 'upload_date',
            'order' => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Build query
        $query = "SELECT * FROM $documents_table WHERE 1=1";
        $query_args = array();
        
        if ($args['candidate_id'] > 0) {
            $query .= " AND candidate_id = %d";
            $query_args[] = $args['candidate_id'];
        }
        
        if (!empty($args['document_type'])) {
            $query .= " AND document_type = %s";
            $query_args[] = $args['document_type'];
        }
        
        if (!empty($args['status'])) {
            $query .= " AND status = %s";
            $query_args[] = $args['status'];
        }
        
        // Order
        $query .= " ORDER BY " . sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);
        
        // Limit
        if ($args['limit'] > 0) {
            $query .= " LIMIT %d";
            $query_args[] = $args['limit'];
            
            if ($args['offset'] > 0) {
                $query .= " OFFSET %d";
                $query_args[] = $args['offset'];
            }
        }
        
        // Prepare query
        if (!empty($query_args)) {
            $query = $wpdb->prepare($query, $query_args);
        }
        
        // Get documents
        $documents = $wpdb->get_results($query);
        
        return $documents;
    }

    /**
     * Get document.
     *
     * @param int $document_id The document ID.
     * @return object|false The document or false if not found.
     */
    public function get_document($document_id) {
        global $wpdb;
        $documents_table = $wpdb->prefix . 'jms_documents';
        
        $document = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $documents_table WHERE id = %d",
            $document_id
        ));
        
        return $document;
    }

    /**
     * Update document status.
     *
     * @param int $document_id The document ID.
     * @param string $status The new status.
     * @param string $notes The notes.
     * @return bool Whether the update was successful.
     */
    public function update_document_status($document_id, $status, $notes = '') {
        global $wpdb;
        $documents_table = $wpdb->prefix . 'jms_documents';
        
        $data = array(
            'status' => $status,
            'last_updated' => current_time('mysql')
        );
        
        $format = array('%s', '%s');
        
        if (!empty($notes)) {
            $data['notes'] = $notes;
            $format[] = '%s';
        }
        
        $updated = $wpdb->update(
            $documents_table,
            $data,
            array('id' => $document_id),
            $format,
            array('%d')
        );
        
        return $updated !== false;
    }

    /**
     * Delete document.
     *
     * @param int $document_id The document ID.
     * @return bool Whether the deletion was successful.
     */
    public function delete_document($document_id) {
        // Get document
        $document = $this->get_document($document_id);
        
        if (!$document) {
            return false;
        }
        
        // Delete file
        $file_path = str_replace(wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $document->document_path);
        
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        // Delete from database
        global $wpdb;
        $documents_table = $wpdb->prefix . 'jms_documents';
        
        $deleted = $wpdb->delete(
            $documents_table,
            array('id' => $document_id),
            array('%d')
        );
        
        return $deleted !== false;
    }

    /**
     * Setup document management.
     *
     * @return bool Whether the setup was successful.
     */
    public function setup() {
        // Create the documents table
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $documents_table = $wpdb->prefix . 'jms_documents';
        
        $sql = "CREATE TABLE $documents_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            candidate_id bigint(20) NOT NULL,
            document_type varchar(50) NOT NULL,
            document_path varchar(255) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'submitted',
            upload_date datetime NOT NULL,
            last_updated datetime DEFAULT NULL,
            notes text DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY candidate_id (candidate_id),
            KEY document_type (document_type),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Create upload directory
        $upload_dir = wp_upload_dir();
        $jms_upload_dir = $upload_dir['basedir'] . '/jms-uploads/documents';
        
        if (!file_exists($jms_upload_dir)) {
            wp_mkdir_p($jms_upload_dir);
            
            // Create index.php file to prevent directory listing
            $index_file = fopen($jms_upload_dir . '/index.php', 'w');
            fwrite($index_file, '<?php // Silence is golden');
            fclose($index_file);
        }
        
        return true;
    }
}
