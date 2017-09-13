=== Libby Event Organizer ===
Contributors: brianjohnhanna, stephenharris
Tags: events, event, event categories, event organizer, events calendar, event management, ical, locations, google map, widget, venues, maps, gigs, shows,
Requires at least: 3.8.0
Tested up to: 4.8.1
Stable tag: {{version}}
License: GPLv3

Create and maintain events, including complex reoccurring patterns, venue management (with Google maps), calendars and customisable event lists

== Description ==

Libby Event Organizer adds event management that integrates well with your WordPress site. By using WordPress' in-built 'custom post type', this plug-in allows you to create events that have the same functionality as posts, while adding further features that allow you to manage your events. This includes the possibility of repeating your event according to complex schedules and assign your events to venues. This can all be done through an intuitive user interface, which allows you to view your events in the familiar WordPress list or in a calendar page in the amin area.

= Features =

* Create one-time events or reoccuring events.
* Allows complex reoccuring patterns for events. You can create events that last an arbirtary time, and repeat over a specified period. Supports complex schedules such as *On the third Tuesday of every fourth month* or *Every month on the 16th*.
* Ability to add or remove specific dates to an event
* **Event functions** available which extend the post functions (e.g. `the_title()`,`get_the_author()`, `the_author()`) to ouput or return event data (the start date-time, the venue etc). For examples of their use see the [documentation](http://www.wp-event-organiser.com/documentation/function-reference/) or the included template files.
* Create and maintain venues for your events, with **Google maps** support and a fully-featured content editor.
* **Widgets**:
  * Calendar widget - displays a calendar (identical to the standard WordPress Calendar)
  * Event List widget - displays a list of events, with options to specify the number of events, restrict by categories or venues etc.
  * Event Agenda widget
* Year, month and day archive pages
* **Shortcodes**:
  * (full)Calendar, includes optional category & venue filters.
  * (widget) Calendar
  * Event List (similar to Event List widget)
  * Event Agenda (similar to Event Agenda widget)
  * Venue map
  * Subscribe to event feeds
* **Relative date queries** (for example, query events that finished in the last 24 hours, or events starting in the coming week).
* Assign events to categories and tags, and view events by category or tag.
* Color-coded event categories.
* **Custom permissions** allow to specifiy which roles have the ability to create, edit and delete events or manage venues.
* Venue pages, to view events by venue.
* **Export/import** events to and from ICAL files.
* Delete individual occurrences of events.
* **Public events feed:** allow visitors to subscribe to your events, or a particular venue / category.
* Supports 'pretty permalinks' for event pages, event archives, event category and venue pages.
* (Optionally) automatically delete expired events.

= What ShortCodes are available? =

Libby Event Organizer provides the following shortcodes:

* `[eo_events]`  - displays a list of events allows with options to filter by venue, categories and dates.
* `[eo_calendar]`  - displays a widget-calendar of your events, similiar to WordPress' calendar, and navigated with AJAX.
* `[eo_fullcalendar]`  - displays a calendar, similiar to the admin calendar, with optional month, week and day views and category and venue filters.
* `[eo_venue_map]` - displays a Google map of the current venue, or of a particular venue given as an attribute.
* `[eo_subscribe]` - wraps the content in a link which allows visitors to subscribe to your events; there are two types: 'Google' and 'Webcal'.
