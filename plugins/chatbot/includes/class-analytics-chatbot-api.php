<?php

if (!defined('ABSPATH')) {
  exit;
}

class Analytics_Chatbot_API
{
  protected $namespace = 'analytics-chatbot/v1';
  private $session_duration = 86400; // 24 hours in seconds
  private $api_base_url = 'https://ea-chatbot-api-production.up.railway.app/';
  private $api_key = ''; // Add your API key here

  public function __construct()
  {
    add_action('rest_api_init', [$this, 'register_routes']);
    add_action('wp_ajax_analytics_chatbot_message', array($this, 'handle_ajax_message'));
    add_action('wp_ajax_nopriv_analytics_chatbot_message', array($this, 'handle_ajax_message'));
    add_action('init', array($this, 'init_session'));
  }

  public function init_session()
  {
    $this->validate_session();
  }

  public function register_routes()
  {
    register_rest_route($this->namespace, '/chat', [
      'methods' => 'POST',
      'callback' => [$this, 'handle_chat'],
      'permission_callback' => [$this, 'check_permission'],
    ]);

    register_rest_route($this->namespace, '/history', [
      'methods' => 'GET',
      'callback' => [$this, 'get_chat_history'],
      'permission_callback' => [$this, 'check_permission'],
    ]);

    register_rest_route($this->namespace, '/analytics', array(
      'methods' => 'GET',
      'callback' => array($this, 'handle_analytics'),
      'permission_callback' => '__return_true',
    ));
  }

  public function check_permission()
  {
    // Allow only logged-in users or valid session
    return is_user_logged_in() || $this->validate_session();
  }

  private function validate_session()
  {
    $session_id = isset($_COOKIE['ea_chatbot_session']) ? sanitize_text_field($_COOKIE['ea_chatbot_session']) : null;
    if (!$session_id) {
      $session_id = wp_generate_uuid4();
      setcookie('ea_chatbot_session', $session_id, time() + $this->session_duration, COOKIEPATH, COOKIE_DOMAIN, true, true);
    }
    return true;
  }

  private function get_user_timezone()
  {
    // Default to WordPress timezone if client timezone not provided
    $wp_timezone = get_option('timezone_string');
    if (empty($wp_timezone)) {
      $wp_timezone = 'UTC';
    }
    return isset($_COOKIE['user_timezone']) ? sanitize_text_field($_COOKIE['user_timezone']) : $wp_timezone;
  }

  public function handle_chat(WP_REST_Request $request)
  {
    $params = json_decode($request->get_body(), true);

    if (empty($params['message'])) {
      return new WP_Error('invalid_message', 'Message is required', ['status' => 400]);
    }

    // Validate session_id
    $session_id = isset($_COOKIE['ea_chatbot_session']) ? sanitize_text_field($_COOKIE['ea_chatbot_session']) : 'guest-' . time();

    try {
      // Store the user message
      $this->store_message($params['message'], 'user', $session_id);

      // Get response from actual API
      $ai_response = $this->generate_ai_response($params['message']);

      // Store the AI response
      $this->store_message($ai_response, 'assistant', $params['session_id']);

      return rest_ensure_response([
        'success' => true,
        'data' => [
          'message' => $ai_response
        ]
      ]);
    } catch (Exception $e) {
      return new WP_Error(
        'processing_error',
        $e->getMessage(),
        ['status' => 500]
      );
    }
  }

  public function get_chat_history(WP_REST_Request $request)
  {
    try {
      $session_id = isset($_COOKIE['ea_chatbot_session']) ? sanitize_text_field($_COOKIE['ea_chatbot_session']) : null;

      $response = wp_remote_get($this->api_base_url . '/history/' . $session_id, [
        'headers' => [
          'Authorization' => 'Bearer ' . $this->api_key,
          'Accept' => 'application/json'
        ]
      ]);

      if (is_wp_error($response)) {
        throw new Exception($response->get_error_message());
      }

      $status_code = wp_remote_retrieve_response_code($response);
      if ($status_code !== 200) {
        throw new Exception('API returned status code: ' . $status_code);
      }

      $body = json_decode(wp_remote_retrieve_body($response), true);

      return rest_ensure_response([
        'success' => true,
        'data' => $body['messages'] ?? []
      ]);
    } catch (Exception $e) {
      error_log('Chatbot History Error: ' . $e->getMessage());
      return new WP_Error('history_error', $e->getMessage(), ['status' => 500]);
    }
  }

  private function store_message($content, $role, $session_id)
  {
    global $wpdb;
    $table_name = $wpdb->prefix . 'chatbot_messages';

    return $wpdb->insert(
      $table_name,
      [
        'session_id' => sanitize_text_field($session_id),
        'content' => sanitize_text_field($content),
        'role' => sanitize_text_field($role),
        'created_at' => current_time('mysql')
      ],
      ['%s', '%s', '%s', '%s']
    );
  }

