jQuery(document).ready(function ($) {
    // Variables
    var currentPage = 1;
    var itemsPerPage = 10;
    var totalPages = 1;
    var jobsList = $('#jms-jobs-list');
    var jobModal = $('#jms-job-modal');
    var deleteModal = $('#jms-delete-modal');
    var jobForm = $('#jms-job-form');
    var jobIdField = $('#jms-job-id');
    var jobToDelete = 0;

    // Initialize
    loadJobs();

    // Event Listeners
    $('.add-new-job').on('click', openAddJobModal);
    $('#jms-filter-jobs').on('click', loadJobs);
    $('.jms-modal-close, .jms-modal-cancel').on('click', closeModals);
    jobForm.on('submit', saveJob);
    $('#jms-confirm-delete').on('click', deleteJob);

    // Pagination
    $('.first-page').on('click', function () {
        if (currentPage > 1) {
            currentPage = 1;
            loadJobs();
        }
    });

    $('.prev-page').on('click', function () {
        if (currentPage > 1) {
            currentPage--;
            loadJobs();
        }
    });

    $('.next-page').on('click', function () {
        if (currentPage < totalPages) {
            currentPage++;
            loadJobs();
        }
    });

    $('.last-page').on('click', function () {
        if (currentPage < totalPages) {
            currentPage = totalPages;
            loadJobs();
        }
    });

    // Functions
    function loadJobs() {
        var status = $('#jms-filter-status').val();
        var location = $('#jms-filter-location').val();
        var search = $('#jms-search-jobs').val();

        jobsList.html('<tr><td colspan="7">Loading jobs...</td></tr>');

        $.ajax({
            url: jms_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'jms_admin_get_jobs',
                nonce: jms_admin_ajax.nonce,
                page: currentPage,
                per_page: itemsPerPage,
                status: status,
                location: location,
                search: search
            },
            success: function (response) {
                if (response.success) {
                    displayJobs(response.data.jobs);
                    updatePagination(response.data.total, response.data.total_pages);
                } else {
                    showNotice('error', response.data.message, '.jms-admin-notices');
                }
            },
            error: function () {
                showNotice('error', 'An error occurred while loading jobs.', '.jms-admin-notices');
            }
        });
    }

    function displayJobs(jobs) {
        if (jobs.length === 0) {
            jobsList.html('<tr><td colspan="7">No jobs found.</td></tr>');
            return;
        }

        var html = '';

        $.each(jobs, function (index, job) {
            html += '<tr>';
            html += '<td class="column-title"><strong><a href="javascript:void(0);" class="edit-job" data-id="' + job.id + '">' + job.title + '</a></strong></td>';
            html += '<td class="column-location">' + job.location + '</td>';
            html += '<td class="column-salary">' + job.salary_range + '</td>';
            html += '<td class="column-status"><span class="jms-status jms-status-' + job.status + '">' + job.status + '</span></td>';
            html += '<td class="column-applications"><a href="admin.php?page=jms-candidates&job_id=' + job.id + '">' + job.application_count + '</a></td>';
            html += '<td class="column-date">' + job.date_posted + '</td>';
            html += '<td class="column-actions">';
            html += '<a href="javascript:void(0);" class="edit-job" data-id="' + job.id + '">Edit</a> | ';
            html += '<a href="javascript:void(0);" class="delete-job" data-id="' + job.id + '">Delete</a>';
            html += '</td>';
            html += '</tr>';
        });

        jobsList.html(html);

        // Attach event listeners to new elements
        $('.edit-job').on('click', function () {
            var jobId = $(this).data('id');
            openEditJobModal(jobId);
        });

        $('.delete-job').on('click', function () {
            var jobId = $(this).data('id');
            openDeleteModal(jobId);
        });
    }

    function updatePagination(total, totalPages) {
        $('#jms-jobs-count').text(total);
        $('#jms-current-page').text(currentPage);
        $('#jms-total-pages').text(totalPages);

        // Update global variable
        window.totalPages = totalPages;
    }

    function openAddJobModal() {
        // Reset form
        jobForm[0].reset();
        jobIdField.val(0);

        // Update modal title
        $('#jms-job-modal-title').text('Add New Job');

        // Show modal
        jobModal.css('display', 'block');
    }

    function openEditJobModal(jobId) {
        // Show loading
        $('#jms-job-modal-title').text('Loading Job...');
        jobModal.css('display', 'block');

        // Get job data
        $.ajax({
            url: jms_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'jms_admin_get_job',
                nonce: jms_admin_ajax.nonce,
                job_id: jobId
            },
            success: function (response) {
                if (response.success) {
                    var job = response.data.job;

                    // Fill form
                    jobIdField.val(job.id);
                    $('#jms-job-title').val(job.title);
                    $('#jms-job-description').val(job.description);
                    $('#jms-job-requirements').val(job.requirements);
                    $('#jms-job-location').val(job.location);
                    $('#jms-job-salary').val(job.salary_range);
                    $('#jms-job-status').val(job.status);

                    // Update modal title
                    $('#jms-job-modal-title').text('Edit Job');
                } else {
                    closeModals();
                    showNotice('error', response.data.message, '.jms-admin-notices');
                }
            },
            error: function () {
                closeModals();
                showNotice('error', 'An error occurred while loading job data.', '.jms-admin-notices');
            }
        });
    }

    function openDeleteModal(jobId) {
        jobToDelete = jobId;
        deleteModal.css('display', 'block');
    }

    function closeModals() {
        jobModal.css('display', 'none');
        deleteModal.css('display', 'none');
    }

    function saveJob(e) {
        e.preventDefault();

        var jobId = jobIdField.val();
        var isNew = jobId == 0;

        // Get form data
        var formData = {
            action: 'jms_admin_save_job',
            nonce: jms_admin_ajax.nonce,
            job_id: jobId,
            title: $('#jms-job-title').val(),
            description: $('#jms-job-description').val(),
            requirements: $('#jms-job-requirements').val(),
            location: $('#jms-job-location').val(),
            salary_range: $('#jms-job-salary').val(),
            status: $('#jms-job-status').val()
        };

        // Validate form
        if (!formData.title || !formData.description || !formData.requirements || !formData.location || !formData.salary_range) {
            showNotice('error', 'Please fill in all required fields.', '.jms-modal-notices');
            return;
        }

        // Save job
        $.ajax({
            url: jms_admin_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response.success) {
                    closeModals();
                    showNotice('success', isNew ? 'Job created successfully.' : 'Job updated successfully.', '.jms-admin-notices');
                    loadJobs();
                } else {
                    showNotice('error', response.data.message, '.jms-modal-notices');
                }
            },
            error: function () {
                showNotice('error', 'An error occurred while saving the job.', '.jms-modal-notices');
            }
        });
    }

    function deleteJob() {
        if (!jobToDelete) {
            return;
        }

        $.ajax({
            url: jms_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'jms_admin_delete_job',
                nonce: jms_admin_ajax.nonce,
                job_id: jobToDelete
            },
            success: function (response) {
                closeModals();

                if (response.success) {
                    showNotice('success', 'Job deleted successfully.', '.jms-admin-notices');
                    loadJobs();
                } else {
                    showNotice('error', response.data.message, '.jms-admin-notices');
                }

                jobToDelete = 0;
            },
            error: function () {
                closeModals();
                showNotice('error', 'An error occurred while deleting the job.', '.jms-admin-notices');
                jobToDelete = 0;
            }
        });
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

    // Candidates Management
    if ($('.jms-candidates-table').length) {
        loadCandidates();

        // Event Listeners
        $('#jms-filter-candidates').on('click', loadCandidates);

        function loadCandidates() {
            var jobId = $('#jms-filter-job').val();
            var status = $('#jms-filter-status').val();
            var search = $('#jms-search-candidates').val();

            $('#jms-candidates-list').html('<tr><td colspan="7">Loading candidates...</td></tr>');

            $.ajax({
                url: jms_admin_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'jms_admin_get_candidates',
                    nonce: jms_admin_ajax.nonce,
                    job_id: jobId,
                    status: status,
                    search: search
                },
                success: function (response) {
                    if (response.success) {
                        displayCandidates(response.data.candidates);
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
            if (!candidates || candidates.length === 0) {
                $('#jms-candidates-list').html('<tr><td colspan="7">No candidates found.</td></tr>');
                return;
            }

            var html = '';

            candidates.forEach(function (candidate) {
                html += '<tr>';
                html += '<td class="column-name">' + candidate.name + '</td>';
                html += '<td class="column-job">' + candidate.job_title + '</td>';
                html += '<td class="column-email">' + candidate.email + '</td>';
                html += '<td class="column-phone">' + candidate.phone + '</td>';
                html += '<td class="column-status"><span class="jms-status jms-status-' + candidate.status + '">' + formatStatus(candidate.status) + '</span></td>';
                html += '<td class="column-date">' + formatDate(candidate.application_date) + '</td>';
                html += '<td class="column-actions">';
                html += '<a href="javascript:void(0);" class="view-candidate" data-id="' + candidate.id + '">View</a> | ';
                html += '<a href="' + candidate.resume_path + '" target="_blank">Resume</a>';
                html += '</td>';
                html += '</tr>';
            });

            $('#jms-candidates-list').html(html);

            // Attach event listeners
            $('.view-candidate').on('click', function () {
                var candidateId = $(this).data('id');
                openCandidateModal(candidateId);
            });
        }

        function formatStatus(status) {
            return status.replace(/_/g, ' ').replace(/\b\w/g, function (l) { return l.toUpperCase(); });
        }

        function formatDate(dateString) {
            var date = new Date(dateString);
            return date.toLocaleDateString();
        }

        function openCandidateModal(candidateId) {
            jQuery.ajax({
                url: jms_admin_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'jms_get_candidate_details',
                    candidate_id: candidateId,
                    nonce: jms_admin_ajax.nonce
                },
                success: function (response) {
                    if (response.success) {
                        const candidate = response.data;
                        const modalContent = `
                            <div class="jms-modal-content">
                                <div class="jms-modal-header">
                                    <h2>${candidate.name}</h2>
                                    <span class="jms-modal-close">&times;</span>
                                </div>
                                <div class="jms-modal-body">
                                    <div class="candidate-info">
                                        <p><strong>Email:</strong> ${candidate.email}</p>
                                        <p><strong>Phone:</strong> ${candidate.phone}</p>
                                        <p><strong>Applied For:</strong> ${candidate.job_title}</p>
                                        <p><strong>Application Date:</strong> ${candidate.application_date}</p>
                                        <p><strong>Status:</strong> ${candidate.status}</p>
                                    </div>
                                    <div class="candidate-details">
                                        <h3>Cover Letter</h3>
                                        <div class="cover-letter">${candidate.cover_letter}</div>

                                        <h3>Experience</h3>
                                        <div class="experience">${candidate.experience}</div>

                                        <h3>Education</h3>
                                        <div class="education">${candidate.education}</div>
                                    </div>
                                    <div class="candidate-actions">
                                        <button class="button button-primary schedule-interview" data-candidate-id="${candidate.id}">
                                            Schedule Interview
                                        </button>
                                        <a href="${candidate.resume_url}" class="button" target="_blank">
                                            View Resume
                                        </a>
                                    </div>
                                </div>
                            </div>
                        `;

                        // Create and show modal
                        const modal = document.createElement('div');
                        modal.className = 'jms-modal';
                        modal.innerHTML = modalContent;
                        document.body.appendChild(modal);

                        // Handle close button
                        modal.querySelector('.jms-modal-close').addEventListener('click', function () {
                            modal.remove();
                        });

                        // Handle schedule interview button
                        modal.querySelector('.schedule-interview').addEventListener('click', function () {
                            openScheduleInterviewModal(candidate);
                        });
                    } else {
                        showNotice('Error loading candidate details', 'error');
                    }
                },
                error: function () {
                    showNotice('Error loading candidate details', 'error');
                }
            });
        }

        function openScheduleInterviewModal(candidate) {
            const modalContent = `
                <div class="jms-modal-content">
                    <div class="jms-modal-header">
                        <h2>Schedule Interview - ${candidate.name}</h2>
                        <span class="jms-modal-close">&times;</span>
                    </div>
                    <div class="jms-modal-body">
                        <form id="schedule-interview-form">
                            <input type="hidden" name="candidate_id" value="${candidate.id}">
                            <div class="form-group">
                                <label for="interview-date">Interview Date and Time:</label>
                                <input type="datetime-local" id="interview-date" name="interview_date" required>
                            </div>
                            <div class="form-group">
                                <label for="interview-type">Interview Type:</label>
                                <select id="interview-type" name="interview_type" required>
                                    <option value="online">Online (Google Meet)</option>
                                    <option value="in-person">In Person</option>
                                    <option value="phone">Phone</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="interview-notes">Notes:</label>
                                <textarea id="interview-notes" name="interview_notes"></textarea>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="button button-primary">Schedule Interview</button>
                            </div>
                        </form>
                    </div>
                </div>
            `;

            // Create and show modal
            const modal = document.createElement('div');
            modal.className = 'jms-modal';
            modal.innerHTML = modalContent;
            document.body.appendChild(modal);

            // Handle close button
            modal.querySelector('.jms-modal-close').addEventListener('click', function () {
                modal.remove();
            });

            // Handle form submission
            modal.querySelector('#schedule-interview-form').addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);

                jQuery.ajax({
                    url: jms_admin_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'jms_schedule_interview',
                        nonce: jms_admin_ajax.nonce,
                        candidate_id: formData.get('candidate_id'),
                        interview_date: formData.get('interview_date'),
                        interview_type: formData.get('interview_type'),
                        interview_notes: formData.get('interview_notes')
                    },
                    success: function (response) {
                        if (response.success) {
                            showNotice('Interview scheduled successfully', 'success');
                            modal.remove();
                            if (response.data.meet_link) {
                                showNotice(`Google Meet link: ${response.data.meet_link}`, 'success');
                            }
                        } else {
                            showNotice(response.data.message || 'Error scheduling interview', 'error');
                        }
                    },
                    error: function () {
                        showNotice('Error scheduling interview', 'error');
                    }
                });
            });
        }
    }
});
