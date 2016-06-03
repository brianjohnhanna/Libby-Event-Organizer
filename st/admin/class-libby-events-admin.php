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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

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
				'show_on_cb' => array( $this, 'venue_has_setup_options' )
    ) );

		// Setup Option
    $metabox->add_field( array(
        'name'       => __( 'Equipment', 'libby' ),
        'id'         => '_libby_equipment',
        'type'       => 'multicheck',
				'options_cb' => array( $this, 'get_venue_equipment_options_array' ),
				'show_on_cb' => array( $this, 'venue_has_equipment' )
    ) );

		$metabox->add_field(array(
			'name'       => __( 'Setup Time Required', 'libby' ),
			'id'         => '_libby_setup_time',
			'type'       => 'text',
			'show_on_cb' => array( $this, 'is_submitted_event' )
		) );

		$metabox->add_field( array(
				'name'       => __( 'Meeting Purpose', 'libby' ),
				'id'         => '_libby_meeting_purpose',
				'type'       => 'textarea',
				'attributes'  => array(
					'readonly' => 'readonly',
					'rows' => 4
				),
				'show_on_cb' => array( $this, 'is_submitted_event' )
		) );

		$metabox->add_field( array(
				'name'       => __( 'Expected Attendance', 'libby' ),
				'id'         => '_libby_expected_attendance',
				'type'       => 'text',
				'show_on_cb' => array( $this, 'is_submitted_event' )
		) );

		// Regular text field
    $metabox->add_field( array(
        'name'       => __( 'Private Note', 'libby' ),
        'id'         => '_libby_private_note',
        'type'       => 'textarea',
				'attributes'  => array(
					'readonly' => 'readonly',
					'rows' => 4
				),
				'show_on_cb' => array( $this, 'is_submitted_event' )
    ) );

		// Regular text field
		$metabox->add_field( array(
				'name'       => __( 'Event Link', 'libby' ),
				'id'         => '_libby_link',
				'type'       => 'text',
		) );

		$metabox->add_field( array(
				'name'       => __( 'Fee', 'libby' ),
				'id'         => '_libby_fee',
				'type'       => 'text',
				'attributes'  => array(
					'readonly' => 'readonly',
				),
				'show_on_cb' => array( $this, 'is_submitted_event' )
		) );

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
		foreach ( $venue_setup_options as $option ) {
			$rtn[$option['title']] = $option['title'];
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
		foreach ( $venue_equipment as $equipment ) {
			$rtn[$equipment['title']] = $equipment['title'];
		}
		return $rtn;
	}

	/**
	 * Register the custom columns for the events admin
	 * @return array $columns Registered columns
	 */
	public function register_custom_columns( $columns ) {
		$columns['status'] = 'Status';
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
			case 'venue' :
				if ( eo_get_venue( $post_id ) && eo_get_venue_meta( $post_id, 'branch' ) ) {
					echo eo_get_venue_meta( $post_id, 'branch', true );
				}
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
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			sprintf( 'From: %s <%s>', get_bloginfo( 'name' ), get_option( 'admin_email' ) )
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

}
