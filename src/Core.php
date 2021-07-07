<?php
/**
 * This file is the main class file for the plugin.
 *
 * @package    GF-Form-Locator
 * @author     Tanner Record <tanner.record@gmail.com>
 * @license    GPL2
 * @since      File available since Release 1.0.0
 */

namespace TAR\GravityFormLocator;

use WP_Query;

/**
 * The main class
 */
class Core {

	/**
	 * The form scan process.
	 *
	 * @var TAR\GravityFormLocator\ScanExistingFormProcess
	 */
	private $scan_site_process;

	/**
	 * Construct the instance
	 */
	public function __construct() {
		$this->scan_site_process = new ScanExistingFormProcess();
	}

	/**
	 * Create the form post table when the plugin is activated
	 *
	 * @return void
	 */
	public static function activate() {

		global $wpdb;

		// Define the table Name.
		$gform_form_page_table = $wpdb->prefix . 'gform_form_page';

		$charset_collate = $wpdb->get_charset_collate();

		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $gform_form_page_table ) );

		// Check if table exists before trying to create it.
		if ( null === $table_exists ) {

			// Create the table.
			$sql = "CREATE TABLE $gform_form_page_table (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			form_id mediumint(8) NOT NULL,
			post_id bigint(20) NOT NULL,
			PRIMARY KEY (id)
			) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

		}

	}

	/**
	 * Init
	 *
	 * @return void
	 */
	public function init() {

		// Update the table with the page/form id when the post is saved.
		add_action( 'save_post', array( $this, 'update_form_page_id' ), 10, 3 );

		// Add new menu item to show list of all forms and their post relations.
		add_filter( 'gform_addon_navigation', array( $this, 'add_location_menu_item' ) );

		// Add location as a view option in the main form view.
		add_filter( 'gform_toolbar_menu', array( $this, 'add_location_form_edit_menu_option' ), 10, 2 );

		// Add action link to view posts that contain the form.
		add_filter( 'gform_form_actions', array( $this, 'add_form_post_action' ), 10, 2 );

		add_action( 'init', array( $this, 'process_handler' ) );

		// Confirm with the user that the scan has started (if it hasn't already finished).
		if ( get_transient( 'gfl_scan_running' ) & ! get_transient( 'gfl_scan_complete' ) ) {
			add_action( 'admin_notices', array( $this, 'scan_running_notice' ) );
			delete_transient( 'gfl_scan_running' );
		}

		// If the scan is complete, show a notice to the user.
		if ( get_transient( 'gfl_scan_complete' ) ) {
			add_action( 'admin_notices', array( $this, 'scan_complete_notice' ) );
			delete_transient( 'gfl_scan_running' );
			delete_transient( 'gfl_scan_complete' );
		}
	}

	/**
	 * Sets up the background process and dispatches the queue.
	 *
	 * @return void
	 */
	public function scan() {

		// Query all posts ignoring the count and returning all.
		$args = array(
			'post_type'     => array( 'page', 'post' ),
			'no_found_rows' => true,
			'nopaging'      => true,
		);

		$post_query = new WP_Query( $args );

		foreach ( $post_query->posts as $post ) {
			$this->scan_site_process->push_to_queue( $post->ID );
		}

		$this->scan_site_process->save()->dispatch();

	}

	/**
	 * Process handler
	 *
	 * @return void
	 */
	public function process_handler() {
		if ( ! isset( $_POST['process'] ) || ! isset( $_POST['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'process' ) ) {
			return;
		}

		if ( 'scan_for_forms' === $_POST['process'] ) {
			$this->scan();
			set_transient( 'gfl_scan_running', true, HOUR_IN_SECONDS );
			wp_safe_redirect( admin_url( 'admin.php?page=locations' ) );
			exit();
		}
	}

	/**
	 * Remove the form post table when plugin is uninstalled
	 *
	 * @return void
	 */
	public static function uninstall() {

		global $wpdb;

		// Define the table Name.
		$gform_form_page_table = $wpdb->prefix . 'gform_form_page';

		// Check if table exists before trying to delete it.
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $gform_form_page_table ) ) === $gform_form_page_table ) {

			$wpdb->query( $wpdb->prepare( 'DROP TABLE %s;', $gform_form_page_table ) );

		}

	}

	/**
	 * Save page/form relation in the database on save
	 *
	 * @param int    $post_id The post id returned from the save_post action.
	 * @param object $post    The post object.
	 * @param bool   $update  Whether the save is updating an existing post or not.
	 *
	 * @return  void
	 */
	public function update_form_page_id( $post_id, $post, $update ) {

		$valid_post_types = get_post_types(
			array(
				'public' => true,
			)
		);

		// Bail if current post type is not public (i.e. not a post, page, attachment or custom post type).
		if ( ! key_exists( $post->post_type, $valid_post_types ) ) {
			return;
		}

		// If this is a revision don't do anything.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		$pattern  = get_shortcode_regex( array( 'gravityform' ) );
		$form_ids = $this->check_for_forms( $post, $pattern );

		// If this is an existing post being updated.
		if ( $update ) {

			// Remove form references and rescan post.
			( new FormPostRelation() )->remove( $post_id );
			( new FormPostRelation() )->add( $form_ids, $post_id );

		} else {

			// Does the post have any forms?
			if ( $form_ids ) {
				( new FormPostRelation() )->add( $form_ids, $post_id );
			}
		}
	}

	/**
	 * Checks for the form shortcode in the post content
	 *
	 * @param object $post    The post object to search in.
	 * @param string $pattern The regex pattern to match the form shortcode.
	 *
	 * @return mixed The form shortcodes or false.
	 */
	public function check_for_forms( $post, $pattern ) {

		if ( is_int( $post ) ) {
			$post = get_post( $post );
		}

		$matches  = array();
		$form_ids = array();

		if ( function_exists( 'has_block' ) && has_block( 'gravityforms/form', $post->post_content ) ) {
			$blocks = parse_blocks( $post->post_content );

			foreach ( $blocks as $block ) {
				if ( 'gravityforms/form' === $block['blockName'] ) {
					array_push( $form_ids, intval( $block['attrs']['formId'] ) );
				}
			}
		}

		preg_match_all( '/' . $pattern . '/s', $post->post_content, $matches );

		// Check if at least 1 shortcode was found.
		if ( ! empty( $matches[0] ) && '' !== $matches[0][0] ) {
			$forms = $this->get_shortcode_ids( $matches[0] );
			if ( is_array( $forms ) ) {
				$form_ids = array_merge( $form_ids, $forms );
			}
		}

		return ( ! empty( $form_ids ) ) ? $form_ids : false;
	}

	/**
	 * Gets the ids from the form shortcodes.
	 *
	 * @param string $shortcodes The shortcodes to get the IDs from.
	 */
	public function get_shortcode_ids( $shortcodes = array() ) {

		$form_ids = array();

		foreach ( $shortcodes as $shortcode ) {
			// Use the match to extract the form id from the shortcode.
			if ( preg_match( '~id=[\"\']?([^\"\'\s]+)[\"\']?~i', $shortcode, $form_id ) ) {

				// If we have the form id, add it to the array.
				array_push( $form_ids, intval( $form_id[1] ) );
			}
		}

		if ( ! empty( $form_ids ) ) {
			return $form_ids;
		}

		return false;
	}

	/**
	 * Adds Location link to form menu
	 *
	 * @param array $actions  The original array of actions.
	 * @param int   $form_id  The form id.
	 *
	 * @return $actions The array of actions.
	 */
	public function add_form_post_action( $actions, $form_id ) {
		$actions['locations'] = array(
			'label'        => __( 'Locations', 'gravity-form-locator' ),
			'title'        => __( 'Posts this form appears on', 'gravity-form-locator' ),
			'url'          => '?page=locations&form_id=' . $form_id,
			'capabilities' => 'gravityforms_edit_forms',
			'priority'     => 699,
		);
		return $actions;
	}

	/**
	 * Add Locations link to form edit page
	 *
	 * @param array $menu_items  The menu items to override.
	 * @param int   $form_id     The form id.
	 *
	 * @return array  The menu items to add to the table.
	 */
	public function add_location_form_edit_menu_option( $menu_items, $form_id ) {

		$edit_capabilities = array( 'gravityforms_edit_forms' );

		$menu_items['locations'] = array(
			'label'        => __( 'Locations', 'gravity-form-locator' ),
			'short_label'  => esc_html__( 'Locations', 'gravity-form-locator' ),
			'icon'         => '<i class="fa fa-map-marker fa-lg"></i>',
			'title'        => __( 'Posts this form appears on', 'gravity-form-locator' ),
			'url'          => '?page=locations&form_id=' . $form_id,
			'menu_class'   => 'gf_form_toolbar_editor',
			'capabilities' => $edit_capabilities,
			'priority'     => 699,
		);

		return $menu_items;

	}

	/**
	 * Adds the Form Locations Menu Item
	 *
	 * @param array $menu_items The menu items to override.
	 *
	 * @return array An array of menu items.
	 */
	public function add_location_menu_item( $menu_items = array() ) {
		$menu_items[] = array(
			'name'       => 'locations',
			'label'      => 'Form Locations',
			'callback'   => array( $this, 'add_location_view' ),
			'permission' => 'edit_posts',
		);
		return $menu_items;
	}

	/**
	 * Add the location view.
	 *
	 * @return void
	 */
	public function add_location_view() {

		include_once dirname( __FILE__ ) . '/views/location.php';

	}

	/**
	 * Display a success message to the user when form scan is complete
	 *
	 * @return void
	 */
	public function scan_complete_notice() {
		?>
		<div class="notice notice-success is-dismissible">
		<?php
		echo sprintf(
			'<strong>Gravity Form Locator:</strong> Full site scan complete. <a href="%s">View Form Locations</a>',
			esc_url( admin_url( 'admin.php?page=locations' ) )
		);
		?>
		</div>
		<?php
	}

	/**
	 * Display a message to the user when form scan has started.
	 *
	 * @return void
	 */
	public function scan_running_notice() {
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php echo '<strong>Gravity Form Locator:</strong> Full site scan has started. You will see a notice when it has completed.'; ?></p>
		</div>
		<?php
	}
}
