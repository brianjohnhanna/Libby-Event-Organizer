<?php

class eventFunctionsTest extends EO_UnitTestCase
{

	public function setUp() {
		parent::setUp();

		$this->event = array(
			'start'	   => new DateTime( '2014-07-09 13:02:00', eo_get_blog_timezone() ),
			'end'	   => new DateTime( '2014-07-09 14:02:00', eo_get_blog_timezone() ),
			'all_day'  => 0,
			'schedule' => 'once',
		);

		$this->event_id      = $this->factory->event->create( $this->event );
		$occurrences         = array_keys( eo_get_the_occurrences( $this->event_id ) );
		$this->occurrence_id = array_pop(  $occurrences );
	}

	public function testGetTheStart(){
		$this->assertEquals( $this->event['start'], eo_get_the_start( DATETIMEOBJ, $this->event_id, $this->occurrence_id ) );
	}

	public function testGetTheEnd(){
		$this->assertEquals( $this->event['end'], eo_get_the_end( DATETIMEOBJ, $this->event_id, $this->occurrence_id ) );
	}

	public function testTheStart(){
		ob_start();
		eo_the_start( 'Y-m-d H:i:s', $this->event_id, $this->occurrence_id );
		$actual = ob_get_contents();
		ob_end_clean();
		$this->assertEquals( '2014-07-09 13:02:00', $actual );
	}

	public function testTheEnd(){
		ob_start();
		eo_the_end( 'Y-m-d H:i:s', $this->event_id, $this->occurrence_id );
		$actual = ob_get_contents();
		ob_end_clean();
		$this->assertEquals( '2014-07-09 14:02:00', $actual );
	}

	public function testDeprecatedAPI(){
		$this->assertEquals( $this->event['start'], eo_get_the_start( DATETIMEOBJ, $this->event_id, null, $this->occurrence_id ) );
		$this->assertEquals( $this->event['end'], eo_get_the_end( DATETIMEOBJ, $this->event_id, null, $this->occurrence_id ) );

		ob_start();
		eo_the_start( 'Y-m-d H:i:s', $this->event_id, null, $this->occurrence_id );
		$actual = ob_get_contents();
		ob_end_clean();

		$this->assertEquals( '2014-07-09 13:02:00', $actual );

		ob_start();
		eo_the_end( 'Y-m-d H:i:s', $this->event_id, null, $this->occurrence_id );
		$actual = ob_get_contents();
		ob_end_clean();

		$this->assertEquals( '2014-07-09 14:02:00', $actual );
	}

	/**
	 * Test that using eo_get_add_to_google_link() does not reset timezone of
	 * start/end date of event
	 * @see https://wordpress.org/support/topic/eo_get_add_to_google_link?replies=1
	 */
	public function testAddToGoogleLink()
	{

		$tz              = ini_get('date.timezone');
		$original_tz     = get_option( 'timezone_string' );
		$original_offset = get_option( 'gmt_offset' );

		update_option( 'timezone_string', '' );
		update_option( 'gmt_offset', 10 );

		$event_id = $this->factory->event->create(
			array(
				'start'		=> new DateTime( '2014-07-09 13:02:00', eo_get_blog_timezone() ),
				'end'		=> new DateTime( '2014-07-09 14:02:00', eo_get_blog_timezone() ),
				'all_day' 	=> 0,
				'schedule'	=> 'once',
			)
		);

		$occurrences    = eo_get_the_occurrences( $event_id );
		$occurrence_ids = array_keys( $occurrences );
		$occurrence_id  = array_shift( $occurrence_ids );

		$actual = eo_get_the_start( 'Y-m-d H:i:s', $event_id, null, $occurrence_id );
		$this->assertEquals( '2014-07-09 13:02:00', $actual );

		eo_get_add_to_google_link( $event_id, $occurrence_id );

		$actual = eo_get_the_start( 'Y-m-d H:i:s', $event_id, null, $occurrence_id );
		$this->assertEquals( '2014-07-09 13:02:00', $actual );

		update_option( 'timezone_string', $original_tz );
		update_option( 'gmt_offset', $original_offset );
	}

	public function testEventMetaList(){

		$event_id = $this->factory->event->create(
			array(
				'start'		=> new DateTime( '2014-07-09 13:02:00', eo_get_blog_timezone() ),
				'end'		=> new DateTime( '2014-07-09 14:02:00', eo_get_blog_timezone() ),
				'all_day' 	=> 0,
				'schedule'	=> 'once',
			)
		);

		$tag = wp_insert_term( 'foobar', 'event-tag' );

		if ( is_wp_error( $tag ) ) {
			throw new Exception( $tag->get_error_message() );
		}

		wp_set_object_terms( $event_id, (int) $tag['term_id'], 'event-tag' );

		$cat = wp_insert_term( 'hellworld', 'event-category' );
		wp_set_object_terms( $event_id, (int) $cat['term_id'], 'event-category' );

		$html = eo_get_event_meta_list( $event_id );

		$expected = file_get_contents( EO_DIR_TESTDATA . '/event-functions/event-meta-list.html' );

		$this->assertXmlStringEqualsXmlString( $expected, $html );

	}

