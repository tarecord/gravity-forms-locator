<?php
/**
 * Plugin Name: Gravity Form Locator
 * Plugin URI: https://github.com/tarecord/gravity-forms-locator/
 * Description: A Gravity Form add-on that shows a list of forms with the page or post they are published on.
 * Version: 1.4.2
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
 * Include the autoloader.
 */
require_once dirname( __FILE__ ) . '/vendor/autoload.php';

/**
 * Since this plugin depends on Gravity Forms, we need to check if
 * Gravity Forms is currently active.
 *
 * If not, display an error to the user explaining why this plugin
 * could not be activated.
 */

add_action( 'plugins_loaded', 'gfl_dependency_check' );

/**
 * Checks to see if Gravity Forms is installed, activated and the correct version.
 *
 * @since 1.3.0
 */
function gfl_dependency_check() {

	// If Parent Plugin is NOT active.
	if ( current_user_can( 'activate_plugins' ) && ! class_exists( 'GFForms' ) ) {

		add_action( 'admin_init', 'gfl_deactivate' );
		add_action( 'admin_notices', 'gfl_admin_notice' );

		/**
		 * Deactivate the plugin.
		 */
		function gfl_deactivate() {
			deactivate_plugins( plugin_basename( __FILE__ ) );
		}

		/**
		 * Throw an Alert to tell the Admin why it didn't activate.
		 */
		function gfl_admin_notice() {
			$gfl_child_plugin  = __( 'Gravity Forms Locator', 'gravity-form-locator' );
			$gfl_parent_plugin = __( 'Gravity Forms', 'gravity-form-locator' );

			echo sprintf(
				'<div class="error"><p>Please activate <strong>%2$s</strong> before activating <strong>%1$s</strong>. For now, the plugin has been deactivated.</p></div>',
				esc_html( $gfl_child_plugin ),
				esc_html( $gfl_parent_plugin )
			);

			// phpcs:disable
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
			// phpcs:enable
		}
	}
}

// Handle activation and uninstalling.
register_activation_hook( __FILE__, array( TAR\GravityFormLocator\Core::class, 'activate' ) );
register_uninstall_hook( __FILE__, array( TAR\GravityFormLocator\Core::class, 'uninstall' ) );

$gfl_plugin = new TAR\GravityFormLocator\Core();
$gfl_plugin->init();
