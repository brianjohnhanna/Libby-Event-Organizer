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
        'id'         => 'venue_setup',
        'type'       => 'radio',
				'options_cb' => array( $this, 'get_venue_setup_options_array' ),
				'show_on_cb' => array( $this, 'venue_has_setup_options' )
    ) );

		// Setup Option
    $metabox->add_field( array(
        'name'       => __( 'Equipment', 'libby' ),
        'id'         => 'venue_equipment',
        'type'       => 'multicheck',
				'options_cb' => array( $this, 'get_venue_equipment_options_array' ),
				'show_on_cb' => array( $this, 'venue_has_equipment' )
    ) );

		$metabox->add_field( array(
				'name'       => __( 'Meeting Purpose', 'libby' ),
				'id'         => '_eventorganiser_meeting_purpose',
				'type'       => 'textarea',
				'attributes'  => array(
					'readonly' => 'readonly',
				),
		) );

		$metabox->add_field( array(
				'name'       => __( 'Expected Attendance', 'libby' ),
				'id'         => '_eventorganiser_expected_attendance',
				'type'       => 'text',
				'attributes'  => array(
					'readonly' => 'readonly',
				),
		) );

		// Regular text field
    $metabox->add_field( array(
        'name'       => __( 'Private Note', 'libby' ),
        'id'         => '_eventorganiser_private_note',
        'type'       => 'textarea',
				'attributes'  => array(
					'readonly' => 'readonly',
				),
    ) );

		// Regular text field
		$metabox->add_field( array(
				'name'       => __( 'Event Link', 'libby' ),
				'id'         => '_eventorganiser_event_link',
				'type'       => 'text',
		) );

		$metabox->add_field( array(
				'name'       => __( 'Fee', 'libby' ),
				'id'         => '_eventorganiser_fee',
				'type'       => 'text',
				'attributes'  => array(
					'readonly' => 'readonly',
				),
		) );

	}

	public function venue_has_setup_options() {
		global $post;
		$venue_id = eo_get_venue( $post->ID );
		return eo_get_venue_meta( $venue_id, '_setup_options', true ) ? true : false;
	}

	public function venue_has_equipment() {
		global $post;
		$venue_id = eo_get_venue( $post->ID );
		return eo_get_venue_meta( $venue_id, '_equipment', true ) ? true : false;
	}

	public function get_venue_setup_options_array() {
		global $post;
		$venue_id = eo_get_venue( $post->ID );
		$venue_setup_options = eo_get_venue_meta( $venue_id, '_setup_options', true );
		$rtn = [];
		foreach ( $venue_setup_options as $option ) {
			$rtn[$option['name']] = $option['name'];
		}
		return $rtn;
	}

	public function get_venue_equipment_options_array() {
		global $post;
		$venue_id = eo_get_venue( $post->ID );
		$venue_equipment = eo_get_venue_meta( $venue_id, '_equipment', true );
		$rtn = [];
		foreach ( $venue_equipment as $equipment ) {
			$rtn[$equipment['name']] = $equipment['name'];
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
	 * Register our custom taxonomies
	 * @param  [type] $post_status [description]
	 * @return [type]              [description]
	 */
	public function register_taxonomy() {

		$labels = array(
			'name'                       => _x( 'Group Types', 'Taxonomy General Name', 'text_domain' ),
			'singular_name'              => _x( 'Group Type', 'Taxonomy Singular Name', 'text_domain' ),
			'menu_name'                  => __( 'Group Types', 'text_domain' ),
			'all_items'                  => __( 'All Group Types', 'text_domain' ),
			'parent_item'                => __( 'Parent Item', 'text_domain' ),
			'parent_item_colon'          => __( 'Parent Item:', 'text_domain' ),
			'new_item_name'              => __( 'New Group Type', 'text_domain' ),
			'add_new_item'               => __( 'Add New Group Type', 'text_domain' ),
			'edit_item'                  => __( 'Edit Group Type', 'text_domain' ),
			'update_item'                => __( 'Update Group Type', 'text_domain' ),
			'view_item'                  => __( 'View Group Type', 'text_domain' ),
			'separate_items_with_commas' => __( 'Separate items with commas', 'text_domain' ),
			'add_or_remove_items'        => __( 'Add or remove items', 'text_domain' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
			'popular_items'              => __( 'Popular Items', 'text_domain' ),
			'search_items'               => __( 'Search Items', 'text_domain' ),
			'not_found'                  => __( 'Not Found', 'text_domain' ),
			'no_terms'                   => __( 'No items', 'text_domain' ),
			'items_list'                 => __( 'Items list', 'text_domain' ),
			'items_list_navigation'      => __( 'Items list navigation', 'text_domain' ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => true,
		);
		register_taxonomy( 'group_type', array( 'event' ), $args );

	}

	/**
	 * Remove the submenu page for the EO plugin if debug is off
	 * @return [type] [description]
	 */
	public function remove_menu_pages() {
		if ( ! defined( 'WP_DEBUG') || ! WP_DEBUG ) {
			remove_submenu_page( 'options-general.php', 'event-settings' );
		}
	}

	public function filter_admin_notices() {
		echo '<style>#eo-notice{display:none;}</style>';
	}

}
