<?php

use Behat\Behat\Context\Context,
	Behat\Behat\Context\SnippetAcceptingContext,
	Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Gherkin\Node\TableNode;
use StephenHarris\WordPressBehatExtension\Context\WordPressInboxFactoryAwareContext;
use Behat\Mink\Exception\ElementNotFoundException,
	Behat\Mink\Exception\ExpectationException;
use \StephenHarris\WordPressBehatExtension\Context\PostTypes\WordPressPostContext;
use \Behat\MinkExtension\Context\MinkAwareContext;
use Behat\Mink\Mink;

//TODO fix sendmail

class FeatureContext extends WordPressPostContext implements Context, MinkAwareContext, SnippetAcceptingContext {

	use StephenHarris\WordPressBehatExtension\Context\Util\Spin {
		fillField as spinFillField;
	}
	
	private $mink;
	
	private $minkParameters;
	
	/**
	 * Location to store screenshots, or false if none are to be taken
	 * @var string|bool
	 */
	protected $screenshot_dir = false;

	public function __construct($screenshot_dir=false) {
		if ( $screenshot_dir ) {
			$this->screenshot_dir = rtrim( $screenshot_dir, '/' ) . '/';
		}
	}

	/**
     * Sets Mink instance.
     *
     * @param Mink $mink Mink session manager
     */
    public function setMink(Mink $mink)
    {
        $this->mink = $mink;
    }
    /**
     * Returns Mink instance.
     *
     * @return Mink
     */
    public function getMink()
    {
        if (null === $this->mink) {
            throw new \RuntimeException(
                'Mink instance has not been set on Mink context class. ' . 
                'Have you enabled the Mink Extension?'
            );
        }
        return $this->mink;
	}
	
	/**
     * Sets parameters provided for Mink.
     *
     * @param array $parameters
     */
    public function setMinkParameters(array $parameters)
    {
        $this->minkParameters = $parameters;
	}
	
	/**
	 * Add these events to this wordpress installation
	 *
	 * @see eo_insert_event
	 *
	 * @Given /^there are events$/
	 */
	public function thereAreEvents(TableNode $table)
	{
		$tz = eo_get_blog_timezone();
		foreach ($table->getHash() as $postData) {

			foreach ( $postData as $key => $value ) {
				switch( $key ) {
					case 'start':
					case 'end':
					case 'until':

						//Support 'relative' placeholders
						$value = str_replace(
							array( 'd', 'm', 'Y'),
							array( date('d'), date('m'), date('Y')
						), $value );

						$postData[$key] = new DateTime( $value, $tz );
						break;
				}
			}

			$event_id = eo_insert_event($postData);

			if (!is_int($event_id)) {

				$message = 'Invalid event information schema';

				if (is_wp_error($event_id)) {
					$message .= ': ' . $event_id->get_error_message();
				}
				throw new \InvalidArgumentException( $message );
			}
		}
	}

	/**
	 * @Given there are venues
	 *
	 * @see eo_insert_venue
	 */
	public function thereAreVenues(TableNode $venues)
	{
		foreach ($venues->getHash() as $venueData) {
			$return = eo_insert_venue($venueData['name'],$venueData);
			if (is_wp_error($return)) {
				throw new \InvalidArgumentException(sprintf("Invalid venue information schema: %s", $return->get_error_message()));
			}
		}
	}


	/**
	 * @Then the event :title should have the following schedule
	 */
	public function theEventShouldHaveTheFollowingSchedule($title, TableNode $fields)
	{

		$event = get_page_by_title( $title, OBJECT, 'event' );
		if ( ! $event ) {
			throw new \InvalidArgumentException( sprintf( 'Event "%s" was  not found', $title ) );
		}

		$correct = true;

		$schedule = eo_get_event_schedule( $event->ID );
		$tz       = eo_get_blog_timezone();
		$actual   = array();

		foreach ( $fields->getRowsHash() as $field => $value ) {

			switch( $field ) {
				case 'start':
				case 'end':
				case 'until':
					$actual_value = $schedule[$field]->format( 'Y-m-d h:ia' );
					break;
				case 'recurrence':
					$actual_value = $schedule['schedule'];
					break;
				case 'recurrence_meta':
					$actual_value = $schedule['schedule_meta'];
					break;
				case 'frequency':
					$actual_value = $schedule[$field];
					break;
				default:
					$actual_value = false;
			}
			$actual[] = array( $field, $actual_value );
		}

		$actual_table = new TableNode( $actual );

		if ( $actual_table->getTableAsString() != $fields->getTableAsString() ) {
			throw new \Exception( sprintf(
				"Actual schedule:\n %s",
				$actual_table->getTableAsString()
			) );
		}

	}

