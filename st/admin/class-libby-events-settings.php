<?php
/****** SETTINGS PAGE ******/

class Libby_Events_Settings_Page {
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
}
