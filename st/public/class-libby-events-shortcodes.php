<?php
/**
  * Class used to create the event calendar shortcode
  *
  * @uses EO_Calendar Widget class to generate calendar html
  * @ignore
  */
class Libby_Events_Shortcodes extends EventOrganiser_Shortcodes {
  static function handle_fullcalendar_shortcode_with_filter( $atts = array() ) {
    wp_enqueue_script( 'libby-events-calendar-filter' );
    return parent::handle_fullcalendar_shortcode( $atts );
  }
}
