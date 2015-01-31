<?php
		$connection_phpmail = get_option('MailPress_connection_phpmail');
?>
		<div id='fragment-MailPress_connection_phpmail'>
						<div>
							<form name='connection_phpmail_form' action='' method='post'>
								<input type='hidden' name='formname' value='connection_phpmail_form' />
								<table class='form-table'>
									<tr valign='top'>
										<th scope='row'>
											<?php _e('Additional_parameters','MailPress_connection_phpmail'); ?>
										</th>
										<td class='field'>
											<input type='text' size='75' name='connection_phpmail[addparm]' value="<?php echo $connection_phpmail['addparm']; ?>" />
											<br />
											<?php  printf(__("(optional) Specify here the 5th parameter of php <a href='%s'>mail()</a> function",'MailPress_connection_phpmail'),__('http://fr.php.net/manual/en/function.mail.php','MailPress_connection_phpmail')); ?>
										</td>
									</tr>
								</table>
<?php mp_settings_save(); ?>
							</form>
						</div>
		</div>