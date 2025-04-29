<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    EA_Webinar
 * @subpackage EA_Webinar/admin
 */

class EAW_Admin
{

  /**
   * Initialize the class and set its properties.
   *
   * @since    1.0.0
   */
  public function __construct()
  {
    // Constructor
  }

  /**
   * Register the stylesheets for the admin area.
   *
   * @since    1.0.0
   */
  public function enqueue_styles()
  {
    wp_enqueue_style('eaw-admin', EAW_PLUGIN_URL . 'assets/css/eaw-admin.css', array(), EAW_VERSION, 'all');
  }

  /**
   * Register the JavaScript for the admin area.
   *
   * @since    1.0.0
   */
  public function enqueue_scripts()
  {
    wp_enqueue_script('eaw-admin', EAW_PLUGIN_URL . 'assets/js/eaw-admin.js', array('jquery'), EAW_VERSION, false);
    wp_localize_script('eaw-admin', 'eaw_admin_vars', array(
      'ajaxurl' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('eaw-admin-nonce')
    ));
  }

  /**
   * Add menu items to the admin area.
   *
   * @since    1.0.0
   */
  public function add_plugin_admin_menu()
  {
    add_menu_page(
      __('EA Webinar', 'ea-webinar'),
      __('EA Webinar', 'ea-webinar'),
      'manage_options',
      'ea-webinar',
      array($this, 'display_plugin_admin_page'),
      'dashicons-video-alt2',
      30
    );

    add_submenu_page(
      'ea-webinar',
      __('Settings', 'ea-webinar'),
      __('Settings', 'ea-webinar'),
      'manage_options',
      'ea-webinar-settings',
      array($this, 'display_plugin_settings_page')
    );
  }

  /**
   * Register webinar post type.
   *
   * @since    1.0.0
   */
  public function register_webinar_post_type()
  {
    $labels = array(
      'name'               => __('Webinars', 'ea-webinar'),
      'singular_name'      => __('Webinar', 'ea-webinar'),
      'menu_name'          => __('Webinars', 'ea-webinar'),
      'add_new'           => __('Add New', 'ea-webinar'),
      'add_new_item'      => __('Add New Webinar', 'ea-webinar'),
      'edit_item'         => __('Edit Webinar', 'ea-webinar'),
      'new_item'          => __('New Webinar', 'ea-webinar'),
      'view_item'         => __('View Webinar', 'ea-webinar'),
      'search_items'      => __('Search Webinars', 'ea-webinar'),
      'not_found'         => __('No webinars found', 'ea-webinar'),
      'not_found_in_trash' => __('No webinars found in trash', 'ea-webinar')
    );

    $args = array(
      'labels'              => $labels,
      'public'              => true,
      'has_archive'         => true,
      'publicly_queryable'  => true,
      'show_ui'             => true,
      'show_in_menu'        => 'ea-webinar',
      'query_var'           => true,
      'rewrite'             => array('slug' => 'webinar'),
      'capability_type'     => 'post',
      'hierarchical'        => false,
      'menu_position'       => null,
      'supports'            => array('title', 'editor', 'thumbnail', 'excerpt'),
      'show_in_rest'        => true
    );

    register_post_type('eaw_webinar', $args);
  }

  /**
   * Add meta boxes for webinar details.
   *
   * @since    1.0.0
   */
  public function add_webinar_meta_boxes()
  {
    add_meta_box(
      'eaw_webinar_details',
      __('Webinar Details', 'ea-webinar'),
      array($this, 'render_webinar_details_meta_box'),
      'eaw_webinar',
      'normal',
      'high'
    );

    add_meta_box(
      'eaw_webinar_registration',
      __('Registration Details', 'ea-webinar'),
      array($this, 'render_registration_details_meta_box'),
      'eaw_webinar',
      'normal',
      'high'
    );
  }

