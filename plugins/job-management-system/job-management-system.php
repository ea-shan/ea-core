<?php

/**
 * Plugin Name: Job Management System
 * Plugin URI: https://expressanalytics.net
 * Description: A comprehensive job posting and candidate management system with resume uploads, interview scheduling, and onboarding features.
 * Version: 1.0.0
 * Author: Express Analytics
 * Author URI: https://expressanalytics.net
 * Text Domain: job-management-system
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('JMS_VERSION', '1.0.0');
define('JMS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JMS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('JMS_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_job_management_system()
{
    require_once JMS_PLUGIN_DIR . 'includes/class-jms-activator.php';
    JMS_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_job_management_system()
{
    require_once JMS_PLUGIN_DIR . 'includes/class-jms-deactivator.php';
    JMS_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_job_management_system');
register_deactivation_hook(__FILE__, 'deactivate_job_management_system');

/**
 * Load required files
 */
require_once JMS_PLUGIN_DIR . 'includes/class-job-management-system.php';
require_once JMS_PLUGIN_DIR . 'includes/class-jms-google-integration.php';
require_once JMS_PLUGIN_DIR . 'includes/class-jms-ajax-handler.php';
require_once JMS_PLUGIN_DIR . 'admin/class-jms-google-settings.php';

/**
 * Begins execution of the plugin.
 */
function run_job_management_system()
{
    $plugin = new Job_Management_System();
    $plugin->run();
}

add_action('plugins_loaded', 'run_job_management_system');