	/**
	 * @Then there should be :num ":element" elements visible
	 */
	public function iThereShouldBeElementsVisible( $num, $element ) {

		$nodes = $this->getSession()->getPage()->findAll( 'css', $element );

		foreach ( $nodes as $index => $node ) {
			if ( ! $node->isVisible() ) {
				unset( $nodes[$index] );
			}
		}

		if ( count( $nodes ) != $num ) {
			throw new \Exception( sprintf(
				'%d %s found on the page, but should be %d.',
				count( $nodes ),
				$this->getMatchingElementRepresentation( 'css', $element, count( $nodes ) !== 1 ),
				$num
			));
		}

	}


	/**
	 * @Then the checkbox in :container for :label should be selected
	 */
	public function theCheckboxForShouldBeSelected( $container, $label ) {

		$page        = $this->getSession()->getPage();
		$container   = $page->find( 'css', $container );
		$label_nodes = $container->findAll( 'css', 'label' );

		$input = false;

		foreach ( $label_nodes as $label_node ) {

			if ( $label_node->getText() !== $label ) {
				continue;
			}

			if ( $label_node->hasAttribute( 'for' )  ) {
				$for   = $label_node->getAttribute( 'for' );
				$input = $container->find( 'css', '#' . $for );

				if ( ! $input ) {
					throw new \Exception(
						'A matching label was found but its for attribute did not point to an existing element: "%s"',
						$for
					);
				}
			} else {
				$input = $label_node->find( 'css', 'input[type=radio]' );
			}

			if ( ! $input ) {
				throw new \Exception( 'An input could not be found' );
			}
		}

		if ( ! $input->isChecked() ) {
			throw new Exception( 'Checkbox with label ' . $label . ' is not checked' );
		}
	}


	/**
	 * @Then /^(?:|I )should see "(?P<text>.+)" in the "(?P<selector>\w+)" element$/
	 */
	public function assertElementText( $text, $selector ) {
		foreach ( $this->getSession()->getPage()->findAll( 'css', $selector ) as $element ) {
			if ( strpos( strtolower( $text ), strtolower( $element->getText() ) === false ) ) {
				throw new \Exception( "Text '{$text}' is not found in the '{$selector}' element." );
			}
		}
	}

	/**
	 * @Then /^(?:|I )should not see "(?P<text>.+)" in the "(?P<selector>\w+)" element$/
	 */
	public function assertElementNotText( $text, $selector ) {
		foreach ( $this->getSession()->getPage()->findAll( 'css', $selector ) as $element ) {
			if ( strpos( strtolower( $text ), strtolower( $element->getText() ) !== false ) ) {
				throw new \Exception( "Text '{$text}' is found in the '{$selector}' element." );
			}
		}
	}

	/**
	 * @Then I should see the following in the repeated :element element
	 */
	public function iShouldSeeTheFollowingInTheRepeatedElementWithinTheContextOfTheElement2( $element, TableNode $table ) {

		$elements = $this->getSession()->getPage()->findAll( 'css', $element );
		$hash = $table->getHash();

		foreach ( $elements as $index => $element ) {
			try {
				if ( ! $element->isVisible() ) {
					unset( $elements[$index] );
				}
			} catch ( \Exception $e ) {
				//do nothing.
			}
		}

		foreach ( $elements as $n => $element ) {
			$actual[] = array( $elements[$n]->getText() );
		}
		$actual_table = new TableNode( $actual );

		if ( $actual_table->getTableAsString() != $table->getTableAsString() ) {
			throw new \Exception( sprintf(
				"Found elements:\n%s",
				$actual_table->getTableAsString()
			) );
		}

	}

	/**
	 * Wait for AJAX to finish.
	 *
	 * @Then /^I wait for AJAX to finish$/
	 */
	public function iWaitForAjaxToFinish() {
		$this->getSession()->wait( 10000, '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))' );
	}



