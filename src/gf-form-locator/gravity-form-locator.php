<?php
/**
 * Plugin Name: Gravity Form Locator
 * Plugin URI: https://github.com/tarecord/gravity-forms-locator/
 * Description: Simple add-on for Gravity Forms that scans your website in the background and shows a list of each form published with the page or post that it is published on.
 * Version: 1.0.1
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
require_once( 'vendor/class-wps-extend-plugin.php' );
require_once( 'classes/class-gravity-form-locator.php' );

new WPS_Extend_Plugin( 'gravityforms/gravityforms.php', __FILE__, '2.0', 'gravity-form-locator' );

// Handle activation and uninstalling.
register_activation_hook( __FILE__, array( 'Gravity_Form_Locator', 'activate' ) );
register_uninstall_hook( __FILE__, array( 'Gravity_Form_Locator', 'uninstall' ) );

// Now, for the main event.
$gravity_form_locator = new Gravity_Form_Locator();
