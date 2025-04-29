<?php

if (!defined('ABSPATH')) {
  exit;
}

class Analytics_Chatbot
{
  private $api;
  private $settings;

  public function init()
  {
    // Initialize components
    $this->init_hooks();
    $this->init_api();
    $this->init_settings();
  }

  private function init_hooks()
  {
    // Enqueue scripts and styles
    add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    add_action('wp_footer', array($this, 'render_chat_widget'));
    add_action('admin_menu', array($this, 'add_admin_menu'));
  }

  private function init_api()
  {
    $this->api = new Analytics_Chatbot_API();
  }

  private function init_settings()
  {
    // Initialize settings
    register_setting('analytics_chatbot_options', 'analytics_chatbot_settings');
  }

  public function enqueue_scripts()
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

    wp_localize_script('analytics-chatbot', 'analyticsChatbotSettings', [
      'apiEndpoint' => rest_url('analytics-chatbot/v1'),
      'nonce' => wp_create_nonce('wp_rest'),
      'endpoints' => [
        'chat' => '/chat',
        'history' => '/history'
      ]
    ]);
  }

  public function enqueue_admin_assets($hook)
  {
    // Only load on analytics page
    if ('analytics-chatbot_page_analytics-chatbot-analytics' !== $hook) {
      return;
    }

    wp_enqueue_style(
      'analytics-chatbot-admin',
      ANALYTICS_CHATBOT_PLUGIN_URL . 'assets/css/admin.css',
      array(),
      ANALYTICS_CHATBOT_VERSION
    );

    // Enqueue Chart.js from CDN
    wp_enqueue_script(
      'chartjs',
      'https://cdn.jsdelivr.net/npm/chart.js',
      array(),
      '4.4.1',
      true
    );

    wp_enqueue_script(
      'analytics-chatbot-admin',
      ANALYTICS_CHATBOT_PLUGIN_URL . 'assets/js/admin.js',
      array('jquery', 'chartjs'),
      ANALYTICS_CHATBOT_VERSION,
      true
    );

    wp_localize_script(
      'analytics-chatbot-admin',
      'analyticsChatbotAdmin',
      array(
        'apiEndpoint' => rest_url('analytics-chatbot/v1'),
        'nonce' => wp_create_nonce('wp_rest'),
        'endpoints' => array(
          'analytics' => '/analytics'
        )
      )
    );
  }

  public function render_chat_widget()
  {
    include ANALYTICS_CHATBOT_PLUGIN_DIR . 'templates/chat-widget.php';
  }

  public function add_admin_menu()
  {
    add_menu_page(
      __('Analytics Chatbot', 'analytics-chatbot'),
      __('Analytics Chatbot', 'analytics-chatbot'),
      'manage_options',
      'analytics-chatbot',
      array($this, 'render_admin_page'),
      'dashicons-format-chat'
    );

    add_submenu_page(
      'analytics-chatbot',
      __('Settings', 'analytics-chatbot'),
      __('Settings', 'analytics-chatbot'),
      'manage_options',
      'analytics-chatbot-settings',
      array($this, 'render_settings_page')
    );

    add_submenu_page(
      'analytics-chatbot',
      __('Analytics', 'analytics-chatbot'),
      __('Analytics', 'analytics-chatbot'),
      'manage_options',
      'analytics-chatbot-analytics',
      array($this, 'render_analytics_page')
    );
  }

  public function render_admin_page()
  {
    include ANALYTICS_CHATBOT_PLUGIN_DIR . 'templates/admin-dashboard.php';
  }

  public function render_settings_page()
  {
    include ANALYTICS_CHATBOT_PLUGIN_DIR . 'templates/admin-settings.php';
  }

  public function render_analytics_page()
  {
    include ANALYTICS_CHATBOT_PLUGIN_DIR . 'templates/admin-analytics.php';
  }
}
