<div class="wrap">
  
<h1>Form Locations</h1>

<a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=locations&process=scan_all_pages' ), 'process' ); ?>">Scan All Pages</a>

<?php
	$locations_table = new Form_Locations_Table();
	$locations_table->prepare_items();
	$locations_table->display();
?>

</div>
