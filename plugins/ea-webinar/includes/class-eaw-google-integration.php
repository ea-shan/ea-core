<?php

/**
 * The Google integration functionality of the plugin.
 *
 * @since      1.0.0
 * @package    EA_Webinar
 * @subpackage EA_Webinar/includes
 */

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;

class EAW_Google_Integration
{

  /**
   * The Google Client instance.
   *
   * @since    1.0.0
   * @access   private
   * @var      Client    $client    The Google Client instance.
   */
  private $client;

  /**
   * Initialize the class and set its properties.
   *
   * @since    1.0.0
   */
  public function __construct()
  {
    require_once EAW_PLUGIN_DIR . 'vendor/autoload.php';

    $this->client = new Client();
    $this->client->setClientId(get_option('eaw_google_client_id'));
    $this->client->setClientSecret(get_option('eaw_google_client_secret'));
    $this->client->setRedirectUri(admin_url('admin-ajax.php?action=eaw_google_oauth_callback'));
    $this->client->addScope(Calendar::CALENDAR);
    $this->client->setAccessType('offline');
    $this->client->setPrompt('consent');
  }

  /**
   * Create a Google Meet event.
   *
   * @since    1.0.0
   * @param    array    $webinar_data    The webinar data.
   * @return   array|WP_Error            The event details or error.
   */
  public function create_meet_event($webinar_data)
  {
    try {
      if (!$this->is_authenticated()) {
        return new WP_Error('not_authenticated', __('Google authentication required.', 'ea-webinar'));
      }

      $service = new Calendar($this->client);

      $event = new Event(array(
        'summary' => $webinar_data['title'],
        'description' => $webinar_data['description'],
        'start' => array(
          'dateTime' => $webinar_data['start_time'],
          'timeZone' => wp_timezone_string(),
        ),
        'end' => array(
          'dateTime' => $webinar_data['end_time'],
          'timeZone' => wp_timezone_string(),
        ),
        'conferenceData' => array(
          'createRequest' => array(
            'requestId' => wp_generate_uuid4(),
            'conferenceSolutionKey' => array(
              'type' => 'hangoutsMeet'
            )
          )
        ),
        'attendees' => $webinar_data['attendees']
      ));

      $event = $service->events->insert('primary', $event, array(
        'conferenceDataVersion' => 1,
        'sendNotifications' => true
      ));

      return array(
        'event_id' => $event->id,
        'meet_link' => $event->hangoutLink,
        'calendar_link' => $event->htmlLink
      );
    } catch (Exception $e) {
      return new WP_Error('google_api_error', $e->getMessage());
    }
  }

  /**
   * Check if the user is authenticated with Google.
   *
   * @since    1.0.0
   * @return   boolean    True if authenticated, false otherwise.
   */
  public function is_authenticated()
  {
    $access_token = get_option('eaw_google_access_token');

    if (!$access_token) {
      return false;
    }

    $this->client->setAccessToken($access_token);

    if ($this->client->isAccessTokenExpired()) {
      $refresh_token = get_option('eaw_google_refresh_token');

      if (!$refresh_token) {
        return false;
      }

      try {
        $this->client->fetchAccessTokenWithRefreshToken($refresh_token);
        update_option('eaw_google_access_token', $this->client->getAccessToken());
      } catch (Exception $e) {
        return false;
      }
    }

    return true;
  }

  /**
   * Handle the OAuth callback from Google.
   *
   * @since    1.0.0
   */
  public function handle_oauth_callback()
  {
    if (!current_user_can('manage_options')) {
      wp_die(__('Unauthorized access', 'ea-webinar'));
    }

    if (!isset($_GET['code'])) {
      wp_die(__('Invalid OAuth callback', 'ea-webinar'));
    }

    try {
      $token = $this->client->fetchAccessTokenWithAuthCode($_GET['code']);

      if (isset($token['access_token'])) {
        update_option('eaw_google_access_token', $token);

        if (isset($token['refresh_token'])) {
          update_option('eaw_google_refresh_token', $token['refresh_token']);
        }

        wp_redirect(admin_url('admin.php?page=ea-webinar-settings&google_auth=success'));
        exit;
      }
    } catch (Exception $e) {
      wp_redirect(admin_url('admin.php?page=ea-webinar-settings&google_auth=error'));
      exit;
    }
  }

  /**
   * Get the Google OAuth URL.
   *
   * @since    1.0.0
   * @return   string    The OAuth URL.
   */
  public function get_auth_url()
  {
    return $this->client->createAuthUrl();
  }
}
