document.addEventListener('DOMContentLoaded', function () {
  const chatWidget = document.getElementById('analytics-chatbot-widget');
  const chatMessages = chatWidget.querySelector('.analytics-chatbot-messages');
  const chatForm = chatWidget.querySelector('.analytics-chatbot-form');
  const chatInput = chatWidget.querySelector('.analytics-chatbot-input');
  const chatTrigger = document.querySelector('.analytics-chatbot-trigger');
  const chatToggle = chatWidget.querySelector('.analytics-chatbot-toggle');
  const chatHistory = chatWidget.querySelector('.analytics-chatbot-history');
  const loadingIndicator = chatWidget.querySelector('.analytics-chatbot-loading');

  let isOpen = false;
  let isLoading = false;

  // Initialize chat
  function initChat() {
    loadChatHistory();
  }

  // Load chat history
  async function loadChatHistory() {
    try {
      showLoading();
      const response = await fetch('/wp-json/analytics-chatbot/v1/history', {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': analyticsChatbot.nonce
        }
      });

      if (!response.ok) throw new Error('Failed to load chat history');

      const data = await response.json();
      if (data.success && data.data.length > 0) {
        chatMessages.innerHTML = ''; // Clear existing messages
        data.data.forEach(message => {
          appendMessage(message.content, message.role === 'assistant');
        });
        scrollToBottom();
      }
    } catch (error) {
      console.error('Error loading chat history:', error);
      appendMessage('Failed to load chat history. Please try again.', true);
    } finally {
      hideLoading();
    }
  }

  // Show loading indicator
  function showLoading() {
    isLoading = true;
    loadingIndicator.style.display = 'flex';
  }

  // Hide loading indicator
  function hideLoading() {
    isLoading = false;
    loadingIndicator.style.display = 'none';
  }

  // Append a message to the chat
  function appendMessage(message, isBot = false) {
    const messageDiv = document.createElement('div');
    messageDiv.classList.add('analytics-chatbot-message');
    messageDiv.classList.add(isBot ? 'bot' : 'user');
    messageDiv.textContent = message;
    chatMessages.appendChild(messageDiv);
    scrollToBottom();
  }

  // Scroll chat to bottom
  function scrollToBottom() {
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }

  // Toggle chat widget
  function toggleChat() {
    isOpen = !isOpen;
    chatWidget.classList.toggle('open', isOpen);
    if (isOpen) {
      chatInput.focus();
      if (chatMessages.children.length === 0) {
        loadChatHistory();
      }
    }
  }

  // Event Listeners
  chatTrigger.addEventListener('click', toggleChat);
  chatToggle.addEventListener('click', toggleChat);
  chatHistory.addEventListener('click', loadChatHistory);

  chatForm.addEventListener('submit', async function (e) {
    e.preventDefault();
    if (isLoading) return;

    const message = chatInput.value.trim();
    if (!message) return;

    appendMessage(message, false);
    chatInput.value = '';
    chatInput.focus();

    try {
      showLoading();
      const response = await fetch('/wp-json/analytics-chatbot/v1/chat', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': analyticsChatbot.nonce
        },
        body: JSON.stringify({ message })
      });

      if (!response.ok) throw new Error('Failed to send message');

      const data = await response.json();
      if (data.success) {
        appendMessage(data.data.message, true);
      } else {
        throw new Error(data.message || 'Failed to get response');
      }
    } catch (error) {
      console.error('Chat error:', error);
      appendMessage('Sorry, I encountered an error. Please try again.', true);
    } finally {
      hideLoading();
    }
  });

  // Initialize
  initChat();
});
