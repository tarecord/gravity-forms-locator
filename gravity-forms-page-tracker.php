<?php
/*
Plugin Name: Gravity Forms Page Tracker Addon
Plugin URI: https://gitlab.com/tanner.record/gravity-forms-page-tracker-addon
Description: A simple addon that displays what page a form is on.
Version: 0.1
Author: Tanner Record
Author URI: http://www.northstarmarketing.com
License: GPL2
*/
/*
Copyright 2017  Tanner Record  (email : tanner.record@gmail.com)

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
 register_activation_hook( __FILE__, 'gravity_forms_page_tracker_activate' );
 register_deactivation_hook( __FILE__, 'gravity_forms_page_tracker_deactivate' );
 register_uninstall_hook( __FILE__, 'gravity_forms_page_tracker_uninstall' );

// Update the table with the page/form id when the post is saved
 add_action( 'save_post', 'update_form_page_id' );
 
/*
 * Global Variables
 */

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
          PRIMARY KEY (id)
          post_id bigint(20) NOT NULL,
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

function update_form_page_id() {
  
}