  private function generate_ai_response($message)
  {
    try {
      $session_id = isset($_COOKIE['ea_chatbot_session']) ?
        sanitize_text_field($_COOKIE['ea_chatbot_session']) :
        'guest-' . time();

      $response = wp_remote_post('https://ea-chatbot-api-production.up.railway.app/chat', [
        'headers' => [
          'Content-Type' => 'application/json',
          'accept' => 'application/json'
        ],
        'body' => json_encode([
          'message' => $message,
          'session_id' => $session_id,
          'timezone' => isset($_COOKIE['user_timezone']) ? $_COOKIE['user_timezone'] : 'UTC'
        ]),
        'timeout' => 30,
        'data_format' => 'body'
      ]);

      if (is_wp_error($response)) {
        error_log('Chatbot API Error: ' . $response->get_error_message());
        throw new Exception($response->get_error_message());
      }

      $status_code = wp_remote_retrieve_response_code($response);
      $body = json_decode(wp_remote_retrieve_body($response), true);

      if ($status_code !== 200) {
        error_log('Chatbot API Error: Status ' . $status_code . ' Body: ' . print_r($body, true));
        throw new Exception('API returned status code: ' . $status_code);
      }

      if (!empty($body['message'])) {
        return $body['message'];
      }

      throw new Exception('Invalid response format from API');
    } catch (Exception $e) {
      error_log('Chatbot API Error: ' . $e->getMessage());
      throw new Exception('Failed to get AI response: ' . $e->getMessage());
    }
  }

  private function get_api_key()
  {
    $options = get_option('analytics_chatbot_settings');
    return isset($options['api_key']) ? $options['api_key'] : '';
  }

  private function get_previous_messages($limit = 5)
  {
    global $wpdb;
    $table_name = $wpdb->prefix . 'chatbot_messages';
    $session_id = isset($_COOKIE['ea_chatbot_session']) ? sanitize_text_field($_COOKIE['ea_chatbot_session']) : '';

    $messages = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT content, role FROM {$table_name}
        WHERE session_id = %s
        ORDER BY created_at DESC
        LIMIT %d",
        $session_id,
        $limit
      )
    );

    return array_map(function ($msg) {
      return [
        'content' => $msg->content,
        'role' => $msg->role
      ];
    }, array_reverse($messages));
  }

  public function handle_analytics($request)
  {
    global $wpdb;
    $table_name = $wpdb->prefix . 'chatbot_messages';

    try {
      // Get total conversations (unique session_ids)
      $total_conversations = $wpdb->get_var("
        SELECT COUNT(DISTINCT session_id)
        FROM $table_name
      ");

      // Get total messages
      $total_messages = $wpdb->get_var("
        SELECT COUNT(*)
        FROM $table_name
      ");

      // Get daily conversation counts for the last 30 days
      $daily_conversations = $wpdb->get_results("
        SELECT DATE(created_at) as date, COUNT(DISTINCT session_id) as count
        FROM $table_name
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date ASC
      ");

      // Get message distribution (user vs assistant)
      $message_distribution = $wpdb->get_results("
        SELECT role, COUNT(*) as count
        FROM $table_name
        GROUP BY role
      ");

      // Get average response time (time between user message and assistant response)
      $avg_response_time = $wpdb->get_var("
        SELECT AVG(TIMESTAMPDIFF(SECOND, t1.created_at, t2.created_at)) as avg_time
        FROM $table_name t1
        JOIN $table_name t2 ON t1.session_id = t2.session_id
        WHERE t1.role = 'user'
        AND t2.role = 'assistant'
        AND t2.created_at > t1.created_at
      ");

      // Calculate success rate (can be customized based on your criteria)
      $success_rate = 0.85; // Placeholder - implement your own success criteria

      return rest_ensure_response([
        'success' => true,
        'data' => [
          'total_conversations' => (int)$total_conversations,
          'total_messages' => (int)$total_messages,
          'avg_response_time' => round($avg_response_time, 2),
          'success_rate' => $success_rate,
          'daily_conversations' => array_map(function ($row) {
            return [
              'date' => $row->date,
              'count' => (int)$row->count
            ];
          }, $daily_conversations),
          'message_distribution' => array_map(function ($row) {
            return [
              'role' => $row->role,
              'count' => (int)$row->count
            ];
          }, $message_distribution)
        ]
      ]);
    } catch (Exception $e) {
      return new WP_Error(
        'analytics_error',
        $e->getMessage(),
        ['status' => 500]
      );
    }
  }

  public function handle_ajax_message()
  {
    check_ajax_referer('analytics_chatbot_nonce', 'nonce');

    $message = sanitize_text_field($_POST['message']);
    $session_id = isset($_COOKIE['ea_chatbot_session']) ? sanitize_text_field($_COOKIE['ea_chatbot_session']) : 'guest-' . time();

    // Make request to Express Analytics Chatbot API
    $response = wp_remote_post($this->api_base_url . '/chat', array(
      'body' => wp_json_encode(array(
        'message' => $message,
        'session_id' => $session_id,
      )),
      'headers' => array(
        'Content-Type' => 'application/json',
      ),
      'timeout' => 45,
    ));

    if (is_wp_error($response)) {
      wp_send_json_error(array(
        'message' => __('Failed to communicate with chatbot server', 'analytics-chatbot'),
      ));
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    wp_send_json_success($data);
  }
}
