jQuery(document).ready(function ($) {
  // Initialize color picker
  $('.color-picker').wpColorPicker({
    change: function (event, ui) {
      $(event.target).closest('td').find('.color-preview').css('background-color', ui.color.toString());
    }
  });

  // Handle range input changes
  $('.button-size-slider, .chat-width-slider').on('input', function () {
    $(this).next('span').text($(this).val() + 'px');
  });

  // Only initialize analytics if we're on the analytics tab
  if ($('.analytics-dashboard').length) {
    console.log('Initializing analytics dashboard...');
    initializeAnalytics();
  }

  function initializeAnalytics() {
    // Show loading state
    $('.analytics-card div').text('Loading...');

    console.log('Fetching analytics data...');
    console.log('AJAX URL:', analyticsChatbotAdmin.ajax_url);

    // Fetch analytics data
    $.ajax({
      url: analyticsChatbotAdmin.ajax_url,
      type: 'POST',
      data: {
        action: 'get_chatbot_analytics',
        nonce: analyticsChatbotAdmin.nonce
      },
      dataType: 'json',
      success: function (response) {
        console.log('Analytics response:', response);
        if (response.success && response.data) {
          updateDashboard(response.data);
        } else {
          console.error('Failed to fetch analytics:', response);
          showError(response.data || 'Invalid response format');
        }
      },
      error: function (xhr, status, error) {
        console.error('Analytics request failed:', {
          status: status,
          error: error,
          response: xhr.responseText
        });
        showError('Failed to fetch analytics data');
      }
    });
  }

  function showError(message) {
    $('.analytics-card div').text('Error loading data');
    $('.chart-container').each(function () {
      $(this).html('<p class="error-message" style="color: #D0102F; text-align: center;">' +
        message + '<br><button class="button retry-analytics" style="margin-top: 10px;">Retry</button></p>');
    });

    // Add retry button handler
    $('.retry-analytics').on('click', function (e) {
      e.preventDefault();
      initializeAnalytics();
    });
  }

  function updateDashboard(data) {
    // Update statistics cards
    $('#total-conversations').text(data.total_conversations.toLocaleString());
    $('#total-messages').text(data.total_messages.toLocaleString());
    $('#avg-response-time').text(data.avg_response_time + ' sec');
    $('#success-rate').text(data.success_rate + '%');

    // Initialize conversations chart
    new Chart(document.getElementById('conversations-chart'), {
      type: 'line',
      data: {
        labels: data.conversations_chart.labels,
        datasets: [{
          label: 'Conversations',
          data: data.conversations_chart.data,
          borderColor: '#D0102F',
          backgroundColor: 'rgba(208, 16, 47, 0.1)',
          tension: 0.4,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0
            }
          }
        }
      }
    });

    // Initialize messages chart
    new Chart(document.getElementById('messages-chart'), {
      type: 'bar',
      data: {
        labels: data.messages_chart.labels,
        datasets: [{
          label: 'User Messages',
          data: data.messages_chart.user_messages,
          backgroundColor: '#D0102F'
        }, {
          label: 'Bot Responses',
          data: data.messages_chart.bot_messages,
          backgroundColor: '#666'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom'
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0
            }
          }
        }
      }
    });

    // Initialize response times chart
    new Chart(document.getElementById('response-times-chart'), {
      type: 'line',
      data: {
        labels: data.response_times.labels,
        datasets: [{
          label: 'Average Response Time (seconds)',
          data: data.response_times.data,
          borderColor: '#2271b1',
          backgroundColor: 'rgba(34, 113, 177, 0.1)',
          tension: 0.4,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        }
      }
    });

    // Initialize success rates chart
    new Chart(document.getElementById('success-rates-chart'), {
      type: 'line',
      data: {
        labels: data.success_rates.labels,
        datasets: [{
          label: 'Success Rate (%)',
          data: data.success_rates.data,
          borderColor: '#00a32a',
          backgroundColor: 'rgba(0, 163, 42, 0.1)',
          tension: 0.4,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            ticks: {
              callback: function (value) {
                return value + '%';
              }
            }
          }
        }
      }
    });
  }

  // Preview changes in real-time
  function updateChatbotPreview() {
    const position = $('#analytics_chatbot_position').val();
    const color = $('.color-picker').val();
    const buttonSize = $('.button-size-slider').val();
    const chatWidth = $('.chat-width-slider').val();

    // Update CSS variables
    document.documentElement.style.setProperty('--theme-color', color);
    document.documentElement.style.setProperty('--button-size', buttonSize + 'px');
    document.documentElement.style.setProperty('--chat-width', chatWidth + 'px');

    // Update position class
    $('.analytics-chatbot-container')
      .removeClass('analytics-chatbot-position-left analytics-chatbot-position-right')
      .addClass('analytics-chatbot-position-' + position);
  }

  // Listen for changes in settings
  $('.color-picker, .button-size-slider, .chat-width-slider, #analytics_chatbot_position').on('change', updateChatbotPreview);
});
