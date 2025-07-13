<?php
/*
Plugin Name: VAPI AI Integration
Description: Integrate VAPI AI chat (streaming) and voice call features into your WordPress site.
Version: 1.0.0
Author: Express Analytics
*/

// Exit if accessed directly outside of WordPress
if (!defined('ABSPATH')) exit;

// =============================
// 1. Register Admin Settings
// =============================
// Add a menu item under 'Settings' in the WordPress admin
add_action('admin_menu', 'vapi_ai_add_admin_menu');
// Register plugin settings
add_action('admin_init', 'vapi_ai_settings_init');

/**
 * Adds the VAPI AI settings page to the WordPress admin menu.
 */
function vapi_ai_add_admin_menu()
{
  add_options_page(
    'VAPI AI Settings', // Page title
    'VAPI AI',          // Menu title
    'manage_options',   // Capability
    'vapi_ai',          // Menu slug
    'vapi_ai_options_page' // Callback function
  );
}

/**
 * Registers the plugin settings, section, and fields.
 */
function vapi_ai_settings_init()
{
  // Register a setting for storing API key and Assistant ID
  register_setting('vapi_ai', 'vapi_ai_settings');

  // Add a section to the settings page
  add_settings_section(
    'vapi_ai_section',
    __('VAPI AI Settings', 'vapi_ai'),
    null,
    'vapi_ai'
  );

  // Add API Key field
  add_settings_field(
    'vapi_ai_api_key',
    __('API Key', 'vapi_ai'),
    'vapi_ai_api_key_render',
    'vapi_ai',
    'vapi_ai_section'
  );

  // Add Assistant ID field
  add_settings_field(
    'vapi_ai_assistant_id',
    __('Assistant ID', 'vapi_ai'),
    'vapi_ai_assistant_id_render',
    'vapi_ai',
    'vapi_ai_section'
  );
}

/**
 * Renders the API Key input field in the settings page.
 */
function vapi_ai_api_key_render()
{
  $options = get_option('vapi_ai_settings');
?>
  <input type='text' name='vapi_ai_settings[vapi_ai_api_key]' value='<?php echo esc_attr($options['vapi_ai_api_key'] ?? ''); ?>' style='width: 400px;'>
<?php
}

/**
 * Renders the Assistant ID input field in the settings page.
 */
function vapi_ai_assistant_id_render()
{
  $options = get_option('vapi_ai_settings');
?>
  <input type='text' name='vapi_ai_settings[vapi_ai_assistant_id]' value='<?php echo esc_attr($options['vapi_ai_assistant_id'] ?? ''); ?>' style='width: 400px;'>
<?php
}

/**
 * Outputs the settings page HTML for the plugin.
 */
function vapi_ai_options_page()
{
?>
  <form action='options.php' method='post'>
    <h2>VAPI AI Settings</h2>
    <?php
    settings_fields('vapi_ai');
    do_settings_sections('vapi_ai');
    submit_button();
    ?>
  </form>
<?php
}

// =============================
// 2. Enqueue Frontend Scripts & Styles
// =============================
// Enqueue plugin CSS and JS for the chat and call widget
add_action('wp_enqueue_scripts', 'vapi_ai_enqueue_scripts');

/**
 * Enqueues the plugin's CSS and JavaScript files on the frontend.
 * Passes API key and Assistant ID to JS via wp_localize_script.
 */
function vapi_ai_enqueue_scripts()
{
  wp_enqueue_style('vapi-ai-style', plugin_dir_url(__FILE__) . 'vapi-ai.css');
  wp_enqueue_script('vapi-ai-script', plugin_dir_url(__FILE__) . 'vapi-ai.js', array('jquery'), null, true);
  $options = get_option('vapi_ai_settings');
  wp_localize_script('vapi-ai-script', 'vapiAISettings', array(
    'apiKey' => $options['vapi_ai_api_key'] ?? '',
    'assistantId' => $options['vapi_ai_assistant_id'] ?? ''
  ));
}

// =============================
// 3. Shortcode for Widget
// =============================
// Register the [vapi_ai_widget] shortcode
add_shortcode('vapi_ai_widget', 'vapi_ai_widget_shortcode');

/**
 * Outputs the HTML for the chat and voice call widget.
 * Usage: [vapi_ai_widget]
 * Place this shortcode in any post or page to display the widget.
 */
function vapi_ai_widget_shortcode()
{
  ob_start();
?>
  <div id="vapi-ai-widget">
    <div id="vapi-ai-chat-container"></div>
    <button id="vapi-ai-voice-call">Start Voice Call</button>
  </div>
<?php
  return ob_get_clean();
}
