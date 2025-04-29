jQuery(document).ready(function ($) {
    // Variables
    var currentPage = 1;
    var itemsPerPage = 10;
    var totalPages = 1;
    var candidatesList = $('#jms-candidates-list');
    var candidateModal = $('#jms-candidate-modal');
    var currentCandidateId = 0;
    var currentJobId = 0;

    // Initialize
    loadCandidates();

    // Event Listeners
    $('#jms-filter-candidates').on('click', loadCandidates);
    $('.jms-modal-close').on('click', closeModals);

    // Pagination
    $('.first-page').on('click', function () {
        if (currentPage > 1) {
            currentPage = 1;
            loadCandidates();
        }
    });

    $('.prev-page').on('click', function () {
        if (currentPage > 1) {
            currentPage--;
            loadCandidates();
        }
    });

    $('.next-page').on('click', function () {
        if (currentPage < totalPages) {
            currentPage++;
            loadCandidates();
        }
    });

    $('.last-page').on('click', function () {
        if (currentPage < totalPages) {
            currentPage = totalPages;
            loadCandidates();
        }
    });

    // Tab navigation
    $(document).on('click', '.jms-tabs-nav li', function () {
        var tab = $(this).data('tab');

        // Update active tab
        $('.jms-tabs-nav li').removeClass('jms-tab-active');
        $(this).addClass('jms-tab-active');

        // Show selected tab content
        $('.jms-tab-content').removeClass('jms-tab-active');
        $('.jms-tab-' + tab).addClass('jms-tab-active');
    });

    // Status update buttons
    $(document).on('click', '.jms-status-button', function () {
        var status = $(this).data('status');
        updateCandidateStatus(currentCandidateId, status);
    });

    // Interview form submission
    $(document).on('submit', '#jms-interview-form', function (e) {
        e.preventDefault();
        scheduleInterview();
    });

    // Document request form submission
    $(document).on('submit', '#jms-document-request-form', function (e) {
        e.preventDefault();
        requestDocuments();
    });

    // Functions
    function loadCandidates() {
        var jobId = $('#jms-filter-job').val();
        var status = $('#jms-filter-status').val();
        var search = $('#jms-search-candidates').val();

        candidatesList.html('<tr><td colspan="7">Loading candidates...</td></tr>');

        $.ajax({
            url: jms_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'jms_admin_get_candidates',
                nonce: jms_admin_ajax.nonce,
                job_id: jobId,
                status: status,
                search: search,
                page: currentPage,
                per_page: itemsPerPage
            },
            success: function (response) {
                if (response.success) {
                    displayCandidates(response.data.candidates);
                    updatePagination(response.data.total, response.data.total_pages);
                } else {
                    showNotice('error', response.data.message, '.jms-admin-notices');
                }
            },
            error: function () {
                showNotice('error', 'An error occurred while loading candidates.', '.jms-admin-notices');
            }
        });
    }

    function displayCandidates(candidates) {
        if (candidates.length === 0) {
            candidatesList.html('<tr><td colspan="7">No candidates found.</td></tr>');
            return;
        }

        var html = '';

        $.each(candidates, function (index, candidate) {
            html += '<tr>';
            html += '<td class="column-name"><strong><a href="javascript:void(0);" class="view-candidate" data-id="' + candidate.id + '">' + candidate.name + '</a></strong></td>';
            html += '<td class="column-job">' + candidate.job_title + '</td>';
            html += '<td class="column-email"><a href="mailto:' + candidate.email + '">' + candidate.email + '</a></td>';
            html += '<td class="column-phone">' + candidate.phone + '</td>';
            html += '<td class="column-status"><span class="jms-status jms-status-' + candidate.status + '">' + getStatusLabel(candidate.status) + '</span></td>';
            html += '<td class="column-date">' + candidate.application_date + '</td>';
            html += '<td class="column-actions">';
            html += '<a href="javascript:void(0);" class="view-candidate" data-id="' + candidate.id + '">View</a>';
            if (candidate.resume_url) {
                html += ' | <a href="' + candidate.resume_url + '" target="_blank">Resume</a>';
            }
            html += '</td>';
            html += '</tr>';
        });

        candidatesList.html(html);

        // Attach event listeners to new elements
        $('.view-candidate').on('click', function () {
            var candidateId = $(this).data('id');
            openCandidateModal(candidateId);
        });
    }

    function updatePagination(total, totalPages) {
        $('#jms-candidates-count').text(total);
        $('#jms-current-page').text(currentPage);
        $('#jms-total-pages').text(totalPages);

        // Update global variable
        window.totalPages = totalPages;
    }

    function openCandidateModal(candidateId) {
        // Reset modal
        $('.jms-candidate-content').hide();
        $('.jms-candidate-loading').show();
        $('.jms-tabs-nav li').removeClass('jms-tab-active');
        $('.jms-tabs-nav li[data-tab="profile"]').addClass('jms-tab-active');
        $('.jms-tab-content').removeClass('jms-tab-active');
        $('.jms-tab-profile').addClass('jms-tab-active');

        // Show modal
        candidateModal.css('display', 'block');

        // Get candidate details
        $.ajax({
            url: jms_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'jms_admin_get_candidate',
                nonce: jms_admin_ajax.nonce,
                candidate_id: candidateId
            },
            success: function (response) {
                if (response.success) {
                    displayCandidateDetails(response.data.candidate);
                } else {
                    closeModals();
                    showNotice('error', response.data.message, '.jms-admin-notices');
                }
            },
            error: function () {
                closeModals();
                showNotice('error', 'An error occurred while loading candidate details.', '.jms-admin-notices');
            }
        });
    }

    function displayCandidateDetails(candidate) {
        // Store current candidate and job IDs
        currentCandidateId = candidate.id;
        currentJobId = candidate.job_id;

        // Basic info
        $('.jms-candidate-name').text(candidate.name);
        $('.jms-candidate-job').text(candidate.job_title);
        $('.jms-candidate-email').html('<strong>Email:</strong> <a href="mailto:' + candidate.email + '">' + candidate.email + '</a>');
        $('.jms-candidate-phone').html('<strong>Phone:</strong> ' + candidate.phone);
        $('.jms-candidate-status').html('<span class="jms-status jms-status-' + candidate.status + '">' + getStatusLabel(candidate.status) + '</span>');
        $('.jms-candidate-date').html('<strong>Applied:</strong> ' + candidate.application_date);

        // Profile tab
        $('.jms-candidate-experience').html(candidate.experience ? formatTextWithParagraphs(candidate.experience) : '<em>No experience provided</em>');
        $('.jms-candidate-education').html(candidate.education ? formatTextWithParagraphs(candidate.education) : '<em>No education provided</em>');
        $('.jms-candidate-skills').html(candidate.skills ? formatTextWithParagraphs(candidate.skills) : '<em>No skills provided</em>');

        // Resume handling
        if (candidate.resume_url) {
            $('.jms-view-resume').attr('href', candidate.resume_url).show();
            $('.jms-download-resume').attr('href', candidate.resume_url + '?download=1').show();
            $('#jms-resume-iframe').attr('src', candidate.resume_url).parent().show();
        } else {
            $('.jms-view-resume, .jms-download-resume').hide();
            $('#jms-resume-iframe').parent().hide();
        }

        // Interviews tab
        displayInterviews(candidate.interviews);
        $('#jms-interview-candidate-id').val(candidate.id);
        $('#jms-interview-job-id').val(candidate.job_id);

        // Documents tab
        displayDocuments(candidate.documents);
        $('#jms-document-candidate-id').val(candidate.id);

        // Disable status buttons based on current status
        updateStatusButtons(candidate.status);

        // Show content
        $('.jms-candidate-loading').hide();
        $('.jms-candidate-content').show();
    }

    function displayInterviews(interviews) {
        var html = '';

        if (!interviews || interviews.length === 0) {
            html = '<div class="jms-no-interviews"><p>No interviews scheduled yet.</p></div>';
        } else {
            html = '<div class="jms-interviews-table">';
            html += '<div class="jms-interview-header">';
            html += '<div class="jms-interview-date-header">Date & Time</div>';
            html += '<div class="jms-interview-type-header">Type</div>';
            html += '<div class="jms-interview-status-header">Status</div>';
            html += '<div class="jms-interview-actions-header">Actions</div>';
            html += '</div>';

            $.each(interviews, function (index, interview) {
                html += '<div class="jms-interview-row">';
                html += '<div class="jms-interview-date">' + interview.interview_date + '</div>';
                html += '<div class="jms-interview-type">' + getInterviewTypeLabel(interview.interview_type) + '</div>';
                html += '<div class="jms-interview-status"><span class="jms-status jms-status-' + interview.status + '">' + getInterviewStatusLabel(interview.status) + '</span></div>';
                html += '<div class="jms-interview-actions">';

                if (interview.google_meet_link) {
                    html += '<a href="' + interview.google_meet_link + '" target="_blank" class="jms-button jms-meet-button">Google Meet</a>';
                }

                html += '</div>';
                html += '</div>';

                if (interview.notes) {
                    html += '<div class="jms-interview-notes"><strong>Notes:</strong> ' + interview.notes + '</div>';
                }
            });

            html += '</div>';
        }

        $('.jms-interviews-list').html(html);
    }

    function displayDocuments(documents) {
        var html = '';

        if (!documents || documents.length === 0) {
            html = '<div class="jms-no-documents"><p>No documents uploaded yet.</p></div>';
        } else {
            html = '<div class="jms-documents-table">';
            html += '<div class="jms-document-header">';
            html += '<div class="jms-document-type-header">Document Type</div>';
            html += '<div class="jms-document-date-header">Upload Date</div>';
            html += '<div class="jms-document-status-header">Status</div>';
            html += '<div class="jms-document-actions-header">Actions</div>';
            html += '</div>';

            $.each(documents, function (index, document) {
                html += '<div class="jms-document-row">';
                html += '<div class="jms-document-type">' + document.document_type_label + '</div>';
                html += '<div class="jms-document-date">' + document.upload_date + '</div>';
                html += '<div class="jms-document-status"><span class="jms-status jms-status-' + document.status + '">' + getDocumentStatusLabel(document.status) + '</span></div>';
                html += '<div class="jms-document-actions">';
                html += '<a href="' + document.document_path + '" target="_blank" class="jms-button jms-view-button">View</a>';

                if (document.status === 'submitted') {
                    html += '<button class="jms-button jms-approve-button" data-id="' + document.id + '">Approve</button>';
                    html += '<button class="jms-button jms-reject-button" data-id="' + document.id + '">Reject</button>';
                }

                html += '</div>';
                html += '</div>';

                if (document.notes) {
                    html += '<div class="jms-document-notes"><strong>Notes:</strong> ' + document.notes + '</div>';
                }
            });

            html += '</div>';
        }

        $('.jms-documents-list').html(html);

        // Attach event listeners
        $('.jms-approve-button').on('click', function () {
            var documentId = $(this).data('id');
            approveDocument(documentId);
        });

        $('.jms-reject-button').on('click', function () {
            var documentId = $(this).data('id');
            rejectDocument(documentId);
        });
    }

    function updateCandidateStatus(candidateId, status) {
        $.ajax({
            url: jms_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'jms_admin_update_candidate_status',
                nonce: jms_admin_ajax.nonce,
                candidate_id: candidateId,
                status: status
            },
            success: function (response) {
                if (response.success) {
                    showNotice('success', response.data.message, '.jms-modal-notices');

                    // Update status in modal
                    $('.jms-candidate-status').html('<span class="jms-status jms-status-' + status + '">' + getStatusLabel(status) + '</span>');

                    // Update status buttons
                    updateStatusButtons(status);

                    // Reload candidates list
                    loadCandidates();
                } else {
                    showNotice('error', response.data.message, '.jms-modal-notices');
                }
            },
            error: function () {
                showNotice('error', 'An error occurred while updating candidate status.', '.jms-modal-notices');
            }
        });
    }

    function scheduleInterview() {
        var formData = $('#jms-interview-form').serialize();
        formData += '&action=jms_admin_schedule_interview&nonce=' + jms_admin_ajax.nonce;

        $.ajax({
            url: jms_admin_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response.success) {
                    showNotice('success', response.data.message, '.jms-modal-notices');

                    // Reset form
                    $('#jms-interview-form')[0].reset();

                    // Reload candidate details
                    openCandidateModal(currentCandidateId);

                    // Reload candidates list
                    loadCandidates();
                } else {
                    showNotice('error', response.data.message, '.jms-modal-notices');
                }
            },
            error: function () {
                showNotice('error', 'An error occurred while scheduling the interview.', '.jms-modal-notices');
            }
        });
    }

    function requestDocuments() {
        var documentTypes = [];
        $('input[name="document_types[]"]:checked').each(function () {
            documentTypes.push($(this).val());
        });

        if (documentTypes.length === 0) {
            showNotice('error', 'Please select at least one document type.', '.jms-modal-notices');
            return;
        }

        $.ajax({
            url: jms_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'jms_admin_request_documents',
                nonce: jms_admin_ajax.nonce,
                candidate_id: currentCandidateId,
                document_types: documentTypes
            },
            success: function (response) {
                if (response.success) {
                    showNotice('success', response.data.message, '.jms-modal-notices');

                    // Reset form
                    $('#jms-document-request-form')[0].reset();
                } else {
                    showNotice('error', response.data.message, '.jms-modal-notices');
                }
            },
            error: function () {
                showNotice('error', 'An error occurred while requesting documents.', '.jms-modal-notices');
            }
        });
    }

    function approveDocument(documentId) {
        $.ajax({
            url: jms_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'jms_admin_manage_documents',
                nonce: jms_admin_ajax.nonce,
                doc_action: 'approve',
                document_id: documentId
            },
            success: function (response) {
                if (response.success) {
                    showNotice('success', response.data.message, '.jms-modal-notices');

                    // Reload candidate details
                    openCandidateModal(currentCandidateId);
                } else {
                    showNotice('error', response.data.message, '.jms-modal-notices');
                }
            },
            error: function () {
                showNotice('error', 'An error occurred while approving the document.', '.jms-modal-notices');
            }
        });
    }

    function rejectDocument(documentId) {
        var notes = prompt('Please provide a reason for rejection:');

        if (notes === null) {
            return; // User cancelled
        }

        $.ajax({
            url: jms_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'jms_admin_manage_documents',
                nonce: jms_admin_ajax.nonce,
                doc_action: 'reject',
                document_id: documentId,
                notes: notes
            },
            success: function (response) {
                if (response.success) {
                    showNotice('success', response.data.message, '.jms-modal-notices');

                    // Reload candidate details
                    openCandidateModal(currentCandidateId);
                } else {
                    showNotice('error', response.data.message, '.jms-modal-notices');
                }
            },
            error: function () {
                showNotice('error', 'An error occurred while rejecting the document.', '.jms-modal-notices');
            }
        });
    }

    function updateStatusButtons(status) {
        // Enable all buttons first
        $('.jms-status-button').prop('disabled', false);

        // Disable buttons based on current status
        switch (status) {
            case 'applied':
                // All buttons enabled
                break;
            case 'shortlisted':
                $('.jms-status-button[data-status="shortlisted"]').prop('disabled', true);
                break;
            case 'interview_scheduled':
                $('.jms-status-button[data-status="shortlisted"]').prop('disabled', true);
                break;
            case 'interviewed':
                $('.jms-status-button[data-status="shortlisted"]').prop('disabled', true);
                $('.jms-status-button[data-status="interviewed"]').prop('disabled', true);
                break;
            case 'offered':
                $('.jms-status-button[data-status="shortlisted"]').prop('disabled', true);
                $('.jms-status-button[data-status="interviewed"]').prop('disabled', true);
                $('.jms-status-button[data-status="offered"]').prop('disabled', true);
                break;
            case 'hired':
                $('.jms-status-button').prop('disabled', true);
                break;
            case 'rejected':
                $('.jms-status-button').prop('disabled', true);
                break;
        }
    }

    function closeModals() {
        candidateModal.css('display', 'none');
    }

    function getStatusLabel(status) {
        switch (status) {
            case 'applied':
                return 'Applied';
            case 'shortlisted':
                return 'Shortlisted';
            case 'interview_scheduled':
                return 'Interview Scheduled';
            case 'interviewed':
                return 'Interviewed';
            case 'offered':
                return 'Offer Extended';
            case 'hired':
                return 'Hired';
            case 'rejected':
                return 'Rejected';
            default:
                return status;
        }
    }

    function getInterviewTypeLabel(type) {
        switch (type) {
            case 'in_person':
                return 'In Person';
            case 'phone':
                return 'Phone';
            case 'google_meet':
                return 'Google Meet';
            default:
                return type;
        }
    }

    function getInterviewStatusLabel(status) {
        switch (status) {
            case 'scheduled':
                return 'Scheduled';
            case 'completed':
                return 'Completed';
            case 'cancelled':
                return 'Cancelled';
            default:
                return status;
        }
    }

    function getDocumentStatusLabel(status) {
        switch (status) {
            case 'submitted':
                return 'Submitted';
            case 'approved':
                return 'Approved';
            case 'rejected':
                return 'Rejected';
            default:
                return status;
        }
    }

    function formatTextWithParagraphs(text) {
        return text.replace(/\n/g, '<br>');
    }

    function showNotice(type, message, container) {
        var notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        $(container).html(notice);

        // Auto dismiss after 5 seconds
        setTimeout(function () {
            notice.fadeOut(function () {
                $(this).remove();
            });
        }, 5000);
    }
});
