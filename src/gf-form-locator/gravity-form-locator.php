<?php
/**
 * Plugin Name: Gravity Form Locator
 * Plugin URI: https://github.com/tarecord/gravity-forms-locator/
 * Description: A Gravity Form add-on that shows a list of forms with the page or post they are published on.
 * Version: 1.3.0
 * Author: Tanner Record
 * Author URI: http://www.tannerrecord.com
 * License: GPL2
 *
 * @package    GF-Form-Locator
 * @author     Tanner Record <tanner.record@gmail.com>
 * @license    GPL2
 * @since      File available since Release 1.0.0
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Since this plugin depends on Gravity Forms, we need to check if
 * Gravity Forms is currently active.
 *
 * If not, display an error to the user explaining why this plugin
 * could not be activated.
 */

add_action( 'plugins_loaded', 'dependency_check' );

/**
 * Checks to see if Gravity Forms is installed, activated and the correct version.
 */
function dependency_check() {

	// If Parent Plugin is NOT active.
	if ( current_user_can( 'activate_plugins' ) && ! class_exists( 'GFForms' ) ) {

		add_action( 'admin_init', 'gffl_deactivate' );
		add_action( 'admin_notices', 'gffl_admin_notice' );

		/**
		 * Deactivate the plugin.
		 */
		function gffl_deactivate() {
			deactivate_plugins( plugin_basename( __FILE__ ) );
		}

		/**
		 * Throw an Alert to tell the Admin why it didn't activate.
		 */
		function gffl_admin_notice() {
			$gffl_child_plugin  = __( 'Gravity Form Locator', 'gravity-form-locator' );
			$gffl_parent_plugin = __( 'Gravity Forms', 'gravity-form-locator' );

			echo sprintf(
				'<div class="error"><p>%1$s requires %2$s to function correctly. Please activate %2$s before activating %1$s. For now, the plugin has been deactivated.</p></div>',
				'<strong>' . esc_html( $gffl_child_plugin ) . '</strong>',
				'<strong>' . esc_html( $gffl_parent_plugin ) . '</strong>'
			);

			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
		}
	} else {

		// Start up the plugin.
		require_once 'classes/class-gravity-form-locator.php';

		// Handle activation and uninstalling.
		register_activation_hook( __FILE__, array( 'Gravity_Form_Locator', 'activate' ) );
		register_uninstall_hook( __FILE__, array( 'Gravity_Form_Locator', 'uninstall' ) );

		new Gravity_Form_Locator();
	}
}
