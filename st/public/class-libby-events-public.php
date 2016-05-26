<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://stboston.com
 * @since      1.0.0
 *
 * @package    Libby_Events
 * @subpackage Libby_Events/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Libby_Events
 * @subpackage Libby_Events/public
 * @author     Stirling Technologies <brian@stboston.com>
 */
class Libby_Events_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The postvars collected during form submission
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $post_vars
	 */
	private $post_vars;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		add_shortcode( 'event_booking_form', array( $this, 'register_booking_form_shortcode' ) );
		add_shortcode( 'mini_calendar_with_list', array( $this, 'register_calendar_with_list_shortcode' ) );
		add_shortcode( 'booking-form', array( $this, 'register_booking_form_shortcode_new') );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_register_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/libby-events-public.css', array(), $this->version, 'all' );
		wp_register_style( $this->plugin_name . '-fc', plugin_dir_url( dirname( __FILE__ ) ) . 'bower_components/fullcalendar/dist/fullcalendar.min.css', array(), $this->version, 'all' );
		wp_register_style( $this->plugin_name . '-fc-scheduler', plugin_dir_url( dirname( __FILE__ ) ) . 'bower_components/fullcalendar-scheduler/dist/scheduler.min.css', array( $this->plugin_name . '-fc' ), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/libby-events-public.js', array( 'jquery', 'jquery-ui-datepicker', $this->plugin_name . '-fc-scheduler', 'wp-util' ), $this->version, false );
		wp_register_script( $this->plugin_name . '-moment',  plugin_dir_url( dirname( __FILE__ ) ) . 'bower_components/moment/min/moment.min.js', array('jquery'), $this->version, true );
		wp_register_script( $this->plugin_name . '-fc',  plugin_dir_url( dirname( __FILE__ ) ) . 'bower_components/fullcalendar/dist/fullcalendar.min.js', array( $this->plugin_name . '-moment' ), $this->version, true );
		wp_register_script( $this->plugin_name . '-fc-scheduler',  plugin_dir_url( dirname( __FILE__ ) ) . 'bower_components/fullcalendar-scheduler/dist/scheduler.min.js', array( $this->plugin_name . '-fc' ), $this->version, true );
		// wp_localize_script( $this->plugin_name, 'fcResources', $this->get_scheduler_resource_array() );
		wp_localize_script( $this->plugin_name , 'ajax_url', admin_url( 'admin-ajax.php' ) );
	}

	function register_booking_form_shortcode_new() {

		wp_enqueue_script( $this->plugin_name . '-fc-scheduler' );
		wp_enqueue_style( $this->plugin_name . '-fc-scheduler' );
		// wp_enqueue_script( $this->plugin_name . '-fes' );
		// wp_enqueue_script( 'eo_front' );
		// wp_enqueue_style( 'eo_front' );
		wp_enqueue_script( $this->plugin_name );
		wp_enqueue_style( $this->plugin_name );

		$venues = eo_get_venues();
		$categories = get_terms( array(
			'taxonomy' => 'event-category',
			'hide_empty' => false,
			'hierarchical' => false
		) );
		$group_types = get_terms( array(
			'taxonomy' => 'group_type',
			'hide_empty' => false,
			'hierarchical' => false
		) );
		require_once plugin_dir_path( __FILE__ ) . '/shortcodes/booking-form.php';
	}

	function register_calendar_with_list_shortcode() {
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_localize_script( 'jquery-ui-datepicker', 'ajaxurl', admin_url( 'admin-ajax.php' ) );
		$todays_events = $this->get_events_by_day();
		include_once plugin_dir_path( __FILE__ ) . '/shortcodes/calendar-with-list.php';
	}

	/**
	 * Retrieve the venue hours based on the date and the
	 * @param  int $venue_id the ID of the venue
	 * @return array $hours The hour types
	 */
	public function get_venue_hours( $venue_id, $date ) {
		$branch_id = eo_get_venue_meta( (int)$venue_id, '_libby_branch', true );
		$hours = libpress_get_hours( (int)$branch_id );
		return $hours[0]['hours'];
	}

	/**
	 * Retrieve all the venue information via AJAX for the booking JS actions
	 * based on the selection by the user in the venue dropdown
	 * @return json $venue_options
	 */
	public function get_venue_details_ajax() {
		$venue_id = $_GET['venueId'];
		$venue_options = array(
			'description' => eo_get_venue_description( (int)$venue_id ),
			'setup' => eo_get_venue_meta( $venue_id, '_libby_setup_options', true ),
			'equipment' => eo_get_venue_meta( $venue_id, '_libby_available_equipment', true ),
			'events' => $this->get_venue_events( $venue_id ),
			'hours' => $this->get_venue_hours( $venue_id )
		);
		echo wp_json_encode( $venue_options );
		wp_die();
	}

	/**
	 * Get an events array that the scheduler
	 * can use to display events
	 * @return array $events An array of events that gets sent to the calendar
	 */
	public function get_venue_events( $venue_id = null ) {
		// We need the slug to use eo_get_events().
		$venue = get_term_by( 'id', (int)$venue_id, 'event-venue' );
		$venue_slug = isset($venue->slug) ? $venue->slug : false;
		$eo_events = eo_get_events(array(
			'event-venue' => $venue_slug
		));
		$events = array();
		foreach ( $eo_events as $event ){
			$events[] = array(
				'id' => $event->ID,
				'title' => $event->post_title,
				'start' => eo_get_the_start( 'Y-m-d H:i:s', $event->ID, null, $event->occurrence_id),
				'end' => eo_get_the_end( 'Y-m-d H:i:s', $event->ID, null, $event->occurrence_id ),
				// 'rendering' => 'inverse-background'
			);
		}
		return $events;
	}

	public function eo_fes_venue_info_display( $element ) {
		wp_enqueue_script( $this->plugin_name );
		wp_enqueue_style( $this->plugin_name );
		include_once FORM_FIELD_TEMPLATE_DIR . 'venue-information.php';
	}

	/**
	 * Modify the output to show start/end times with scheduler
	 * @return [type] [description]
	 */
	public function eo_fes_start_end_display( $element ) {
		wp_enqueue_script( $this->plugin_name . '-fc-scheduler' );
		wp_enqueue_style( $this->plugin_name . '-fc-scheduler' );
		wp_enqueue_script( $this->plugin_name );
		wp_enqueue_style( $this->plugin_name );
		include_once FORM_FIELD_TEMPLATE_DIR . 'booking-calendar.php';
	}

	public function eo_fes_taxonomy_display() {
		$group_types = get_terms(array(
			'taxonomy' => 'group_type',
			'hide_empty' => false
		));
		include_once FORM_FIELD_TEMPLATE_DIR . 'group-type.php';
	}

	/**
	 * Get the post vars from the form submittion and assign to the class
	 * to be added after successful form submission
	 * @param  array $form All the form vars
	 *
	 * @see vendor/event-organizer-fes/includes/actions.php
	 */
	public function eo_fes_process_form_submission( $form ) {
		// wp_die(var_dump($_POST));
		if ( isset( $_POST['libby'] ) ) {
			$this->post_vars = $_POST['libby'];
		}
	}

	/**
	 * Update/add any of our custom meta to the event
	 * @param  int $event_id The ID of the event being created/updated
	 * @param  array $event  The array of all the event data being saved by EO
	 * @param  array $form   All the form parameters
	 */
	public function eo_fes_save_custom_vars( $event_id, $event, $form ) {
		foreach ( $this->post_vars as $key => $value ) {
			if ( $key === 'group_type' ) {
				wp_set_object_terms( $event_id, (int)$value, 'group_type' );
			}
			else {
				update_post_meta( $event_id, $key, $value );
			}
		}
	}

	/**
	 * Filter out the available options on the booking form
	 * so we only display meeting rooms.
	 * @return [type] [description]
	 */
	public function booking_form_filter_meeting_rooms( $terms, $taxonomies, $args ) {
		//Only hide it for public facing pages, and only if they can't manage venues
		if( is_admin() ){
			return $terms;
		}

		if( ( is_array( $taxonomies ) && !in_array( 'event-venue', $taxonomies ) ) || ( !is_array( $taxonomies ) && $taxonomies != 'event-venue' ) ){
			return $terms;
		}

		// We'll check if we're using the booking form shortcode, so we can filter out the return array.
		global $post;

		if( has_shortcode( $post->post_content, 'event_submission_form' ) ) {
			foreach ( $terms as $key => $term ){
				$venue_id = !is_object( $term ) ? $term : intval( $term->term_id );
				if ( eo_get_venue_meta( $venue_id, '_libby_type', true ) !== 'meeting_room' ){
					unset( $terms[$key] );
				}
				elseif ( eo_get_venue_meta( $venue_id, '_libby_staff_only', true ) && !is_user_logged_in() ) {
					unset( $terms[$key] );
				}
			}
		}

		return $terms;

	}

}
