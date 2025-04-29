<?php
class Analytics_Chatbot_Settings
{
  private $options;
  private $active_tab;

  public function __construct()
  {
    add_action('admin_init', [$this, 'page_init']);
    add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
  }

  public function enqueue_admin_scripts($hook)
  {
    if ('settings_page_analytics-chatbot-settings' !== $hook) {
      return;
    }

    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');

    // Enqueue Chart.js
    wp_enqueue_script(
      'chartjs',
      'https://cdn.jsdelivr.net/npm/chart.js',
      array(),
      '3.7.0',
      true
    );

    // Enqueue our admin script
    wp_enqueue_script(
      'analytics-chatbot-admin',
      plugin_dir_url(dirname(__FILE__)) . 'assets/js/admin.js',
      array('jquery', 'wp-color-picker', 'chartjs'),
      ANALYTICS_CHATBOT_VERSION,
      true
    );

    wp_localize_script('analytics-chatbot-admin', 'analyticsChatbotAdmin', array(
      'ajax_url' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('analytics_chatbot_admin')
    ));
  }

  public function create_admin_page()
  {
    $this->options = get_option('analytics_chatbot_settings');
    $this->active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'appearance';
?>
    <div class="wrap">
      <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

      <h2 class="nav-tab-wrapper">
        <a href="?page=analytics-chatbot-settings&tab=appearance"
          class="nav-tab <?php echo $this->active_tab == 'appearance' ? 'nav-tab-active' : ''; ?>">
          <?php _e('Appearance', 'analytics-chatbot'); ?>
        </a>
        <a href="?page=analytics-chatbot-settings&tab=analytics"
          class="nav-tab <?php echo $this->active_tab == 'analytics' ? 'nav-tab-active' : ''; ?>">
          <?php _e('Analytics', 'analytics-chatbot'); ?>
        </a>
      </h2>

      <?php if ($this->active_tab == 'appearance'): ?>
        <form method="post" action="options.php">
          <?php
          settings_fields('analytics_chatbot_settings_group');
          do_settings_sections('analytics-chatbot-settings');
          submit_button();
          ?>
        </form>
      <?php else: ?>
        <div class="analytics-dashboard">
          <div class="analytics-cards">
            <div class="analytics-card">
              <h3><?php _e('Total Conversations', 'analytics-chatbot'); ?></h3>
              <div id="total-conversations">Loading...</div>
            </div>
            <div class="analytics-card">
              <h3><?php _e('Total Messages', 'analytics-chatbot'); ?></h3>
              <div id="total-messages">Loading...</div>
            </div>
            <div class="analytics-card">
              <h3><?php _e('Average Response Time', 'analytics-chatbot'); ?></h3>
              <div id="avg-response-time">Loading...</div>
            </div>
            <div class="analytics-card">
              <h3><?php _e('Success Rate', 'analytics-chatbot'); ?></h3>
              <div id="success-rate">Loading...</div>
            </div>
          </div>

          <div class="analytics-charts">
            <div class="chart-container">
              <h3><?php _e('Daily Conversations', 'analytics-chatbot'); ?></h3>
              <canvas id="conversations-chart"></canvas>
            </div>
            <div class="chart-container">
              <h3><?php _e('Message Distribution', 'analytics-chatbot'); ?></h3>
              <canvas id="messages-chart"></canvas>
            </div>
            <div class="chart-container">
              <h3><?php _e('Response Times', 'analytics-chatbot'); ?></h3>
              <canvas id="response-times-chart"></canvas>
            </div>
            <div class="chart-container">
              <h3><?php _e('Success Rates', 'analytics-chatbot'); ?></h3>
              <canvas id="success-rates-chart"></canvas>
            </div>
          </div>
        </div>

        <style>
          .analytics-dashboard {
            margin-top: 20px;
          }

          .analytics-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
          }

