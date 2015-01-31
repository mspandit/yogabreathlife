<?php
if (!current_user_can('MailPress_view_logs')) wp_die(__('You do not have sufficient permissions to access this page.'));

function mp_viewlog_row($file, $url_parms)
{
	static $row_class = '';

	$f = substr($file,strpos($file,'wp-content'));
	$view_url 	= "../" . MailPress_view_logs::get_path() . '/' . $f;
	$delete_url	= MP_Admin::url( MailPress_view_logs  ."&action=delete&id=$file",	"delete-file_$id" ,	$url_parms );

	$actions['view'] = "<a href='$view_url' target='_blank' title='" . sprintf( __('View "%1$s"','MailPress') , $file ) . "'>"	. __('View','MailPress') . '</a>';

//	$actions['delete']  = "<a href='$delete_url' class='delete:the-file-list:file-$file delete'>" . __('Delete','MailPress') . '</a>';

	$row_class = (" class='alternate'" == $row_class) ? '' : " class='alternate'";
	$attributes = "class='post-title column-title'";
?>
				<tr<?php echo $row_class; ?>>
					<th class="check-column" scope="row">
						<input type="checkbox" value="<?php echo $file; ?>" name="delete[]" />
					</th>
					<td  <?php echo $attributes ?>>
						<span style='display:block;'>
							<strong style='display:inline;'>
								<a class='row-title' target='_blank' href='<?php echo $view_url; ?>' title='<?php printf( __('View "%1$s"','MailPress') , $file ); ?>'>
									<?php echo $file; ?>
								</a>
							</strong>
						</span>
						<div class="row-actions">
<?php
						$action_count = count($actions);
						$i = 0;
						foreach ( $actions as $action => $link ) {
							++$i;
							( $i == $action_count ) ? $sep = '' : $sep = ' | ';
							echo "<span class='$action'>$link$sep</span>\n";
						}
?>
						</div>
					</td>
				</tr>
<?php
}

global $wpdb;
$ftmplt	= (isset($wpdb->blogid)) ? 'MP_Log_' . $wpdb->blogid . '_mailpress_' : 'MP_Log_mailpress_' ;
$path 	= '../' . MailPress_view_logs::get_path();

$url_parms = MP_Admin::get_url_parms();
if (empty($url_parms['s'])) unset($url_parms['s']);

$logs = array();
if (is_dir($path) && ($l = opendir($path))) 
{
	while (($file = readdir($l)) !== false) 
	{
      	switch (true)
		{
			case ($file  == '.') :
			break;
			case ($file  == '..') :
			break;
			case (isset($url_parms['s'])) :
				if ((strstr($file,$ftmplt)) && (strstr($file,$url_parms['s'])))
					$logs[] = $file;
			break;
			case (strstr($file,$ftmplt)) :
				$logs[] = $file;
			break;
		}
	}
	closedir($l);
}

// sort logs by date
function mp_compare_date_logs($a,$b) {  if ($a[1] == $b[1]) return 0;  return ($a[1] > $b[1]) ? -1 : 1; }
$files = array();
foreach ($logs as $log)	$files[] = array($log, filemtime($path . '/' . $log));
usort($files,'mp_compare_date_logs');
$logs = array();
foreach ($files as $file) { $logs[] = $file[0]; }

//
// MANAGING CHECKBOX RESULTS
//
if ( isset( $_GET['deleted'] )  ) 
{
	$deleted   		= isset( $_GET['deleted'] )   	? (int) $_GET['deleted']   	: 0;

	if ( $deleted > 0 ) 
	{
		$fade = sprintf( __ngettext( __('%s file deleted', 'MailPress'), __('%s files deleted', 'MailPress'), $deleted ), $deleted );
	}
}

//
// MANAGING TITLE
//
$title = __('View Logs','MailPress');

//
// MANAGING SUBSUBSUB URL
//
$status_links 	= array();
$status_links[] 	= "	<li><a href=\"" . MailPress_view_logs . "\" class='current'>".__('Show All Logs','MailPress')."</a>";
$subsubsub_urls = implode(' | </li>', $status_links) . '</li>';
unset($status_links);

