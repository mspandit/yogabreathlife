<?php
$tracking = get_option('MailPress_tracking');
include(MP_MailPress_tracking_TMP . '/mp-includes/tracking-reports.php');

?>
		<div id='fragment-MailPress_tracking'>
						<div>
							<form name='tracking_form' action='' method='post' class='mp_settings'>
								<input type='hidden' name='formname' value='tracking_form' />
								<table class='form-table'>
									<tr valign='top'>
										<th scope='row'>
											<?php _e('User','MailPress'); ?>
										</th>
										<td class='field'>
<?php
global $mp_general;
$gmapkey = $mp_general['gmapkey'];
foreach ($tracking_reports['user'] as $k => $v)
{
?>
<input type='checkbox' name='tracking[<?php echo $k; ?>]' value='<?php echo $k; ?>' <?php checked($k,$tracking[$k]); ?> />&nbsp;<?php echo $v['title']; ?><br />
<?php
	if ((('m006' == $k) || ('u006' == $k)) && !class_exists('MailPress_IP_user_info'))
	{
?>
										<a target='_blank' style='color:#333;' href='http://www.google.com/apis/maps/signup.html'><?php _e('Google Map API Key','MailPress'); ?></a>
										&nbsp;<input type="text" size="90"  name="general[gmapkey]" value="<?php echo $mp_general['gmapkey']; ?>" />
<?php
	}
}
?>
										</td>
									</tr>
									<tr valign='top'>
										<th scope='row'>
											<?php _e('Mail','MailPress'); ?>
										</th>
										<td class='field'>

<?php
foreach ($tracking_reports['mail'] as $k => $v)
{
?>
<input type='checkbox' name='tracking[<?php echo $k; ?>]' value='<?php echo $k; ?>' <?php checked($k,$tracking[$k]); ?> />&nbsp;<?php echo $v['title']; ?><br />
<?php
}
?>
										</td>
									</tr>

								</table>
<?php mp_settings_save(); ?>
							</form>
						</div>
		</div>