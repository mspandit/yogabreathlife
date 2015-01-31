<?php 
if (!current_user_can('MailPress_import')) wp_die(__('You do not have sufficient permissions to access this page.'));

global $mp_importers;

include(MP_MailPress_import_TMP . '/mp-admin/includes/import.php'); 

if (isset($_GET['import'])) {

	$importer = $_GET['import'];

	// Allow plugins to define importers as well

	if (! is_callable($mp_importers[$importer][2]))
	{
		if (! file_exists(MP_MailPress_import_TMP . "/mp-admin/import/$importer.php"))
		{
			wp_die(__('Cannot load importer.','MailPress'));
		}
		include(MP_MailPress_import_TMP . "/mp-admin/import/$importer.php");
	}


	define('MP_IMPORTING', true);

	call_user_func($mp_importers[$importer][2]);
}
else
{
?>
<div class="wrap">
<div id="icon-mailpress-tools" class="icon32"><br /></div>
<h2><?php _e('Import'); ?></h2>
<p><?php _e('If you have emails in another system, MailPress can import those into this blog. To get started, choose a system to import from below:','MailPress'); ?></p>

<?php

// Load all importers so that they can register.
$import_loc = MP_MailPress_import_PATH . 'mp-admin/import';
$import_root = ABSPATH.$import_loc;
$imports_dir = @ opendir($import_root);
if ($imports_dir) {
	while (($file = readdir($imports_dir)) !== false) {
		if ($file{0} == '.') {
			continue;
		} elseif (substr($file, -4) == '.php') {
			require_once($import_root . '/' . $file);
		}
	}
}
@closedir($imports_dir);

$importers = mp_get_importers();

if (empty ($importers)) 
{
	echo '<p>'.__('No importers are available.','MailPress').'</p>'; // TODO: make more helpful
} 
else 
{
	$importers_columns = MailPress_import::manage_list_columns();
	$hidden = (array) get_user_option( "_MailPress_manage-importers-columns-hidden" );
?>
<table class="widefat">
	<thead>
		<tr>
<?php importers_columns(); ?>
		  </tr>
	</thead>
<?php
	$style = '';
	foreach ($importers as $id => $data) {

		$alternate = ('class="alternate"' == $alternate || 'class="alternate active"' == $alternate) ? '' : 'alternate';
		$action = "<a href='" . MailPress_import  . "&amp;import=$id' title='".wptexturize(strip_tags($data[1]))."'>{$data[0]}</a>";

		if ($alternate != '') $alternate = 'class="'.$alternate .'"';
?>
			<tr <?php echo $alternate; ?>>
<?php
		$import_url = MailPress_import  . "&amp;import=$id";
		$import	= "<a href='$import_url' title='".wptexturize(strip_tags($data[1]))."'>$data[0]</a>";		

		foreach ( $importers_columns as $column_name=>$column_display_name ) 
		{
			$class = "class=\"$column_name column-$column_name\"";

			$style = '';
			if ( in_array($column_name, $hidden) ) $style = ' style="display:none;"';

			$attributes = "$class$style";

			switch ($column_name) 
			{
				case 'name' :
					$attributes = 'class="post-title column-title import-system row-title"' . $style;
?>
		<td  <?php echo $attributes ?>>
			<?php echo $import; ?>

		</td>
<?php
				break;
				case 'desc' :
?>
		<td  <?php echo $attributes ?>>	
			<?php echo $data[1]; ?>
		</td>
<?php
				break;
				default:
?>
		<td  <?php echo $attributes ?>>
			<?php	do_action('MailPress_manage_users_custom_column', $column_name, $user, $url_parms); ?>
		</td>
<?php
				break;
			}
		}
?>
			</tr>
<?php
	}
?>

</table>
<?php
}
?>

</div>
<?php
}
?>