//
// MANAGING PAGINATION
//
	$url_parms['apage'] = $page	= isset($_GET['apage'])		? $_GET['apage'] : 1;
	$total 				= count($logs);

	do
	{
		$start = ($page - 1) * 20;
		$_logs = array_slice ($logs, ($page - 1) * 20, 25); // Grab a few extra
		$page--;
	} while ($total <= $start);

	$files 		= array_slice($_logs, 0, 20);
	$extra_files 	= array_slice($_logs, 20);

	$page_links = paginate_links	(array(	'base' => add_query_arg( 'apage', '%#%' ),
								'format' => '',
								'total' => ceil($total / 20),
								'current' => $url_parms['apage']
							)
						);

	if ($url_parms['apage'] == 1) unset($url_parms['apage']);

?>
<?php if (isset($fade)) MP_Admin::message($fade); ?>
<div class='wrap'>
	<div id="icon-mailpress-tools" class="icon32"><br /></div>
	<h2>
		<?php echo $title; ?>
	</h2>
	<ul class='subsubsub'>

<?php echo $subsubsub_urls; ?>

	</ul>
	<form id='search-form' action='' method='get'>
		<input type='hidden' name='page' value='<?php echo MailPress_page_view_logs; ?>' />
		<p id='post-search' class='search-box'>
			<input type='text' id='file-search-input' name='s' value='<?php echo $url_parms['s']; ?>' class="search-input" />
			<input type='submit' value="<?php _e( 'Search Logs','MailPress' ); ?>" class='button' />
		</p>
	</form>
	<form id='posts-filter' action='' method='get'>
		<input type='hidden' name='page' value='<?php echo MailPress_page_view_logs; ?>' />
<?php MP_Admin::post_url_parms((array) $url_parms); ?>

		<div class='tablenav'>
			<div class='alignleft actions'>
				<input type='submit' value="<?php _e('Delete','MailPress'); ?>" name='deleteit' class='button-secondary delete' />
			</div>
<?php 	if ( $page_links ) echo "			<div class='tablenav-pages'>$page_links</div>"; ?>
			<div class="clear"></div>
		</div>
		<div class="clear"></div>
<?php
	if ($files)
	{
?>
		<table class='widefat'>
			<thead>
				<tr>
					<th scope='col' class='check-column'><input type='checkbox' /></th>
					<th scope='col'><?php _e('File','MailPress') ?></th>
				  </tr>
			</thead>
			<tfoot>
				<tr>
					<th scope='col' class='check-column'><input type='checkbox' /></th>
					<th scope='col'><?php _e('File','MailPress') ?></th>
				  </tr>
			</tfoot>
			<tbody id='the-file-list' class='list:file'>
<?php
		foreach ($files as $file)
		{
			mp_viewlog_row($file, $url_parms);
		}
?>
			</tbody>
			<tbody id='the-extra-file-list' class='list:file' style='display:none;'>
<?php
		foreach ($extra_files as $file)
		{
			mp_viewlog_row($file, $url_parms);
		}
?>
			</tbody>
		</table>
		<div class='tablenav'>
<?php 	if ( $page_links ) echo "			<div class='tablenav-pages'>$page_links</div>"; ?>
			<div class='alignleft actions'>
				<input type='submit' value="<?php _e('Delete','MailPress'); ?>" name='deleteit' class='button-secondary delete' />
			</div>
			<br class='clear' />
		</div>
	</form>

	<form id='get-extra-files' method='post' action='' class='add:the-extra-file-list:' style='display: none;'>
<?php MP_Admin::post_url_parms((array) $url_parms); ?>
<?php wp_nonce_field( 'add-file', '_ajax_nonce', false ); ?>
	</form>

	<div id='ajax-response'></div>
<?php
} else {
?>
	</form>
		<p>
<?php
		if (is_dir($path)) _e('No logs available','MailPress'); 
		else  printf( __('Wrong path : %s', 'MailPress'), $path );

?>
		</p>
<?php
}
?>
</div>