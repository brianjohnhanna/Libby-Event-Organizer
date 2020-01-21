<?php

/**
 * The functionality for the events custom post type
 *
 * @link       http://stboston.com
 * @since      1.0.0
 *
 * @package    Libby_Events
 * @subpackage Libby_Events/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Libby_Events
 * @subpackage Libby_Events/admin
 * @author     Stirling Technologies <brian@stboston.com>
 */
class Libby_Events_Admin {

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
	 * Keep track of whether we have checked event conflicts
	 * @var bool 	$event_conflicts_checked  Whether we've checked for event conflicts for the current event
	 */
	protected $event_conflicts_checked;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $messenger ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->messenger = $messenger;

	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name . '-events-admin', plugin_dir_url( __FILE__ ) . 'js/libby-events-admin.js', array( 'jquery' ), $this->version, true );
	}

	/**
	 * Register the metaboxes for the venues
	 *
	 * @since 1.0.0
	 */
	public function register_vendor_metaboxes_and_fields() {
		$metabox = new_cmb2_box( array(
        'id'            => 'libby_event_metabox',
        'title'         => __( 'Event Details', 'libby' ),
        'object_types'  => array( 'event' ), // Post type
        'context'       => 'normal',
        'priority'      => 'default',
        'show_names'    => true, // Show field names on the left
    ) );

		// Regular text field
    $metabox->add_field( array(
        'name'       => __( 'Contact First Name', 'libby' ),
        'id'         => '_eventorganiser_fes_fname',
        'type'       => 'text',
    ) );

		$metabox->add_field( array(
        'name'       => __( 'Contact Last Name', 'libby' ),
        'id'         => '_eventorganiser_fes_lname',
        'type'       => 'text',
    ) );

		// Regular text field
    $metabox->add_field( array(
        'name'       => __( 'Contact Email', 'libby' ),
        'id'         => '_eventorganiser_fes_email',
        'type'       => 'text_email',
    ) );

		// Regular text field
		$metabox->add_field( array(
				'name'       => __( 'Contact Phone', 'libby' ),
				'id'         => 'libby-contact-phone',
				'type'       => 'text',
		) );

		// Setup Option
    $metabox->add_field( array(
        'name'       => __( 'Setup', 'libby' ),
        'id'         => '_libby_setup_options',
        'type'       => 'radio',
				'options_cb' => array( $this, 'get_venue_setup_options_array' ),
				'before' => array( $this, 'setup_equipment_options_disclaimer' )
    ) );

		// Setup Option
    $metabox->add_field( array(
        'name'       => __( 'Equipment', 'libby' ),
        'id'         => '_libby_equipment',
        'type'       => 'multicheck',
				'options_cb' => array( $this, 'get_venue_equipment_options_array' ),
				'before' => array( $this, 'setup_equipment_options_disclaimer' )
    ) );

		$event_metabox_extra_fields = array(
			'setup_time_required' => array(
				'name'       => __( 'Setup Time Required', 'libby' ),
				'id'         => '_libby_setup_time',
				'type'       => 'text',
				// 'show_on_cb' => array( $this, 'is_submitted_event' )
			),
			'breakdown_time_required' => array(
				'name'       => __( 'Breakdown Time Required', 'libby' ),
				'id'         => '_libby_breakdown_time',
				'type'       => 'text',
				// 'show_on_cb' => array( $this, 'is_submitted_event' )
			),
			'meeting_purpose' => array(
					'name'       => __( 'Meeting Purpose', 'libby' ),
					'id'         => '_libby_meeting_purpose',
					'type'       => 'textarea',
					'attributes'  => array(
						// 'readonly' => 'readonly',
						'rows' => 4
					),
			),
			'expected_attendance' => array(
					'name'       => __( 'Expected Attendance', 'libby' ),
					'id'         => '_libby_expected_attendance',
					'type'       => 'text',
					'show_on_cb' => array( $this, 'is_submitted_event' )
			),
			'private_note' => array(
	        'name'       => __( 'Private Note', 'libby' ),
	        'id'         => '_libby_private_note',
	        'type'       => 'textarea',
					'attributes'  => array(
						'readonly' => 'readonly',
						'rows' => 4
					),
					'show_on_cb' => array( $this, 'is_submitted_event' )
	    ),
			'event_link' => array(
					'name'       => __( 'Event Link', 'libby' ),
					'id'         => '_libby_link',
					'type'       => 'text',
			),
			'fee' => array(
					'name'       => __( 'Fee', 'libby' ),
					'id'         => '_libby_fee',
					'type'       => 'text',
					'attributes'  => array(
						'readonly' => 'readonly',
					),
					'show_on_cb' => array( $this, 'is_submitted_event' )
			)
		);

		/**
		 * Apply filters to add/modify event extra fields. Returning false will not register any additional fields.
		 * @var [type]
		 */
		$event_metabox_extra_fields = apply_filters( 'libby/events/event-meta-fields', $event_metabox_extra_fields );

		if ( $event_metabox_extra_fields && is_array( $event_metabox_extra_fields) ) {
			foreach ( $event_metabox_extra_fields as $key => $event_metabox_field ) {
				$metabox->add_field( $event_metabox_field );
			}
		}

	}

	public function setup_equipment_options_disclaimer( $field_args, $field ) {
		if ( ! $this->is_venue_set() ) {
				printf( '%s options will appear here after selecting a venue and saving or publishing the event.', $field_args['name'] );
		}
		else if (
			( $field_args['name'] === 'Equipment' && ! $this->venue_has_equipment() ) ||
			( $field_args['name'] === 'Setup' && ! $this->venue_has_setup_options() ) ) {
			printf( 'There are no %s options configured for the selected venue.', $field_args['name'] );
		}
	}

	/**
	 * Determine if the event was submitted
	 * @return boolean
	 */
	public function is_submitted_event() {
			global $post;
			return get_post_meta( $post->ID, '_eventorganiser_fes' );
	}

	/**
	 * Determine if the event venue has setup options to choose from
	 * @return boolean
	 */
	public function venue_has_setup_options() {
		global $post;
		$venue_id = eo_get_venue( $post->ID );
		return eo_get_venue_meta( $venue_id, '_libby_setup_options', true ) ? true : false;
	}

	/**
	 * Check if the venue has been set for the event
	 */
	public function is_venue_set() {
		global $post;
		return eo_get_venue( $post->ID ) ? true : false;
	}

	/**
	 * Determine if the event venue has equipment to choose from
	 * @return boolean
	 */
	public function venue_has_equipment() {
		global $post;
		$venue_id = eo_get_venue( $post->ID );
		return eo_get_venue_meta( $venue_id, '_libby_available_equipment', true ) ? true : false;
	}

	/**
	 * Create an options array of available setup options for CMB2 metaboxes
	 * @return array An array of equipment keyed by the name of the setup option
	 */
	public function get_venue_setup_options_array() {
		global $post;
		$venue_id = eo_get_venue( $post->ID );
		$venue_setup_options = eo_get_venue_meta( $venue_id, '_libby_setup_options', true );
		$rtn = [];
		if ( $venue_setup_options ) {
			foreach ( $venue_setup_options as $option ) {
				$rtn[$option['title']] = $option['title'];
			}
		}
		return $rtn;
	}

	/**
	 * Create an options array of available venue equipment for CMB2 metaboxes
	 * @return array An array of equipment keyed by the name of the equipment
	 */
	public function get_venue_equipment_options_array() {
		global $post;
		$venue_id = eo_get_venue( $post->ID );
		$venue_equipment = eo_get_venue_meta( $venue_id, '_libby_available_equipment', true );
		$rtn = [];
		if ( $venue_equipment ) {
			foreach ( $venue_equipment as $equipment ) {
				$rtn[$equipment['title']] = $equipment['title'];
			}
		}
		return $rtn;
	}

	/**
	 * Register the custom columns for the events admin
	 * @return array $columns Registered columns
	 */
	public function register_custom_columns( $columns ) {
		// $columns['status'] = 'Status';
		unset($columns['taxonomy-event-venue']);
		$columns = apply_filters( 'libby/events/event-columns', $columns );
		return $columns;
	}

	/**
	 * Render the custom column registered in register_custom_columns
	 * @param  string $column  The name of the column
	 * @param  int $post_id The ID of the current post row
	 */
	public function render_custom_columns( $column, $post_id ) {
		switch( $column ) {
			case 'status' :
				echo ucwords( get_post_status( $post_id ) );
				break;
		}
	}

	/**
	 * Hook into the pending_to_publish action to send a confirmation email for
	 * submitted events when they are published
	 * @param  obj $post The WP_Post object of the event
	 */
	public function send_event_published_email( $post ) {
		// Make sure we have the correct post type, as this will fire for all post types.
		if ( get_post_type( $post ) !== 'event' ) {
			return;
		}

		// Make sure it was a front-end submitted event
		if ( ! get_post_meta( $post->ID, '_eventorganiser_fes' ) ) {
			return;
		}

		$eo_fes_data = get_post_meta( $post->ID, '_eventorganiser_fes_data', true );
		$to = $eo_fes_data['email'];

		$subject = sprintf( 'Your request for %s has been approved.', $post->post_title );
		$body = sprintf(
			'Dear %1$s, <br /><br /> The event administrators have approved your room request for %2$s. You can see it on the website at the following URL: <a href="%3$s" target="_blank">%3$s</a>. If you owe any fees associated with the event, please make sure to bring or mail a check to the library prior to the start of your event.',
			implode( ' ', $eo_fes_data['name'] ),
			$post->post_title,
			get_the_permalink( $post )
		);
		$booking_form_from_email = apply_filters( 'libby/events/form/admin-email', get_option( 'admin_email' ) );
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			sprintf( 'From: %s <%s>', get_bloginfo( 'name' ),  $booking_form_from_email ),
			sprintf( 'Reply-To: %s <%s>', get_bloginfo( 'name' ),  $booking_form_from_email )
		);

		wp_mail( $to, $subject, $body, $headers );

	}

	/**
	 * Remove the submenu page for the EO plugins if debug is off
	 */
	public function remove_menu_pages() {
		if ( ! defined( 'WP_DEBUG') || ! WP_DEBUG ) {
			remove_submenu_page( 'options-general.php', 'event-settings' );
			remove_submenu_page( 'edit.php?post_type=event', 'eo-addons' );
		}
	}

	/**
	 * Ensure that EO doesn't add any notices that we don't want to appear
	 *
	 * We keep them on if we are in debug mode
	 */
	public function filter_admin_notices() {
		if ( ! defined( 'WP_DEBUG') || ! WP_DEBUG ) {
			echo '<style>#eo-notice{display:none;}</style>';
		}
	}

	/**
	 * Modify the custom post type registration args
	 * @param  array $args The predefined arguments
	 * @return array The modified arguments
	 */
	public function modify_event_cpt_args( $args ) {
		$args['supports'][] = 'publicize';
		$args['taxonomies'][] = 'group-type';
		return $args;
	}

	/**
	 * Validate the event before publishing
	 */
	public function validate_event( $event_ID ) {
		$prevent_publish = false;
		// @TODO need to include logic here to check license key, prevent publish if so

		$prevent_publish = $this->check_event_conflicts( $event_ID );
		if ( $prevent_publish ) {
			$this->prevent_publish( $event_ID );
		}
	}

	/**
	 * Check to see if the event if double booked based on the post meta
	 * @return boolean Whether there is/are conflicting event(s)
	 */
	public function check_event_conflicts( $event_ID ) {
		global $wpdb;

		// Set a class var so we don't call this function twice, since it's attached to multiple hooks.
		if ( $this->event_conflicts_checked === true ) {
			return;
		}
		$this->event_conflicts_checked = true;

		$skip_all_day_conflicts = apply_filters('libby/events/skip-all-day-conflicts', true);

		// If the event is all day, let's bail... Probably not looking for those kinds of conflicts.
		if ( $skip_all_day_conflicts && eo_is_all_day( $event_ID ) ) {
			return false;
		}

		// If we don't have a venue, there's really nothing to check here...If so we'll store the venue ID for check in SQL
		$venue = eo_get_venue( $event_ID );
		if ( ! $venue ) {
			return false;
		}

		// Get the schedule for the event
		$schedule = eo_get_event_schedule( $event_ID );

		// Get all the occurrence dates to check against
		$upcoming = $wpdb->get_results($wpdb->prepare(
			"SELECT StartDate, EndDate from {$wpdb->prefix}eo_events
				WHERE post_id = %d",
			$event_ID
		));

		// Map the start and end dates out of the sql results into respective arrays for the prepare method
		$start_dates = array_map(function($occurrence){
			return $occurrence->StartDate;
		}, $upcoming);
		$end_dates = array_map(function($occurrence){
			return $occurrence->EndDate;
		}, $upcoming);

		// We have to create placeholders for the start/end dates so we can use them with the $wpdb->prepare method
		// i.e. %s, %s, %s...
		$in_clause_placeholders = array_fill( 0, count( $upcoming ), '%s' );
		$in_clause_format = implode( ', ', $in_clause_placeholders );

		// Create the prepare array, since we'll pass it as variable instead of sprintf type.
		// @TODO Probably a more elegant way to do this.

		$prepare = $end_dates;
		$prepare[] = $schedule['start']->modify('+1 second')->format( 'H:i:s' );
		$prepare[] = $schedule['end']->modify('+1 second')->format( 'H:i:s' );
		$prepare = array_merge( $prepare, $start_dates );
		$prepare[] = $schedule['start']->modify('-2 second')->format( 'H:i:s' );
		$prepare[] = $schedule['end']->modify('-2 second')->format( 'H:i:s' );
		$prepare = array_merge( $prepare, $start_dates );
		$prepare = array_merge( $prepare, $end_dates );
		$prepare[] = $schedule['start']->modify('+1 second')->format( 'H:i:s' );
		$prepare[] = $schedule['end']->modify('+1 second')->format( 'H:i:s' );
		$prepare[] = $schedule['start']->format( 'H:i:s' );
		$prepare[] = $schedule['end']->format( 'H:i:s' );
		$prepare[] = $venue;
		$prepare[] = $event_ID;

		// Run the query to look for conflicts
		// First WHERE checks to see if any events end date/time is during our event
		// Second WHERE checks to see if any events start date/time is during our event
		// Last WHERE checks to see if the any events with the same start and end date either start/end during our event or before/after it
		$conflicts = $wpdb->get_results( $wpdb->prepare(
			"SELECT * from {$wpdb->prefix}eo_events
				LEFT JOIN $wpdb->term_relationships ON ( post_id = $wpdb->term_relationships.object_id )
				LEFT JOIN $wpdb->term_taxonomy ON ( $wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id )
				WHERE  (
					( EndDate IN ({$in_clause_format}) AND ( FinishTime BETWEEN %s AND %s ) )
				OR
					( StartDate IN ({$in_clause_format}) AND ( StartTime BETWEEN %s AND %s ) )
				OR
					( StartDate IN ({$in_clause_format}) AND EndDate IN ({$in_clause_format})
					AND (
						( StartTime < %s AND FinishTime > %s )
						OR
						( StartTime > %s AND FinishTime < %s ) )
					)
				)
				AND taxonomy = 'event-venue'
				AND term_id = %d
				AND post_id <> %d",
			$prepare
		) );

		if ( ! $conflicts ) {
			return false;
		}

		$conflicts_html = array();
		foreach ( $conflicts as $conflict ) {
			// If the event conflict is an all day event, it's probably not the kind of conflict we're looking for.
			if ( $skip_all_day_conflicts && eo_is_all_day( $conflict->post_id ) ) {
				continue;
			}
			$conflicts_html[] = sprintf(
				'<li><a href="%s">%s</a> starts at %s and ends at %s on %s in %s.</li>',
				get_edit_post_link( $conflict->post_id ),
				get_the_title( $conflict->post_id ),
				date( 'g:i a', strtotime( $conflict->StartTime ) ),
				date( 'g:i a', strtotime( $conflict->FinishTime ) ),
				date( 'm/d/y', strtotime( $conflict->StartDate ) ),
				eo_get_venue_name( eo_get_venue( $conflict->post_id ) )
			);
		}

		// If we've looped through all the conflicts and have nothing in the array, we'll bail.
		if ( empty( $conflicts_html ) ) {
			return false;
		}
		// Build the HTML to add to the message handler.
		$message_html = '<ul>' . implode( $conflicts_html ) . '</ul>';

		$this->messenger->add_message( 'You have a conflict with the following event(s): <br /><br />' . $message_html );

		return true;

	}

	/**
	 * Prevent publish of an event by drafting it
	 * @param  int $event_id The ID of the event
	 */
	protected function prevent_publish( $event_id ) {
		// Remove the save_post hook so we don't end up in an infinite loop
		remove_action( 'save_post', 'eventorganiser_details_save' );

		// Update the post to a draft
		// @TODO probably need to check and see if it was pending already so the proper people get notified
		// if the date of their event is moved
		wp_update_post( array( 'ID' => $event_id, 'post_status' => 'draft' ) );

		// Change the message to drafted instead of published.
		add_action( 'redirect_post_location', function( $location, $event_id ) {
			return add_query_arg( 'message', 10, $location );
		}, 10, 2);

		// Reapply the action
		add_action( 'save_post', 'eventorganiser_details_save' );
	}

}
