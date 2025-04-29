jQuery(document).ready(function ($) {
  // Get candidate details
  function getCandidateDetails(candidateId) {
    return $.ajax({
      url: jmsAjax.ajaxurl,
      type: 'POST',
      data: {
        action: 'jms_get_candidate_details',
        nonce: jmsAjax.nonce,
        candidate_id: candidateId
      }
    });
  }

  // Schedule interview
  function scheduleInterview(data) {
    return $.ajax({
      url: jmsAjax.ajaxurl,
      type: 'POST',
      data: {
        action: 'jms_schedule_interview',
        nonce: jmsAjax.nonce,
        ...data
      }
    });
  }

  // Handle view candidate details button click
  $(document).on('click', '.view-candidate-details', function (e) {
    e.preventDefault();
    const candidateId = $(this).data('candidate-id');

    getCandidateDetails(candidateId)
      .done(function (response) {
        if (response.success) {
          // Update modal with candidate details
          $('#candidate-name').text(response.data.name);
          $('#candidate-email').text(response.data.email);
          $('#candidate-phone').text(response.data.phone);
          $('#candidate-resume').attr('href', response.data.resume_url);
          $('#candidate-applied-date').text(response.data.application_date);

          // Show the modal
          $('#candidate-details-modal').modal('show');
        } else {
          alert(response.data.message || 'Error fetching candidate details');
        }
      })
      .fail(function (jqXHR, textStatus, errorThrown) {
        alert('Error: ' + errorThrown);
      });
  });

  // Handle schedule interview form submission
  $('#schedule-interview-form').on('submit', function (e) {
    e.preventDefault();

    const formData = {
      candidate_id: $('#candidate-id').val(),
      interview_date: $('#interview-date').val(),
      interview_time: $('#interview-time').val(),
      interview_type: $('#interview-type').val(),
      interviewer_email: $('#interviewer-email').val(),
      additional_notes: $('#additional-notes').val()
    };

    scheduleInterview(formData)
      .done(function (response) {
        if (response.success) {
          alert('Interview scheduled successfully!');
          $('#schedule-interview-modal').modal('hide');
          // Optionally refresh the page or update UI
          location.reload();
        } else {
          alert(response.data.message || 'Error scheduling interview');
        }
      })
      .fail(function (jqXHR, textStatus, errorThrown) {
        alert('Error: ' + errorThrown);
      });
  });
});
