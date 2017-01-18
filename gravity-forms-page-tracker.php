<?php
/*
Plugin Name: Gravity Forms - Page Tracker Addon
Plugin URI: https://gitlab.com/tanner.record/gravity-forms-page-tracker-addon
Description: A simple addon that displays what page a form is on.
Version: 1.2
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

/**
 * Since this plugin depends on Gravity Forms, we need to check if
 * Gravity Forms is currently active.
 * 
 * If not, display an error to the user explaining why this plugin
 * could not be activated.
 * 
 */
require_once( 'classes/WPS_Extend_Plugin.php' );

new WPS_Extend_Plugin( 'gravityforms/gravityforms.php', __FILE__, '2.1.1', 'gform-page-tracker' );

// Handle activation and uninstalling
register_activation_hook( __FILE__, array( 'Gravity_Forms_Page_Tracker', 'activate' ) );
register_uninstall_hook( __FILE__, array( 'Gravity_Forms_Page_Tracker', 'uninstall' ) );

// Now, for the main event...
class Gravity_Forms_Page_Tracker {

    /**
     * Construct the instance
     */
    public function __construct() {
        
        add_action( 'plugins_loaded', array( $this, 'init' ) );
        
        // Update the table with the page/form id when the post is saved
        add_action( 'save_post', array( $this, 'update_form_page_id' ) );
        
        // Add new menu item to show list of all forms and their post relations
        add_filter( 'gform_addon_navigation', array( $this, 'add_location_menu_item') );
        
        // Add location as a view option in the main form view
        add_filter( 'gform_toolbar_menu', array( $this, 'add_location_form_edit_menu_option' ), 10, 2 );
        
        // Add action link to view posts that contain the form
        add_filter( 'gform_form_actions', array( $this , 'add_form_post_action' ), 10, 2 );
        
        add_action( 'init', array( $this, 'process_handler' ) );
        
        // If the scan is complete, show a notice to the user
        if (get_transient('scan_complete')){
          
          add_action( 'admin_notices', array( $this, 'scan_complete_notice' ) );
          
          delete_transient('scan_complete');
          
        }
      
    }
    
    /**
     * Create the form post table when the plugin is activated
     * 
     * @return void
     */
    public static function activate() {
      
        global $wpdb;
        
        // Define the table Name
        $gform_form_page_table = $wpdb->prefix . "gform_form_page";
       
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
    
    /**
  	 * Init
  	 */
  	public function init() {
      require_once plugin_dir_path( __FILE__ ) . '/classes/WP_Async_Request.php';
      require_once plugin_dir_path( __FILE__ ) . '/classes/WP_Background_Process.php';
      require_once plugin_dir_path( __FILE__ ) . '/classes/WP_Scan_Existing_Forms.php';
      
      $this->scan_site_process = new WP_Scan_Existing_Forms();
  	}
    
    public function scan() {
      
      $args = array(
        'posts_per_page' => -1,
        'post_type' => array('post', 'page'),
        // get all types of posts except revisions and posts in the trash
        'status' => array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'trash' ),
      );
      
      $posts = get_posts($args);
      
      foreach ( $posts as $post ) {
          $this->scan_site_process->push_to_queue( $post );
      }
      
      $this->scan_site_process->save()->dispatch();
    }
    
