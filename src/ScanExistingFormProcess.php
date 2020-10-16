<?php
/**
 * This file is responsible for setting up the background scanning process.
 *
 * @package    GF-Form-Locator
 * @author     Tanner Record <tanner.record@gmail.com>
 * @license    GPL2
 * @since      File available since Release 1.0.0
 */

namespace TAR\GravityFormLocator;

use TAR\GravityFormLocator\Core;

/**
 * Extends WP_Background_Process and sets up a new background process.
 */
class ScanExistingFormProcess extends WP_Background_Process {

	/**
	 * The background process action name.
	 *
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
	 * @param mixed $post Queue item to iterate over.
	 *
	 * @return mixed
	 */
	protected function task( $post ) {
		$core = new Core();

		$pattern = get_shortcode_regex( array( 'gravityform' ) );

		$form_ids = $core->check_for_forms( $post, $pattern );

		if ( ! empty( $form_ids ) ) {

			$core->add_form_post_relations( $form_ids, $post->ID );

		}

		return false;
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 *
	 * @return void
	 */
	protected function complete() {
		// Display the success message when scan is complete.
		set_transient( 'gfl_scan_complete', true, DAY_IN_SECONDS );

		parent::complete();
	}


}
