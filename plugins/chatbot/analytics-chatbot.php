<?php
/*
Plugin Name: EA AI Chatbot
Plugin URI: https://expressanalytics.com
Description: An AI chatbot plugin that adds an interactive chat interface to your WordPress site
Version: 1.0.0
Author: Express Analytics
Author URI: https://expressanalytics.com
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: ea-analytics-chatbot
Domain Path: /languages
Update URI: false
*/

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

// Define plugin constants
define('ANALYTICS_CHATBOT_VERSION', '1.0.0');
define('ANALYTICS_CHATBOT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ANALYTICS_CHATBOT_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once ANALYTICS_CHATBOT_PLUGIN_DIR . 'includes/class-analytics-chatbot.php';
require_once ANALYTICS_CHATBOT_PLUGIN_DIR . 'includes/class-analytics-chatbot-api.php';
require_once ANALYTICS_CHATBOT_PLUGIN_DIR . 'includes/class-analytics-chatbot-settings.php';

// Initialize the plugin
function analytics_chatbot_init()
{
  $plugin = new Analytics_Chatbot();
  $settings = new Analytics_Chatbot_Settings();
  $plugin->init();
}
add_action('plugins_loaded', 'analytics_chatbot_init');

// Activation hook
register_activation_hook(__FILE__, 'analytics_chatbot_activate');
function analytics_chatbot_activate()
{
  global $wpdb;
  $charset_collate = $wpdb->get_charset_collate();
  $table_name = $wpdb->prefix . 'chatbot_messages';

  $sql = "CREATE TABLE IF NOT EXISTS $table_name (
      id bigint(20) NOT NULL AUTO_INCREMENT,
      session_id varchar(255) NOT NULL,
      content text NOT NULL,
      role varchar(50) NOT NULL,
      created_at datetime NOT NULL,
      PRIMARY KEY  (id),
      KEY session_id (session_id)
  ) $charset_collate;";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'analytics_chatbot_deactivate');
function analytics_chatbot_deactivate()
{
  // Cleanup tasks if needed
}

// Add settings link on plugin page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'analytics_chatbot_settings_link');
function analytics_chatbot_settings_link($links)
{
  $settings_link = '<a href="admin.php?page=analytics-chatbot-settings">' . __('Settings', 'analytics-chatbot') . '</a>';
  array_unshift($links, $settings_link);
  return $links;
}

// Add this inside your Analytics_Chatbot class or in the appropriate location
function enqueue_scripts()
{
  wp_enqueue_style(
    'analytics-chatbot',
    ANALYTICS_CHATBOT_PLUGIN_URL . 'assets/css/chat-widget.css',
    [],
    ANALYTICS_CHATBOT_VERSION
  );

  wp_enqueue_script(
    'analytics-chatbot',
    ANALYTICS_CHATBOT_PLUGIN_URL . 'assets/js/chat-widget.js',
    ['jquery'],
    ANALYTICS_CHATBOT_VERSION,
    true
  );

  wp_localize_script('analytics-chatbot', 'analyticsChatbotSettings', array(
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('chatbot_nonce')
  ));
}

function handle_chat_ajax()
{
  // Verify nonce and other security checks
  check_ajax_referer('chatbot_nonce', 'nonce');

  $message = sanitize_text_field($_POST['message']);
  $session_id = isset($_POST['session_id']) ? sanitize_text_field($_POST['session_id']) : uniqid();

  $api_url = 'https://ea-chatbot-api-production.up.railway.app/chat';

  $args = array(
    'method' => 'POST',
    'headers' => array(
      'Content-Type' => 'application/json',
      'accept' => 'application/json'
    ),
    'body' => json_encode(array(
      'message' => $message,
      'session_id' => $session_id,
      'timezone' => 'UTC'  // You can make this dynamic if needed
    ))
  );

  $response = wp_remote_post($api_url, $args);

  if (is_wp_error($response)) {
    wp_send_json_error(array(
      'message' => 'API request failed: ' . $response->get_error_message()
    ));
  }

  $body = wp_remote_retrieve_body($response);
  $data = json_decode($body, true);

  wp_send_json_success($data);
}

// Add AJAX handlers
add_action('wp_ajax_get_chatbot_analytics', 'handle_get_chatbot_analytics');

function handle_get_chatbot_analytics()
{
  check_ajax_referer('analytics_chatbot_admin', 'nonce');

  if (!current_user_can('manage_options')) {
    wp_send_json_error('Unauthorized');
  }

  // API endpoints
  $metrics_url = 'https://ea-chatbot-api-production.up.railway.app/metrics';
  $analytics_url = 'https://ea-chatbot-api-production.up.railway.app/analytics';

  error_log('Fetching metrics from: ' . $metrics_url);
  error_log('Fetching analytics from: ' . $analytics_url);

  // Fetch metrics data
  $metrics_response = wp_remote_get($metrics_url, array(
    'timeout' => 30,
    'headers' => array(
      'Accept' => 'application/json'
    )
  ));

  // Fetch analytics data
  $analytics_response = wp_remote_get($analytics_url, array(
    'timeout' => 30,
    'headers' => array(
      'Accept' => 'application/json'
    )
  ));

  // Handle metrics response
  if (is_wp_error($metrics_response)) {
    error_log('Metrics API Error: ' . $metrics_response->get_error_message());
    wp_send_json_error(array(
      'message' => 'Failed to fetch metrics data',
      'error' => $metrics_response->get_error_message()
    ));
    return;
  }

  // Handle analytics response
  if (is_wp_error($analytics_response)) {
    error_log('Analytics API Error: ' . $analytics_response->get_error_message());
    wp_send_json_error(array(
      'message' => 'Failed to fetch analytics data',
      'error' => $analytics_response->get_error_message()
    ));
    return;
  }

  $metrics_status = wp_remote_retrieve_response_code($metrics_response);
  $analytics_status = wp_remote_retrieve_response_code($analytics_response);

  // Log response codes
  error_log('Metrics API Status: ' . $metrics_status);
  error_log('Analytics API Status: ' . $analytics_status);

  $metrics_body = wp_remote_retrieve_body($metrics_response);
  $analytics_body = wp_remote_retrieve_body($analytics_response);

  $metrics_data = json_decode($metrics_body, true);
  $analytics_data = json_decode($analytics_body, true);

  if (json_last_error() !== JSON_ERROR_NONE) {
    $error_message = 'JSON decode error: ' . json_last_error_msg();
    error_log($error_message);
    wp_send_json_error(array('message' => $error_message));
    return;
  }

  // Format metrics data according to API documentation
  $formatted_metrics = array(
    'total_conversations' => $metrics_data['total_conversations'] ?? 0,
    'total_messages' => $metrics_data['total_messages'] ?? 0,
    'average_response_time' => number_format($metrics_data['average_response_time'] ?? 0, 2),
    'success_rate' => number_format(($metrics_data['success_rate'] ?? 0) * 100, 1),
    'daily_metrics' => array(
      'labels' => array(),
      'response_times' => array(),
      'success_rates' => array(),
      'message_counts' => array()
    )
  );

  // Process daily metrics if available
  if (isset($metrics_data['daily_metrics']) && is_array($metrics_data['daily_metrics'])) {
    foreach ($metrics_data['daily_metrics'] as $metric) {
      $date = date('M j', strtotime($metric['date']));
      $formatted_metrics['daily_metrics']['labels'][] = $date;
      $formatted_metrics['daily_metrics']['response_times'][] = $metric['average_response_time'] ?? 0;
      $formatted_metrics['daily_metrics']['success_rates'][] = ($metric['success_rate'] ?? 0) * 100;
      $formatted_metrics['daily_metrics']['message_counts'][] = $metric['message_count'] ?? 0;
    }
  }

  // Format analytics data according to API documentation
  $formatted_analytics = array(
    'conversation_stats' => array(
      'total' => $analytics_data['total_conversations'] ?? 0,
      'active' => $analytics_data['active_conversations'] ?? 0,
      'completed' => $analytics_data['completed_conversations'] ?? 0
    ),
    'message_stats' => array(
      'total' => $analytics_data['total_messages'] ?? 0,
      'user' => $analytics_data['user_messages'] ?? 0,
      'bot' => $analytics_data['bot_messages'] ?? 0
    ),
    'daily_stats' => array(
      'labels' => array(),
      'conversations' => array(),
      'user_messages' => array(),
      'bot_messages' => array()
    )
  );

  // Process daily analytics if available
  if (isset($analytics_data['daily_stats']) && is_array($analytics_data['daily_stats'])) {
    foreach ($analytics_data['daily_stats'] as $stat) {
      $date = date('M j', strtotime($stat['date']));
      $formatted_analytics['daily_stats']['labels'][] = $date;
      $formatted_analytics['daily_stats']['conversations'][] = $stat['conversations'] ?? 0;
      $formatted_analytics['daily_stats']['user_messages'][] = $stat['user_messages'] ?? 0;
      $formatted_analytics['daily_stats']['bot_messages'][] = $stat['bot_messages'] ?? 0;
    }
  }

  // Combine all data
  $combined_data = array(
    'metrics' => $formatted_metrics,
    'analytics' => $formatted_analytics
  );

  error_log('Sending formatted data: ' . json_encode($combined_data));
  wp_send_json_success($combined_data);
}
