<?php if (!defined('ABSPATH')) exit; ?>

<?php
$settings = get_option('analytics_chatbot_settings', array());
$position = isset($settings['position']) ? $settings['position'] : 'right';
$theme_color = isset($settings['theme_color']) ? $settings['theme_color'] : '#D0102F';
?>

<div class="analytics-chatbot-container analytics-chatbot-position-<?php echo esc_attr($position); ?>" style="--theme-color: <?php echo esc_attr($theme_color); ?>; --theme-color-hover: <?php echo esc_attr(adjust_brightness($theme_color, -10)); ?>">
  <div id="analytics-chatbot-widget" class="analytics-chatbot-widget">
    <div class="analytics-chatbot-header">
      <h3><?php _e('Chat Support', 'analytics-chatbot'); ?></h3>
      <div class="analytics-chatbot-header-actions">
        <button type="button" class="analytics-chatbot-history" aria-label="<?php esc_attr_e('View chat history', 'analytics-chatbot'); ?>">
          <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
            <path d="M13.5,8H12v5l4.28,2.54.72-1.21-3.5-2.08V8M13,3a9,9 0 0,0-9,9H1l3.96,4.03L9,12H6a7,7 0 0,1 7-7a7,7 0 0,1 7,7a7,7 0 0,1-7,7c-1.93,0-3.68-0.79-4.94-2.06l-1.42,1.42A8.896,8.896 0 0,0 13,21a9,9 0 0,0 9-9a9,9 0 0,0-9-9" />
          </svg>
        </button>
        <button type="button" class="analytics-chatbot-toggle" aria-label="<?php esc_attr_e('Close chat', 'analytics-chatbot'); ?>">&times;</button>
      </div>
    </div>

    <div class="analytics-chatbot-messages">
      <div class="analytics-chatbot-loading" style="display: none;">
        <div class="loading-spinner"></div>
        <?php _e('Processing...', 'analytics-chatbot'); ?>
      </div>
    </div>

    <form class="analytics-chatbot-form" autocomplete="off">
      <div class="analytics-chatbot-input-wrapper">
        <input type="text"
          class="analytics-chatbot-input"
          placeholder="<?php esc_attr_e('Type your message...', 'analytics-chatbot'); ?>"
          aria-label="<?php esc_attr_e('Message', 'analytics-chatbot'); ?>"
          required>
        <button type="submit" class="analytics-chatbot-submit">
          <?php _e('Send', 'analytics-chatbot'); ?>
        </button>
      </div>
    </form>
  </div>

  <button type="button" class="analytics-chatbot-trigger" aria-label="<?php esc_attr_e('Open chat', 'analytics-chatbot'); ?>">
    <span>AI</span>
    <span>Chatbot</span>
  </button>
</div>

<?php
function adjust_brightness($hex, $steps)
{
  // Strip # if present
  $hex = ltrim($hex, '#');

  // Convert to RGB
  $r = hexdec(substr($hex, 0, 2));
  $g = hexdec(substr($hex, 2, 2));
  $b = hexdec(substr($hex, 4, 2));

  // Adjust brightness
  $r = max(0, min(255, $r + $steps));
  $g = max(0, min(255, $g + $steps));
  $b = max(0, min(255, $b + $steps));

  // Convert back to hex
  return sprintf("#%02x%02x%02x", $r, $g, $b);
}
?>
