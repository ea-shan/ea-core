<?php

/**
 * Fired during plugin activation
 *
 * @link       https://expressanalytics.com
 * @since      1.0.0
 *
 * @package    EA_Webinar
 * @subpackage EA_Webinar/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    EA_Webinar
 * @subpackage EA_Webinar/includes
 * @author     Express Analytics <info@expressanalytics.com>
 */
class EAW_Activator
{

  /**
   * Activate the plugin.
   *
   * Creates necessary database tables and sets up initial plugin options.
   *
   * @since    1.0.0
   */
  public static function activate()
  {
    global $wpdb;

    // Create tables if they don't exist
    $charset_collate = $wpdb->get_charset_collate();

    // Webinar registrations table
    $table_name = $wpdb->prefix . 'eaw_registrations';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            webinar_id bigint(20) NOT NULL,
            name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            company varchar(100),
            job_title varchar(100),
            questions text,
            registration_date datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Set default options
    add_option('eaw_reminder_time', '24');
    add_option('eaw_email_template', 'Dear {name},

Thank you for registering for {webinar_title}!

Here are your webinar details:
Date: {webinar_date}
Time: {webinar_time}

You will receive the meeting link closer to the webinar date.

Best regards,
{site_name} Team');
  }
}