  /**
   * Render webinar details meta box.
   *
   * @since    1.0.0
   * @param    WP_Post    $post    The post object.
   */
  public function render_webinar_details_meta_box($post)
  {
    // Add nonce for security
    wp_nonce_field('eaw_webinar_details', 'eaw_webinar_details_nonce');

    // Get saved values
    $date = get_post_meta($post->ID, '_eaw_webinar_date', true);
    $time = get_post_meta($post->ID, '_eaw_webinar_time', true);
    $duration = get_post_meta($post->ID, '_eaw_webinar_duration', true);
    $presenter = get_post_meta($post->ID, '_eaw_webinar_presenter', true);

    // Output the form fields
?>
    <div class="eaw-meta-box-row">
      <label for="eaw_webinar_date"><?php _e('Date:', 'ea-webinar'); ?></label>
      <input type="date" id="eaw_webinar_date" name="eaw_webinar_date" value="<?php echo esc_attr($date); ?>">
    </div>

    <div class="eaw-meta-box-row">
      <label for="eaw_webinar_time"><?php _e('Time:', 'ea-webinar'); ?></label>
      <input type="time" id="eaw_webinar_time" name="eaw_webinar_time" value="<?php echo esc_attr($time); ?>">
    </div>

    <div class="eaw-meta-box-row">
      <label for="eaw_webinar_duration"><?php _e('Duration (minutes):', 'ea-webinar'); ?></label>
      <input type="number" id="eaw_webinar_duration" name="eaw_webinar_duration" value="<?php echo esc_attr($duration); ?>">
    </div>

    <div class="eaw-meta-box-row">
      <label for="eaw_webinar_presenter"><?php _e('Presenter:', 'ea-webinar'); ?></label>
      <input type="text" id="eaw_webinar_presenter" name="eaw_webinar_presenter" value="<?php echo esc_attr($presenter); ?>">
    </div>
  <?php
  }

  /**
   * Render registration details meta box.
   *
   * @since    1.0.0
   * @param    WP_Post    $post    The post object.
   */
  public function render_registration_details_meta_box($post)
  {
    // Add nonce for security
    wp_nonce_field('eaw_registration_details', 'eaw_registration_details_nonce');

    // Get saved values
    $max_attendees = get_post_meta($post->ID, '_eaw_max_attendees', true);
    $registration_deadline = get_post_meta($post->ID, '_eaw_registration_deadline', true);

    // Output the form fields
  ?>
    <div class="eaw-meta-box-row">
      <label for="eaw_max_attendees"><?php _e('Maximum Attendees:', 'ea-webinar'); ?></label>
      <input type="number" id="eaw_max_attendees" name="eaw_max_attendees" value="<?php echo esc_attr($max_attendees); ?>">
    </div>

    <div class="eaw-meta-box-row">
      <label for="eaw_registration_deadline"><?php _e('Registration Deadline:', 'ea-webinar'); ?></label>
      <input type="datetime-local" id="eaw_registration_deadline" name="eaw_registration_deadline" value="<?php echo esc_attr($registration_deadline); ?>">
    </div>
<?php
  }

  /**
   * Save webinar meta box data.
   *
   * @since    1.0.0
   * @param    int    $post_id    The post ID.
   */
  public function save_webinar_meta($post_id)
  {
    // Check if our nonce is set and verify it
    if (
      !isset($_POST['eaw_webinar_details_nonce']) ||
      !wp_verify_nonce($_POST['eaw_webinar_details_nonce'], 'eaw_webinar_details')
    ) {
      return;
    }

    if (
      !isset($_POST['eaw_registration_details_nonce']) ||
      !wp_verify_nonce($_POST['eaw_registration_details_nonce'], 'eaw_registration_details')
    ) {
      return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return;
    }

    // Check the user's permissions
    if (isset($_POST['post_type']) && 'eaw_webinar' == $_POST['post_type']) {
      if (!current_user_can('edit_post', $post_id)) {
        return;
      }
    }

    // Save webinar details
    $fields = array(
      '_eaw_webinar_date',
      '_eaw_webinar_time',
      '_eaw_webinar_duration',
      '_eaw_webinar_presenter',
      '_eaw_max_attendees',
      '_eaw_registration_deadline'
    );

    foreach ($fields as $field) {
      if (isset($_POST[str_replace('_eaw_', 'eaw_', $field)])) {
        update_post_meta(
          $post_id,
          $field,
          sanitize_text_field($_POST[str_replace('_eaw_', 'eaw_', $field)])
        );
      }
    }
  }

  /**
   * Display the plugin admin page.
   *
   * @since    1.0.0
   */
  public function display_plugin_admin_page()
  {
    include_once EAW_PLUGIN_DIR . 'admin/partials/ea-webinar-admin-display.php';
  }

  /**
   * Display the plugin settings page.
   *
   * @since    1.0.0
   */
  public function display_plugin_settings_page()
  {
    include_once EAW_PLUGIN_DIR . 'admin/partials/ea-webinar-settings-display.php';
  }

  /**
   * Register plugin settings.
   *
   * @since    1.0.0
   */
  public function register_settings()
  {
    register_setting('eaw_options', 'eaw_google_client_id');
    register_setting('eaw_options', 'eaw_google_client_secret');
    register_setting('eaw_options', 'eaw_email_template');
    register_setting('eaw_options', 'eaw_reminder_time');
  }
}
