<?php
if (!current_user_can('MailPress_manage_autoresponders')) wp_die(__('You do not have sufficient permissions to access this page.'));

function autoresponders_columns($id=true)
{
	global $mp_screen;

	$autoresponders_columns = MailPress_autoresponder::manage_list_columns();
	$hidden = (array) get_user_option( "manage" . $mp_screen . "columnshidden" );
	foreach ( $autoresponders_columns as $autoresponders_column_key => $column_display_name ) {
		if ( 'cb' === $autoresponders_column_key )
			$class = ' class="check-column"';
		else
			$class = " class=\"manage-column column-$autoresponders_column_key\"";

		$style = '';
		if ( in_array($autoresponders_column_key, $hidden) )
			$style = ' style="display:none;"';
?>
					<th scope="col" <?php if ($id) echo "id=\"$autoresponders_column_key\""; echo $class; echo $style?>><?php echo $column_display_name; ?></th>
<?php	} 
}

function autoresponders_rows( $page = 1, $pagesize = 20 ) 
{
// Get a page worth of autoresponders
	$start = ($page - 1) * $pagesize;

	$args = (!empty($_GET['s'])) ? array('search' => $_GET['s']) : array();
	$autoresponders = MailPress_autoresponder::get_all($args);

// convert it to table rows
	$out = '';
	$count = 0;
	foreach( $autoresponders as $autoresponder ) $out .= MailPress_autoresponder::row( $autoresponder, ++$count % 2 ? ' class="iedit alternate"' : ' class="iedit"', $page );

// filter and send to screen
	echo $out;
	return $count;
}


include(MP_MailPress_autoresponder_TMP . "/mp-includes/options.php");

global $action;
global $mp_screen;

wp_reset_vars(array('action'));

if ('edit' == $action) 
{
	$h3 = __('Update the autoresponder','MailPress');
	$action = 'edited';
	$disabled = " disabled='disabled'";
	$cancel = "<input type='submit' class='button' name='cancel' value=\"" . __('Cancel','MailPress') . "\" />\n";

	$id = (int) $_GET['id'];
	$autoresponder = MailPress_autoresponder::get($id);

	$hidden = "<input type='hidden' name='id'   value=\"" . $id . "\" />\n";
	$hidden .="<input name='name' type='hidden' value=\"" . attribute_escape($autoresponder->name) . "\"/>";

	$_mails = MailPress_autoresponder::get_all_mails($id);
}
else 
{
	$customfield = array();
	$h3 = __('Add an autoresponder','MailPress');
	$action = 'add';
	$hidden = '';
	$disabled = '';
	$cancel = '';

	$_mails = false;
}


$url_parms = MP_Admin::get_url_parms();
//
// MANAGING MESSAGE
//

$messages[1] = __('Autoresponder added.','MailPress');
$messages[2] = __('Autoresponder deleted.','MailPress');
$messages[3] = __('Autoresponder updated.','MailPress');
$messages[4] = __('Autoresponder not added.','MailPress');
$messages[5] = __('Autoresponder not updated.','MailPress');
$messages[6] = __('Autoresponders deleted.','MailPress');

if (isset($_GET['message']))
{
	$fade = $messages[$_GET['message']];
	$_SERVER['REQUEST_URI'] = remove_query_arg(array('message'), $_SERVER['REQUEST_URI']);
}

//
// MANAGING PAGINATION
//
$url_parms['apage']	= isset($_GET['apage'])		? $_GET['apage'] : 1;
if( !$autorespondersperpage || $autorespondersperpage < 0 ) $autorespondersperpage = 20;

$page_links = paginate_links	(array(	'base' => add_query_arg( 'apage', '%#%' ),
							'format' => '',
							'total' => ceil(wp_count_terms(MailPress_taxonomy_autoresponder) / $autorespondersperpage),
							'current' => $url_parms['apage']
						)
					);
?>
<div class="wrap nosubsub">
	<div id="icon-mailpress-tools" class="icon32"><br/></div>
	<h2 class="floatedh2"><?php _e('Autoresponders','MailPress'); ?></h2>
<?php if (isset($fade)) MP_Admin::message($fade); ?>
	<form class='search-form topmargin' action='' method='get'>
		<p class='search-box'>
			<input type='hidden' name='page' value='<?php echo MailPress_page_autoresponders; ?>' />
			<input type='text' id='post-search-input' name='s' value='<?php echo $url_parms['s']; ?>' class="search-input"  />
			<input type='submit' value="<?php _e( 'Search Autoresponders','MailPress' ); ?>" class='button' />
		</p>
	</form>
	<br class='clear' />
	<div id="col-container">
		<div id="col-right">
			<div class="col-wrap">	
				<form id='posts-filter' action='' method='get'>
					<div class='tablenav'>
<?php 	if ( $page_links ) echo "						<div class='tablenav-pages'>$page_links</div>"; ?>
						<div class='alignleft actions'>
							<input type='submit' value="<?php _e('Delete','MailPress'); ?>" name='deleteit' class='button-secondary delete action' />
							<input type='hidden' name='page' value='<?php echo MailPress_page_autoresponders; ?>' />
						</div>
						<br class='clear' />
					</div>
					<div class="clear"></div>
					<table class='widefat'>
						<thead>
							<tr>
<?php autoresponders_columns(); ?>
							</tr>
						</thead>
						<tfoot>
							<tr>
<?php autoresponders_columns(false); ?>
							</tr>
						</tfoot>
						<tbody id='the_arlist' class='list:map'>
<?php
autoresponders_rows($url_parms['apage'], $autorespondersperpage );
?>
						</tbody>
					</table>
					<div class='tablenav'>