    /**
  	 * Process handler
  	 */
  	public function process_handler() {
  		if ( ! isset( $_GET['process'] ) || ! isset( $_GET['_wpnonce'] ) ) {
  			return;
  		}

  		if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'process') ) {
  			return;
  		}

  		if ( 'scan_all_pages' === $_GET['process'] ) {
  			$this->scan();
  		}
      
  	}
    
    /**
     * Remove the form post table when plugin is uninstalled
     * 
     * @return void
     */
    public static function uninstall () {
      
        global $wpdb;
        
        // Define the table Name
        $gform_form_page_table = $wpdb->prefix . "gform_form_page";
        
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
    public function update_form_page_id($post_id) {
      
        // If this is a revision just return
        if ( wp_is_post_revision( $post_id ) )
        
        return;
        
        
        global $wpdb;
          
        // Grab the content from the form post
        $content = stripslashes($_POST['content']);
        
        $pattern = get_shortcode_regex();

        $form_id = $this->check_for_form($content, $pattern);
        
        $this->add_form_post_relation($form_id, $post_id);
      
    }
    
    /**
     * Checks for the form shortcode in the post content
     * 
     * @param  string $content The post content to search in
     * @param  string $pattern The regex pattern to match the form shortcode
     * 
     * @return int            The form_id found in the content
     */
    public function check_for_form( $content, $pattern ) {
      
        // Check if shortcode exists in the content
        if (   preg_match_all( '/'. $pattern .'/s', $content, $matches ) && array_key_exists( 2, $matches ) && in_array( 'gravityform', $matches[2] ) ){
            
            // Use the match to extract the form id from the shortcode
            preg_match('~id="(.*)\"~', $matches[3][0], $form_id);
            
            // Convert the form id to an int
            $form_id = (int) $form_id[1];
            
            return $form_id;
        }
      
    }
    
    /**
     * Adds the form_id and post_id to the table
     * 
     * @param int|string $form_id
     * @param int|string $post_id
     */
    public function add_form_post_relation( $form_id, $post_id ) {
      
        global $wpdb;
        
        // Define the table Name
        $gform_form_page_table = $wpdb->prefix . "gform_form_page";
        
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
    
    /**
     * Adds Location link to form menu
     * 
     * @param array $actions
     * @param int $form_id
     */
    public function add_form_post_action($actions, $form_id){
      $actions['locations'] = array(
    					'label'        => __( 'Locations', 'gravityforms' ),
    					'title'        => __( 'Posts this form appears on', 'gravityforms' ),
    					'url'          => '?page=locations&form_id=' . $form_id,
    					'capabilities' => 'gravityforms_edit_forms',
    					'priority'     => 699,
    				);
      return $actions;
    }
    
    /**
     * Add Locations link to form edit page
     * 
     * @param array $menu_items
     * @param int $form_id
     */
    public function add_location_form_edit_menu_option($menu_items, $form_id){
      
      $edit_capabilities = array( 'gravityforms_edit_forms' );
      
      $menu_items['locations'] = array(
    			'label'        => __( 'Locations', 'gravityforms' ),
    			'short_label' => esc_html__( 'Locations', 'gravityforms' ),
    			'icon'         => '<i class="fa fa-map-marker fa-lg"></i>',
    			'title'        => __( 'Posts this form appears on', 'gravityforms' ),
    			'url'          => '?page=locations&form_id=' . $form_id,
    			'menu_class'   => 'gf_form_toolbar_editor',
    			//'link_class'   => GFForms::toolbar_class( 'editor' ),
    			'capabilities' => $edit_capabilities,
    			'priority'     => 699,
    		);

      return $menu_items;
      
    }
    
    /**
     * Adds the Form Locations Menu Item
     * @param array $menu_items
     */
    public function add_location_menu_item( $menu_items ) {
        $menu_items[] = array( "name" => "locations", "label" => "Form Locations", "callback" => array( $this, "add_location_view" ), "permission" => "edit_posts" );
        return $menu_items;
    }
    
    /**
     * Add the location view
     * 
     * @param string $view
     * @param int $id
     */
    public function add_location_view( $view, $id ){
        
        require_once( plugin_dir_path( __FILE__ ) . '/includes/location.php' );
        
    }
    
    /**
     * Display a success message to the user when form scan is complete
     * 
     */
    public function scan_complete_notice() {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e( '<strong>Gravity Forms - Page Tracker Addon:</strong> Full site scan complete. <a href="' . admin_url( 'admin.php?page=locations') . '">View Form Locations</a>', 'gravity-forms-page-tracker-addon' ); ?></p>
        </div>
        <?php
    }
  
}

// Include WP_List_Table class
if ( ! class_exists( 'WP_List_Table' ) ) {
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

// Include the Form_Locations_Table class
if ( ! class_exists( 'Form_Locations_Table' ) ) {
  require_once( plugin_dir_path( __FILE__ ) . 'classes/Form_Locations_Table.php' );
}

$Gravity_Forms_Page_Tracker = new Gravity_Forms_Page_Tracker();