	/**
	 * Checks a checkbox/radio with specified label.
	 *
	 * @When /^(?:|I )check the element in "(?P<container>[^"]*)" with label "(?P<label>[^"]*)"$/
	 */
	public function assertTypedFormElementOnPage( $container, $label ) {

		$page        = $this->getSession()->getPage();
		$container   = $page->find( 'css', $container );
		$label_nodes = $container->findAll( 'css', 'label' );

		$input = false;

		foreach ( $label_nodes as $label_node ) {

			if ( $label_node->getText() !== $label ) {
				continue;
			}

			if ( $label_node->hasAttribute( 'for' )  ) {
				$for   = $label_node->getAttribute( 'for' );
				$input = $container->find( 'css', '#' . $for );

				if ( ! $input ) {
					throw new \Exception(
						'A matching label was found but its for attribute did not point to an existing element: "%s"',
						$for
					);
				}
			} else {
				$input = $label_node->find( 'css', 'input[type=radio]' );
			}

			if ( ! $input ) {
				throw new \Exception( 'An input could not be found' );
			}
		}

		$input->selectOption( $input->getAttribute( 'value' ), false );

	}

	/**
	 * @When I wait :seconds seconds
	 */
	public function iWaitSeconds( $seconds ) {
		$this->getSession()->wait( $seconds * 1000 );
	}


	/**
	 * @When /^I hover over the element "([^"]*)"$/
	 */
	public function iHoverOverTheElement($locator)
	{
		$this->spin(function($context) use ($locator) {
			$session = $context->getSession(); // get the mink session
			$element = $session->getPage()->find('css', $locator); // runs the actual query and returns the element
			if (null !== $element) {
				$element->mouseOver();
				return true;
			}
		});
	}

	/**
	 * @When /^I focus on the element "([^"]*)"$/
	 */
	public function iFocusOnTheElement($locator)
	{
		$session = $this->getSession(); // get the mink session
		$element = $session->getPage()->find('css', $locator); // runs the actual query and returns the element

		// errors must not pass silently
		if (null === $element) {
			throw new \InvalidArgumentException(sprintf('Could not evaluate CSS selector: "%s"', $locator));
		}

		// ok, let's hover it
		$element->focus();
	}

	/**
	 * @Then the Event List Widget should display
	 */
	public function theEventListWidgetShouldDisplay(TableNode $table)
	{
		$elements = $this->getSession()->getPage()->findAll( 'css', '.eo-events-widget li' );
		$hash = $table->getHash();

		$actual = array();
		foreach ( $elements as $n => $element ) {
			$actual[] = array( $elements[$n]->getText() );
		}
		$actual_table = new TableNode( $actual );

		if ( $actual_table->getTableAsString() != $table->getTableAsString() ) {
			throw new \Exception( sprintf(
				"Found events:\n%s",
				$actual_table->getTableAsString()
			) );
		}
	}

	/**
	 * @Given event templates are enabled
	 */
	public function eventTemplatesAreEnabled()
	{
		$options = eventorganiser_get_option( false );
		$options['templates'] = 1;
		update_option( 'eventorganiser_options', $options );
	}

	/**
	 * @Given event templates are disabled
	 */
	public function eventTemplatesAreDisabled()
	{
		$options = eventorganiser_get_option( false );
		$options['templates'] = 0;
		update_option( 'eventorganiser_options', $options );
	}

	/**
	 * @Given theme compatability is enabled
	 */
	public function themeCompatabilityIsEnabled()
	{
		$options = eventorganiser_get_option( false );
		$options['templates'] = 'themecompat';
		update_option( 'eventorganiser_options', $options );
	}

