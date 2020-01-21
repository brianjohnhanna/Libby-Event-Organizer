<?php

class venueTest extends EO_UnitTestCase
{

	public function setUp() {
		parent::setUp();

		global $wpdb;

		$wpdb->query( 'DELETE FROM ' . $wpdb->eo_venuemeta );
	}

	/**
	 * When an event is saved, if a new venue fails to create because it already exists
	 * we assign the event the ID of the pre-existing venue. To identify if a duplicate
	 * venue is created we use the returned error code (returned by wp_insert_term()).
	 * This unit test is to check if that error code ever changes!
	 *
	 * @see https://github.com/stephenharris/Event-Organiser/issues/202
	 */
	public function testExistingVenue()
	{
		$venue = array(
			'name'        => 'Test Venue',
		 	'description' => 'Description',
			'address'     => '1 Test Road',
			'city'        => 'Testville',
			'state'       => 'Testas',
			'country'     => 'United States of Tests',
 			'latitude'    => 0,
			'longitude'  => 0,
		);

		$venue_ids = eo_insert_venue( $venue['name'], $venue );
		$this->assertFalse( is_wp_error( $venue_ids ) );

		$venue_ids = eo_insert_venue( $venue['name'], $venue );
		$this->assertTrue( is_wp_error( $venue_ids ) );
		$this->assertEquals( 'term_exists', $venue_ids->get_error_code() );

	}

	/**
	 * When an event is saved, if a new venue fails to create because it already exists
	 * we assign the event the ID of the pre-existing venue. To identify if a duplicate
	 * venue is created we use the returned error code (returned by wp_insert_term()).
	 * This unit test is to check if that error code ever changes!
	 *
	 * @see https://github.com/stephenharris/Event-Organiser/issues/202
	 */
	public function testVenueHasLatLng()
	{
		$venue = array(
			'name'        => 'Test Venue',
			'description' => 'Description',
			'address'     => '1 Test Road',
			'city'        => 'Testville',
			'state'       => 'Testas',
			'country'     => 'United States of Tests',
			'latitude'    => 0,
			'longitude'  => 0,
		);

		$venue_ids = eo_insert_venue( $venue['name'], $venue );
		$this->assertFalse( eo_venue_has_latlng( $venue_ids['term_id'] ) );

		$venue = array(
			'name'        => 'Test Venue 2',
			'description' => 'Description',
			'address'     => '1 Test Road',
			'city'        => 'Testville',
			'state'       => 'Testas',
			'country'     => 'United States of Tests',
			'latitude'    => 0.001,
			'longitude'  => 0,
		);

		$venue_ids = eo_insert_venue( $venue['name'], $venue );
		$this->assertTrue( eo_venue_has_latlng( $venue_ids['term_id'] ) );

		$venue = array(
			'name'        => 'Test Venue 3',
			'description' => 'Description',
			'address'     => '1 Test Road',
			'city'        => 'Testville',
			'state'       => 'Testas',
			'country'     => 'United States of Tests',
			'latitude'    => 0,
			'longitude'  => 0.001,
		);

		$venue_ids = eo_insert_venue( $venue['name'], $venue );
		$this->assertTrue( eo_venue_has_latlng( $venue_ids['term_id'] ) );
	}

	/**
	 * Same test as above, but using longtitude rather tha longitude for backwards compatability
	 * @see https://wp-event-organiser.com/forums/topic/incorrect-spelling-of-argument-in-eo_insert_venue/
	 * @see https://github.com/ stephenharris/Event-Organiser/issues/202
	 */
	public function testVenueHasLatLngBackwardsCompatabilityLongitude()
	{
		$venue = array(
			'name'        => 'Test Venue',
			'description' => 'Description',
			'address'     => '1 Test Road',
			'city'        => 'Testville',
			'state'       => 'Testas',
			'country'     => 'United States of Tests',
			'latitude'    => 0,
			'longtitude'  => 0,
		);

		$venue_ids = eo_insert_venue( $venue['name'], $venue );
		$this->assertFalse( eo_venue_has_latlng( $venue_ids['term_id'] ) );

		$venue = array(
			'name'        => 'Test Venue 2',
			'description' => 'Description',
			'address'     => '1 Test Road',
			'city'        => 'Testville',
			'state'       => 'Testas',
			'country'     => 'United States of Tests',
			'latitude'    => 0.001,
			'longtitude'  => 0,
		);

		$venue_ids = eo_insert_venue( $venue['name'], $venue );
		$this->assertTrue( eo_venue_has_latlng( $venue_ids['term_id'] ) );

		$venue = array(
			'name'        => 'Test Venue 3',
			'description' => 'Description',
			'address'     => '1 Test Road',
			'city'        => 'Testville',
			'state'       => 'Testas',
			'country'     => 'United States of Tests',
			'latitude'    => 0,
			'longtitude'  => 0.001,
		);

		$venue_ids = eo_insert_venue( $venue['name'], $venue );
		$this->assertTrue( eo_venue_has_latlng( $venue_ids['term_id'] ) );
	}

