<?php
/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Job_Management_System
 * @subpackage Job_Management_System/includes
 */

class JMS_Deactivator {

    /**
     * Plugin deactivation tasks.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // Flush rewrite rules on deactivation
        flush_rewrite_rules();
        
        // We don't delete tables or data on deactivation to prevent data loss
        // Tables will be removed only if the plugin is uninstalled
    }
}
