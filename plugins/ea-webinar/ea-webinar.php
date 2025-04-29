<?php

/**
 * Plugin Name: EA Webinar
 * Plugin URI: https://expressanalytics.net
 * Description: A comprehensive webinar management system with registration, Google Meet integration, and promotional features.
 * Version: 1.0.0
 * Author: Express Analytics
 * Author URI: https://expressanalytics.net
 * Text Domain: ea-webinar
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
  die;
}

// Define plugin constants
define('EAW_VERSION', '1.0.0');
define('EAW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EAW_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EAW_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_ea_webinar()
{
  require_once EAW_PLUGIN_DIR . 'includes/class-eaw-activator.php';
  EAW_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_ea_webinar()
{
  require_once EAW_PLUGIN_DIR . 'includes/class-eaw-deactivator.php';
  EAW_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_ea_webinar');
register_deactivation_hook(__FILE__, 'deactivate_ea_webinar');

/**
 * Load required files
 */
require_once EAW_PLUGIN_DIR . 'includes/class-ea-webinar.php';
require_once EAW_PLUGIN_DIR . 'includes/class-eaw-google-integration.php';
require_once EAW_PLUGIN_DIR . 'includes/class-ea-webinar-public.php';

/**
 * Begins execution of the plugin.
 */
function run_ea_webinar()
{
  $plugin = new EA_Webinar();
  $plugin->run();
}

add_action('plugins_loaded', 'run_ea_webinar');
