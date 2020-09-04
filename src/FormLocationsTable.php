<?php
/**
 * This file is responsible for setting up the Location Table
 *
 * @package    GF-Form-Locator
 * @author     Tanner Record <tanner.record@gmail.com>
 * @license    GPL2
 * @since      File available since Release 1.0.0
 */

namespace TAR\GravityFormLocator;

use GFAPI;
use WP_List_Table;

/**
 * The Form_Locations_Table class extends WP_List_Table and creates the table
 */
class FormLocationsTable extends WP_List_Table {

	/**
	 * Class constructor
	 */
	public function __construct() {

		parent::__construct(
			array(
				'singular' => __( 'Location', 'gform-page-tracker' ), // singular name of the listed records.
				'plural'   => __( 'Locations', 'gform-page-tracker' ), // plural name of the listed records.
				'ajax'     => false, // should this table support ajax?
			)
		);

	}

	/**
	 * Retrieve customer’s data from the database.
	 *
	 * @return array An array of data for the table.
	 */
	public function get_locations() {

		global $wpdb;

		if ( ! empty( $_GET['form_id'] ) ) {
			$form_id = sanitize_text_field( wp_unslash( $_GET['form_id'] ) );
			$sql     = "SELECT * FROM {$wpdb->prefix}gform_form_page WHERE form_id = {$form_id}";
		} else {
			$sql = "SELECT * FROM {$wpdb->prefix}gform_form_page";
		}

		if ( ! empty( $_REQUEST['orderby'] ) ) {

			$orderby = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );
			$order   = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) );

			$sql .= ' ORDER BY ' . esc_sql( $orderby );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $order ) : ' ASC';
		}

		$result = $wpdb->get_results( $sql, 'ARRAY_A' ); //phpcs:ignore

		return $result;
	}

	/**
	 * Set up the form_id column
	 *
	 * @param object $item The current item.
	 *
	 * @return string
	 */
	function column_form_id( $item ) {

		$gf_api = new GFAPI();
		$form   = $gf_api::get_form( $item['form_id'] );

		// Form does not exist.
		if ( ! $form ) {
			return '<span style="color:red">Invalid Form ID Used</span>';
		}

		return '<a href="?page=gf_edit_forms&id=' . $form['id'] . '">' . $form['title'] . '</a>';
	}

	/**
	 * Set up the post_id column
	 *
	 * @param object $item The current item.
	 *
	 * @return string
	 */
	function column_post_id( $item ) {

		$post = get_post( $item['post_id'] );

		return $post->post_title;
	}

	/**
	 * Set up the post_status column
	 *
	 * @param object $item The current item.
	 *
	 * @return string
	 */
	function column_post_status( $item ) {

		$post = get_post( $item['post_id'] );

		if ( 'publish' === $post->post_status || 'future' === $post->post_status || 'pending' === $post->post_status ) {

			return '<span style="color: #21759B;">Published</span>';

		} elseif ( 'draft' === $post->post_status || 'auto-draft' === $post->post_status ) {

			return 'Draft';

		} elseif ( 'private' === $post->post_status ) {

			return '<span style="color: #21759B;">Private</span>';

		} elseif ( 'trash' === $post->post_status ) {

			return '<span style="color: red;">Trashed</span>';

		}

	}

	/**
	 * Set up the links in the post_actions column
	 *
	 * @param object $item The current item.
	 *
	 * @return string
	 */
	function column_post_actions( $item ) {

		$post = get_post( $item['post_id'] );

		return '<a href="post.php?post=' . $post->ID . '&action=edit">Edit</a> | <a href="' . get_post_permalink( $post->ID ) . '">View</a>';
	}

	/**
	 * Define all column names
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = array(
			'form_id'      => __( 'Form Title', 'gform-page-tracker' ),
			'post_id'      => __( 'Post Title', 'gform-page-tracker' ),
			'post_status'  => __( 'Post Status', 'gform-page-tracker' ),
			'post_actions' => __( 'Post Actions', 'gform-page-tracker' ),
		);
		return $columns;
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'post_id' => array( 'post_id', true ),
			'form_id' => array( 'form_id', false ),
		);

		return $sortable_columns;
	}

	/**
	 * Prepare the items for display
	 *
	 * @return void
	 */
	function prepare_items() {
		$columns               = $this->get_columns();
		$sortable              = $this->get_sortable_columns();
		$hidden                = array();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$per_page     = 25;
		$current_page = $this->get_pagenum();
		$data         = $this->get_locations( $per_page, $current_page );
		$total_items  = count( $data );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);

		$this->items = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );
	}

}