<?php 	if ( $page_links ) echo "						<div class='tablenav-pages'>$page_links</div>\n"; ?>
						<div class='alignleft actions'>
							<input type='submit' value="<?php _e('Delete','MailPress'); ?>" name='deleteit' class='button-secondary delete' />
						</div>
						<br class='clear' />
					</div>
					<br class='clear' />
				</form>
			</div>
		</div><!-- /col-right -->

		<div id="col-left">
			<div class="col-wrap">
				<div class="form-wrap">
					<h3><?php echo $h3; ?></h3>
					<div id="ajax-response"></div>
					<form name='<?php echo $action; ?>'  id='<?php echo $action; ?>'  method='post' action='' class='<?php echo $action; ?>:the_arlist: validate'>
						<input type='hidden' name='action'   value='<?php echo $action; ?>' />
						<input type='hidden' name='formname' value='autoresponder_form' />
						<?php echo $hidden; ?>
						<?php wp_nonce_field('update-autoresponder'); ?>
						<div class="form-field form-required" style='margin:0;padding:0;'>
							<label for='autoresponder_name'><?php _e('Name','MailPress'); ?></label>
							<input name='name' id='autoresponder_name' type='text'<?php echo $disabled; ?> value="<?php echo attribute_escape($autoresponder->name); ?>" size='40' aria-required='true' />
							<p><?php _e('The name is used to identify the autoresponder almost everywhere.','MailPress'); ?></p>
						</div>
						<div class="form-field form-required" style='margin:0;padding:0;'>
							<label for='autoresponder_slug'><?php _e('Slug','MailPress') ?></label>
							<input name='slug' id='autoresponder_slug' type='text' value="<?php echo attribute_escape(apply_filters('MailPress_autoresponder_remove_slug', $autoresponder->slug)); ?>" size='40' />
							<p><?php _e('The &#8220;slug&#8221; is a unique id for the autoresponder (not so friendly !).','MailPress'); ?></p>
						</div>
						<div class="form-field" style='margin:0;padding:0;'>
							<label><?php _e('Description','MailPress') ?></label>
							<input type="text" id='autoresponder_description' name='autoresponder[description][desc]' value="<?php echo htmlentities(stripslashes($autoresponder->description['desc']),ENT_QUOTES); ?>" size="40"/>
						</div>
						<div class="form-field" style='margin:0;padding:0;'>
							<label><?php _e('Event','MailPress') ?></label>
							<select id='autoresponder_event' name='autoresponder[description][event]'>
<?php MP_Admin::select_option($mp_autoresponders_by_id,$autoresponder->description['event']); ?>
							</select>
						</div>
						<div class="form-field" style='margin:0;padding:0;'>
							<label><?php _e('Active','MailPress') ?></label>
							<input type="checkbox" id='autoresponder_active' name='autoresponder[description][active]'<?php if (isset($autoresponder->description['active'])) echo " checked='checked'"; ?> style='width:auto;'/>
							<p><?php _e("If unchecked during a certain period of time, All mails that should have been sent on time will be cancelled. Following mails (if any) will be lost as well.",'MailPress'); ?></p>
						</div>
<?php if ($_mails) : ?>
						<div class="form-field" style='margin:0;padding:0;'>
							<label><?php _e('Mails','MailPress') ?></label>
							<table class="widefat" style='width:100%;'>
								<thead>
									<tr>
										<th><?php _e('mail','MailPress'); ?></th>
										<th><?php _e('subject','MailPress'); ?></th>
										<th><?php _e('m/d/h','MailPress'); ?></th>
									</tr>
								</thead>
								<tbody>
<?php 	foreach($_mails as $_mail) 
		{ 
			$id = $_mail['mail_id'];
			$mail 	= MP_Mail::get( $id );
			$subject_display = htmlspecialchars($mail->subject,ENT_QUOTES);
			if ( strlen($subject_display) > 40 )	$subject_display = substr($subject_display, 0, 39) . '...';
			if ( '' == $mail->subject)  			$subject_display = $mail->subject = htmlspecialchars(__('(no subject)','MailPress'),ENT_QUOTES);

			$edit_url    	= clean_url(MailPress_edit . "&id=$id");
			$actions['edit']    = "<a href='$edit_url'   title='" . sprintf( __('Edit "%1$s"','MailPress') , $subject_display ) . "'>" . $_mail['mail_id'] . '</a>';

			$view_url		= clean_url(get_option('siteurl') . '/' . MP_PATH . "mp-includes/action.php?action=iview&id=$id&KeepThis=true&TB_iframe=true&width=600&height=400");
			$actions['view'] = "<a href='$view_url' class='thickbox'  title='" . sprintf( __('View "%1$s"','MailPress') , $subject_display ) . "'>" . $subject_display . '</a>';
?>
									<tr>
										<td>
											<?php echo $actions['edit']; ?>
										</td>
										<td>
											<?php echo $actions['view']; ?>
										</td>
										<td>
											<?php echo substr($_mail['schedule'],0,2) . '/' . substr($_mail['schedule'],2,2) . '/' . substr($_mail['schedule'],4,2) ; ?>
										</td>
									<tr>
<?php 	} ?>
								</tbody>
							</table>
							<p></p>
						</div>
<?php endif; ?>
						<p class='submit'>
							<input type='submit' class='button' name='submit' id='autoresponder_submit' value="<?php echo $h3; ?>" />
							<?php echo $cancel; ?>
						</p>
					</form>
				</div>
			</div>
		</div><!-- /col-left -->
	</div><!-- /col-container -->
</div><!-- /wrap -->
