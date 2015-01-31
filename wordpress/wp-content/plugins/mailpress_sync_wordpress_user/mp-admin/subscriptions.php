<?php
if (!current_user_can('MailPress_manage_subscriptions')) wp_die(__('You do not have sufficient permissions to access this page.'));

//
//
//

$email	= MailPress::get_wp_user_email();
$mp_user_id    	= MP_User::get_id_by_email($email);

$active 	= ('active' == MP_User::get_status($mp_user_id)) ? true : false;

if ($mp_user_id)
{
	if (isset($_POST['formname']) && ('sync_wordpress_user_subscriptions' == $_POST['formname']))
	{
		MailPress::update_mp_user_comments($mp_user_id);
		if ($active)
		{
			MP_Newsletter::update_mp_user_newsletters($mp_user_id);
			if (class_exists('MailPress_mailing_lists')) MailPress_mailing_lists::update_mp_user_mailinglists($mp_user_id);
		}
		$fade = __('Subscription saved','MailPress');
	}

	$check_comments = MailPress::checklist_mp_user_comments($mp_user_id);
	if ($active)
	{
		$check_newsletters = MP_Newsletter::checklist_mp_user_newsletters($mp_user_id);
		if (class_exists('MailPress_mailing_lists')) $checklist_mailinglists  = MailPress_mailing_lists::checklist_mp_user_mailinglists($mp_user_id);
	}
		
//
// MANAGING TITLE
//
	$title    =  sprintf(__('Manage Subscription (%1$s)','MailPress'), $email);

	if (isset($fade)) MP_Admin::message($fade); 
?>
<div class='wrap'>
	<form id='posts-filter' action='' method='post'>
		<div id="icon-mailpress-users" class="icon32"><br /></div>
		<h2>
			<?php echo $title; ?>
		</h2>
		<input type='hidden' name='page' value='<?php echo MP_MailPress_sync_wordpress_user_FOLDER; ?>/mp-admin/subscriptions.php' />
		<input type='hidden' name='formname' value='sync_wordpress_user_subscriptions' />

		<table class="form-table">
<?php if ($check_comments) : ?>
			<tr>
				<th scope="row">
					<?php _e('Comments'); ?>
				</th>
				<td>
					<?php echo $check_comments; $ok = true; ?>
				</td>
			</tr>
<?php endif; ?> 	

<?php if ($check_newsletters) : ?>
			<tr>
				<th scope="row">
					<?php _e('Newsletters','MailPress'); ?>
				</th>
				<td>
					<?php echo $check_newsletters ; $ok = true; ?>
				</td>
			</tr>
<?php endif; ?> 	

<?php if ($checklist_mailinglists) : ?>
			<tr>
				<th scope="row">
					<?php _e('Mailing lists','MailPress'); ?>
				</th>
				<td>
					<?php echo $checklist_mailinglists ; $ok = true; ?>
				</td>
			</tr>
<?php endif; ?>
<?php if ($checklist_custom_fields) : ?>
			<tr>
				<th scope="row">
					<?php _e('Custom Fields'); ?>
				</th>
				<td>
					<?php echo $checklist_custom_fields; $ok = true; ?>
				</td>
			</tr>
<?php endif; ?>
		</table>

<?php if (isset($ok)) : ?> 
		<p class='submit'>
			<input class='button-primary' type='submit' name='Submit' value='<?php  _e('Save','MailPress'); ?>' />
		</p>
<?php else : ?> 
		<p>
<?php 
		if ($active) 	_e('Nothing to subscribe for ...','MailPress');
		else			_e('Your email has been deactivated, ask the administrator of this site ...','MailPress');
?>
		</p>
<?php endif; ?> 

	</form>
</div>
<?php
}
?>