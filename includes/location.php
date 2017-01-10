<h1>Form Locations</h1>

<?php // TODO: Add form post/page association table ?><?php
<?php
    $locations_table = new Form_Locations_Table();
    $locations_table->prepare_items();
    $locations_table->display();
?>

