<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the registration form aspect of the plugin.
 *
 * @link       https://expressanalytics.com
 * @since      1.0.0
 *
 * @package    EA_Webinar
 * @subpackage EA_Webinar/public/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
  die;
}

$webinar_date = get_post_meta($webinar->ID, '_eaw_date', true);
$webinar_time = get_post_meta($webinar->ID, '_eaw_time', true);
$spots_available = get_post_meta($webinar->ID, '_eaw_spots', true);
?>

<div class="eaw-registration-form-wrapper">
  <h2><?php echo esc_html($webinar->post_title); ?></h2>

  <?php if ($webinar_date && $webinar_time): ?>
    <div class="eaw-webinar-details">
      <p><strong><?php esc_html_e('Date:', 'ea-webinar'); ?></strong> <?php echo esc_html($webinar_date); ?></p>
      <p><strong><?php esc_html_e('Time:', 'ea-webinar'); ?></strong> <?php echo esc_html($webinar_time); ?></p>
    </div>
  <?php endif; ?>

  <?php if ($spots_available): ?>
    <div class="eaw-spots-count">
      <p><?php printf(esc_html__('Only %s spots remaining!', 'ea-webinar'), esc_html($spots_available)); ?></p>
    </div>
  <?php endif; ?>

  <form id="eaw-registration-form" class="eaw-registration-form">
    <input type="hidden" name="webinar_id" value="<?php echo esc_attr($webinar->ID); ?>">
    <input type="hidden" name="action" value="eaw_register_webinar">
    <?php wp_nonce_field('eaw-registration-nonce', 'nonce'); ?>

    <div class="eaw-form-group">
      <label for="eaw-name"><?php esc_html_e('Name *', 'ea-webinar'); ?></label>
      <input type="text" id="eaw-name" name="name" required>
    </div>

    <div class="eaw-form-group">
      <label for="eaw-email"><?php esc_html_e('Email *', 'ea-webinar'); ?></label>
      <input type="email" id="eaw-email" name="email" required>
    </div>

    <div class="eaw-form-group">
      <label for="eaw-company"><?php esc_html_e('Company', 'ea-webinar'); ?></label>
      <input type="text" id="eaw-company" name="company">
    </div>

    <div class="eaw-form-group">
      <label for="eaw-job-title"><?php esc_html_e('Job Title', 'ea-webinar'); ?></label>
      <input type="text" id="eaw-job-title" name="job_title">
    </div>

    <div class="eaw-form-group">
      <label for="eaw-questions"><?php esc_html_e('Questions or Comments', 'ea-webinar'); ?></label>
      <textarea id="eaw-questions" name="questions" rows="4"></textarea>
    </div>

    <div class="eaw-form-submit">
      <button type="submit" class="eaw-submit-button">
        <?php esc_html_e('Register Now', 'ea-webinar'); ?>
      </button>
    </div>
  </form>

  <div id="eaw-message" class="eaw-message" style="display: none;"></div>
</div>
