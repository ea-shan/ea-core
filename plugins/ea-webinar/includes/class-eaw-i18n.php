<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://expressanalytics.com
 * @since      1.0.0
 *
 * @package    EA_Webinar
 * @subpackage EA_Webinar/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    EA_Webinar
 * @subpackage EA_Webinar/includes
 * @author     Express Analytics <info@expressanalytics.com>
 */
class EAW_i18n
{

  /**
   * Load the plugin text domain for translation.
   *
   * @since    1.0.0
   */
  public function load_plugin_textdomain()
  {
    load_plugin_textdomain(
      'ea-webinar',
      false,
      dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
    );
  }
}
