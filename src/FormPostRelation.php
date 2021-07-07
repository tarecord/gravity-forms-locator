<?php
/**
 * This file is the main class file for the plugin.
 *
 * @package    GF-Form-Locator
 * @author     Tanner Record <tanner.record@gmail.com>
 * @license    GPL2
 * @since      File available since Release 1.4.3
 */

namespace TAR\GravityFormLocator;

/**
 * Links Forms
 */
class FormPostRelation {

	/**
	 * The database resource.
	 *
	 * @var wpdb
	 */
	protected $wpdb;

	/**
	 * The table name.
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * The constructor
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb  = $wpdb;
		$this->table = $wpdb->prefix . 'gform_form_page';
	}

	/**
	 * Creates a relationship between form_ids and a post.
	 *
	 * @param array $form_ids The ids of the forms.
	 * @param int   $post_id  The id of the post.
	 *
	 * @return void
	 */
	public function add( $form_ids = array(), $post_id ) {

		// Bail if no form_ids.
		if ( empty( $form_ids ) ) {
			return;
		}

		foreach ( $form_ids as $form_id ) {

			// Check to see if the form/post relation already exists in the table.
			$sql = $this->wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->table} WHERE form_id = %d AND post_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$form_id,
				$post_id
			);

			$form_post_count = $this->wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			// If the form and post don't already have a relation.
			if ( intval( $form_post_count ) < 1 ) {

				$this->wpdb->insert(
					$this->table,
					array(
						'form_id' => $form_id,
						'post_id' => $post_id,
					),
					array(
						'%d',
						'%d',
					)
				);
			}
		}
	}

	/**
	 * Remove a relationship between form and post.
	 *
	 * Providing both post and form IDs will guarantee that only that relationship is removed.
	 * There could be cases where multiple forms show on the same post. If only the post ID is
	 * given, then all form relationships with that post will be removed.
	 *
	 * @param int $post_id The post's ID.
	 * @param int $form_id The form's ID.
	 *
	 * @return void
	 */
	public function remove( $post_id, $form_id = 0 ) {
		if ( 0 !== $form_id ) {
			$this->wpdb->delete(
				$this->table,
				array(
					'post_id' => $post_id,
					'form_id' => $form_id,
				)
			);
		} else {
			$this->wpdb->delete(
				$this->table,
				array(
					'post_id' => $post_id,
				)
			);
		}
	}
}
