<?php
		$import = get_option('MailPress_import');
?>
		<div id='fragment-MailPress_import'>
						<div>
							<form name='import_form' action='' method='post'>
								<input type='hidden' name='formname' value='import_form' />
								<table class='form-table'>
									<tr>
										<th scope='row'><?php _e('Logging','MailPress'); ?></th>
										<td>
<?php MP_Log::form('import', $import, __('Import log','MailPress'), __('(for <b>ALL</b> imports through MailPress)','MailPress'), __('Number of Import log files : ','MailPress')); ?>
										</td>
									</tr>
								</table>
<?php mp_settings_save(); ?>
							</form>
						</div>
		</div>