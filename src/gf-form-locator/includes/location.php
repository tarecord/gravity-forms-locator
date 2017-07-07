<?php
/**
 * This file shows the location admin page.
 *
 * @package    GF-Form-Locator
 * @author     Tanner Record <tanner.record@gmail.com>
 * @license    GPL2
 * @since      File available since Release 1.0.0
 */

?>

<div class="wrap">

<h1>Form Locations</h1>

<a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=locations&process=scan_all_pages' ), 'process' ); ?>">Scan All Pages</a>

<?php
$locations_table = new Form_Locations_Table();
$locations_table->prepare_items();
if ( ! empty( $locations_table->items ) ) {

	$locations_table->display();

} else {
?>

<h2>No Forms Found</h2>

<?php
}
?>

</div>
