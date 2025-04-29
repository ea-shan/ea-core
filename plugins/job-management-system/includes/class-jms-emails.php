<?php
/**
 * Email notification functionality for the Job Management System.
 *
 * @link       https://expressanalytics.net
 * @since      1.0.0
 *
 * @package    Job_Management_System
 * @subpackage Job_Management_System/includes
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Email notification class.
 */
class JMS_Emails {

    /**
     * Initialize the class.
     */
    public function __construct() {
        // Nothing to initialize
    }

    /**
     * Send application confirmation email to candidate.
     *
     * @param int $candidate_id The candidate ID.
     * @return bool Whether the email was sent successfully.
     */
    public function send_application_confirmation($candidate_id) {
        // Get candidate
        $jms_candidates = new JMS_Candidates();
        $candidate = $jms_candidates->get_candidate($candidate_id);
        
        if (!$candidate) {
            return false;
        }
        
        // Get job
        $jms_jobs = new JMS_Jobs();
        $job = $jms_jobs->get_job($candidate->job_id);
        
        if (!$job) {
            return false;
        }
        
        // Get email template
        $subject = $this->get_email_subject('application_confirmation');
        $message = $this->get_email_template('application_confirmation');
        
        // Replace placeholders
        $subject = $this->replace_placeholders($subject, array(
            'job_title' => $job->title,
            'company_name' => get_bloginfo('name')
        ));
        
        $message = $this->replace_placeholders($message, array(
            'candidate_name' => $candidate->name,
            'job_title' => $job->title,
            'company_name' => get_bloginfo('name'),
            'application_date' => date_i18n(get_option('date_format'), strtotime($candidate->application_date))
        ));
        
        // Send email
        return $this->send_email($candidate->email, $subject, $message);
    }

    /**
     * Send candidate status notification email.
     *
     * @param object $candidate The candidate object.
     * @param string $status The new status.
     * @return bool Whether the email was sent successfully.
     */
    public function send_candidate_status_notification($candidate, $status) {
        if (!$candidate) {
            return false;
        }
        
        // Get job
        $jms_jobs = new JMS_Jobs();
        $job = $jms_jobs->get_job($candidate->job_id);
        
        if (!$job) {
            return false;
        }
        
        // Get email template based on status
        $template_key = 'status_' . $status;
        $subject = $this->get_email_subject($template_key);
        $message = $this->get_email_template($template_key);
        
        // If template doesn't exist, use generic status update template
        if (empty($message)) {
            $subject = $this->get_email_subject('status_update');
            $message = $this->get_email_template('status_update');
        }
        
        // Replace placeholders
        $subject = $this->replace_placeholders($subject, array(
            'job_title' => $job->title,
            'company_name' => get_bloginfo('name')
        ));
        
        $message = $this->replace_placeholders($message, array(
            'candidate_name' => $candidate->name,
            'job_title' => $job->title,
            'company_name' => get_bloginfo('name'),
            'status' => $this->get_status_label($status)
        ));
        
        // Send email
        return $this->send_email($candidate->email, $subject, $message);
    }

