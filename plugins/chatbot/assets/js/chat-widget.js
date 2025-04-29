jQuery(document).ready(function ($) {
  const widget = $('#analytics-chatbot-widget');
  const trigger = $('.analytics-chatbot-trigger');
  const messages = $('.analytics-chatbot-messages');
  const form = $('.analytics-chatbot-form');
  const input = $('.analytics-chatbot-input');
  const loading = $('.analytics-chatbot-loading');
  let isOpen = false;

  // Set user's timezone in cookie
  function setUserTimezone() {
    const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    document.cookie = `user_timezone=${timezone}; path=/; max-age=86400; secure; samesite=strict`;
  }

  // Get cookie value by name
  function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) {
      return parts.pop().split(';').shift();
    }
    return null;
  }

  // Check session status
  function checkSession() {
    const sessionId = getCookie('ea_chatbot_session');
    if (!sessionId) {
      // Only show message if widget is open
      if (isOpen) {
        appendMessage('Session expired. Please refresh the page.', true);
      }
      return false;
    }
    return true;
  }

  // Initialize
  function initializeChat() {
    validateSession();
    setUserTimezone();
  }

  // Toggle chat widget
  trigger.on('click', function (e) {
    e.preventDefault();
    isOpen = !isOpen;
    widget.toggleClass('is-open', isOpen);

    if (isOpen) {
      input.prop('disabled', false);
      if (messages.children().length <= 1) {
        loadChatHistory();
      }
    }
  });

  $('.analytics-chatbot-toggle').on('click', function () {
    isOpen = false;
    widget.removeClass('is-open');
  });

  // Function to extract links from HTML and create buttons
  function createLinkButtons(html) {
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;
    const links = tempDiv.getElementsByTagName('a');
    const buttons = [];

    Array.from(links).forEach(link => {
      const button = $('<button>')
        .addClass('analytics-chatbot-link-button')
        .text(link.textContent)
        .attr('data-href', link.href)
        .on('click', function () {
          window.open($(this).attr('data-href'), '_blank');
        });
      buttons.push(button);
    });

    // Remove links from HTML after extracting them
    Array.from(links).forEach(link => {
      link.parentNode.replaceChild(document.createTextNode(link.textContent), link);
    });

    return {
      cleanText: tempDiv.innerHTML,
      buttons: buttons
    };
  }

  // Updated appendMessage function
  function appendMessage(content, isBot, isThinking = false) {
    const messageDiv = $('<div>')
      .addClass('analytics-chatbot-message')
      .addClass(isBot ? 'bot' : 'user');

    if (isThinking) {
      messageDiv.addClass('thinking');
      messageDiv.html(`${content}<div class="thinking-dots"><span></span><span></span><span></span></div>`);
    } else {
      if (isBot && typeof content === 'string' && content.includes('<')) {
        // Handle HTML content
        const { cleanText, buttons } = createLinkButtons(content);

        // Create message content div
        const contentDiv = $('<div>')
          .addClass('analytics-chatbot-message-content')
          .html(cleanText);

        messageDiv.append(contentDiv);

        // Add buttons if any
        if (buttons.length > 0) {
          const buttonContainer = $('<div>')
            .addClass('analytics-chatbot-button-container');

          buttons.forEach(button => {
            buttonContainer.append(button);
          });

          messageDiv.append(buttonContainer);
        }
      } else {
        // Handle plain text
        messageDiv.text(content);
      }
    }

    messages.append(messageDiv);
    messages.scrollTop(messages[0].scrollHeight);
  }

  // Load chat history
  async function loadChatHistory() {
    const sessionId = getCookie('ea_chatbot_session');

    if (!sessionId) {
      console.log('No session ID found, creating new session...');
      validateSession();
      return;
    }

    console.log('Loading chat history for session:', sessionId);

    try {
      loading.show();
      messages.empty();
      appendMessage('Loading chat history...', true, true);

      const response = await $.ajax({
        url: 'https://ea-chatbot-api-production.up.railway.app/chat/history',
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        data: {
          session_id: sessionId
        },
        dataType: 'json'
      });

      console.log('History response:', response);
      messages.empty();

      if (response && Array.isArray(response)) {
        if (response.length === 0) {
          appendMessage('No previous chat history found. Start a new conversation!', true);
        } else {
          response.forEach(message => {
            const content = message.content || message.message;
            const isBot = message.role === 'assistant' || message.sender === 'bot';
            appendMessage(content, isBot);
          });
        }
      } else if (response && response.messages && Array.isArray(response.messages)) {
        if (response.messages.length === 0) {
          appendMessage('No previous chat history found. Start a new conversation!', true);
        } else {
          response.messages.forEach(message => {
            const content = message.content || message.message;
            const isBot = message.role === 'assistant' || message.sender === 'bot';
            appendMessage(content, isBot);
          });
        }
      } else if (response && response.detail) {
        console.log('Server detail message:', response.detail);
        appendMessage(response.detail, true);
      } else {
        console.error('Invalid history format:', response);
        appendMessage('Unable to load chat history. Please try refreshing.', true);
      }
    } catch (error) {
      console.error('Chat history error:', error);
      messages.empty();

      let errorMessage = 'Unable to load chat history. Please try refreshing.';

      try {
        const errorResponse = JSON.parse(error.responseText);
        if (errorResponse && errorResponse.detail) {
          errorMessage = errorResponse.detail;
        }
      } catch (e) {
        console.error('Error parsing error response:', e);
      }

      appendMessage(errorMessage, true);
    } finally {
      loading.hide();
    }
  }

  // Update the session management
  function validateSession() {
    let sessionId = getCookie('ea_chatbot_session');
    if (!sessionId) {
      sessionId = 'guest-' + Date.now();
      const expiryDate = new Date();
      expiryDate.setDate(expiryDate.getDate() + 1); // 24 hours from now

      document.cookie = `ea_chatbot_session=${sessionId}; path=/; expires=${expiryDate.toUTCString()}; secure; samesite=strict`;
      console.log('Created new session:', sessionId);
    } else {
      console.log('Using existing session:', sessionId);
    }
    return sessionId;
  }

  // Update the form submission handler to properly handle HTML responses
  form.on('submit', async function (e) {
    e.preventDefault();

    const message = input.val().trim();
    if (!message) return;

    const sessionId = validateSession();
    input.prop('disabled', true);
    appendMessage(message, false);
    appendMessage('Thinking...', true, true);

    try {
      loading.show();
      const response = await $.ajax({
        url: 'https://ea-chatbot-api-production.up.railway.app/chat',
        type: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        data: JSON.stringify({
          message: message,
          session_id: sessionId,
          timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
        }),
        dataType: 'json'
      });

      console.log('Chat response:', response);
      messages.children().last().remove(); // Remove thinking message

      if (response && response.response) {
        appendMessage(response.response, true);
      } else if (response && response.message) {
        appendMessage(response.message, true);
      } else if (response && response.detail) {
        console.log('Server detail message:', response.detail);
        appendMessage(response.detail, true);
      } else {
        console.error('Invalid response format:', response);
        appendMessage('Error: Unable to get response from chatbot.', true);
      }
    } catch (error) {
      console.error('Message error:', error);
      messages.children().last().remove(); // Remove thinking message

      let errorMessage = 'Error sending message. Please try again.';

      try {
        const errorResponse = JSON.parse(error.responseText);
        if (errorResponse && errorResponse.detail) {
          errorMessage = errorResponse.detail;
        }
      } catch (e) {
        console.error('Error parsing error response:', e);
      }

      appendMessage(errorMessage, true);
    } finally {
      input.prop('disabled', false).val('').focus();
      loading.hide();
    }
  });

  // Add this near the initialization
  initializeChat();

  // Handle mobile keyboard
  if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
    input.addEventListener('focus', function () {
      widget.classList.add('keyboard-open');
      // Scroll to bottom when keyboard opens
      setTimeout(() => {
        const messages = widget.querySelector('.analytics-chatbot-messages');
        messages.scrollTop = messages.scrollHeight;
      }, 100);
    });

    input.addEventListener('blur', function () {
      widget.classList.remove('keyboard-open');
    });

    // Prevent zoom on iOS when focusing input
    const viewport = document.querySelector('meta[name=viewport]');
    if (!viewport) {
      const meta = document.createElement('meta');
      meta.name = 'viewport';
      meta.content = 'width=device-width, initial-scale=1, maximum-scale=1';
      document.head.appendChild(meta);
    } else {
      viewport.content = 'width=device-width, initial-scale=1, maximum-scale=1';
    }
  }

  // Handle window resize
  let resizeTimeout;
  window.addEventListener('resize', function () {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(function () {
      if (widget.classList.contains('is-open')) {
        const messages = widget.querySelector('.analytics-chatbot-messages');
        messages.scrollTop = messages.scrollHeight;
      }
    }, 250);
  });
});
