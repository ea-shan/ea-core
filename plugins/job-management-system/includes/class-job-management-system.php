<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since      1.0.0
 * @package    Job_Management_System
 * @subpackage Job_Management_System/includes
 */

class Job_Management_System
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      JMS_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        $this->version = JMS_VERSION;
        $this->plugin_name = 'job-management-system';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - JMS_Loader. Orchestrates the hooks of the plugin.
     * - JMS_i18n. Defines internationalization functionality.
     * - JMS_Admin. Defines all hooks for the admin area.
     * - JMS_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once JMS_PLUGIN_DIR . 'includes/class-jms-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once JMS_PLUGIN_DIR . 'includes/class-jms-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once JMS_PLUGIN_DIR . 'admin/class-jms-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once JMS_PLUGIN_DIR . 'includes/class-jms-public.php';

        /**
         * The class responsible for handling job-related functionality.
         */
        require_once JMS_PLUGIN_DIR . 'includes/class-jms-jobs.php';

        /**
         * The class responsible for handling candidate-related functionality.
         */
        require_once JMS_PLUGIN_DIR . 'includes/class-jms-candidates.php';

        /**
         * The class responsible for handling interview scheduling.
         */
        require_once JMS_PLUGIN_DIR . 'includes/class-jms-interviews.php';

        /**
         * The class responsible for handling onboarding documents.
         */
        require_once JMS_PLUGIN_DIR . 'includes/class-jms-documents.php';

        /**
         * The class responsible for handling email notifications.
         */
        require_once JMS_PLUGIN_DIR . 'includes/class-jms-emails.php';

        /**
         * The class responsible for Google Calendar and Meet integration.
         */
        require_once JMS_PLUGIN_DIR . 'includes/class-jms-google-integration.php';

        /**
         * The class responsible for handling AJAX requests.
         */
        require_once JMS_PLUGIN_DIR . 'includes/class-jms-ajax-handler.php';

        $this->loader = new JMS_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the JMS_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {
        $plugin_i18n = new JMS_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new JMS_Admin($this->get_plugin_name(), $this->get_version());

        // Admin scripts and styles
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        // Admin menu pages
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');

        // Admin AJAX handlers
        $this->loader->add_action('wp_ajax_jms_admin_get_candidates', $plugin_admin, 'ajax_get_candidates');
        $this->loader->add_action('wp_ajax_jms_admin_update_candidate_status', $plugin_admin, 'ajax_update_candidate_status');
        $this->loader->add_action('wp_ajax_jms_admin_schedule_interview', $plugin_admin, 'ajax_schedule_interview');
        $this->loader->add_action('wp_ajax_jms_admin_manage_documents', $plugin_admin, 'ajax_manage_documents');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {
        $plugin_public = new JMS_Public($this->get_plugin_name(), $this->get_version());

        // Public scripts and styles
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

        // Register custom post type for jobs
        $this->loader->add_action('init', $plugin_public, 'register_job_post_type');

        // Register shortcodes
        $this->loader->add_action('init', $plugin_public, 'register_shortcodes');

        // Public AJAX handlers
        $this->loader->add_action('wp_ajax_jms_submit_application', $plugin_public, 'ajax_submit_application');
        $this->loader->add_action('wp_ajax_nopriv_jms_submit_application', $plugin_public, 'ajax_submit_application');

        // Handle file uploads
        $this->loader->add_action('wp_ajax_jms_upload_document', $plugin_public, 'ajax_upload_document');
        $this->loader->add_action('wp_ajax_nopriv_jms_upload_document', $plugin_public, 'ajax_upload_document');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    JMS_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }
}
