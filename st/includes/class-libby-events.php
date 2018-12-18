<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://stboston.com
 * @since      1.0.0
 *
 * @package    Libby_Events
 * @subpackage Libby_Events/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Libby_Events
 * @subpackage Libby_Events/includes
 * @author     Stirling Technologies <brian@stboston.com>
 */

define( 'FORM_FIELD_TEMPLATE_DIR', plugin_dir_path( dirname( __FILE__ ) ) . 'public/booking-form-fields/' );
define( 'LIBBY_EVENTS_NAME', 'Libby Event Organizer' );
define( 'LIBBY_PLUGIN_SLUG', 'libby-event-organizer' );

class Libby_Events {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Libby_Events_Loader    $loader    Maintains and registers all hooks for the plugin.
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
	public function __construct() {

		$this->plugin_name = 'libby-events';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->init_update_check();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Libby_Events_Loader. Orchestrates the hooks of the plugin.
	 * - Libby_Events_i18n. Defines internationalization functionality.
	 * - Libby_Events_Admin. Defines all hooks for the admin area.
	 * - Libby_Events_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * Autoload for our composer dependencies
		 */
		// require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/event-organiser-fes/event-organiser-fes.php';

		// require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/cmb2/init.php';

		/**
		 * Load any API functions for the plugin
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/api-functions.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-libby-events-loader.php';

		/**
		 * Register all custom post types and taxonomies
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/taxonomy/group-type.php';

		/**
		 * The admin messages container
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-libby-admin-notices.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area for events.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-libby-events-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area for venues.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-libby-venue-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area for event categories.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-libby-event-category-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area for group type taxonomy.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-libby-event-group-type-admin.php';

		/**
		 * The class responsible for defining all settings hooks that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-libby-events-settings.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-libby-events-public.php';

		/**
		 * The class to register our shortcodes. Extends EventOrganiser_Shortcodes class.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-libby-events-shortcodes.php';

		/**
		 * The library responsible for checking for updates against our updates server
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/update-check/plugin-update-checker.php';

		$this->loader = new Libby_Events_Loader();

	}

	public function init_update_check() {
		$updateCheck = PucFactory::buildUpdateChecker(
		    'http://stboston.com/updates/?action=get_metadata&slug=' . LIBBY_PLUGIN_SLUG,
		    plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . 'event-organiser.php',
				LIBBY_PLUGIN_SLUG
		);

		$updateCheck->addQueryArgFilter(function( $queryArgs ){
			$queryArgs['license_key'] = md5( get_site_url() );
			return $queryArgs;
		});
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$messenger = new Libby\Events\Admin_Notice_Messenger();
		add_action( 'admin_notices', array( $messenger,  'get_messages' ) );

		$plugin_admin = new Libby_Events_Admin( $this->get_plugin_name(), $this->get_version(), $messenger );
		$this->loader->add_action( 'cmb2_admin_init', $plugin_admin, 'register_vendor_metaboxes_and_fields' );
		$this->loader->add_filter( 'manage_event_posts_columns', $plugin_admin, 'register_custom_columns', 2 );
		$this->loader->add_action( 'manage_event_posts_custom_column', $plugin_admin, 'render_custom_columns', 10, 2 );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'remove_menu_pages', 200 );
		$this->loader->add_action( 'admin_head', $plugin_admin, 'filter_admin_notices' );
		$this->loader->add_action( 'pending_to_publish', $plugin_admin, 'send_event_published_email' );
		$this->loader->add_filter( 'eventorganiser_event_properties', $plugin_admin, 'modify_event_cpt_args' );
		$this->loader->add_action( 'eventorganiser_save_event', $plugin_admin, 'validate_event', 999, 1 );
		$this->loader->add_action( 'eventorganiser_updated_event', $plugin_admin, 'validate_event', 999, 1 );
		// $this->loader->add_action( 'publish_event', $plugin_admin, 'license_check_to_save_or_update' );
		// $this->loader->add_action( 'admin_notices', $plugin_admin, 'admin_notices' );
		// $this->loader->add_action( 'redirect_post_location', $plugin_admin, 'display_draft_message_for_invalid_license', 10, 2 );

		$venue_admin = new Libby_Events_Venue_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'cmb2_admin_init', $venue_admin, 'register_custom_fields' );
		$this->loader->add_filter( 'manage_edit-event-venue_columns', $venue_admin, 'register_custom_columns' );
		$this->loader->add_action( 'manage_event-venue_custom_column', $venue_admin, 'render_custom_columns', 10, 3 );
		$this->loader->add_filter( 'eventorganiser_register_taxonomy_event-venue', $venue_admin, 'eo_filter_taxonomy_registration' );

		$event_category_admin = new Libby_Events_Event_Category_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'cmb2_admin_init', $event_category_admin, 'register_custom_fields' );

		$group_type_admin = new Libby_Events_Event_Group_Type_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'cmb2_admin_init', $group_type_admin, 'register_custom_fields' );

		$settings_page = new Libby_Events_Settings_Page();
		$this->loader->add_filter( 'eventorganiser_options', $settings_page, 'set_default_options' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Libby_Events_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_filter( 'eventorganiser_widget_event_categories_dropdown_args', $plugin_public, 'dropdown_category_args' );
		$this->loader->add_action( 'eventorganiser_widget_event_categories_args', $plugin_public, 'dropdown_category_args' );

		/**
		 * Make sure that we only use the venues designated as meeting rooms for the booking
		 *
		 * @TODO May need to adjust filter to not display meeting rooms on the front-end calendar shortcode embed
		 */
		$this->loader->add_filter( 'get_terms', $plugin_public, 'booking_form_filter_meeting_rooms', 10, 3 );

		/**
		 * Register Custom AJAX Options for Booking Form
		 */
		$this->loader->add_action( 'wp_ajax_get_venue_details_ajax', $plugin_public, 'get_venue_details_ajax' );
		$this->loader->add_action( 'wp_ajax_nopriv_get_venue_details_ajax', $plugin_public, 'get_venue_details_ajax' );
		$this->loader->add_action( 'wp_ajax_get_daily_hours_ajax', $plugin_public, 'get_daily_hours_ajax' );
		$this->loader->add_action( 'wp_ajax_nopriv_get_daily_hours_ajax', $plugin_public, 'get_daily_hours_ajax' );

		/**
		 * Register the AJAX actions for the calendar widget
		 */
		$this->loader->add_action( 'wp_ajax_get_events_ajax', $plugin_public, 'get_events_ajax' );
		$this->loader->add_action( 'wp_ajax_nopriv_get_events_ajax', $plugin_public, 'get_events_ajax' );

		/**
		 * Register the AJAX actions for the booking venue selection
		 */
		$this->loader->add_action( 'wp_ajax_get_all_venues_ajax', $plugin_public, 'get_all_venues_ajax' );
		$this->loader->add_action( 'wp_ajax_nopriv_get_all_venues_ajax', $plugin_public, 'get_all_venues_ajax' );

		/**
		 * Process requests to download an ical for a single event
		 */
		$this->loader->add_action( 'parse_request', $plugin_public, 'download_event_ical' );
		$this->loader->add_filter( 'query_vars', $plugin_public, 'add_query_vars' );

		/**
		 * Process booking form submissions with our custom values
		 */
		$this->loader->add_action( 'eventorganiser_validate_fes_form_submission', $plugin_public, 'eo_fes_process_form_submission', 10, 1 );
		$this->loader->add_action( 'eventorganiser_fes_submitted_event', $plugin_public, 'eo_fes_save_custom_vars', 10, 3 );

		/**
		 * Filter out the EO branding on the email template and replace with Libby branding.
		 *
		 */
		$this->loader->add_filter( 'eventorganiser_template_eo-email-template-event-organiser.php', $plugin_public, 'eo_override_email_template' );

		/**
		 * Add our custom actions for the booking form to be used in the form builder
		 */
		$this->loader->add_action( 'libby/events/form/group_type', $plugin_public, 'eo_fes_taxonomy_display' );
		$this->loader->add_action( 'libby/events/form/calendar', $plugin_public, 'eo_fes_start_end_display' );
		$this->loader->add_action( 'libby/events/form/venue_info', $plugin_public, 'eo_fes_venue_info_display' );
		$this->loader->add_action( 'libby/events/form/setup_breakdown_time', $plugin_public, 'eo_fes_setup_breakdown_display' );

		add_shortcode( 'libby_fullcalendar', array( 'Libby_Events_Shortcodes', 'handle_fullcalendar_shortcode_with_filter' ) );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Libby_Events_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
