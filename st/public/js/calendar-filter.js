(function($){
  function filterByVenue() {
    var venues, venue, children = [];
    $.get(ajax_url, {action:'get_all_venues_ajax'}, function(response){
      venues = response.data;
    });
    wp.hooks.addFilter( 'eventorganiser.fullcalendar_render_event', function( render, event, element, view ) {
			var venueSlug = $('.eo-fc-filter-venue').val();
			if (typeof venueSlug === 'undefined') {
				return render;
			}
      venue = $.grep(venues, function(e){ return e.slug === venueSlug; });
			if (typeof venue[0] !== 'undefined' && venue[0].parent === 0) {
        // We have a parent venue... Now we need to make an array of children's slugs
        children = $.grep(venues, function(e){ return e.parent === venue[0].term_id; });
        for ( var i=0; i<children.length; i++ ) {
          if (children[i].slug === event.venue_slug) {
            return true;
          }
          return false;
        }
      }
			return render;
		});
  }

  jQuery(document).ready(function($){
		if (typeof wp === 'undefined' || typeof wp.hooks === 'undefined') return;
    filterByVenue();
  });
})(jQuery);
