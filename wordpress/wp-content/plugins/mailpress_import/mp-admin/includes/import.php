<?php

function mp_get_importers() {
	global $mp_importers;
	if ( is_array($mp_importers) )
		uasort($mp_importers, create_function('$a, $b', 'return strcmp($a[0], $b[0]);'));
	return $mp_importers;
}

function mp_register_importer( $id, $name, $description, $callback ) {
	global $mp_importers;
	if ( is_wp_error( $callback ) )
		return $callback;
	$mp_importers[$id] = array ( $name, $description, $callback );
}

function mp_import_cleanup( $id ) {
	wp_delete_attachment( $id );
}

function mp_import_handle_upload() {
	$overrides = array( 'test_form' => false, 'test_type' => false );
	$_FILES['import']['name'] .= '.import';
	$file = mp_handle_upload( $_FILES['import'], $overrides );

	if ( isset( $file['error'] ) )
		return $file;

	$url = $file['url'];
	$type = $file['type'];
	$file = addslashes( $file['file'] );
	$filename = basename( $file );

	// Construct the object array
	$object = array( 'post_title' => $filename,
		'post_content' => $url,
		'post_mime_type' => $type,
		'guid' => $url
	);

	// Save the data
	$id = mp_insert_attachment( $object, $file );

	return array( 'file' => $file, 'id' => $id );
}

function importers_columns($id=true) {
	$importers_columns = MailPress_import::manage_list_columns();
	$hidden = (array) get_user_option( "_MailPress_manage-importers-columns-hidden" );
	foreach ( $importers_columns as $importer_column_key => $column_display_name ) {
		if ( 'cb' === $importer_column_key )
			$class = ' class="check-column"';
		else
			$class = " class=\"manage-column column-$importer_column_key\"";

		$style = '';
		if ( in_array($importer_column_key, $hidden) )
			$style = ' style="display:none;"';
?>
					<th scope="col" <?php if ($id) echo "id=\"$importer_column_key\""; echo $class; echo $style?>><?php echo $column_display_name; ?></th>
<?php }
}

?>
