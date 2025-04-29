jQuery(document).ready(function ($) {
  const form = $('.ea-webinar-registration-form');
  const submitButton = form.find('input[type="submit"]');
  const messageContainer = $('.ea-webinar-message');
  const spotsCountElement = $('.ea-webinar-spots-count');

  form.on('submit', function (e) {
    e.preventDefault();

    // Clear previous messages
    messageContainer.removeClass('success error').empty();

    // Disable submit button
    submitButton.prop('disabled', true);

    // Create FormData object
    const formData = new FormData(this);
    formData.append('action', 'ea_webinar_register');
    formData.append('security', ea_webinar_obj.nonce);

    // Send AJAX request
    $.ajax({
      url: ea_webinar_obj.ajax_url,
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        if (response.success) {
          // Show success message
          messageContainer
            .addClass('success')
            .html(response.data.message);

          // Clear form
          form[0].reset();

          // Update spots count if provided
          if (response.data.spots_remaining !== undefined) {
            spotsCountElement.text(response.data.spots_remaining + ' spots remaining');

            // Add warning class if spots are low
            if (response.data.spots_remaining < 5) {
              spotsCountElement.addClass('low');
            }
          }
        } else {
          // Show error message
          messageContainer
            .addClass('error')
            .html(response.data.message || 'Registration failed. Please try again.');
        }
      },
      error: function (xhr, status, error) {
        // Show error message
        messageContainer
          .addClass('error')
          .html('An error occurred. Please try again later.');

        console.error('AJAX Error:', status, error);
      },
      complete: function () {
        // Re-enable submit button
        submitButton.prop('disabled', false);
      }
    });
  });

  // Optional: Add client-side validation
  form.find('input[required]').on('input', function () {
    const isValid = this.checkValidity();
    $(this).toggleClass('invalid', !isValid);
  });
});
