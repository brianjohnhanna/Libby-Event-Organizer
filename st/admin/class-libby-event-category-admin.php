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
class Libby_Events_Event_Category_Admin {

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
		$category_meta = new_cmb2_box( array(
			'id'               => $prefix . 'category_meta',
			'title'            => __( 'Category Information', 'libby' ), // Doesn't output for term boxes
			'object_types'     => array( 'term' ), // Tells CMB2 to use term_meta vs post_meta
			'taxonomies'       => array( 'event-category' ), // Tells CMB2 which taxonomies should have these fields
			'new_term_section' => true, // Will display in the "Add New Category" section
		) );

		$category_meta->add_field( array(
		    'name' => 'Make Category Public',
				'desc' => 'Make an option for submitted events.',
		    'id'   => $prefix . 'public',
		    'type' => 'checkbox',
		) );

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

}
