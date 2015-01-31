<?php
		$sync_wordpress_user = get_option('MailPress_sync_wordpress_user');
?>
		<div id='fragment-MailPress_sync_wordpress_user'>
						<div>
							<form name='sync_wordpress_user_form' action='' method='post'>
								<input type='hidden' name='formname' value='sync_wordpress_user_form' />
								<table class='form-table'>
									<tr valign='top'>
										<th scope='row'>
											<?php _e('Allow subscriptions from','MailPress'); ?>
										</th>
										<td class='field'>
											<input type='checkbox' name='sync_wordpress_user[register_form]'<?php checked($sync_wordpress_user['register_form'],'on'); ?> />&nbsp;&nbsp;<?php _e('Registration Form','MailPress'); ?><br />
										</td>
									</tr>
								</table>
<?php mp_settings_save(); ?>
							</form>
						</div>
		</div>