	/**
	 * This test checks that the callback to the split_shared_term
	 * hook (handles venues being split is doing its job.
	 *
	 * @requires WordPress >= 4.2-alpha Term splitting did not happen before 4.2
	 */
	public function testPreSplitTerms() {
		global $wpdb;

		register_taxonomy( 'wptests_tax', 'event' );

		$t1 = wp_insert_term( 'Foo', 'wptests_tax' );
		$t2 = eo_insert_venue( 'Foo', array(
			'address' => 'Edinburgh Castle',
			'city'    => 'Edinburgh',
			'country' => 'UK'
		) );

		// Manually modify because split terms shouldn't naturally occur.
		$wpdb->update( $wpdb->term_taxonomy,
			array( 'term_id'          => $t2['term_id'] ),
			array( 'term_taxonomy_id' => $t1['term_taxonomy_id'] ),
			array( '%d' ),
			array( '%d' )
		);

		$events = $this->factory->event->create_many( 2 );
		wp_set_object_terms( $events[0], array( 'Foo' ), 'wptests_tax' );
		wp_set_object_terms( $events[1], array( 'Foo' ), 'event-venue' );

		// Verify that the terms are shared.
		$t1_terms = wp_get_object_terms( $events[0], 'wptests_tax' );
		$t2_terms = wp_get_object_terms( $events[1], 'event-venue' );
		$this->assertSame( $t1_terms[0]->term_id, $t2_terms[0]->term_id );

		//Split by updating venue
		eo_update_venue(  $t2_terms[0]->term_id, array(
			'name' => 'New Foo',
		) );

		$t1_terms = wp_get_object_terms( $events[0], 'wptests_tax' );
		$t2_terms = wp_get_object_terms( $events[1], 'event-venue' );
		$this->assertNotEquals( $t1_terms[0]->term_id, $t2_terms[0]->term_id );

		$address = eo_get_venue_address( $t2_terms[0]->term_id );
		$this->assertEquals( 'Edinburgh', $address['city'] );

	}

