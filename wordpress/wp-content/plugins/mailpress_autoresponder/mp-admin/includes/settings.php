<?php
		$autoresponder = get_option('MailPress_autoresponder');
?>
		<div id='fragment-MailPress_autoresponder'>
						<div>
							<form name='autoresponder_form' action='' method='post'>
								<input type='hidden' name='formname' value='autoresponder_form' />
								<table class='form-table'>
									<tr>
										<th scope='row'><?php _e('Logging','MailPress'); ?></th>
										<td>
<?php MP_Log::form('autoresponder', $autoresponder, __('Autoresponder log','MailPress'), __('(for <b>ALL</b> Autoresponders through MailPress)','MailPress'), __('Number of Autoresponder log files : ','MailPress')); ?>
										</td>
									</tr>
								</table>
<?php mp_settings_save(); ?>
							</form>
						</div>
		</div>