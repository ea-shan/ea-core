<?php

/**
 * Registration form template
 *
 * @since      1.0.0
 * @package    EA_Webinar
 * @subpackage EA_Webinar/templates
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
  die;
}

$webinar_id = get_the_ID();
$webinar_date = get_post_meta($webinar_id, '_eaw_webinar_date', true);
$webinar_time = get_post_meta($webinar_id, '_eaw_webinar_time', true);
$max_attendees = get_post_meta($webinar_id, '_eaw_max_attendees', true);
$current_attendees = get_post_meta($webinar_id, '_eaw_current_attendees', true) ?: 0;
$registration_deadline = get_post_meta($webinar_id, '_eaw_registration_deadline', true);

$registration_closed = false;
if ($registration_deadline && strtotime($registration_deadline) < current_time('timestamp')) {
  $registration_closed = true;
}

$spots_filled = false;
if ($max_attendees && $current_attendees >= $max_attendees) {
  $spots_filled = true;
}
?>

<div class="eaw-registration-form-wrapper">
  <?php if ($registration_closed): ?>
    <div class="eaw-notice eaw-notice-error">
      <p><?php _e('Registration for this webinar has closed.', 'ea-webinar'); ?></p>
    </div>
  <?php elseif ($spots_filled): ?>
    <div class="eaw-notice eaw-notice-error">
      <p><?php _e('This webinar is fully booked.', 'ea-webinar'); ?></p>
    </div>
  <?php else: ?>
    <form id="eaw-registration-form" class="eaw-registration-form">
      <input type="hidden" name="webinar_id" value="<?php echo esc_attr($webinar_id); ?>">
      <?php wp_nonce_field('eaw_register_webinar', 'eaw_registration_nonce'); ?>

      <div class="eaw-form-row">
        <label for="eaw_name"><?php _e('Full Name *', 'ea-webinar'); ?></label>
        <input type="text" id="eaw_name" name="name" required>
      </div>

      <div class="eaw-form-row">
        <label for="eaw_email"><?php _e('Email Address *', 'ea-webinar'); ?></label>
        <input type="email" id="eaw_email" name="email" required>
      </div>

      <div class="eaw-form-row">
        <label for="eaw_company"><?php _e('Company', 'ea-webinar'); ?></label>
        <input type="text" id="eaw_company" name="company">
      </div>

      <div class="eaw-form-row">
        <label for="eaw_job_title"><?php _e('Job Title', 'ea-webinar'); ?></label>
        <input type="text" id="eaw_job_title" name="job_title">
      </div>

      <div class="eaw-form-row">
        <label for="eaw_questions"><?php _e('Questions or Comments', 'ea-webinar'); ?></label>
        <textarea id="eaw_questions" name="questions" rows="4"></textarea>
      </div>

      <div class="eaw-form-row eaw-form-checkbox">
        <input type="checkbox" id="eaw_consent" name="consent" required>
        <label for="eaw_consent">
          <?php _e('I agree to receive communications about this webinar.', 'ea-webinar'); ?>
        </label>
      </div>

      <div class="eaw-form-row">
        <button type="submit" class="eaw-submit-button">
          <?php _e('Register for Webinar', 'ea-webinar'); ?>
        </button>
      </div>

      <div class="eaw-form-messages"></div>
    </form>

    <div class="eaw-webinar-info">
      <p>
        <strong><?php _e('Date:', 'ea-webinar'); ?></strong>
        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($webinar_date))); ?>
      </p>
      <p>
        <strong><?php _e('Time:', 'ea-webinar'); ?></strong>
        <?php echo esc_html(date_i18n(get_option('time_format'), strtotime($webinar_time))); ?>
      </p>
      <?php if ($max_attendees): ?>
        <p>
          <strong><?php _e('Available Spots:', 'ea-webinar'); ?></strong>
          <?php echo esc_html($max_attendees - $current_attendees); ?>
        </p>
      <?php endif; ?>
      <?php if ($registration_deadline): ?>
        <p>
          <strong><?php _e('Registration Deadline:', 'ea-webinar'); ?></strong>
          <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($registration_deadline))); ?>
        </p>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>
