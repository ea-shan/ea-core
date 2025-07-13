(function ($) {
  $(document).ready(function () {
    // Chat UI
    var chatContainer = $('#vapi-ai-chat-container');
    chatContainer.append('<div id="vapi-ai-messages"></div><input id="vapi-ai-user-input" type="text" placeholder="Type your message..." style="width: 80%;"><button id="vapi-ai-send">Send</button>');

    // Send chat message
    $('#vapi-ai-send').on('click', function () {
      var userInput = $('#vapi-ai-user-input').val();
      if (!userInput) return;
      $('#vapi-ai-user-input').val('');
      $('#vapi-ai-messages').append('<div class="vapi-ai-user-msg">' + $('<div>').text(userInput).html() + '</div>');
      vapiAIStreamChat(userInput);
    });

    // Enter key sends message
    $('#vapi-ai-user-input').on('keypress', function (e) {
      if (e.which === 13) $('#vapi-ai-send').click();
    });

    // Streaming chat function
    function vapiAIStreamChat(input) {
      var apiKey = vapiAISettings.apiKey;
      var assistantId = vapiAISettings.assistantId;
      var url = 'https://api.vapi.ai/chat';
      var messagesDiv = $('#vapi-ai-messages');
      var aiMsgDiv = $('<div class="vapi-ai-ai-msg"></div>');
      messagesDiv.append(aiMsgDiv);

      fetch(url, {
        method: 'POST',
        headers: {
          'Authorization': 'Bearer ' + apiKey,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          assistantId: assistantId,
          input: input,
          stream: true
        })
      }).then(response => {
        if (!response.body) {
          aiMsgDiv.append('<span style="color:red;">Streaming not supported.</span>');
          return;
        }
        const reader = response.body.getReader();
        let decoder = new TextDecoder();
        let buffer = '';
        function read() {
          reader.read().then(({ done, value }) => {
            if (done) return;
            buffer += decoder.decode(value, { stream: true });
            // Try to parse JSON lines
            var lines = buffer.split('\n');
            for (var i = 0; i < lines.length - 1; i++) {
              try {
                var data = JSON.parse(lines[i]);
                if (data && data.text) {
                  aiMsgDiv.append($('<span>').text(data.text).html());
                }
              } catch (e) { }
            }
            buffer = lines[lines.length - 1];
            read();
          });
        }
        read();
      }).catch(err => {
        aiMsgDiv.append('<span style="color:red;">Error: ' + err.message + '</span>');
      });
    }

    // Voice Call Button
    $('#vapi-ai-voice-call').on('click', function () {
      var apiKey = vapiAISettings.apiKey;
      var assistantId = vapiAISettings.assistantId;
      // Option 1: Open VAPI web call widget if available
      // window.open('https://app.vapi.ai/call?assistantId=' + encodeURIComponent(assistantId), '_blank');
      // Option 2: Initiate call via API (phone number prompt)
      var phone = prompt('Enter your phone number to receive a call:');
      if (!phone) return;
      fetch('https://api.vapi.ai/calls', {
        method: 'POST',
        headers: {
          'Authorization': 'Bearer ' + apiKey,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          assistantId: assistantId,
          phone: phone
        })
      }).then(res => res.json()).then(data => {
        alert('Call initiated!');
      }).catch(err => {
        alert('Error: ' + err.message);
      });
    });
  });
})(jQuery);
