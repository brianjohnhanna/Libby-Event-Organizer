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

		wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/libby-events-public.js', array( 'jquery', 'jquery-ui-datepicker', $this->plugin_name . '-fc-scheduler' ), $this->version, false );
		wp_register_script( $this->plugin_name . '-moment',  plugin_dir_url( dirname( __FILE__ ) ) . 'bower_components/moment/min/moment.min.js', array('jquery'), $this->version, true );
		wp_register_script( $this->plugin_name . '-fc',  plugin_dir_url( dirname( __FILE__ ) ) . 'bower_components/fullcalendar/dist/fullcalendar.min.js', array( $this->plugin_name . '-moment' ), $this->version, true );
		wp_register_script( $this->plugin_name . '-fc-scheduler',  plugin_dir_url( dirname( __FILE__ ) ) . 'bower_components/fullcalendar-scheduler/dist/scheduler.min.js', array( $this->plugin_name . '-fc' ), $this->version, true );
		// wp_localize_script( $this->plugin_name, 'fcResources', $this->get_scheduler_resource_array() );
		wp_localize_script( $this->plugin_name , 'ajax_url', admin_url( 'admin-ajax.php' ) );
	}

	public function dropdown_category_args( $cat_args ) {
		// $cat_args['show_count'] = true;
		// $cat_args['exclude'] = '47';
		return $cat_args;
	}

	/**
	 * Register the front-end registration form
	 *
	 * @since    1.0.0
	 */
	public function register_cmb2_event_form() {
		$cmb = new_cmb2_box( array(
        'id'           => 'libby-booking-form',
        'object_types' => array( 'event' ),
        'hookup'       => false,
        'save_fields'  => false,
    ) );

    $cmb->add_field( array(
        'name'    => __( 'Event Name', 'wds-post-submit' ),
        'id'      => 'libby_event_name',
        'type'    => 'text',
				'attributes'  => array(
		        'required'    => 'required',
		    ),
    ) );

    $cmb->add_field( array(
        'name'    => __( 'Event Description', 'wds-post-submit' ),
        'id'      => 'libby_event_description',
        'type'    => 'wysiwyg',
        'options' => array(
            'textarea_rows' => 12,
            'media_buttons' => false,
        ),
    ) );

		// Regular text field
    $cmb->add_field( array(
        'name'       => __( 'Contact Name', 'libby' ),
        'desc'       => __( 'The name of the person to contact with questions.', 'libby' ),
        'id'         => 'libby_contact_name',
        'type'       => 'text',
				'attributes'  => array(
		        'required'    => 'required',
		    ),
    ) );

		// Regular text field
    $cmb->add_field( array(
        'name'       => __( 'Contact Email', 'libby' ),
        'id'         => 'libby_contact_email',
        'type'       => 'text_email',
    ) );

		// Regular text field
		$cmb->add_field( array(
				'name'       => __( 'Contact Phone', 'libby' ),
				'id'         => 'libby_contact_phone',
				'type'       => 'text',
				'attributes'  => array(
		        'required'    => 'required',
		    ),
		) );

    $cmb->add_field( array(
        'name'       => __( 'Event Image', 'wds-post-submit' ),
        'id'         => 'submitted_post_thumbnail',
        'type'       => 'text',
        'attributes' => array(
            'type' => 'file', // Let's use a standard file upload field
        ),
    ) );

    $cmb->add_field( array(
        'name' => __( 'Actual Event Start Date/Time', 'wds-post-submit' ),
        'desc' => __( 'Start and end times for actual event.', 'wds-post-submit' ),
        'id'   => 'actual_event_start_time',
        'type' => 'text_datetime_timestamp',
				'before_row' => array( $this, 'render_fc_scheduler' ),
				'attributes'  => array(
		        'required'    => 'required',
		    ),
    ) );

		$cmb->add_field( array(
        'name' => __( 'Actual Event End Date/Time', 'wds-post-submit' ),
        'desc' => __( 'End times for actual event.', 'wds-post-submit' ),
        'id'   => 'actual_event_end_time',
        'type' => 'text_datetime_timestamp',
				'attributes'  => array(
		        'required'    => 'required',
		    ),
    ) );

		$cmb->add_field( array(
        'name' => __( 'Setup Time' ),
        'desc' => __( 'Please select the time you need before the event for setup.', 'wds-post-submit' ),
        'id'   => 'setup_time',
        'type' => 'select',
				'options' => array(
					'0' => 'None',
					'15' => '15 Minutes',
					'30' => '30 Minutes',
					'45' => '45 Minutes',
					'60' => '1 Hour'
				)
    ) );

		$cmb->add_field( array(
        'name' => __( 'Breakdown Time' ),
        'desc' => __( 'Please select the time needed after the event for breakdown.', 'wds-post-submit' ),
        'id'   => 'breakdown_time',
        'type' => 'select',
				'options' => array(
					'0' => 'None',
					'15' => '15 Minutes',
					'30' => '30 Minutes',
					'45' => '45 Minutes',
					'60' => '1 Hour'
				)
    ) );

		$cmb->add_field( array(
		    'name'     => __( 'Venue/Room', 'libby' ),
		    'id'       => 'venue',
		    'taxonomy' => 'event-venue', //Enter Taxonomy Slug
		    'type'     => 'radio',
				'options_cb' => array( $this, 'get_venues_options_array' ),
				// 'before' => 'Please select a room. Capacity is marked in brackets. Note that some rooms are not available for booking to the public and are not listed here.<br /><br />',
				'attributes'  => array(
		        'required'    => 'required',
		    ),
		) );

		// $cmb->add_field( array(
		//     'name'     => __( 'Group Type', 'libby' ),
		//     'id'       => 'group_type',
		//     'taxonomy' => 'group_type', //Enter Taxonomy Slug
		//     'type'     => 'taxonomy_select',
		// 		'attributes'  => array(
		//         'required'    => 'required',
		//     ),
		// ) );

		$cmb->add_field( array(
		    'name'    => __( 'Equipment', 'libby' ),
		    'id'      => 'equipment',
		    'type'    => 'multicheck',
		    'options_cb' => array( $this, 'get_venue_equipment_options_array' )
		) );

		$cmb->add_field( array(
		    'name' => __( 'Terms & Conditions', 'libby' ),
		    'desc' => 'You can view the terms <a href="#">here</a>',
		    'id'   => 'terms_acknowledgement',
		    'type' => 'checkbox',
				'attributes'  => array(
		        'required'    => 'required',
		    ),
		) );

		$cmb->add_field( array(
		    'name' => __( 'Private Message to Calendar Editor', 'libby' ),
		    'desc' => 'Please provide any relevant details to this booking request.',
		    'id' => 'private_message',
		    'type' => 'textarea'
		) );

		$cmb->add_field( array(
		    'name' => __( 'Event Link', 'libby' ),
		    'id'   => 'event_url',
		    'type' => 'text_url',
		    // 'protocols' => array( 'http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet' ), // Array of allowed protocols
		) );

	}

	/**
	 * Handle the cmb-frontend-form shortcode
	 *
	 * @param  array  $atts Array of shortcode attributes
	 * @return string       Form html
	 */
	public function register_booking_form_shortcode( $atts = array() ) {

			wp_enqueue_script( $this->plugin_name . '-fc-scheduler' );
			wp_enqueue_style( $this->plugin_name . '-fc-scheduler' );

			wp_enqueue_style( $this->plugin_name );

	    // Current user
	    $user_id = get_current_user_id();

	    // Use ID of metabox in wds_frontend_form_register
	    $metabox_id = 'libby-booking-form';

	    // since post ID will not exist yet, just need to pass it something
	    $object_id  = 'fake-oject-id';

	    // Get CMB2 metabox object
	    $cmb = cmb2_get_metabox( $metabox_id, $object_id );

	    // Get $cmb object_types
	    $post_types = $cmb->prop( 'object_types' );

	    // Parse attributes. These shortcode attributes can be optionally overridden.
	    $atts = shortcode_atts( array(
	        'post_author' => $user_id ? $user_id : 1, // Current user, or admin
	        'post_status' => 'pending',
	        'post_type'   => reset( $post_types ), // Only use first object_type in array
	    ), $atts );

	    // Initiate our output variable
	    $output = '';

	    // Our CMB2 form stuff goes here
	    $output .= cmb2_get_metabox_form( $cmb, $object_id, array( 'save_button' => __( 'Submit Post', 'wds-post-submit' ) ) );

	    return $output;
	}

	function register_booking_form_shortcode_new() {

		wp_enqueue_script( $this->plugin_name . '-fc-scheduler' );
		wp_enqueue_style( $this->plugin_name . '-fc-scheduler' );
		// wp_enqueue_script( $this->plugin_name . '-fes' );
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

	function get_events_by_day( $date = 'today' ) {
		$events = eo_get_events(array(
      'numberposts'=>10,
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
	 * Get an array of the venues with necessary metadata
	 * to be used in the radio select dropdown
	 *
	 * Note: may need to be deprecated
	 */
	public function get_venues_options_array() {
		$venues = array();

		$eo_venues = eo_get_venues();
		foreach ( $eo_venues as $venue ) {
			$string = '';
			if ( eo_get_venue_meta( $venue->term_id, '_staff_only', true ) == 1 ) {
				continue;
			}
			$string .= $venue->name;
			$branch_id = eo_get_venue_meta( $venue->term_id, '_branch', true );
			if ( $branch_id ) {
				$string .= ' - ' . get_the_title( $branch_id );
			}
			$capacity = eo_get_venue_meta( $venue->term_id, '_capacity', true );
			if ( $capacity ) {
				$string .= ' [' . $capacity . ']';
			}

			$venues[$venue->term_id] = $string;
		}
		return $venues;
	}

	public function get_venue_equipment_options_array() {
		$equipment = array();
		foreach ( $this->get_venue_equipment(true) as $venue_name => $venue_equipment ){
			if ( is_array( $venue_equipment ) ) {
				foreach ( $venue_equipment as $item ) {
					$equipment[] = $venue_name . ' - ' . $item['name'];
				}
			}
		};
		return $equipment;
	}

	/**
	 * Retrieve the venue hours based on the date and the
	 * @param  int $venue_id the ID of the venue
	 * @return array $hours The hour types
	 */
	public function get_venue_hours( $venue_id, $date ) {
		$branch_id = eo_get_venue_meta( (int)$venue_id, '_branch', true );
		$hours = libpress_get_hours( (int)$branch_id );
		return $hours[0]['hours'];
	}

	/**
	 * Get an events array that the scheduler
	 * can use to display events
	 * @return array $events An array of events that gets sent to the calendar
	 */
	public function get_scheduler_events_array( $venue_id = null ) {
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
				'resourceId' => eo_get_venue( $event->ID ),
				'start' => eo_get_the_start( 'Y-m-d H:i:s', $event->ID, null, $event->occurrence_id),
				'end' => eo_get_the_end( 'Y-m-d H:i:s', $event->ID, null, $event->occurrence_id ),
				// 'rendering' => 'inverse-background'
			);
		}
		return $events;
	}

	/**
	 * Get an array of resources that can be used by the fullCalendar scheduler
	 * @return array $venues An array of all the venues
	 */
	public function get_scheduler_resource_array() {
		$eo_venues = eo_get_venues();
		$venues = array();
		foreach ( $eo_venues as $venue ) {
			if ( eo_get_venue_meta( $venue->term_id, '_staff_only', true ) == 1 ) {
				continue;
			}
			$branch_id = eo_get_venue_meta( $venue->term_id, '_branch', true );
			$branch_name = get_the_title( $branch_id );
			$capacity = eo_get_venue_meta( $venue->term_id, '_seating_limit', true );

			$venues[] = array(
				'id' => $venue->term_id,
				'title' => $venue->name,
				'branch' => $branch_name,
				'capacity' => $capacity
			);
		}
		return $venues;
	}

	/**
	 * Get an array of all the equipment for all venues or for a specific venue
	 * @return array $equipment All the equipment
	 */
	public function get_venue_equipment( $venue_name_as_key = false, $venue_id = array() ) {
		return $this->get_venue_meta( '_equipment', $venue_name_as_key, $venue_id );
	}

	/**
	 * Get the setup options for all venues or a specific venue
	 */
	public function get_venue_setup_options( $venue_name_as_key = false, $venue_id = array() ) {
		return $this->get_venue_meta( '_setup_options', $venue_name_as_key, $venue_id );
	}

	/**
	 * Get venue meta, optionally filtered by a venue ID
	 * @param  string $meta_key The key of the meta field
	 * @param  array  $venue_id The term id of the venue
	 * @param  bool  $include_key Whether to include the venue name as the key
	 * @return object|array the venue meta array or singluar object
	 */
	public function get_venue_meta( $meta_key, $venue_name_as_key = false, $venue_id = array() ) {
		if ( count($venue_id) > 1 ) {
			$venues = eo_get_venues( (array)$venue_id );
			foreach ( $venues as $key => $venue ) {
				if ($venue_name_as_key) {
					$key = $venue->name;
				}
				$values[$key] = eo_get_venue_meta( $venue->term_id, $meta_key, true );
			}
			return $values;
		}
		else {
			return eo_get_venue_meta( $venue_id, $meta_key, true );
		}
	}


	function get_venue_setup_options_ajax() {
		$venue_id = $_GET['venueId'];
		$venue_options = array(
			'description' => eo_get_venue_description( (int)$venue_id ),
			'setup' => $this->get_venue_setup_options( false, $venue_id ),
			'equipment' => $this->get_venue_equipment( false, $venue_id ),
			'events' => $this->get_scheduler_events_array( $venue_id ),
			'hours' => $this->get_venue_hours( $venue_id )
		);
		echo json_encode( $venue_options );
		wp_die();
	}

	public function eo_fes_venue_display( $element ) {
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

}
