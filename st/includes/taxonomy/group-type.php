<?php
/**
 * Register the Group Type Tag Taxonomy
 */

if ( ! function_exists( 'libby_register_event_group_type_taxonomy' ) ) {

  // Register Custom Taxonomy
  function libby_register_event_group_type_taxonomy() {

  	$labels = array(
  		'name'                       => _x( 'Group Types', 'Taxonomy General Name', 'libby' ),
  		'singular_name'              => _x( 'Group Type', 'Taxonomy Singular Name', 'libby' ),
  		'menu_name'                  => __( 'Group Types', 'libby' ),
  		'all_items'                  => __( 'All Group Types', 'libby' ),
  		'parent_item'                => __( 'Parent Group Type', 'libby' ),
  		'parent_item_colon'          => __( 'Parent Group Type:', 'libby' ),
  		'new_item_name'              => __( 'New Group Type Name', 'libby' ),
  		'add_new_item'               => __( 'Add New Group Type', 'libby' ),
  		'edit_item'                  => __( 'Edit Group Type', 'libby' ),
  		'update_item'                => __( 'Update Group Type', 'libby' ),
  		'view_item'                  => __( 'View Group Type', 'libby' ),
  		'separate_items_with_commas' => __( 'Separate group types with commas', 'libby' ),
  		'add_or_remove_items'        => __( 'Add or remove group types', 'libby' ),
  		'choose_from_most_used'      => __( 'Choose from the most used', 'libby' ),
  		'popular_items'              => __( 'Popular Group Types', 'libby' ),
  		'search_items'               => __( 'Search Group Types', 'libby' ),
  		'not_found'                  => __( 'Not Found', 'libby' ),
  		'no_terms'                   => __( 'No group types', 'libby' ),
  		'items_list'                 => __( 'Group Types list', 'libby' ),
  		'items_list_navigation'      => __( 'Group Types list navigation', 'libby' ),
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
  	register_taxonomy( 'group-type', array( 'event' ), $args );

  }
  add_action( 'init', 'libby_register_event_group_type_taxonomy', 99 );

}
