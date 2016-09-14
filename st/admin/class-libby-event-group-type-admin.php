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
class Libby_Events_Event_Group_Type_Admin {

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
		$group_type_meta = new_cmb2_box( array(
			'id'               => $prefix . 'group_type_meta',
			'title'            => __( 'Group Type Information', 'libby' ), // Doesn't output for term boxes
			'object_types'     => array( 'term' ), // Tells CMB2 to use term_meta vs post_meta
			'taxonomies'       => array( 'group-type' ), // Tells CMB2 which taxonomies should have these fields
			'new_term_section' => true, // Will display in the "Add New Category" section
		) );

		$group_type_meta->add_field( array(
		    'name' => 'Make Group Type Public',
				'desc' => 'Make an option for submitted events.',
		    'id'   => $prefix . 'public',
		    'type' => 'checkbox',
		) );

	}

}
