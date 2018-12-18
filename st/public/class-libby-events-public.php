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
		add_shortcode( 'ical-event-subscribe', array( $this, 'register_ical_subscribe_shortcode' ) );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_register_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/libby-events-public.css', array(), $this->version, 'all' );
		wp_register_style( $this->plugin_name . '-fc', plugin_dir_url( dirname( __FILE__ ) ) . 'css/fullcalendar.min.css', array(), $this->version, 'all' );
		//wp_register_style( $this->plugin_name . '-fc-scheduler', plugin_dir_url( dirname( __FILE__ ) ) . 'bower_components/fullcalendar-scheduler/dist/scheduler.min.css', array( $this->plugin_name . '-fc' ), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_register_script( $this->plugin_name . '-booking-form', plugin_dir_url( __FILE__ ) . 'js/booking-form.js', array( 'jquery', 'jquery-ui-datepicker', $this->plugin_name . '-fc', 'wp-util' ), $this->version, false );
		wp_register_script( $this->plugin_name . '-moment',  plugin_dir_url( dirname( __FILE__ ) ) . 'js/moment.min.js', array('jquery'), $this->version, true );
		wp_register_script( $this->plugin_name . '-fc',  plugin_dir_url( dirname( __FILE__ ) ) . 'js/fullcalendar.min.js', array( $this->plugin_name . '-moment' ), $this->version, true );
		wp_register_script( $this->plugin_name . '-calendar-filter', plugin_dir_url( __FILE__ ) . 'js/calendar-filter.js', array(), $this->version, true );

		// Enable access to the ajax_url
		wp_localize_script( $this->plugin_name . '-booking-form' , 'ajax_url', admin_url( 'admin-ajax.php' ) );
		wp_localize_script( $this->plugin_name . '-calendar-filter' , 'ajax_url', admin_url( 'admin-ajax.php' ) );
	}

	function register_calendar_with_list_shortcode() {
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_localize_script( 'jquery-ui-datepicker', 'ajaxurl', admin_url( 'admin-ajax.php' ) );
		$todays_events = $this->get_events_by_day();
		include_once plugin_dir_path( __FILE__ ) . '/shortcodes/calendar-with-list.php';
	}

	/**
	 * Register the shortcode for showing the ical event download button
	 * @return [type] [description]
	 */
	public function register_ical_subscribe_shortcode() {
		return eo_get_event_ical_link();
	}

	public function get_branch_id_by_venue_id( $venue_id ) {
		return eo_get_venue_meta( (int)$venue_id, '_libby_branch', true );
	}

	/**
	 * Retrieve the venue hours based on the date and the
	 * @param  int $venue_id the ID of the venue
	 * @return array $hours The hour types
	 */
	public function get_venue_hours( $venue_id, $date = null ) {
		$branch_id = $this->get_branch_id_by_venue_id( $venue_id );
		$hours = libby_get_hours( (int)$branch_id );
		return reset($hours);
	}

	/**
	 * Retrieve all the venue information via AJAX for the booking JS actions
	 * based on the selection by the user in the venue dropdown
	 * @return json $venue_options
	 */
	public function get_venue_details_ajax() {
		if (empty($_GET['venueId']) || !is_numeric($_GET['venueId'])) {
			return new \WP_Error('invalid-request', 'venueId must be valid ID');
		}
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

	function get_events_by_day( $date = 'today' ) {
		$events = eo_get_events(array(
			'numberposts'=>20,
			'ondate'=> $date,
		));
		return $events;
	}

	function get_events_ajax() {
		$date = $_GET['date'];
		$events = $this->get_events_by_day( $date );
		echo wp_json_encode( $events );
		wp_die();
	}

	/**
	 * Get all the venues via AJAX and return JSON.
	 *
	 * Used to retrieve a list of all venues to check against hierarchial venues
	 *
	 * @return json The encoded JSON or an error
	 */
	public function get_all_venues_ajax() {
		$venues = get_terms(array(
			'taxonomy' => 'event-venue',
			'hide_empty' => false,
		));
		if ( ! is_wp_error( $venues ) ) {
			wp_send_json_success( $venues );
		}
		else {
			wp_send_json_error( $venues->get_error_message() );
		}
	}

	/**
	 * Get the hours for a particular day
	 * @return json $venue_options
	 */
	public function get_daily_hours_ajax() {
		$date = $_GET['date'];
		$venue_id = $_GET['venueId'];
		$branch_id = $this->get_branch_id_by_venue_id( $venue_id );
		$daily_hours = libpress_get_hours_by_date( $branch_id, $date );
		echo wp_json_encode( $daily_hours );
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
		$event_args = array(
			'event-venue' => $venue_slug,
			'event_start_after' => 'today',
			'post_status' => array( 'pending', 'publish' )
		);
		$eo_events = eo_get_events( $event_args );
		$events = array();
		foreach ( $eo_events as $key => $event ){
			$start = eo_get_the_start( DATETIMEOBJ, $event->ID, null, $event->occurrence_id);
			$end = eo_get_the_end( DATETIMEOBJ, $event->ID, null, $event->occurrence_id );
			$setup_time = get_post_meta( $event->ID, '_libby_setup_time', true );
			$breakdown_time = get_post_meta( $event->ID, '_libby_breakdown_time', true );
			if ( $setup_time ) {
				$start->modify( '-' . $setup_time . ' mins' );
			}
			if ( $breakdown_time ) {
				$end->modify( '+' . $breakdown_time . ' mins' );
			}
			$events[$key] = array(
				'id' => $event->ID,
				'title' => $event->post_title,
				'start' => $start->format( 'Y-m-d H:i:s' ),
				'end' => $end->format( 'Y-m-d H:i:s' )
			);
			// We set pending events to render in the background...
			if ( $event->post_status === 'pending' ) {
				$events[$key]['rendering'] = 'background';
			}
		}
		return $events;
	}

	/**
	 * Show the venue information on the booking form
	 */
	public function eo_fes_venue_info_display( $element ) {
		wp_enqueue_script( $this->plugin_name );
		wp_enqueue_style( $this->plugin_name );
		include_once FORM_FIELD_TEMPLATE_DIR . 'venue-information.php';
	}

	/**
	 * Show the booking calendar so people can select a slot after selecting
	 * a venue in eo_fes_venue_info_display()
	 */
	public function eo_fes_start_end_display( $element ) {
		wp_enqueue_script( $this->plugin_name . '-fc' );
		wp_enqueue_style( $this->plugin_name . '-fc' );
		wp_enqueue_script( $this->plugin_name . '-booking-form' );
		wp_enqueue_style( $this->plugin_name );
		include_once FORM_FIELD_TEMPLATE_DIR . 'booking-calendar.php';
	}

	/**
	 * Add any custom taxonomies to the room booking form
	 */
	public function eo_fes_taxonomy_display() {
		$group_types = get_terms(array(
			'taxonomy' => 'group-type',
			'hide_empty' => false
		));
		include_once FORM_FIELD_TEMPLATE_DIR . 'group-type.php';
	}

	/**
	 * Display the setup and breakdown time options
	 * for booking a room
	 */
	public function eo_fes_setup_breakdown_display() {
		include_once FORM_FIELD_TEMPLATE_DIR . 'setup-breakdown.php';
	}

	/**
	 * Get the post vars from the form submittion and assign to the class
	 * to be added after successful form submission
	 * @param  array $form All the form vars
	 *
	 * @see vendor/event-organizer-fes/includes/actions.php
	 */
	public function eo_fes_process_form_submission( $form ) {
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

	function eo_override_email_template( $body ) {
		$str_to_remove = 'Powered by <a href="http://wp-event-organiser.com">Event Organiser</a>';
		$body = str_replace(
			$str_to_remove,
			sprintf(
				'Powered by %2$s',
				get_site_url(),
				LIBBY_EVENTS_NAME
			),
			$body
		);
		return $body;
	}

	/**
	 * Filter out the available options on the booking form
	 * so we only display meeting rooms.
	 * @return array $terms The filtered array of terms
	 */
	public function booking_form_filter_meeting_rooms( $terms, $taxonomies, $args ) {
		//Only hide it for public facing pages, and only if they can't manage venues
		if( is_admin() ){
			return $terms;
		}

		global $post;
		if ( ! is_object( $post ) ) {
			return $terms;
		}

		if( ! has_shortcode( $post->post_content, 'event_submission_form' ) ) {
			return $terms;
		}

		// Filter out the event venues to only show those that are meeting rooms and that are not staff only
		if( ( is_array( $taxonomies ) && in_array( 'event-venue', $taxonomies ) ) || ( !is_array( $taxonomies ) && $taxonomies == 'event-venue' ) ){
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

		// Filter out event categories that are not public
		if ( ( is_array( $taxonomies ) && in_array( 'event-category', $taxonomies ) ) || ( !is_array( $taxonomies ) && $taxonomies == 'event-category' ) ) {
			foreach ( $terms as $key => $term ){
				$cat_id = !is_object( $term ) ? $term : intval( $term->term_id );
				if ( ! get_term_meta( $cat_id, '_libby_public', true ) ){
					unset( $terms[$key] );
				}
			}
		}

		// Filter out event group types that are not public
		if ( ( is_array( $taxonomies ) && in_array( 'group-type', $taxonomies ) ) || ( !is_array( $taxonomies ) && $taxonomies == 'event-category' ) ) {
			foreach ( $terms as $key => $term ){
				$group_type_id = !is_object( $term ) ? $term : intval( $term->term_id );
				if ( ! get_term_meta( $group_type_id, '_libby_public', true ) ){
					unset( $terms[$key] );
				}
			}
		}

		return $terms;

	}

	public function download_event_ical( $query ) {
		if ( !is_admin() && isset( $query->query_vars['ical_download'] ) ) {
			$event_id = $query->query_vars['ical_download'];
			$venue_id = eo_get_venue( $event_id );
			$event = get_post( $event_id );
			$scheduled_events = new WP_Query(array(
				'post_type'         => 'event',
				'event_start_after' => 'today',
				'posts_per_page'    => 100,
				'event_series'      => $event_id,
				'group_events_by'   => 'occurrence',
			));

			$ical = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//hacksw/handcal//NONSGML v1.0//EN\r\nCALSCALE:GREGORIAN\r\n";
			foreach ( $scheduled_events->get_posts() as $scheduled_event ) {
				$ical .= sprintf(
			    'BEGIN:VEVENT' . PHP_EOL .
			    'DTEND:%1$s' . PHP_EOL .
			    'UID:%2$s' . PHP_EOL .
			    'DTSTAMP:%3$s' . PHP_EOL .
			    'LOCATION:%4$s' . PHP_EOL .
			    'DESCRIPTION:%5$s' . PHP_EOL .
			    'URL;VALUE=URI:%6$s' . PHP_EOL .
			    'SUMMARY:%7$s' . PHP_EOL .
			    'DTSTART:%8$s' . PHP_EOL .
			    'END:VEVENT' . PHP_EOL,
					eo_get_the_end( 'Ymd\THis\Z', $event->ID, null, $scheduled_event->occurrence_id ), //End date/time
					uniqid(),
					date( 'Ymd\THis\Z' ), //date/timestamp
					eo_get_venue_address( eo_get_venue( $event_id ) ), //Address
					esc_html( $event->post_content ), //description
					esc_url( get_the_permalink( $event->ID ) ), //URL
					get_the_title( $event->ID ), //Title/Summary
					eo_get_the_start( 'Ymd\THis\Z', $event->ID, null, $scheduled_event->occurrence_id ) //Start date/time
			  );
			}
			$ical .= 'END:VCALENDAR';
			header('Content-type: text/calendar; charset=utf-8');
			header('Content-Disposition: attachment; filename=event.ics');
			header('Connection: close');
			die(trim($ical));
		}
	}

	/**
	 * Register any query vars needed by adding to the $vars array and
	 * hooking into query_vars filter
	 * @param array $vars The original vars plus our added vars
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'ical_download';
		$vars[] = 'occurrence';
		return $vars;
	}

}