	/**
	 * @Given I have an event list widget in :sidebar
	 */
	public function iHaveAnEventListWidgetIn($sidebar, TableNode $table)
	{
		$widget = 'EO_Event_List_Widget';

		//Register sidebar. TODO Why is this necessary?
		register_sidebar( array(
			'name' => __( 'Main Sidebar', 'theme-slug' ),
			'id' => 'sidebar-1',
			'description' => __( 'Widgets in this area will be shown on all posts and pages.', 'theme-slug' ),
			'before_widget' => '<li id="%1$s" class="widget %2$s">',
			'after_widget'  => '</li>',
			'before_title'  => '<h2 class="widgettitle">',
			'after_title'   => '</h2>',
		) );

		//Get sidebar
		$sidebar_id = $this->_findSidebar( $sidebar );

		//Compile widget settings
		$values = $table->getRow( 1 ); //we only support one widget for now
		$args   = array();

		foreach ( $table->getRow( 0 ) as $index => $key ) {

			$key = strtolower( $key );
			switch( $key ) {

				case 'no. of events':
					$args['numberposts'] = $values[$index];
					break;
				case 'group events':
					$args['group_events_by'] = ! empty ( $values[$index] ) ? 'series' : false;
					break;
				case 'venue':
					$venue = get_term_by( 'name', $values[$index], 'event-venue' );
					$args['venue'] = $venue->slug;
					break;
				case 'category':
					$category = get_term_by( 'name', $values[$index], 'event-category' );
					$args['event-category'] = $category->slug;
					break;
				case 'interval':
					$args['scope'] = strtolower( $values[$index] );
					break;
				default:
					$args[$key] = $values[$index];
					break;
			}
		}
		$args = wp_parse_args( $args, EO_Event_List_Widget::$w_arg ); //merge in default values
		$this->_addWidgetToSidbar( $sidebar_id, $widget, $args );
	}

	private function _findSidebar($sidebar)
	{
		global $wp_registered_sidebars;

		$sidebar_id = null;

		if ( ! isset( $wp_registered_sidebars[$sidebar] ) ) {
			foreach( $wp_registered_sidebars as $_sidebar_id => $_sidebar ) {
				if ( $sidebar == $_sidebar['name'] ) {
					$sidebar_id = $_sidebar_id;
					break;
				}
			}
		} else {
			$sidebar_id = $sidebar;
		}

		if ( is_null( $sidebar_id ) ) {
			throw new \Exception( sprintf( 'Sidebar "%s" does not exist', $sidebar ) );
		}

		return $sidebar_id;
	}


	private function _addWidgetToSidbar($sidebar_id, $widget, $args)
	{

		// Get this widget's 'counter' - used to store its settings:
		$widget           = strtolower( $widget );
		$existing_widgets = get_option( 'widget_' . $widget , array() );
		$counter          = count( $existing_widgets ) + 1;

		//Store widget settings
		$existing_widgets[$counter] = $args;
		update_option( 'widget_' . $widget , $existing_widgets );

		// Add widget to sidebar
		$active_widgets = get_option( 'sidebars_widgets', array() );
		$active_widgets[ $sidebar_id ] = isset( $active_widgets[ $sidebar_id ] ) ? $active_widgets[ $sidebar_id ] : array();
		array_unshift( $active_widgets[ $sidebar_id ], $widget . '-' . $counter );
		update_option( 'sidebars_widgets', $active_widgets );

	}

	/**
	 * Fills in form field with specified id|name|label|value.
	 *
	 * @overide When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" with "(?P<value>(?:[^"]|\\")*)"$/
	 * @overide When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" with:$/
	 * @overide When /^(?:|I )fill in "(?P<value>(?:[^"]|\\")*)" for "(?P<field>(?:[^"]|\\")*)"$/
	 */
	public function fillField($field, $value)
	{
		$this->spinFillField($field, $value);
	}

	
	/**
	 * @AfterScenario
	 */
	public function takeScreenshotAfterFailedStep(AfterScenarioScope $scope)
	{

		if ($this->screenshot_dir && TestResult::FAILED === $scope->getTestResult()->getResultCode()) {

			$feature  = $scope->getFeature();
			$scenario = $scope->getScenario();
			$filename = basename( $feature->getFile(), '.feature' ) . '-' . $scenario->getLine();

			if ($this->getSession()->getDriver() instanceof \Behat\Mink\Driver\Selenium2Driver) {
				$screenshot = $this->getSession()->getDriver()->getScreenshot();
				file_put_contents( $this->screenshot_dir . $filename . '.png', $screenshot);
			}

			//Store HTML markup of the page also - useful for non-js tests
			file_put_contents( $this->screenshot_dir . $filename . '.html', $this->getSession()->getPage()->getHtml());
		}
	}

