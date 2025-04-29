<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap">
  <h1><?php _e('EA Analytics Chatbot', 'analytics-chatbot'); ?></h1>

  <div class="analytics-chatbot-welcome">
    <div class="analytics-chatbot-welcome-content">
      <h2><?php _e('Welcome to Analytics Chatbot', 'analytics-chatbot'); ?></h2>
      <p><?php _e('Get started by configuring your chatbot settings and monitoring your analytics.', 'analytics-chatbot'); ?></p>

      <div class="analytics-chatbot-quick-links">
        <a href="<?php echo admin_url('admin.php?page=analytics-chatbot-settings'); ?>" class="button button-primary">
          <?php _e('Configure Settings', 'analytics-chatbot'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=analytics-chatbot-analytics'); ?>" class="button">
          <?php _e('View Analytics', 'analytics-chatbot'); ?>
        </a>
      </div>
    </div>
  </div>

  <div class="analytics-chatbot-overview">
    <div class="analytics-chatbot-card">
      <h3><?php _e('Recent Activity', 'analytics-chatbot'); ?></h3>
      <div class="analytics-chatbot-loading">
        <?php _e('Loading recent conversations...', 'analytics-chatbot'); ?>
      </div>
      <div class="analytics-chatbot-recent-activity" style="display: none;"></div>
    </div>

    <div class="analytics-chatbot-card">
      <h3><?php _e('Quick Stats', 'analytics-chatbot'); ?></h3>
      <div class="analytics-chatbot-loading">
        <?php _e('Loading statistics...', 'analytics-chatbot'); ?>
      </div>
      <div class="analytics-chatbot-quick-stats" style="display: none;"></div>
    </div>
  </div>
</div>
