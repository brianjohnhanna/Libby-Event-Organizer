<?php

/**
 * Modifications to Event Organiser by Stephen Harris
 *
 * Forked by Stirling Technologies for Libby Event Organizer
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-libby-events.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_libby_events() {

	$plugin = new Libby_Events();
	$plugin->run();

}
run_libby_events();
