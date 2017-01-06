<?php
/*
Plugin Name: Gravity Forms - Page Tracker Addon
Plugin URI: https://gitlab.com/tanner.record/gravity-forms-page-tracker-addon
Description: A simple addon that displays what page a form is on.
Version: 0.1
Author: Tanner Record
Author URI: http://www.northstarmarketing.com
License: GPL2
*/
/*
Copyright (c) 2017  Tanner Record  (email : tanner.record@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
 * Registered Actions, Filters and Hooks
 */

//////////////////////////////////////////
//Registered Actions, Filters and Hooks //
//////////////////////////////////////////

 register_activation_hook( __FILE__, 'gravity_forms_page_tracker_activate' );
 register_deactivation_hook( __FILE__, 'gravity_forms_page_tracker_deactivate' );
 register_uninstall_hook( __FILE__, 'gravity_forms_page_tracker_uninstall' );

// Update the table with the page/form id when the post is saved
 add_action( 'save_post', 'update_form_page_id' );
 
/*
 * Global Variables
 */
 
 
//////////////////////
// global Variables //
//////////////////////

global $gform_form_page_table, $wpdb;
$gform_form_page_table = $wpdb->prefix . "gform_form_page";



// Install table when activated
function gravity_forms_page_tracker_activate () {
  
    global $wpdb, $gform_form_page_table;
   
    $charset_collate = $wpdb->get_charset_collate();

    // Check if table exists before trying to create it
    if($wpdb->get_var("SHOW TABLES LIKE '$gform_form_page_table'") != $gform_form_page_table){
      
        // Create the table
        $sql = "CREATE TABLE $gform_form_page_table (
          id mediumint(9) NOT NULL AUTO_INCREMENT,
          form_id mediumint(8) NOT NULL,
          post_id bigint(20) NOT NULL,
          PRIMARY KEY (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        
    }
  
}

// Remove table when uninstalled
function gravity_forms_page_tracker_uninstall () {
  
    global $wpdb, $gform_form_page_table;
    
    // Check if table exists before trying to delete it
    if($wpdb->get_var("SHOW TABLES LIKE '$gform_form_page_table'") == $gform_form_page_table){
      
        $wpdb->query("DROP TABLE $gform_form_page_table;");
        
    }
  
}

/**
 * Save page/form relation in the database on save
 * 
 * @param  int  $post_id the post id returned from the save_post action
 * 
 * @return  null
 */
function update_form_page_id($post_id) {
  
    // If this is a revision just return
    if ( wp_is_post_revision( $post_id ) )
    
    return;
    
    
    global $wpdb, $gform_form_page_table;
      
    // Grab the content from the form post
    $content = stripslashes($_POST['content']);
    
    $pattern = get_shortcode_regex();

    // Check if shortcode exists in the content
    if (   preg_match_all( '/'. $pattern .'/s', $content, $matches ) && array_key_exists( 2, $matches ) && in_array( 'gravityform', $matches[2] ) ){
        
        // Use the match to extract the form id from the shortcode
        preg_match('~id="(.*)\"~', $matches[3][0], $form_id);
        
        // Convert the form id to an int
        $form_id = (int) $form_id[1];
    }
    
    
    // Add the relationship to the table
    if($wpdb->get_var("SHOW TABLES LIKE '$gform_form_page_table'") == $gform_form_page_table){
        
        // Check to see if the form/post relation already exists in the table
        $form_post_count = $wpdb->get_var("SELECT COUNT(*) FROM `$gform_form_page_table` WHERE form_id='$form_id' AND post_id='$post_id'");
        
        if($form_post_count < 1){
        
            $wpdb->insert(
              	$gform_form_page_table,
              	array(
              		'form_id' => $form_id,
              		'post_id' => $post_id
              	),
              	array(
              		'%d',
              		'%d'
              	)
            );
          
        }
        
    }
  
}
