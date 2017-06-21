<?php

class WP_Scan_Existing_Forms extends WP_Background_Process {

	/**
	 * @var string
	 */
	protected $action = 'scan_existing_forms';
	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over
	 *
	 * @return mixed
	 */
	protected function task( $post ) {

		// Grab the content from the form post
		$content = stripslashes( $post->post_content );

		$pattern = get_shortcode_regex();

		// Check if shortcode exists in the content
		if ( preg_match_all( '/' . $pattern . '/s', $content, $matches ) && array_key_exists( 2, $matches ) && in_array( 'gravityform', $matches[2] ) ) {

			// Use the match to extract the form id from the shortcode
			preg_match( '~id="(.*)\"~', $matches[3][0], $form_id );

			// Convert the form id to an int
			$form_id = (int) $form_id[1];
		}
		$gravity_form_locator = new Gravity_Form_Locator();

		if ( isset( $form_id ) ) {

			$gravity_form_locator->add_form_post_relation( $form_id, $post->ID );

		}

		return false;
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		// Display the success message when scan is complete.
		set_transient( 'scan_complete' );

		parent::complete();
	}


}