          .analytics-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
          }

          .analytics-card h3 {
            margin-top: 0;
            color: #23282d;
            font-size: 14px;
            margin-bottom: 10px;
          }

          .analytics-card div {
            font-size: 24px;
            font-weight: bold;
            color: #D0102F;
          }

          .analytics-charts {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
          }

          .chart-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
          }

          .chart-container h3 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #23282d;
            text-align: center;
          }

          @media (max-width: 768px) {
            .analytics-charts {
              grid-template-columns: 1fr;
            }
          }
        </style>
      <?php endif; ?>
    </div>
  <?php
  }

  public function page_init()
  {
    register_setting(
      'analytics_chatbot_settings_group',
      'analytics_chatbot_settings',
      [$this, 'sanitize']
    );

    add_settings_section(
      'appearance_section',
      __('Appearance Settings', 'analytics-chatbot'),
      [$this, 'appearance_section_info'],
      'analytics-chatbot-settings'
    );

    // Position
    add_settings_field(
      'position',
      __('Button Position', 'analytics-chatbot'),
      [$this, 'position_callback'],
      'analytics-chatbot-settings',
      'appearance_section'
    );

    // Theme Color
    add_settings_field(
      'theme_color',
      __('Theme Color', 'analytics-chatbot'),
      [$this, 'theme_color_callback'],
      'analytics-chatbot-settings',
      'appearance_section'
    );

    // Button Size
    add_settings_field(
      'button_size',
      __('Button Size', 'analytics-chatbot'),
      [$this, 'button_size_callback'],
      'analytics-chatbot-settings',
      'appearance_section'
    );

    // Chat Window Width
    add_settings_field(
      'chat_width',
      __('Chat Window Width', 'analytics-chatbot'),
      [$this, 'chat_width_callback'],
      'analytics-chatbot-settings',
      'appearance_section'
    );

    // Custom CSS
    add_settings_field(
      'custom_css',
      __('Custom CSS', 'analytics-chatbot'),
      [$this, 'custom_css_callback'],
      'analytics-chatbot-settings',
      'appearance_section'
    );
  }

  public function sanitize($input)
  {
    $new_input = array();

    if (isset($input['position'])) {
      $new_input['position'] = sanitize_text_field($input['position']);
    }

    if (isset($input['theme_color'])) {
      $new_input['theme_color'] = sanitize_hex_color($input['theme_color']);
    }

    if (isset($input['button_size'])) {
      $new_input['button_size'] = absint($input['button_size']);
    }

    if (isset($input['chat_width'])) {
      $new_input['chat_width'] = absint($input['chat_width']);
    }

    if (isset($input['custom_css'])) {
      $new_input['custom_css'] = wp_strip_all_tags($input['custom_css']);
    }

    return $new_input;
  }

  public function appearance_section_info()
  {
    _e('Customize the appearance of your chatbot:', 'analytics-chatbot');
  }

  public function position_callback()
  {
    $position = isset($this->options['position']) ? $this->options['position'] : 'right';
  ?>
    <select name="analytics_chatbot_settings[position]">
      <option value="right" <?php selected($position, 'right'); ?>><?php _e('Bottom Right', 'analytics-chatbot'); ?></option>
      <option value="left" <?php selected($position, 'left'); ?>><?php _e('Bottom Left', 'analytics-chatbot'); ?></option>
    </select>
  <?php
  }

  public function theme_color_callback()
  {
    $color = isset($this->options['theme_color']) ? $this->options['theme_color'] : '#D0102F';
  ?>
    <input type="color"
      name="analytics_chatbot_settings[theme_color]"
      value="<?php echo esc_attr($color); ?>"
      class="color-picker">
    <div class="color-preview" style="background-color: <?php echo esc_attr($color); ?>"></div>
  <?php
  }

  public function button_size_callback()
  {
    $size = isset($this->options['button_size']) ? absint($this->options['button_size']) : 60;
  ?>
    <input type="range"
      name="analytics_chatbot_settings[button_size]"
      min="40"
      max="80"
      value="<?php echo esc_attr($size); ?>"
      class="button-size-slider">
    <span class="button-size-value"><?php echo esc_html($size); ?>px</span>
  <?php
  }

  public function chat_width_callback()
  {
    $width = isset($this->options['chat_width']) ? absint($this->options['chat_width']) : 350;
  ?>
    <input type="range"
      name="analytics_chatbot_settings[chat_width]"
      min="300"
      max="500"
      value="<?php echo esc_attr($width); ?>"
      class="chat-width-slider">
    <span class="chat-width-value"><?php echo esc_html($width); ?>px</span>
  <?php
  }

  public function custom_css_callback()
  {
    $custom_css = isset($this->options['custom_css']) ? $this->options['custom_css'] : '';
  ?>
    <textarea name="analytics_chatbot_settings[custom_css]"
      rows="8"
      cols="50"
      class="large-text code"
      placeholder="/* Add your custom CSS here */"><?php echo esc_textarea($custom_css); ?></textarea>
    <p class="description"><?php _e('Add custom CSS to further customize the chatbot appearance.', 'analytics-chatbot'); ?></p>
<?php
  }
}
