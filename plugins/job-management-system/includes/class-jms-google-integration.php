<?php

/**
 * Google Calendar and Meet integration for the Job Management System
 *
 * @link       https://expressanalytics.net
 * @since      1.0.0
 *
 * @package    Job_Management_System
 * @subpackage Job_Management_System/includes
 */

defined('WPINC') || exit;

/**
 * Google integration class.
 */
class JMS_Google_Integration
{
  /**
   * Google Client instance
   *
   * @var Google_Client
   */
  private $client;

  /**
   * Google Calendar Service instance
   *
   * @var Google_Service_Calendar
   */
  private $calendar_service;

  /**
   * Initialize the class and set its properties.
   */
  public function __construct()
  {
    require_once JMS_PLUGIN_DIR . 'vendor/autoload.php';

    $this->client = new Google_Client();
    $this->client->setApplicationName('Job Management System');
    $this->client->setScopes(Google_Service_Calendar::CALENDAR);

    // Get credentials from WordPress options
    $credentials = get_option('jms_google_credentials');
    if ($credentials) {
      $this->client->setAuthConfig(json_decode($credentials, true));
    }

    // Get access token from WordPress options
    $access_token = get_option('jms_google_token');
    if ($access_token) {
      $this->client->setAccessToken($access_token);
    }

    if ($this->client->isAccessTokenExpired()) {
      if ($this->client->getRefreshToken()) {
        $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
        update_option('jms_google_token', $this->client->getAccessToken());
      }
    }

    $this->calendar_service = new Google_Service_Calendar($this->client);
  }

  /**
   * Create a Google Calendar event with Meet link for an interview
   *
   * @param array $interview_data Interview details including candidate and job information
   * @return array|false Event details including Meet link and Calendar link
   */
  public function create_interview_event($interview_data)
  {
    try {
      // Format the event time
      $start_time = new DateTime($interview_data['interview_date'] . ' ' . $interview_data['interview_time']);
      $end_time = clone $start_time;
      $end_time->modify('+1 hour');

      $event = new Google_Service_Calendar_Event(array(
        'summary' => sprintf(
          'Interview: %s - %s (%s)',
          $interview_data['candidate_name'],
          $interview_data['job_title'],
          $interview_data['interview_type']
        ),
        'description' => $this->generate_event_description($interview_data),
        'start' => array(
          'dateTime' => $start_time->format('c'),
          'timeZone' => wp_timezone_string(),
        ),
        'end' => array(
          'dateTime' => $end_time->format('c'),
          'timeZone' => wp_timezone_string(),
        ),
        'attendees' => array(
          array('email' => $interview_data['candidate_email']),
          array('email' => $interview_data['interviewer_email']),
        ),
        'conferenceData' => array(
          'createRequest' => array(
            'requestId' => uniqid('jms-'),
            'conferenceSolutionKey' => array('type' => 'hangoutsMeet'),
          ),
        ),
        'reminders' => array(
          'useDefault' => false,
          'overrides' => array(
            array('method' => 'email', 'minutes' => 1440), // 24 hours
            array('method' => 'popup', 'minutes' => 30),
          ),
        ),
      ));

      $event = $this->calendar_service->events->insert(
        'primary',
        $event,
        array('conferenceDataVersion' => 1, 'sendUpdates' => 'all')
      );

      // Store the meeting details in WordPress
      $this->store_meeting_details($interview_data, $event);

      return array(
        'event_id' => $event->id,
        'meet_link' => $event->hangoutLink,
        'calendar_link' => $event->htmlLink,
      );
    } catch (Exception $e) {
      error_log('Google Calendar/Meet Error: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * Cancel an interview event
   *
   * @param string $event_id The Google Calendar event ID
   * @return boolean Success status
   */
  public function cancel_interview_event($event_id)
  {
    try {
      $this->calendar_service->events->delete('primary', $event_id, array(
        'sendUpdates' => 'all'
      ));

      // Update the meeting status in WordPress
      $this->update_meeting_status($event_id, 'cancelled');

      return true;
    } catch (Exception $e) {
      error_log('Google Calendar/Meet Error: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * Store meeting details in WordPress
   *
   * @param array $interview_data Interview data
   * @param Google_Service_Calendar_Event $event Calendar event
   */
  private function store_meeting_details($interview_data, $event)
  {
    global $wpdb;
    $table_name = $wpdb->prefix . 'jms_interviews';

    $wpdb->insert(
      $table_name,
      array(
        'candidate_id' => $interview_data['candidate_id'],
        'job_id' => $interview_data['job_id'],
        'event_id' => $event->id,
        'meet_link' => $event->hangoutLink,
        'calendar_link' => $event->htmlLink,
        'interview_time' => $interview_data['interview_date'] . ' ' . $interview_data['interview_time'],
        'interview_type' => $interview_data['interview_type'],
        'interviewer_email' => $interview_data['interviewer_email'],
        'status' => 'scheduled',
        'created_at' => current_time('mysql'),
      ),
      array(
        '%d',
        '%d',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s'
      )
    );
  }

  /**
   * Update meeting status in WordPress
   *
   * @param string $event_id Google Calendar event ID
   * @param string $status New status
   */
  private function update_meeting_status($event_id, $status)
  {
    global $wpdb;
    $table_name = $wpdb->prefix . 'jms_interviews';

    $wpdb->update(
      $table_name,
      array('status' => $status),
      array('event_id' => $event_id),
      array('%s'),
      array('%s')
    );
  }

  /**
   * Generate event description
   *
   * @param array $interview_data Interview data
   * @return string Formatted description
   */
  private function generate_event_description($interview_data)
  {
    $description = sprintf(
      "Job Interview for %s\n\n" .
        "Candidate Information:\n" .
        "Name: %s\n" .
        "Email: %s\n" .
        "Phone: %s\n\n" .
        "Interview Type: %s\n" .
        "Interviewer: %s\n\n",
      $interview_data['job_title'],
      $interview_data['candidate_name'],
      $interview_data['candidate_email'],
      $interview_data['candidate_phone'],
      $interview_data['interview_type'],
      $interview_data['interviewer_email']
    );

    if (!empty($interview_data['additional_notes'])) {
      $description .= "Additional Notes:\n" . $interview_data['additional_notes'];
    }

    return $description;
  }

  /**
   * Check if Google integration is properly configured
   *
   * @return boolean
   */
  public function is_configured()
  {
    return !empty(get_option('jms_google_credentials')) &&
      !empty(get_option('jms_google_token'));
  }
}