    /**
     * Send interview notification email.
     *
     * @param object $candidate The candidate object.
     * @param int $interview_id The interview ID.
     * @param string $google_meet_link The Google Meet link (optional).
     * @return bool Whether the email was sent successfully.
     */
    public function send_interview_notification($candidate, $interview_id, $google_meet_link = '') {
        if (!$candidate || !$interview_id) {
            return false;
        }
        
        // Get job
        $jms_jobs = new JMS_Jobs();
        $job = $jms_jobs->get_job($candidate->job_id);
        
        if (!$job) {
            return false;
        }
        
        // Get interview details
        global $wpdb;
        $interviews_table = $wpdb->prefix . 'jms_interviews';
        
        $interview = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $interviews_table WHERE id = %d",
            $interview_id
        ));
        
        if (!$interview) {
            return false;
        }
        
        // Get email template
        $template_key = 'interview_' . $interview->interview_type;
        $subject = $this->get_email_subject($template_key);
        $message = $this->get_email_template($template_key);
        
        // If template doesn't exist, use generic interview template
        if (empty($message)) {
            $subject = $this->get_email_subject('interview_scheduled');
            $message = $this->get_email_template('interview_scheduled');
        }
        
        // Format interview date
        $interview_date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($interview->interview_date));
        
        // Replace placeholders
        $subject = $this->replace_placeholders($subject, array(
            'job_title' => $job->title,
            'company_name' => get_bloginfo('name')
        ));
        
        $message = $this->replace_placeholders($message, array(
            'candidate_name' => $candidate->name,
            'job_title' => $job->title,
            'company_name' => get_bloginfo('name'),
            'interview_date' => $interview_date,
            'interview_type' => $this->get_interview_type_label($interview->interview_type),
            'google_meet_link' => $google_meet_link,
            'interview_notes' => $interview->notes
        ));
        
        // Send email
        return $this->send_email($candidate->email, $subject, $message);
    }

    /**
     * Send document request email.
     *
     * @param object $candidate The candidate object.
     * @param array $document_types The requested document types.
     * @return bool Whether the email was sent successfully.
     */
    public function send_document_request($candidate, $document_types) {
        if (!$candidate || empty($document_types)) {
            return false;
        }
        
        // Get job
        $jms_jobs = new JMS_Jobs();
        $job = $jms_jobs->get_job($candidate->job_id);
        
        if (!$job) {
            return false;
        }
        
        // Get document type labels
        $jms_documents = new JMS_Documents();
        $all_document_types = $jms_documents->get_document_types();
        
        $document_list = '';
        foreach ($document_types as $type) {
            $label = isset($all_document_types[$type]) ? $all_document_types[$type] : $type;
            $document_list .= '- ' . $label . "\n";
        }
        
        // Get email template
        $subject = $this->get_email_subject('document_request');
        $message = $this->get_email_template('document_request');
        
        // Replace placeholders
        $subject = $this->replace_placeholders($subject, array(
            'job_title' => $job->title,
            'company_name' => get_bloginfo('name')
        ));
        
        $message = $this->replace_placeholders($message, array(
            'candidate_name' => $candidate->name,
            'job_title' => $job->title,
            'company_name' => get_bloginfo('name'),
            'document_list' => $document_list,
            'dashboard_link' => site_url('/candidate-dashboard/')
        ));
        
        // Send email
        return $this->send_email($candidate->email, $subject, $message);
    }

    /**
     * Send document status notification email.
     *
     * @param object $candidate The candidate object.
     * @param object $document The document object.
     * @param string $status The new status.
     * @return bool Whether the email was sent successfully.
     */
    public function send_document_status_notification($candidate, $document, $status) {
        if (!$candidate || !$document) {
            return false;
        }
        
        // Get job
        $jms_jobs = new JMS_Jobs();
        $job = $jms_jobs->get_job($candidate->job_id);
        
        if (!$job) {
            return false;
        }
        
        // Get document type label
        $jms_documents = new JMS_Documents();
        $document_types = $jms_documents->get_document_types();
        $document_type = isset($document_types[$document->document_type]) ? $document_types[$document->document_type] : $document->document_type;
        
        // Get email template
        $template_key = 'document_' . $status;
        $subject = $this->get_email_subject($template_key);
        $message = $this->get_email_template($template_key);
        
        // Replace placeholders
        $subject = $this->replace_placeholders($subject, array(
            'job_title' => $job->title,
            'company_name' => get_bloginfo('name')
        ));
        
        $message = $this->replace_placeholders($message, array(
            'candidate_name' => $candidate->name,
            'job_title' => $job->title,
            'company_name' => get_bloginfo('name'),
            'document_type' => $document_type,
            'document_notes' => $document->notes,
            'dashboard_link' => site_url('/candidate-dashboard/')
        ));
        
        // Send email
        return $this->send_email($candidate->email, $subject, $message);
    }

    /**
     * Get email subject.
     *
     * @param string $template_key The template key.
     * @return string The email subject.
     */
    private function get_email_subject($template_key) {
        $subjects = array(
            'application_confirmation' => 'Application Received: {job_title} - {company_name}',
            'status_update' => 'Application Status Update: {job_title} - {company_name}',
            'status_shortlisted' => 'You\'ve Been Shortlisted: {job_title} - {company_name}',
            'status_interview_scheduled' => 'Interview Scheduled: {job_title} - {company_name}',
            'status_interviewed' => 'Interview Completed: {job_title} - {company_name}',
            'status_offered' => 'Job Offer: {job_title} - {company_name}',
            'status_hired' => 'Welcome to {company_name}!',
            'status_rejected' => 'Application Update: {job_title} - {company_name}',
            'interview_scheduled' => 'Interview Scheduled: {job_title} - {company_name}',
            'interview_in_person' => 'In-Person Interview: {job_title} - {company_name}',
            'interview_phone' => 'Phone Interview: {job_title} - {company_name}',
            'interview_google_meet' => 'Google Meet Interview: {job_title} - {company_name}',
            'document_request' => 'Document Request: {job_title} - {company_name}',
            'document_approved' => 'Document Approved: {job_title} - {company_name}',
            'document_rejected' => 'Document Needs Attention: {job_title} - {company_name}'
        );
        
        return isset($subjects[$template_key]) ? $subjects[$template_key] : 'Update from {company_name}';
    }

    /**
     * Get email template.
     *
     * @param string $template_key The template key.
     * @return string The email template.
     */
    private function get_email_template($template_key) {
        $templates = array(
            'application_confirmation' => "Dear {candidate_name},\n\nThank you for applying for the position of {job_title} at {company_name}. We have received your application on {application_date}.\n\nOur team will review your application and get back to you soon. If your qualifications match our requirements, we will contact you for the next steps in the hiring process.\n\nThank you for your interest in joining our team.\n\nBest regards,\nHR Team\n{company_name}",
            
            'status_update' => "Dear {candidate_name},\n\nWe would like to inform you that your application status for the position of {job_title} at {company_name} has been updated to: {status}.\n\nIf you have any questions, please don't hesitate to contact us.\n\nBest regards,\nHR Team\n{company_name}",
            
            'status_shortlisted' => "Dear {candidate_name},\n\nWe are pleased to inform you that your application for the position of {job_title} at {company_name} has been shortlisted.\n\nOur team was impressed with your qualifications and experience. We will be in touch soon to schedule an interview.\n\nThank you for your interest in joining our team.\n\nBest regards,\nHR Team\n{company_name}",
            
            'status_interviewed' => "Dear {candidate_name},\n\nThank you for attending the interview for the position of {job_title} at {company_name}.\n\nWe appreciate the time you took to discuss your qualifications and experience with us. Our team is currently evaluating all candidates, and we will be in touch soon with the next steps.\n\nThank you for your interest in joining our team.\n\nBest regards,\nHR Team\n{company_name}",
            
            'status_offered' => "Dear {candidate_name},\n\nWe are pleased to inform you that we would like to offer you the position of {job_title} at {company_name}.\n\nWe were impressed with your qualifications, experience, and performance during the interview process. We believe you would be a valuable addition to our team.\n\nA formal offer letter with detailed terms and conditions will be sent to you shortly. Please review it carefully and let us know if you have any questions.\n\nWe look forward to welcoming you to our team.\n\nBest regards,\nHR Team\n{company_name}",
            
            'status_hired' => "Dear {candidate_name},\n\nCongratulations and welcome to {company_name}!\n\nWe are excited to have you join our team as {job_title}. We believe your skills and experience will be a great addition to our company.\n\nPlease complete the onboarding documents in your candidate dashboard to help us prepare for your arrival.\n\nWe look forward to working with you.\n\nBest regards,\nHR Team\n{company_name}",
            
            'status_rejected' => "Dear {candidate_name},\n\nThank you for your interest in the {job_title} position at {company_name} and for taking the time to go through our application process.\n\nAfter careful consideration, we have decided to move forward with other candidates whose qualifications better match our current needs.\n\nWe appreciate your interest in our company and wish you the best in your job search.\n\nBest regards,\nHR Team\n{company_name}",
            
            'interview_scheduled' => "Dear {candidate_name},\n\nWe are pleased to inform you that we would like to invite you for an interview for the position of {job_title} at {company_name}.\n\nInterview Details:\nDate and Time: {interview_date}\nType: {interview_type}\n\n{interview_notes}\n\nPlease confirm your availability by replying to this email. If you need to reschedule, please let us know as soon as possible.\n\nWe look forward to meeting you.\n\nBest regards,\nHR Team\n{company_name}",
            
            'interview_in_person' => "Dear {candidate_name},\n\nWe are pleased to inform you that we would like to invite you for an in-person interview for the position of {job_title} at {company_name}.\n\nInterview Details:\nDate and Time: {interview_date}\nType: In-Person Interview\n\n{interview_notes}\n\nPlease confirm your availability by replying to this email. If you need to reschedule, please let us know as soon as possible.\n\nWe look forward to meeting you.\n\nBest regards,\nHR Team\n{company_name}",
            
            'interview_phone' => "Dear {candidate_name},\n\nWe are pleased to inform you that we would like to invite you for a phone interview for the position of {job_title} at {company_name}.\n\nInterview Details:\nDate and Time: {interview_date}\nType: Phone Interview\n\nOur HR representative will call you at the phone number provided in your application.\n\n{interview_notes}\n\nPlease confirm your availability by replying to this email. If you need to reschedule, please let us know as soon as possible.\n\nWe look forward to speaking with you.\n\nBest regards,\nHR Team\n{company_name}",
            
            'interview_google_meet' => "Dear {candidate_name},\n\nWe are pleased to inform you that we would like to invite you for a Google Meet interview for the position of {job_title} at {company_name}.\n\nInterview Details:\nDate and Time: {interview_date}\nType: Google Meet Interview\nLink: {google_meet_link}\n\nPlease click on the Google Meet link at the scheduled time to join the interview. Make sure your camera and microphone are working properly before the interview.\n\n{interview_notes}\n\nPlease confirm your availability by replying to this email. If you need to reschedule, please let us know as soon as possible.\n\nWe look forward to meeting you virtually.\n\nBest regards,\nHR Team\n{company_name}",
            
            'document_request' => "Dear {candidate_name},\n\nAs part of the hiring process for the position of {job_title} at {company_name}, we need you to submit the following documents:\n\n{document_list}\n\nPlease upload these documents through your candidate dashboard: {dashboard_link}\n\nIf you have any questions or need assistance, please don't hesitate to contact us.\n\nThank you for your cooperation.\n\nBest regards,\nHR Team\n{company_name}",
            
            'document_approved' => "Dear {candidate_name},\n\nWe are pleased to inform you that your document ({document_type}) for the position of {job_title} at {company_name} has been approved.\n\nThank you for your prompt submission.\n\nYou can view the status of all your documents in your candidate dashboard: {dashboard_link}\n\nBest regards,\nHR Team\n{company_name}",
            
            'document_rejected' => "Dear {candidate_name},\n\nWe need to inform you that your document ({document_type}) for the position of {job_title} at {company_name} has been rejected.\n\nReason: {document_notes}\n\nPlease upload a new version of this document through your candidate dashboard: {dashboard_link}\n\nIf you have any questions or need assistance, please don't hesitate to contact us.\n\nThank you for your cooperation.\n\nBest regards,\nHR Team\n{company_name}"
        );
        
        return isset($templates[$template_key]) ? $templates[$template_key] : '';
    }

    /**
     * Replace placeholders in a string.
     *
     * @param string $string The string with placeholders.
     * @param array $replacements The replacements array.
     * @return string The string with replaced placeholders.
     */
    private function replace_placeholders($string, $replacements) {
        foreach ($replacements as $placeholder => $value) {
            $string = str_replace('{' . $placeholder . '}', $value, $string);
        }
        
        return $string;
    }

    /**
     * Send email.
     *
     * @param string $to The recipient email.
     * @param string $subject The email subject.
     * @param string $message The email message.
     * @return bool Whether the email was sent successfully.
     */
    private function send_email($to, $subject, $message) {
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        
        // Get from email
        $from_email = get_option('admin_email');
        $from_name = get_bloginfo('name');
        
        $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
        
        // Send email
        return wp_mail($to, $subject, $message, $headers);
    }

    /**
     * Get status label.
     *
     * @param string $status The status.
     * @return string The status label.
     */
    private function get_status_label($status) {
        $labels = array(
            'applied' => 'Applied',
            'shortlisted' => 'Shortlisted',
            'interview_scheduled' => 'Interview Scheduled',
            'interviewed' => 'Interviewed',
            'offered' => 'Offer Extended',
            'hired' => 'Hired',
            'rejected' => 'Not Selected'
        );
        
        return isset($labels[$status]) ? $labels[$status] : $status;
    }

    /**
     * Get interview type label.
     *
     * @param string $type The interview type.
     * @return string The interview type label.
     */
    private function get_interview_type_label($type) {
        $labels = array(
            'in_person' => 'In Person',
            'phone' => 'Phone',
            'google_meet' => 'Google Meet'
        );
        
        return isset($labels[$type]) ? $labels[$type] : $type;
    }
}
