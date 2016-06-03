<?php

/**
 * The admin-specific functionality of the plugin.
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
 * @author     Stirling Technologies <info@meetlibby.com>
 */
class Libby_Events_Venue_Admin {

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
	 * Register the taxonomy custom fields
	 */
	public function register_custom_fields() {
		$prefix = '_libby_';
		/**
		 * Metabox to add fields to categories and tags
		 */
		$venue_info = new_cmb2_box( array(
			'id'               => $prefix . 'info',
			'title'            => __( 'Venue Information', 'libby' ), // Doesn't output for term boxes
			'object_types'     => array( 'term' ), // Tells CMB2 to use term_meta vs post_meta
			'taxonomies'       => array( 'event-venue' ), // Tells CMB2 which taxonomies should have these fields
			'new_term_section' => true, // Will display in the "Add New Category" section
		) );

		$venue_info->add_field( array(
			'name'     => __( 'Venue Details', 'libby' ),
			'id'       => $prefix . 'extra_info',
			'type'     => 'title',
		) );

		$venue_info->add_field( array(
		    'name' => 'Venue Type',
		    'id'   => $prefix . 'type',
				'type' => 'select',
				'options' => array(
					'branch' => 'Branch',
					'meeting_room' => 'Meeting Room',
				),
				'attributes'  => array(
	        'required'    => 'required',
	    	),
			) );


		$venue_info->add_field( array(
			'name' => __( 'Branch', 'libby' ),
			'id'   => $prefix . 'branch',
			'type' => 'select',
			'options_cb' => array( $this, 'get_branches_options_array' ),
			'attributes'  => array(
        'required'    => 'required',
    	),
		) );

		$venue_info->add_field( array(
			'name' => __( 'Venue Image', 'libby' ),
			'id'   => $prefix . 'image',
			'type' => 'file',
			'options' => array(
        'url' => false, // Hide the text input for the url
    	),
			'text'    => array(
        'add_upload_file_text' => 'Add Image' // Change upload button text. Default: "Add or Upload File"
    	),
		) );

		$room_details = new_cmb2_box( array(
			'id'               => $prefix . 'room_info',
			'title'            => __( 'Venue Information', 'libby' ), // Doesn't output for term boxes
			'object_types'     => array( 'term' ), // Tells CMB2 to use term_meta vs post_meta
			'taxonomies'       => array( 'event-venue' ), // Tells CMB2 which taxonomies should have these fields
			'new_term_section' => false, // Will display in the "Add New Category" section
			'show_on_cb'			 => array( $this, 'is_room' )
		) );

		$room_details->add_field( array(
		    'name' => 'Staff Only',
				'desc' => 'Venue that can only be booked by website users.',
		    'id'   => $prefix . 'staff_only',
		    'type' => 'checkbox',
		) );

		$room_details->add_field( array(
			'name' => __( 'Seating Limit', 'libby' ),
			'id'   => $prefix . 'seating_limit',
			'type' => 'text',
		) );

		$room_details->add_field( array(
			'name' => __( 'Room Number', 'libby' ),
			'id'   => $prefix . 'room_number',
			'type' => 'text',
		) );


		$venue_setup_equipment = new_cmb2_box( array(
			'id'               => $prefix . 'edit',
			'title'            => __( 'Available Equipment', 'libby' ), // Doesn't output for term boxes
			'object_types'     => array( 'term' ), // Tells CMB2 to use term_meta vs post_meta
			'taxonomies'       => array( 'event-venue' ), // Tells CMB2 which taxonomies should have these fields
			'new_term_section' => false, // Will display in the "Add New Category" section
			'show_on_cb'			 => array( $this, 'is_room' )
		) );

		$venue_equipment = $venue_setup_equipment->add_field( array(
		    'id'          => $prefix . 'available_equipment',
		    'type'        => 'group',
		    'description' => __( 'Configure available venue/room equipment', 'cmb2' ),
		    // 'repeatable'  => false, // use false if you want non-repeatable group
		    'options'     => array(
		        'group_title'   => __( 'Equipment Option {#}', 'cmb2' ), // since version 1.1.4, {#} gets replaced by row number
		        'add_button'    => __( 'Add More Equipment', 'cmb2' ),
		        'remove_button' => __( 'Remove Equipment', 'cmb2' ),
		        'sortable'      => true, // beta
		        'closed'     => true, // true to have the groups closed by default
		    ),
		) );

		$venue_setup_equipment->add_group_field( $venue_equipment, array(
		    'name' => 'Title',
		    'id'   => 'title',
		    'type' => 'text',
				'attributes'  => array(
					'placeholder' => 'E.g. Wireless Microphone, Podium'
	    	),
		) );

		$venue_setup_equipment->add_group_field( $venue_equipment, array(
		    'name' => 'Description',
		    'id'   => 'description',
		    'type' => 'textarea',
				'attributes'  => array(
	        'rows'    => 3,
	    	),
		) );

		$venue_setup_equipment->add_group_field( $venue_equipment, array(
		    'name' => 'Equipment Image',
		    'id'   => 'image',
		    'type' => 'file',
		) );

		$venue_setup_equipment->add_group_field( $venue_equipment, array(
		    'name' => 'Requires Training?',
		    'id'   => 'training_required',
		    'type' => 'checkbox',
		) );

		$venue_setup = $venue_setup_equipment->add_field( array(
		    'id'          => $prefix . 'setup_options',
				'title'				=> 'Setup Options',
		    'type'        => 'group',
		    'description' => __( 'Configure available setup configurations', 'cmb2' ),
		    // 'repeatable'  => false, // use false if you want non-repeatable group
		    'options'     => array(
		        'group_title'   => __( 'Setup Option {#}', 'cmb2' ), // since version 1.1.4, {#} gets replaced by row number
		        'add_button'    => __( 'Add More Setup Options', 'cmb2' ),
		        'remove_button' => __( 'Remove Setup Option', 'cmb2' ),
		        'sortable'      => true, // beta
		        'closed'     => true, // true to have the groups closed by default
		    ),
				'show_on_cb'			 => array( $this, 'is_room' )
		) );

		$venue_setup_equipment->add_group_field( $venue_setup, array(
		    'name' => 'Setup Title',
		    'id'   => 'title',
		    'type' => 'text',
				'attributes'  => array(
					'placeholder' => 'E.g. Lecture, Musical Performance'
	    	),
		) );

		$venue_setup_equipment->add_group_field( $venue_setup, array(
		    'name' => 'Description',
		    'id'   => 'description',
		    'type' => 'textarea',
				'attributes'  => array(
	        'rows'    => 3,
	    	),
		) );

		$venue_setup_equipment->add_group_field( $venue_setup, array(
			'name'    => 'Setup Diagram',
			'desc'    => 'Upload an image or PDF.',
			'id'      => 'diagram',
			'type'    => 'file',
			'options' => array(
					'url' => false, // Hide the text input for the url
			),
			'text'    => array(
					'add_upload_file_text' => 'Add Setup Diagram' // Change upload button text. Default: "Add or Upload File"
			),
		) );

	}

