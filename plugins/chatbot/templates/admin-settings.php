<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap">
  <h1><?php _e('EA Analytics Chatbot Settings', 'analytics-chatbot'); ?></h1>

  <form method="post" action="options.php">
    <?php
    settings_fields('analytics_chatbot_options');
    do_settings_sections('analytics_chatbot_options');
    ?>

    <table class="form-table">
      <tr>
        <th scope="row">
          <label for="analytics_chatbot_position">
            <?php _e('Button Position', 'analytics-chatbot'); ?>
          </label>
        </th>
        <td>
          <select name="analytics_chatbot_settings[position]" id="analytics_chatbot_position">
            <?php
            $settings = get_option('analytics_chatbot_settings', array());
            $current_position = isset($settings['position']) ? $settings['position'] : 'right';
            ?>
            <option value="right" <?php selected($current_position, 'right'); ?>>
              <?php _e('Bottom Right', 'analytics-chatbot'); ?>
            </option>
            <option value="left" <?php selected($current_position, 'left'); ?>>
              <?php _e('Bottom Left', 'analytics-chatbot'); ?>
            </option>
          </select>
        </td>
      </tr>

      <tr>
        <th scope="row">
          <label for="analytics_chatbot_color">
            <?php _e('Theme Color', 'analytics-chatbot'); ?>
          </label>
        </th>
        <td>
          <input type="color"
            name="analytics_chatbot_settings[theme_color]"
            id="analytics_chatbot_color"
            value="<?php echo isset($settings['theme_color']) ? esc_attr($settings['theme_color']) : '#D0102F'; ?>">
          <p class="description"><?php _e('Choose the main color for the chatbot interface.', 'analytics-chatbot'); ?></p>
        </td>
      </tr>

      <tr>
        <th scope="row">
          <label for="analytics_chatbot_appearance">
            <?php _e('Color Scheme', 'analytics-chatbot'); ?>
          </label>
        </th>
        <td>
          <select name="analytics_chatbot_settings[appearance]" id="analytics_chatbot_appearance">
            <?php
            $current_appearance = isset($settings['appearance']) ? $settings['appearance'] : 'light';
            ?>
            <option value="light" <?php selected($current_appearance, 'light'); ?>>
              <?php _e('Light', 'analytics-chatbot'); ?>
            </option>
            <option value="dark" <?php selected($current_appearance, 'dark'); ?>>
              <?php _e('Dark', 'analytics-chatbot'); ?>
            </option>
          </select>
        </td>
      </tr>
    </table>

    <?php submit_button(); ?>
  </form>
</div>

<style>
  .form-table input[type="color"] {
    width: 100px;
    height: 30px;
    padding: 0;
    cursor: pointer;
  }
</style>