	/**
	 * Check that the upgrade routine run for users updating EO after
	 * updating to WP 4.2 is able to recover 'lost' data.
	 *
	 * @requires WordPress >= 4.2-alpha Testing post-WP-4.2 upgrade routine
	 */
	public function testPostSplitTermsUpgrade() {
		global $wpdb;

		remove_action( 'split_shared_term', '_eventorganiser_handle_split_shared_terms', 10 );

		register_taxonomy( 'wptests_tax', 'event' );

		//Create terms - they'll have unique term IDs
		$t1 = wp_insert_term( 'Foo', 'wptests_tax' );
		$t2 = eo_insert_venue( 'Foo', array(
			'address' => 'Edinburgh Castle',
			'city'    => 'Edinburgh',
			'country' => 'UK'
		) );
		$t3 = wp_insert_term( 'Foo', 'event-category' );

		//Manually modify the terms so they share term IDs
		$wpdb->update( $wpdb->term_taxonomy,
			array( 'term_id'          => $t1['term_id'] ),
			array( 'term_taxonomy_id' => $t2['term_taxonomy_id'] ),
			array( '%d' ),
			array( '%d' )
		);
		$wpdb->update( $wpdb->term_taxonomy,
			array( 'term_id'          => $t1['term_id'] ),
			array( 'term_taxonomy_id' => $t3['term_taxonomy_id'] ),
			array( '%d' ),
			array( '%d' )
		);

		//Insert/move data so it is assigned to 'pre-split ID'.
		update_option( 'eo-event-category_' . $t1['term_id'], array(
			'colour' => '#ff0000'
		) );
		$wpdb->update( $wpdb->eo_venuemeta,
			array( 'eo_venue_id' => $t1['term_id'] ),
			array( 'eo_venue_id' => $t2['term_id'] )
		);

		//Create event and assign terms so we can retrieve term IDs
		$events = $this->factory->event->create_many( 3 );
		wp_set_object_terms( $events[0], array( 'Foo' ), 'wptests_tax' );
		wp_set_object_terms( $events[1], array( 'Foo' ), 'event-venue' );
		wp_set_object_terms( $events[2], array( 'Foo' ), 'event-category' );

		// Verify that the term IDs are shared.
		$t1_terms = wp_get_object_terms( $events[0], 'wptests_tax' );
		$t2_terms = wp_get_object_terms( $events[1], 'event-venue' );
		$t3_terms = wp_get_object_terms( $events[2], 'event-category' );
		$this->assertSame( $t1_terms[0]->term_id, $t2_terms[0]->term_id );
		$this->assertSame( $t1_terms[0]->term_id, $t3_terms[0]->term_id );

		//Split by updating venue
		eo_update_venue( $t2_terms[0]->term_id, array(
			'name' => 'Venue Foo',
		) );
		wp_update_term( $t3_terms[0]->term_id, 'event-category', array(
			'name' => 'Category Foo',
		));

		//Check meta data is "lost"
		$t2_terms = wp_get_object_terms( $events[1], 'event-venue' );
		$t3_terms = wp_get_object_terms( $events[2], 'event-category' );
		$address = eo_get_venue_address( $t2_terms[0]->term_id );
		$this->assertEquals( '', '' );
		$meta = get_option( 'eo-event-category_' . $t3_terms[0]->term_id );
		$this->assertEquals( false, $meta );

		//Run upgrade routine
		eventorganiser_021200_update();

		//Check data is recovered
		$t2_terms = wp_get_object_terms( $events[1], 'event-venue' );
		$t3_terms = wp_get_object_terms( $events[2], 'event-category' );
		$address = eo_get_venue_address( $t2_terms[0]->term_id );
		$this->assertEquals( 'Edinburgh', $address['city'] );
		$meta = get_option( 'eo-event-category_' . $t3_terms[0]->term_id );
		$this->assertEquals( '#ff0000', $meta['colour'] );

		add_action( 'split_shared_term', '_eventorganiser_handle_split_shared_terms', 10, 4 );
	}


	public function testInsertLongitude(){

		$venue = array(
			'name'        => 'Test Venue',
			'description' => 'Description',
			'address'     => '1 Test Road',
			'city'        => 'Testville',
			'state'       => 'Testas',
			'country'     => 'United States of Tests',
			'latitude'    => 0,
			'longitude'   => 456,
		);
		$venue_id = eo_insert_venue( $venue['name'], $venue );
		$this->assertEquals(456, eo_get_venue_lng($venue_id['term_id']));
	}

	/**
	 * @see https://wp-event-organiser.com/forums/topic/incorrect-spelling-of-argument-in-eo_insert_venue/
	 */
	public function testInsertLongitudeBackwardsCompatability(){

		$venue = array(
			'name'        => 'Test Venue',
			'description' => 'Description',
			'address'     => '1 Test Road',
			'city'        => 'Testville',
			'state'       => 'Testas',
			'country'     => 'United States of Tests',
			'latitude'    => 0,
			'longtitude'   => 456,
		);
		$venue_id = eo_insert_venue( $venue['name'], $venue );
		$this->assertEquals(456, eo_get_venue_lng($venue_id['term_id']));
	}