	public function testMicroDataEventFormat(){
		$event_id = $this->factory->event->create(
			array(
				'start'    => new DateTime( '2014-07-09 13:02:00', eo_get_blog_timezone() ),
				'end'      => new DateTime( '2014-07-09 14:02:00', eo_get_blog_timezone() ),
				'all_day'  => 0,
				'schedule' => 'once',
			)
		);
		$occurrence_ids = array_keys( eo_get_the_occurrences_of( $event_id ) );
		$occurrence_id = array_shift( $occurrence_ids );

		$expected = '<time itemprop="startDate" datetime="2014-07-09T13:02:00+00:00">July 9, 2014 1:02 pm</time>'
					.' &ndash; <time itemprop="endDate" datetime="2014-07-09T14:02:00+00:00">2:02 pm</time>';

		$this->assertEquals( $expected, eo_format_event_occurrence( $event_id, $occurrence_id ) );

	}

	public function testMicroDataAllDayEventFormat(){
		$event_id = $this->factory->event->create(
			array(
				'start'    => new DateTime( '2014-07-09 13:02:00', eo_get_blog_timezone() ),
				'end'      => new DateTime( '2014-07-09 14:02:00', eo_get_blog_timezone() ),
				'all_day'  => 1,
				'schedule' => 'once',
			)
		);
		$occurrence_ids = array_keys( eo_get_the_occurrences_of( $event_id ) );
		$occurrence_id = array_shift( $occurrence_ids );

		$expected = '<time itemprop="startDate" datetime="2014-07-09">July 9, 2014</time>';

		$this->assertEquals( $expected, eo_format_event_occurrence( $event_id, $occurrence_id ) );

	}


	public function testMicroDataAllDayLongEventFormat(){
		$event_id = $this->factory->event->create(
			array(
				'start'    => new DateTime( '2014-07-09 13:02:00', eo_get_blog_timezone() ),
				'end'      => new DateTime( '2014-07-10 14:02:00', eo_get_blog_timezone() ),
				'all_day'  => 1,
				'schedule' => 'once',
			)
		);
		$occurrence_ids = array_keys( eo_get_the_occurrences_of( $event_id ) );
		$occurrence_id  = array_shift( $occurrence_ids );

		$expected = '<time itemprop="startDate" datetime="2014-07-09">July 9</time>'
		.' &ndash; <time itemprop="endDate" datetime="2014-07-10">10, 2014</time>';

		$this->assertEquals( $expected, eo_format_event_occurrence( $event_id, $occurrence_id ) );

	}

	public function testEventFormat(){
		$event_id = $this->factory->event->create(
			array(
				'start'    => new DateTime( '2014-07-09 13:02:00', eo_get_blog_timezone() ),
				'end'      => new DateTime( '2014-07-09 14:02:00', eo_get_blog_timezone() ),
				'all_day'  => 0,
				'schedule' => 'once',
			)
		);
		$occurrence_ids = array_keys( eo_get_the_occurrences_of( $event_id ) );
		$occurrence_id = array_shift( $occurrence_ids );

		$expected = 'July 9, 2014 1:02 pm &ndash; 2:02 pm';

		$this->assertEquals( $expected, eo_format_event_occurrence( $event_id, $occurrence_id, false, false, ' &ndash; ', false ) );

	}

	public function testAllDayEventFormat(){
		$event_id = $this->factory->event->create(
			array(
				'start'    => new DateTime( '2014-07-09 13:02:00', eo_get_blog_timezone() ),
				'end'      => new DateTime( '2014-07-09 14:02:00', eo_get_blog_timezone() ),
				'all_day'  => 1,
				'schedule' => 'once',
				)
		);
		$occurrence_ids = array_keys( eo_get_the_occurrences_of( $event_id ) );
		$occurrence_id = array_shift( $occurrence_ids );

		$expected = 'July 9, 2014';

		$this->assertEquals( $expected, eo_format_event_occurrence( $event_id, $occurrence_id, false, false, ' &ndash; ', false ) );

	}

	public function testAllDayLongEventFormat(){
		$event_id = $this->factory->event->create(
			array(
				'start'    => new DateTime( '2014-07-09 13:02:00', eo_get_blog_timezone() ),
				'end'      => new DateTime( '2014-07-10 14:02:00', eo_get_blog_timezone() ),
				'all_day'  => 1,
				'schedule' => 'once',
			)
		);
		$occurrence_ids = array_keys( eo_get_the_occurrences_of( $event_id ) );
		$occurrence_id  = array_shift( $occurrence_ids );

		$expected = 'July 9 &ndash; 10, 2014';

		$this->assertEquals( $expected, eo_format_event_occurrence( $event_id, $occurrence_id, false, false, ' &ndash; ', false ) );

	}

