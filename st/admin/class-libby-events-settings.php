<?php
/****** SETTINGS PAGE ******/

class Libby_Events_Settings_Page {

	public function rename_settings_page() {
	  global $menu;
	   global $submenu;

	  //  $submenu['options-general.php'][5][0];
	   foreach ($submenu['options-general.php'] as $key => $option) {
	     if ($option[2] === ''){return false;};
	   }

	  //  var_dump($menu);
  }

	/**
	 * Set default options for EO using eventorganiser_options
	 *
	 * @param array $options_array The default options
	 * @see vendor/event-organiser/event-organiser.php
	 */
	public function set_default_options( $options ) {
		$options['supports'] = array( 'title', 'editor', 'thumbnail', 'eventtag', 'event-venue' );
		return $options;
	}

	/**
	 * Add tabs to the EO Settings Page
	 * @param array $tabs The tabs to output to the settings page, keyed by a unique identifier
	 */
	public function add_settings_tabs( $tabs ) {
		$tabs['libby_bookings'] = 'Bookings';
		return $tabs;
	}
}
