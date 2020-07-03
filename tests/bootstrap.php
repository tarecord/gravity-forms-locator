<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Gravity_Form_Locator
 */

require '/wp-phpunit/includes/functions.php';

tests_add_filter( 'muplugins_loaded', function () {
	require dirname( __FILE__ ) . '/../src/gravity-form-locator.php';
} );

require '/wp-phpunit/includes/bootstrap.php';
