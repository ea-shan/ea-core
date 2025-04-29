<?php
/**
 * Admin template for managing candidates.
 *
 * @link       https://expressanalytics.net
 * @since      1.0.0
 *
 * @package    Job_Management_System
 * @subpackage Job_Management_System/templates/admin
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap jms-admin">
    <h1 class="wp-heading-inline"><?php _e('Candidate Management', 'job-management-system'); ?></h1>
    <hr class="wp-header-end">

    <div class="jms-admin-notices"></div>

    <!-- Candidate Filters -->
    <div class="tablenav top">
        <div class="alignleft actions">
            <select id="jms-filter-job">
                <option value=""><?php _e('All Jobs', 'job-management-system'); ?></option>
                <?php
                // Get all jobs
                $jms_jobs = new JMS_Jobs();
                $jobs = $jms_jobs->get_jobs(array('limit' => 0));
                
                foreach ($jobs as $job) {
                    echo '<option value="' . esc_attr($job->id) . '">' . esc_html($job->title) . '</option>';
                }
                ?>
            </select>
            <select id="jms-filter-status">
                <option value=""><?php _e('All Statuses', 'job-management-system'); ?></option>
                <option value="applied"><?php _e('Applied', 'job-management-system'); ?></option>
                <option value="shortlisted"><?php _e('Shortlisted', 'job-management-system'); ?></option>
                <option value="interview_scheduled"><?php _e('Interview Scheduled', 'job-management-system'); ?></option>
                <option value="interviewed"><?php _e('Interviewed', 'job-management-system'); ?></option>
                <option value="offered"><?php _e('Offer Extended', 'job-management-system'); ?></option>
                <option value="hired"><?php _e('Hired', 'job-management-system'); ?></option>
                <option value="rejected"><?php _e('Rejected', 'job-management-system'); ?></option>
            </select>
            <input type="text" id="jms-search-candidates" placeholder="<?php _e('Search candidates', 'job-management-system'); ?>">
            <button class="button" id="jms-filter-candidates"><?php _e('Filter', 'job-management-system'); ?></button>
        </div>
        <div class="tablenav-pages">
            <span class="displaying-num"><span id="jms-candidates-count">0</span> <?php _e('items', 'job-management-system'); ?></span>
            <span class="pagination-links">
                <a class="first-page button" href="javascript:void(0);"><span class="screen-reader-text"><?php _e('First page', 'job-management-system'); ?></span><span aria-hidden="true">«</span></a>
                <a class="prev-page button" href="javascript:void(0);"><span class="screen-reader-text"><?php _e('Previous page', 'job-management-system'); ?></span><span aria-hidden="true">‹</span></a>
                <span class="paging-input">
                    <span class="tablenav-paging-text"><span id="jms-current-page">1</span> <?php _e('of', 'job-management-system'); ?> <span id="jms-total-pages">1</span></span>
                </span>
                <a class="next-page button" href="javascript:void(0);"><span class="screen-reader-text"><?php _e('Next page', 'job-management-system'); ?></span><span aria-hidden="true">›</span></a>
                <a class="last-page button" href="javascript:void(0);"><span class="screen-reader-text"><?php _e('Last page', 'job-management-system'); ?></span><span aria-hidden="true">»</span></a>
            </span>
        </div>
        <br class="clear">
    </div>

    <!-- Candidates Table -->
    <table class="wp-list-table widefat fixed striped jms-candidates-table">
        <thead>
            <tr>
                <th class="column-name"><?php _e('Name', 'job-management-system'); ?></th>
                <th class="column-job"><?php _e('Job', 'job-management-system'); ?></th>
                <th class="column-email"><?php _e('Email', 'job-management-system'); ?></th>
                <th class="column-phone"><?php _e('Phone', 'job-management-system'); ?></th>
                <th class="column-status"><?php _e('Status', 'job-management-system'); ?></th>
                <th class="column-date"><?php _e('Applied', 'job-management-system'); ?></th>
                <th class="column-actions"><?php _e('Actions', 'job-management-system'); ?></th>
            </tr>
        </thead>
        <tbody id="jms-candidates-list">
            <tr>
                <td colspan="7"><?php _e('Loading candidates...', 'job-management-system'); ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Candidate Details Modal -->
    <div id="jms-candidate-modal" class="jms-modal">
        <div class="jms-modal-content jms-modal-large">
            <span class="jms-modal-close">&times;</span>
            <h2 id="jms-candidate-modal-title"><?php _e('Candidate Details', 'job-management-system'); ?></h2>
            <div class="jms-modal-notices"></div>
            <div id="jms-candidate-details">
                <div class="jms-candidate-loading"><?php _e('Loading candidate details...', 'job-management-system'); ?></div>
                <div class="jms-candidate-content" style="display: none;">
                    <div class="jms-candidate-header">
                        <div class="jms-candidate-info">
                            <h3 class="jms-candidate-name"></h3>
                            <div class="jms-candidate-job"></div>
                            <div class="jms-candidate-contact">
                                <div class="jms-candidate-email"></div>
                                <div class="jms-candidate-phone"></div>
                            </div>
                        </div>
                        <div class="jms-candidate-status-section">
                            <div class="jms-candidate-status"></div>
                            <div class="jms-candidate-date"></div>
                        </div>
                    </div>
                    
                    <div class="jms-candidate-tabs">
                        <ul class="jms-tabs-nav">
                            <li class="jms-tab-active" data-tab="profile"><?php _e('Profile', 'job-management-system'); ?></li>
                            <li data-tab="resume"><?php _e('Resume', 'job-management-system'); ?></li>
                            <li data-tab="interviews"><?php _e('Interviews', 'job-management-system'); ?></li>
                            <li data-tab="documents"><?php _e('Documents', 'job-management-system'); ?></li>
                        </ul>
                        
                        <div class="jms-tab-content jms-tab-profile jms-tab-active">
                            <div class="jms-profile-section">
                                <h4><?php _e('Experience', 'job-management-system'); ?></h4>
                                <div class="jms-candidate-experience"></div>
                            </div>
                            <div class="jms-profile-section">
                                <h4><?php _e('Education', 'job-management-system'); ?></h4>
                                <div class="jms-candidate-education"></div>
                            </div>
                            <div class="jms-profile-section">
                                <h4><?php _e('Skills', 'job-management-system'); ?></h4>
                                <div class="jms-candidate-skills"></div>
                            </div>
                        </div>
                        
                        <div class="jms-tab-content jms-tab-resume">
                            <div class="jms-resume-actions">
                                <a href="#" class="jms-button jms-view-resume" target="_blank"><?php _e('View Resume', 'job-management-system'); ?></a>
                                <a href="#" class="jms-button jms-download-resume"><?php _e('Download Resume', 'job-management-system'); ?></a>
                            </div>
                            <div class="jms-resume-preview">
                                <iframe id="jms-resume-iframe" src="" width="100%" height="500px"></iframe>
                            </div>
                        </div>
                        
                        <div class="jms-tab-content jms-tab-interviews">
                            <div class="jms-interviews-list"></div>
                            <div class="jms-schedule-interview">
                                <h4><?php _e('Schedule Interview', 'job-management-system'); ?></h4>
                                <form id="jms-interview-form">
                                    <input type="hidden" id="jms-interview-candidate-id" name="candidate_id" value="">
                                    <input type="hidden" id="jms-interview-job-id" name="job_id" value="">
                                    
                                    <div class="jms-form-row">
                                        <label for="jms-interview-date"><?php _e('Interview Date & Time', 'job-management-system'); ?> <span class="required">*</span></label>
                                        <input type="datetime-local" id="jms-interview-date" name="interview_date" required>
                                    </div>
                                    
                                    <div class="jms-form-row">
                                        <label for="jms-interview-type"><?php _e('Interview Type', 'job-management-system'); ?> <span class="required">*</span></label>
                                        <select id="jms-interview-type" name="interview_type" required>
                                            <option value="in_person"><?php _e('In Person', 'job-management-system'); ?></option>
                                            <option value="phone"><?php _e('Phone', 'job-management-system'); ?></option>
                                            <option value="google_meet"><?php _e('Google Meet', 'job-management-system'); ?></option>
                                        </select>
                                    </div>
                                    
                                    <div class="jms-form-row">
                                        <label for="jms-interview-notes"><?php _e('Notes', 'job-management-system'); ?></label>
                                        <textarea id="jms-interview-notes" name="notes" rows="3"></textarea>
                                    </div>
                                    
                                    <div class="jms-form-actions">
                                        <button type="submit" class="button button-primary"><?php _e('Schedule Interview', 'job-management-system'); ?></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div class="jms-tab-content jms-tab-documents">
                            <div class="jms-documents-list"></div>
                            <div class="jms-document-request">
                                <h4><?php _e('Request Documents', 'job-management-system'); ?></h4>
                                <form id="jms-document-request-form">
                                    <input type="hidden" id="jms-document-candidate-id" name="candidate_id" value="">
                                    
                                    <div class="jms-form-row">
                                        <label><?php _e('Document Types', 'job-management-system'); ?> <span class="required">*</span></label>
                                        <div class="jms-document-types-list">
                                            <?php
                                            $jms_documents = new JMS_Documents();
                                            $document_types = $jms_documents->get_document_types();
                                            
                                            foreach ($document_types as $type => $label) {
                                                echo '<div class="jms-document-type-option">';
                                                echo '<input type="checkbox" id="jms-document-type-' . esc_attr($type) . '" name="document_types[]" value="' . esc_attr($type) . '">';
                                                echo '<label for="jms-document-type-' . esc_attr($type) . '">' . esc_html($label) . '</label>';
                                                echo '</div>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    
                                    <div class="jms-form-actions">
                                        <button type="submit" class="button button-primary"><?php _e('Send Request', 'job-management-system'); ?></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="jms-candidate-actions">
                        <h4><?php _e('Update Status', 'job-management-system'); ?></h4>
                        <div class="jms-status-buttons">
                            <button class="button jms-status-button" data-status="shortlisted"><?php _e('Shortlist', 'job-management-system'); ?></button>
                            <button class="button jms-status-button" data-status="interviewed"><?php _e('Mark as Interviewed', 'job-management-system'); ?></button>
                            <button class="button jms-status-button" data-status="offered"><?php _e('Extend Offer', 'job-management-system'); ?></button>
                            <button class="button jms-status-button" data-status="hired"><?php _e('Mark as Hired', 'job-management-system'); ?></button>
                            <button class="button jms-status-button jms-reject-button" data-status="rejected"><?php _e('Reject', 'job-management-system'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
