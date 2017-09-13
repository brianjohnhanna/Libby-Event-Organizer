<?php

namespace Libby\Events;

class Admin_Notice_Messenger {

  const MESSAGE_OPTION_NAME = 'libby-events-error-messages';

  protected $errors;

  public function add_message( $message, $type = 'error' ) {
    // Add the message to the array of errors and then update the option.
    $this->errors[] = array(
      'message' => $message,
      'type' => esc_attr( $type )
    );
    $this->serialize_messages();
  }

  /**
   * Serialize the messages and update the option
   * @return [type] [description]
   */
  protected function serialize_messages() {
    update_option( self::MESSAGE_OPTION_NAME, $this->errors );
  }

  /**
   * Unserialize, retrieve messages from DB then delete option
   * @return [type] [description]
   */
  protected function retrieve_messages() {
    $this->errors = get_option( self::MESSAGE_OPTION_NAME );
    delete_option( self::MESSAGE_OPTION_NAME );
  }

  protected function display_messages() {

  	if ( empty( $this->errors ) ) {
  		return;
  	}

  	// Create the list of errors.
  	$errors = '<ul>';
  	foreach ( $this->errors as $error ) {
  		$errors .= '<li>' . $error['message'] . '</li>';
  	}
  	$errors .= '</ul>';

  	// Create the markup for the error message.
  	$html = "
  		<div class='notice notice-error'>
  			$errors
  		</div><!-- .notice-error -->
  	";

  	// Set the HTML we'll allow for sanitization.
  	$allowed_html = array(
  		'div' => array(
  			'class' => array(),
  		),
  		'ul' => array(),
  		'li' => array(),
      'a' => array(
        'href' => true
      ),
      'br' => true
  	);

  	echo wp_kses( $html, $allowed_html );
  }

  /**
   * Get the messages and then display them
   */
  public function get_messages() {
    $this->retrieve_messages();
    $this->display_messages();
  }


}
