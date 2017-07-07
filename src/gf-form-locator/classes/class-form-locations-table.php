<?php
/**
 * This file is responsible for setting up the Location Table
 *
 * @package    GF-Form-Locator
 * @author     Tanner Record <tanner.record@gmail.com>
 * @license    GPL2
 * @since      File available since Release 1.0.0
 */

/**
 * The Form_Locations_Table class extends WP_List_Table and creates the table
 */
class Form_Locations_Table extends WP_List_Table {

	/**
	 * Class constructor
	 */
	public function __construct() {

		parent::__construct( array(
			'singular' => __( 'Location', 'gform-page-tracker' ), // singular name of the listed records
			'plural'   => __( 'Locations', 'gform-page-tracker' ), // plural name of the listed records
			'ajax'     => false, // should this table support ajax?
			)
		);

	}

	/**
	 * Retrieve customer’s data from the database
	 *
	 * @param int $per_page 		The number of entries to list on a page.
	 * @param int $page_number 	The page number.
	 *
	 * @return mixed
	 */
	public static function get_locations( $per_page, $page_number = 1 ) {

		global $wpdb;

		if ( ! empty( $_GET['form_id'] ) ) {
			$form_id = sanitize_text_field( wp_unslash( $_GET['form_id'] ) );
			$sql = "SELECT * FROM {$wpdb->prefix}gform_form_page WHERE form_id = {$form_id}";
		} else {
			$sql = "SELECT * FROM {$wpdb->prefix}gform_form_page";
		}

		if ( ! empty( $_REQUEST['orderby'] ) ) {

			$orderby = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );
			$order = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) );

			$sql .= ' ORDER BY ' . esc_sql( $orderby );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $order ) : ' ASC';
		}

		if ( isset( $per_page ) ) {

			$sql .= " LIMIT $per_page";

			$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

		}

		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

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

		$form = GFAPI::get_form( $item['form_id'] );

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
			'form_id' => __( 'Form Title', 'gform-page-tracker' ),
			'post_id' => __( 'Post Title', 'gform-page-tracker' ),
			'post_status' => __( 'Post Status', 'gform-page-tracker' ),
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
		$columns = $this->get_columns();
		$hidden = array();
		$this->_column_headers = array( $columns, $hidden );
		$this->items = self::get_locations();
	}

}
