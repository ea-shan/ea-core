<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap">
  <h1><?php _e('EA Analytics Dashboard', 'analytics-chatbot'); ?></h1>

  <div class="analytics-chatbot-dashboard">
    <div class="analytics-chatbot-loading">
      <?php _e('Loading analytics data...', 'analytics-chatbot'); ?>
    </div>

    <div class="analytics-chatbot-stats" style="display: none;">
      <div class="analytics-chatbot-stat-card">
        <h3><?php _e('Total Conversations', 'analytics-chatbot'); ?></h3>
        <div class="stat-value" id="total-conversations">0</div>
      </div>

      <div class="analytics-chatbot-stat-card">
        <h3><?php _e('Total Messages', 'analytics-chatbot'); ?></h3>
        <div class="stat-value" id="total-messages">0</div>
      </div>

      <div class="analytics-chatbot-stat-card">
        <h3><?php _e('Average Response Time', 'analytics-chatbot'); ?></h3>
        <div class="stat-value" id="avg-response-time">0s</div>
      </div>
    </div>

    <div class="analytics-chatbot-charts">
      <div class="analytics-chatbot-chart">
        <h3><?php _e('Conversations Over Time', 'analytics-chatbot'); ?></h3>
        <canvas id="conversations-chart"></canvas>
      </div>

      <div class="analytics-chatbot-chart">
        <h3><?php _e('Popular Topics', 'analytics-chatbot'); ?></h3>
        <canvas id="topics-chart"></canvas>
      </div>
    </div>
  </div>
</div>
