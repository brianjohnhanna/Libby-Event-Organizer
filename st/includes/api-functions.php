<?php

/**
 * Define API functions to be used in the theme templates and sister plugins
 */

if ( ! function_exists( 'eo_get_event_ical_link' ) ) :
  function eo_get_event_ical_link( $event_id = null ) {
    if ( ! isset( $event_id ) ) {
      global $post;
      $event_id = $post->ID;
    }

    return add_query_arg(array(
      'ical_download' => $event_id,
    ));
  }
endif;
