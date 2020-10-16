<?php
/**
 * This file shows the location admin page.
 *
 * @package    GF-Form-Locator
 * @author     Tanner Record <tanner.record@gmail.com>
 * @license    GPL2
 * @since      File available since Release 1.0.0
 */

namespace TAR\GravityFormLocator\Views;

use TAR\GravityFormLocator\FormLocationsTable;

?>

<div class="wrap">

<h1>Form Locations</h1>

<?php
$locations_table = new FormLocationsTable();
$locations_table->prepare_items();
if ( ! empty( $locations_table->items ) ) {

	$locations_table->display();

} else {
	?>
	<h2>No Forms Found</h2>
	<?php
}
?>

<form action="<?php echo admin_url( 'admin.php?page=locations' ); ?>" method="post">
	<?php wp_nonce_field( 'process' ); ?>
	<input type="hidden" name="process" value="scan_for_forms">
	<input type="submit" name="submit" id="submit" class="button button-primary" value="Scan For Forms">
</form>

</div>
