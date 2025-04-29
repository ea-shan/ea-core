jQuery(document).ready(function ($) {
  'use strict';

  // Modal elements
  const candidateModal = $('#candidate-details-modal');
  const interviewModal = $('#interview-scheduling-modal');
  const candidateDetailsContainer = $('#candidate-details-container');
  const interviewForm = $('#interview-scheduling-form');

  // View candidate details
  $(document).on('click', '.view-candidate-details', function (e) {
    e.preventDefault();
    const candidateId = $(this).data('candidate-id');

    $.ajax({
      url: jms_ajax_object.ajax_url,
      type: 'POST',
      data: {
        action: 'get_candidate_details',
        candidate_id: candidateId,
        nonce: jms_ajax_object.nonce
      },
      beforeSend: function () {
        candidateDetailsContainer.html('<div class="loading">Loading...</div>');
        candidateModal.show();
      },
      success: function (response) {
        if (response.success) {
          candidateDetailsContainer.html(response.data.html);
        } else {
          candidateDetailsContainer.html('<div class="error">Error loading candidate details.</div>');
        }
      },
      error: function () {
        candidateDetailsContainer.html('<div class="error">Error loading candidate details.</div>');
      }
    });
  });

  // Schedule interview button click
  $(document).on('click', '.schedule-interview-btn', function (e) {
    e.preventDefault();
    const candidateId = $(this).data('candidate-id');
    $('#interview_candidate_id').val(candidateId);
    candidateModal.hide();
    interviewModal.show();
  });

  // Submit interview scheduling form
  interviewForm.on('submit', function (e) {
    e.preventDefault();
    const formData = $(this).serialize();

    $.ajax({
      url: jms_ajax_object.ajax_url,
      type: 'POST',
      data: formData + '&action=schedule_interview&nonce=' + jms_ajax_object.nonce,
      beforeSend: function () {
        interviewForm.find('button[type="submit"]').prop('disabled', true).text('Scheduling...');
      },
      success: function (response) {
        if (response.success) {
          alert('Interview scheduled successfully!');
          interviewModal.hide();
          interviewForm[0].reset();
        } else {
          alert(response.data.message || 'Error scheduling interview.');
        }
      },
      error: function () {
        alert('Error scheduling interview. Please try again.');
      },
      complete: function () {
        interviewForm.find('button[type="submit"]').prop('disabled', false).text('Schedule Interview');
      }
    });
  });

  // Close modal buttons
  $('.modal-close').on('click', function () {
    $(this).closest('.modal').hide();
  });

  // Close modal when clicking outside
  $(window).on('click', function (e) {
    if ($(e.target).hasClass('modal')) {
      $('.modal').hide();
    }
  });

  // Prevent modal close when clicking inside
  $('.modal-content').on('click', function (e) {
    e.stopPropagation();
  });
});