	/**
	 * Determine whether the term is a meeting room for conditional display of
	 * venue custom fields
	 */
	public function is_room() {
		$tag_ID = (int) $_REQUEST['tag_ID'];
		return get_term_meta( $tag_ID, '_libby_type', true) === 'meeting_room';

	}

	/**
	 * Register the custom taxonomy columns
	 */
	public function register_custom_columns( $columns ) {
		$columns = array(
		'cb' => '<input type="checkbox" />',
			'name' => 'Name',
			'type' => 'Type',
			'branch' => 'Branch'
		);
		return $columns;
	}

	public function render_custom_columns( $deprecated, $column, $term_id ) {
		switch ( $column ) {
			case 'type':
				echo ucwords( str_replace( '_', ' ', get_term_meta( $term_id, '_libby_type', true ) ) );
				break;
			case 'branch':
				echo get_the_title( (int)get_term_meta( $term_id, '_libby_branch', true ) );
		}
	}

	/**
	 * Get an array of branches keyed by the branch post ID. Used by CMB2 to display venue branch options.
	 * @return array $rtn An array of branches
	 */
	public function get_branches_options_array() {
		$branches = get_posts(array(
			'post_type' => 'branch',
			'posts_per_page' => 100,
		));
		$rtn = array();
		foreach ( $branches as $branch ) {
			$rtn[$branch->ID] = $branch->post_title;
		}
		return $rtn;
	}

}
