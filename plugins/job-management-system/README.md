# WordPress Job Management System Plugin

A comprehensive WordPress plugin for job posting, candidate management, interview scheduling, and onboarding document management.

## Features

- **Job Posting Management**
  - Create and manage job listings
  - Set job status (open/closed)
  - Define job details (title, description, requirements, location, salary range)

- **Candidate Application System**
  - Application form with resume upload
  - Candidate dashboard for application status tracking
  - Resume/CV upload in PDF, DOC, DOCX formats

- **Admin Dashboard**
  - Filter candidates by job, status, and search terms
  - View candidate profiles and resumes
  - Update candidate status (applied, shortlisted, interviewed, etc.)
  - Schedule interviews with Google Meet integration

- **Email Notifications**
  - Customizable email templates
  - Notifications for application confirmation, status updates, interviews, etc.
  - Professional email formatting

- **Google Meet Integration**
  - Schedule virtual interviews
  - Generate and share meeting links
  - Track interview status

- **Onboarding Document Management**
  - Request and manage required documents
  - Document approval/rejection workflow
  - Support for various document types (ID proofs, certificates, etc.)

## Installation

1. Upload the `job-management-system` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin settings under 'Job Management' in the admin menu

## Usage

### For Administrators

1. **Managing Jobs**
   - Go to Job Management > Jobs to create and manage job listings
   - Fill in all required details and set job status

2. **Managing Candidates**
   - Go to Job Management > Candidates to view and manage applications
   - Use filters to find specific candidates
   - Click on a candidate to view their profile, resume, and manage their status

3. **Scheduling Interviews**
   - From the candidate details page, go to the Interviews tab
   - Schedule new interviews with date, time, and type (in-person, phone, Google Meet)
   - For Google Meet interviews, a meeting link will be automatically generated

4. **Managing Documents**
   - From the candidate details page, go to the Documents tab
   - Request documents from candidates
   - Review, approve, or reject submitted documents

### For Candidates

1. **Applying for Jobs**
   - Browse available jobs on the Jobs page
   - Click on a job to view details
   - Fill in the application form and upload resume

2. **Tracking Application Status**
   - Log in to view the Candidate Dashboard
   - Check application status and interview details
   - Upload requested documents for onboarding

## Shortcodes

- `[jms_job_list]` - Displays a list of available jobs
- `[jms_job_details id="X"]` - Displays details for a specific job
- `[jms_job_application_form job_id="X"]` - Displays the application form for a specific job
- `[jms_candidate_dashboard]` - Displays the candidate dashboard (for logged-in users)

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- MySQL 5.6 or higher

## Support

For support or feature requests, please contact info@expressanalytics.net

## License

This plugin is licensed under the GPL v2 or later.

---

Developed by Express Analytics
