<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://expressanalytics.com
 * @since      1.0.0
 *
 * @package    EA_Webinar
 * @subpackage EA_Webinar/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    EA_Webinar
 * @subpackage EA_Webinar/includes
 * @author     Express Analytics <info@expressanalytics.com>
 */
class EAW_Deactivator
{

  /**
   * Deactivate the plugin.
   *
   * Clean up plugin data if necessary.
   * Note: We're not deleting tables or options here to preserve user data.
   * Use uninstall.php for complete cleanup if needed.
   *
   * @since    1.0.0
   */
  public static function deactivate()
  {
    // Clear any scheduled events
    wp_clear_scheduled_hook('eaw_send_reminder_emails');
  }
}