    /**
     * WordPress disables the save/publish buttons when autosaving.
     * This circumvents that issue.
     * @When I save the event
     */
    public function iSaveTheEvent()
    {
		$button = $this->fixStepArgument('save-post');

		//If the button is out of view then the window will be scrolled so that it aligns
		//with the top of the screen. But then it is obscured by the #wpadminbar.
		//We scroll so that the top of the window is aligned with the button, then move the
		//view-port up by more than the height of the admin bar so that the button is visible.
		$this->getSession()->executeScript(
			'var element = document.getElementById("save-post");
			element.scrollIntoView(true);
			window.scrollBy(0, -50);'
		);
    	$this->spin(function($context) use ($button) {
    		$context->getSession()->getPage()->pressButton($button);
    		return true;
    	});
    }

    /**
     * @Given I include past events
     */
    public function iIncludePastEvents()
    {
		$options = eventorganiser_get_option( false );
		$options['showpast'] = 1;
		update_option( 'eventorganiser_options', $options );
    }

    /**
     * @Given I have an event calendar widget in :arg1
     */
	public function iHaveAnEventCalendarWidgetIn($sidebar, TableNode $table)
	{
		//Register sidebar. TODO Why is this necessary?
		register_sidebar( array(
			'name'           => __( 'Main Sidebar', 'theme-slug' ),
			'id'            => 'sidebar-1',
			'description'   => __( 'Widgets in this area will be shown on all posts and pages.', 'theme-slug' ),
			'before_widget' => '<li id="%1$s" class="widget %2$s">',
			'after_widget'  => '</li>',
			'before_title'  => '<h2 class="widgettitle">',
			'after_title'   => '</h2>',
		) );

		//Get sidebar
		$sidebar_id = $this->_findSidebar( $sidebar );

		//Compile widget settings
		$values = $table->getRow( 1 ); //we only support one widget for now
		$args   = array();

		foreach ( $table->getRow( 0 ) as $index => $key ) {

			$key = strtolower( $key );
			switch( $key ) {
				case 'event categories':
					$args['event-category'] = $values[$index];
					break;
				case 'event venue':
					$args['event-venue'] = $values[$index];
					break;
				case 'include past events':
					$args['showpastevents'] = $values[$index];
					break;
				case 'show-long':
					$args['show-long'] = $values[$index];
					break;
				case 'link-to-single':
					$args['link-to-single'] = $values[$index];
					break;
				case 'show-long':
					$args[$key] = $values[$index];
					break;
				default:
					$args[$key] = $values[$index];
					break;
			}
		}
		$args = wp_parse_args( $args, EO_Calendar_Widget::$w_arg ); //merge in default values
		$this->_addWidgetToSidbar( $sidebar_id, 'EO_Calendar_Widget', $args );
	}


	/**
	 * Add these posts to this wordpress installation
	 *
	 * @see wp_insert_post
	 *
	 * @override /^there are posts$/
	 */
	public function thereArePosts(TableNode $table)
	{
		foreach ($table->getHash() as $postData) {
			//We add this step to allow IDs to be implicitly referenced:
			//{{id of event "My Event Title"}}
			$postData['post_content'] = preg_replace_callback( '/{{id of ([^}]*) "([^}"]*)"}}/i', function($match){
				$event = get_page_by_title( $match[2], 'OBJECT', $match[1] );
				if ( $event ) {
					return $event->ID;
				}
				return '';
			}, $postData['post_content'] );

			var_dump($postData);
			if (!is_int(wp_insert_post($postData))) {
				throw new \InvalidArgumentException("Invalid post information schema.");
			}
		}
	}


    /**
     * @When the calendar finishes loading
     */
    public function theCalendarFinishesLoading()
    {
		$mink = $this->getMink();
    	$this->spin(function($context) use ($mink) {
    		$mink->assertSession()->pageTextNotContains('Loading');
    		return true;
    	});
    }

		/**
     * @Then /^I should see a link "([^"]*)"$/
     */
    public function iShouldSeeALink($text) {
			$link = $this->getSession()->getPage()->findLink($text);
			if (null === $link) {
					throw new ElementNotFoundException(
						$this->getSession()->getDriver(),
						'link', 'id|title|alt|text',
						$text
					);
			}
    }

		/**
		 * @Then /^I should not see a link "([^"]*)"$/
		 */
		public function iShouldNotSeeALink($text) {
			$link = $this->getSession()->getPage()->findLink($text);
			if (null !== $link) {
					throw new ExpectationException(
						sprintf( 'Link "%s" exists but it should not', $text ),
						$this->getSession()->getDriver()
					);
			}
		}
}