	public function testNextOccurrence(){

		$now = new DateTime( 'now', eo_get_blog_timezone() );

		$start = clone $now;
		$start->modify( '-5 days' );
		$end = clone $start;
		$end->modify( '+1 hour' );

		$tomorrow = clone $now;
		$tomorrow->modify( '+1 day' );

		$event_id = $this->factory->event->create(
			array(
				'start'     => $start,
				'end'       => $end,
				'all_day'   => 0,
				'schedule'  => 'daily',
				'frequency' => 2,
				'until'     =>  new DateTime( '+3 days', eo_get_blog_timezone() )
			)
		);

		ob_start();
		eo_next_occurrence( 'Y-m-d H:i', $event_id );
		$actual = ob_get_contents();
		ob_end_clean();

		$this->assertEquals( $tomorrow->format(  'Y-m-d H:i' ), $actual );
	}

	public function testNextOccurrenceOf(){

		$now = new DateTime( 'now', eo_get_blog_timezone() );

		$start = clone $now;
		$start->modify( '-5 days' );
		$end = clone $start;
		$end->modify( '+1 hour' );

		$tomorrow = clone $now;
		$tomorrow->modify( '+1 day' );
		$tomorrow->setTime ( $tomorrow->format("H"), $tomorrow->format("i"), 0 );

		$event_id = $this->factory->event->create(
			array(
				'start'     => $start,
				'end'       => $end,
				'all_day'   => 0,
				'schedule'  => 'daily',
				'frequency' => 2,
				'until'     =>  new DateTime( '+3 days', eo_get_blog_timezone() )
			)
		);

		$occurrence = eo_get_next_occurrence_of( $event_id );

		$this->assertInstanceOf('DateTime', $occurrence['start']);
		$this->assertInstanceOf('DateTime', $occurrence['end']);
		$this->assertInternalType('int', $occurrence['occurrence_id']);

		$this->assertEquals( $tomorrow, $occurrence['start'] );
	}

	public function testNextOccurrenceOfDoesNotExist(){

		$start = new DateTime( '-5 days', eo_get_blog_timezone() );
		$end = clone $start;
		$end->modify( '+1 hour' );

		$event_id = $this->factory->event->create(
			array(
				'start'     => $start,
				'end'       => $end,
				'all_day'   => 0,
				'schedule'  => 'daily',
				'frequency' => 2,
				'until'     =>  new DateTime( '-1 day', eo_get_blog_timezone() )
			)
		);

		$actual = eo_get_next_occurrence_of( $event_id );

		$this->assertEquals( false, $actual );
	}

	public function testCurrentOccurrence(){

		$now = new DateTime( 'now', eo_get_blog_timezone() );
		$hour_ago = new DateTime( '-1 hour', eo_get_blog_timezone() );
		$hour_ago->setTime ( $hour_ago->format("H"), $hour_ago->format("i"), 0 );

		$start = clone $hour_ago;
		$start->modify( '-2 days' );
		$start->setTime( $hour_ago->format('H'), $hour_ago->format('i'), 0 );
		$end = clone $start;
		$end->modify( '+2 hours' );

		$tomorrow = clone $now;
		$tomorrow->modify( '+1 day' );

		$event_id = $this->factory->event->create(
			array(
				'start'     => $start,
				'end'       => $end,
				'all_day'   => 0,
				'schedule'  => 'daily',
				'frequency' => 1,
				'until'     =>  new DateTime( '+3 days', eo_get_blog_timezone() )
			)
		);

		$occurrence = eo_get_current_occurrence_of( $event_id );

		$occurrences = eo_get_the_occurrences_of( $event_id );
		$formatted_occurrences = $this->format_datetime_ranges( $occurrences, 'Y-m-d H:i:s' );

		$this->assertInstanceOf(
			'DateTime',
			$occurrence['start'],
			sprintf( 'Found dates: %s', implode( ', ', $formatted_occurrences ) )
		);
		$this->assertInstanceOf(
			'DateTime',
			$occurrence['end'],
			sprintf( 'Found dates: %s', implode( ', ', $formatted_occurrences ) )
		);

		$this->assertEquals( $hour_ago, $occurrence['start'] );
	}

	private function format_datetime_ranges( $occurrences, $format ) {
		$formatted = array();
		foreach ( $occurrences as $occurrence ) {
			$formatted[] = eo_format_datetime_range( $occurrence['start'], $occurrence['end'], $format );
		}
		return $formatted;
	}

	public function testCurrentOccurrenceDoesNotExist(){

		$now = new DateTime( 'now', eo_get_blog_timezone() );
		$in_one_hour = new DateTime( '+1 hour', eo_get_blog_timezone() );

		$start = clone $in_one_hour;
		$start->modify( '-2 days' );
		$start->setTime( $in_one_hour->format('H'), $in_one_hour->format('i'), 0 );
		$end = clone $start;
		$end->modify( '+2 hours' );

		$tomorrow = clone $now;
		$tomorrow->modify( '+1 day' );

		$event_id = $this->factory->event->create(
			array(
				'start'     => $start,
				'end'       => $end,
				'all_day'   => 0,
				'schedule'  => 'daily',
				'frequency' => 1,
				'until'     =>  new DateTime( '+3 days' )
			)
		);

		$occurrence = eo_get_current_occurrence_of( $event_id );

		$this->assertEquals(
			false,
			$occurrence,
			'A current occurrence found when an occurrence shouldn\'t be running'
		);
	}

}