	/**
	 * @see https://wp-event-organiser.com/forums/topic/incorrect-spelling-of-argument-in-eo_insert_venue/
	 */
	public function testInsertLongitudeOverridesLongtitude(){

		$venue = array(
			'name'        => 'Test Venue',
			'description' => 'Description',
			'address'     => '1 Test Road',
			'city'        => 'Testville',
			'state'       => 'Testas',
			'country'     => 'United States of Tests',
			'latitude'    => 0,
			'longitude'   => 456,
			'longtitude'   => 789,
		);
		$venue_id = eo_insert_venue( $venue['name'], $venue );
		$this->assertEquals(456, eo_get_venue_lng($venue_id['term_id']));
	}

	public function testUpdateLongitude(){

		$venue = array(
			'name'        => 'Test Venue',
			'description' => 'Description',
			'address'     => '1 Test Road',
			'city'        => 'Testville',
			'state'       => 'Testas',
			'country'     => 'United States of Tests',
			'latitude'    => 0,
			'longitude'  => 0,
		);
		$venue_id = eo_insert_venue( $venue['name'], $venue );

		eo_update_venue($venue_id['term_id'],array(
			'longitude' => 123
		));

		$this->assertEquals(123, eo_get_venue_lng($venue_id['term_id']));
	}

	/**
	 * @see https://wp-event-organiser.com/forums/topic/incorrect-spelling-of-argument-in-eo_insert_venue/
	 */
	public function testUpdateLongtitudeBackwardsCompatability(){
		$venue = array(
			'name'        => 'Test Venue',
			'description' => 'Description',
			'address'     => '1 Test Road',
			'city'        => 'Testville',
			'state'       => 'Testas',
			'country'     => 'United States of Tests',
			'latitude'    => 0,
			'longitude'  => 0,
		);
		$venue_id = eo_insert_venue( $venue['name'], $venue );

		eo_update_venue($venue_id['term_id'],array(
			'longtitude' => 123
		));

		$this->assertEquals(123, eo_get_venue_lng($venue_id['term_id']));
	}

	/**
	 * @see https://wp-event-organiser.com/forums/topic/incorrect-spelling-of-argument-in-eo_insert_venue/
	 */
	public function testUpdateLongitudeOveridesLongtitude(){
		$venue = array(
			'name'        => 'Test Venue',
			'description' => 'Description',
			'address'     => '1 Test Road',
			'city'        => 'Testville',
			'state'       => 'Testas',
			'country'     => 'United States of Tests',
			'latitude'    => 0,
			'longitude'  => 0,
		);
		$venue_id = eo_insert_venue( $venue['name'], $venue );

		eo_update_venue($venue_id['term_id'],array(
			'longitude' => 123,
			'longtitude' => 456,
		));

		$this->assertEquals(123, eo_get_venue_lng($venue_id['term_id']));
	}

	public function testPreInsertVenueFilter()
	{
		add_filter('eventorganiser_pre_insert_venue', array($this, 'pre_insert_venue_callback'));

		$venue1 = array(
			'name'        => 'Test Venue',
			'description' => 'Description',
			'address'     => '1 Test Road',
			'city'        => 'Testville',
			'state'       => 'Testas',
			'country'     => 'United States of Tests',
			'latitude'    => 0,
			'longitude'  => 0,
		);
		$venue_ids = eo_insert_venue( $venue1['name'], $venue1 );
		
		$venue1 = eo_get_venue_by( 'id', $venue_ids['term_id'] );
		$this->assertEquals('test-venue-testville', $venue1->slug);

		$venue2 = array(
			'name'        => 'Test Venue',
			'description' => 'Description',
			'address'     => '1 Test Road',
			'city'        => 'AnotherCity',
			'state'       => 'Testas',
			'country'     => 'United States of Tests',
			'latitude'    => 0,
			'longitude'  => 0,
		);

		$venue_ids = eo_insert_venue( $venue2['name'], $venue2 );
		
		$venue2 = eo_get_venue_by( 'id', $venue_ids['term_id'] );
		$this->assertEquals('test-venue-anothercity', $venue2->slug);

		remove_filter('eventorganiser_pre_insert_venue', array($this, 'pre_insert_venue_callback'));
	}

	public function pre_insert_venue_callback($args){
		$args['slug'] = sanitize_title($args['name'] . ' ' . $args['city']);
		return $args;
	}

}
