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
  public static function get_locations($per_page = 10, $page_number = 1) {

    global $wpdb;
    
    if ( !empty($_GET['form_id']) ){
      $sql = "SELECT * FROM {$wpdb->prefix}gform_form_page WHERE form_id = {$_GET['form_id']}";
    } else {
      $sql = "SELECT * FROM {$wpdb->prefix}gform_form_page";
    }
    
    if ( ! empty( $_REQUEST['orderby'] ) ) {
      $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
      $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
    }

    $sql .= " LIMIT $per_page";

    $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

    $result = $wpdb->get_results( $sql, 'ARRAY_A' );

    return $result;
  }
  
  function column_form_id($item){
    
    $form = GFAPI::get_form( $item['form_id'] );
    
    return '<a href="?page=gf_edit_forms&id=' . $form['id'] . '">' . $form['title'] . '</a>';
  }
  
  function column_post_id($item){
    
    $post = get_post($item['post_id']);
    
    return $post->post_title;
  }
  
  function column_post_actions($item){
    
    $post = get_post($item['post_id']);
    
    return '<a href="post.php?post='. $post->ID .'&action=edit">Edit</a> | <a href="' . get_post_permalink($post->ID) . '">View</a>';
  }
  
  function get_columns(){
    $columns = array(
      'form_id' => __('Form Title', 'gform-page-tracker'),
      'post_id' => __('Post Title', 'gform-page-tracker'),
      'post_actions' => __('Post Actions', 'gform-page-tracker'),
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
      'form_id' => array( 'form_id', false )
    );

    return $sortable_columns;
  }

  function prepare_items() {
    $columns = $this->get_columns();
    $hidden = array();
    $this->_column_headers = array($columns, $hidden);
    $this->items = self::get_locations();
  }
  
}