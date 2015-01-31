<?php
if (!current_user_can('MailPress_manage_mailinglists')) wp_die(__('You do not have sufficient permissions to access this page.'));

global $action;
global $mp_screen;

function mailinglists_columns($id=true)
{
	global $mp_screen;

	$mailinglists_columns = MailPress_mailing_lists::manage_list_columns();
	$hidden = (array) get_user_option( "manage" . $mp_screen . "columnshidden" );
	foreach ( $mailinglists_columns as $mailinglists_column_key => $column_display_name ) {
		if ( 'cb' === $mailinglists_column_key )
			$class = ' class="check-column"';
		else
			$class = " class=\"manage-column column-$mailinglists_column_key\"";

		$style = '';
		if ( in_array($mailinglists_column_key, $hidden) )
			$style = ' style="display:none;"';
?>
					<th scope="col" <?php if ($id) echo "id=\"$mailinglists_column_key\""; echo $class; echo $style?>><?php echo $column_display_name; ?></th>
<?php	} 
}

wp_reset_vars(array('action'));

if ('user' == $_GET['file'])
{
	include('includes/user_mailinglist.php');
}
elseif ('edit' == $action)
{
	$mailinglist_ID = (int) $_GET['id'];
	$mailinglist = get_mailinglist_to_edit($mailinglist_ID);
	include('edit-mailinglist-form.php');
}
else
{
	$url_parms = MP_Admin::get_url_parms();
//
// MANAGING MESSAGE
//
	$messages[1] = __('Mailing list added.','MailPress');
	$messages[2] = __('Mailing list deleted.','MailPress');
	$messages[3] = __('Mailing list updated.','MailPress');
	$messages[4] = __('Mailing list not added.','MailPress');	
	$messages[5] = __('Mailing list not updated.','MailPress');
	if (isset($_GET['message']))
	{
		$fade = $messages[$_GET['message']];
		$_SERVER['REQUEST_URI'] = remove_query_arg(array('message'), $_SERVER['REQUEST_URI']);
	}
//
// MANAGING TITLE
//
	if ( true ) $title = sprintf(__('Manage Mailing lists (<a href="%s">Add New</a>)','MailPress'), '#add');
	else		$title = __('Manage Mailing lists','MailPress');
//
// MANAGING PAGINATION
//
	$url_parms['apage']	= isset($_GET['apage'])		? $_GET['apage'] : 1;
	$total 			= wp_count_terms(MailPress_taxonomy_mailing_lists);
	if( !$mailinglistsperpage || $mailinglistsperpage < 0 ) $mailinglistsperpage = 20;
	$page_links = paginate_links	(array(	'base' => add_query_arg( 'apage', '%#%' ),
								'format' => '',
								'total' => ceil($total / $mailinglistsperpage),
								'current' => $url_parms['apage']
							)
						);

?>
<?php if (isset($fade)) MP_Admin::message($fade); ?>
<div class='wrap'>
	<div id="icon-mailpress-users" class="icon32"><br /></div>
	<h2 class="floatedh2"><?php _e('Mailing lists','MailPress'); ?></h2>
	<form class='search-form topmargin' action='' method='get'>
		<p class='search-box'>
			<input type='hidden' name='page' value='<?php echo MailPress_page_mailinglists; ?>' />
			<input type='text' id='post-search-input' name='s' value='<?php echo $url_parms['s']; ?>' class="search-input"  />
			<input type='submit' value="<?php _e( 'Search Mailing lists','MailPress' ); ?>" class='button' />
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
							<input type='hidden' name='page' value='<?php echo MailPress_page_mailinglists; ?>' />
						</div>
						<br class='clear' />
					</div>
					<div class="clear"></div>
					<table class='widefat'>
						<thead>
							<tr>
<?php mailinglists_columns(); ?>
							</tr>
						</thead>
						<tfoot>
							<tr>
<?php mailinglists_columns(false); ?>
							</tr>
						</tfoot>
						<tbody id='the-list' class='list:mailinglist'>
<?php
mailinglist_rows(0, 0, 0, $url_parms['apage'], $mailinglistsperpage );
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
				<div class="form-wrap">
					<p><?php printf(__('<strong>Note:</strong><br />Deleting a mailing list does not delete the MailPress users in that mailing list. Instead, MailPress users that were only assigned to the deleted mailing list are set to the mailing list <strong>%s</strong>.','MailPress'), apply_filters('the_mailinglist', get_mailinglistname(get_option('MailPress_default_mailinglist')))) ?></p>
				</div>
			</div>
		</div><!-- /col-right -->
		<div id="col-left">
			<div class="col-wrap">
<?php do_action('MailPress_add_mailinglist_form_pre', $mailinglist); ?>
				<div class="form-wrap">
					<h3><?php _e('Add Mailing list','MailPress'); ?></h3>
					<div id="ajax-response"></div>
					<form name='add'  id='add'  method='post' action='' class='add:the-list: validate'>
						<input type='hidden' name='action' value='add' />
						<?php wp_nonce_field('update-mailinglist'); ?>
						<div class="form-field form-required">
							<label for='mailinglist_name'><?php _e('Mailing list Name','MailPress'); ?></label>
							<input name='mailinglist_name' id='mailinglist_name' type='text' value="<?php echo attribute_escape($mailinglist->name); ?>" size='40' aria-required='true' />
							<p><?php _e('The name is used to identify the mailing list almost everywhere.','MailPress'); ?></p>
						</div>
						<div class="form-field">
							<label for='mailinglist_nicename'><?php _e('Mailing list Slug','MailPress') ?></label>
							<input name='mailinglist_nicename' id='mailinglist_nicename' type='text' value="<?php echo attribute_escape(apply_filters('editable_slug', $mailinglist->slug)); ?>" size='40' />
							<p><?php _e('The &#8220;slug&#8221; is a unique id for the mailing list (not so friendly !). In case of conflict, new mailing list is not created or when updating, slug might be regenerated. It is usually all lowercase and contains only letters, numbers, and hyphens. It is never displayed.','MailPress'); ?></p>
						</div>
						<div class="form-field">
							<label for='mailinglist_parent'><?php _e('Mailing list Parent','MailPress') ?></label>
							<?php mp_dropdown_mailinglists(array('hide_empty' => 0, 'name' => 'mailinglist_parent', 'orderby' => 'name', 'selected' => $mailinglist->parent, 'hierarchical' => true, 'show_option_none' => __('None','MailPress'))); ?>
							<p><?php _e("Mailing list can have a hierarchy. You might have a Rock'n roll mailing list, and under that have children mailing lists for Elvis and The Beatles. Totally optional !",'MailPress'); ?></p>
						</div>
						<div class="form-field">
							<label for='mailinglist_description'><?php _e('Description','MailPress') ?></label>
							<textarea name='mailinglist_description' id='mailinglist_description' rows='5' cols='50' style='width: 97%;'><?php echo wp_specialchars($mailinglist->description); ?></textarea>
							<p><?php _e('The description is not prominent by default.','MailPress'); ?></p>
						</div>
						<p class='submit'><input type='submit' class='button' name='submit' value="<?php _e('Add Mailing list','MailPress'); ?>" /></p>
					</form>
				</div>
			</div>
		</div><!-- /col-left -->
	</div><!-- /col-container -->
</div><!-- /wrap -->
<?php
}
?>