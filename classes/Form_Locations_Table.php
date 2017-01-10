<?php

class Form_Locations_Table extends WP_List_Table {
  
  /** Class constructor */
	public function __construct() {

		parent::__construct( array(
			'singular' => __( 'Location', 'gform-page-tracker' ), //singular name of the listed records
			'plural'   => __( 'Locations', 'gform-page-tracker' ), //plural name of the listed records
			'ajax'     => false //should this table support ajax?
      )
    );

	}

  /**
   * Retrieve customer’s data from the database
   *
   * @param int $per_page
   * @param int $page_number
   *
   * @return mixed
   */
  public static function get_locations() {

    global $wpdb;

    $sql = "SELECT * FROM {$wpdb->prefix}gform_form_page WHERE form_id = {$_GET['id']}";

    $result = $wpdb->get_results( $sql, 'ARRAY_A' );

    return $result;
  }
  
  function column_post_id($item){
    
    $post = get_post($item['post_id']);
    
    return '<a href="post.php?post='. $post->ID .'&action=edit">'. $post->post_title .'</a>';
  }
  
  function get_columns(){
    $columns = array(
      'post_id' => 'Title',
    );
    return $columns;
  }

  function prepare_items() {
    $columns = $this->get_columns();
    $hidden = array();
    $sortable = array();
    $this->_column_headers = array($columns, $hidden, $sortable);
    $this->items = self::get_locations();
  }
  
}