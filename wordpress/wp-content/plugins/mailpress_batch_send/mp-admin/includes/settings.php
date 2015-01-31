<?php

$xevery = array (		30 	=> sprintf(__('%1$s seconds','MailPress'),'30'), 
				45 	=> sprintf(__('%1$s seconds','MailPress'),'45'), 
				60 	=> sprintf(__('%1$s minute','MailPress') ,''), 
				120 	=> sprintf(__('%1$s minutes','MailPress'),'2'), 
				300 	=> sprintf(__('%1$s minutes','MailPress'),'5'),
				900 	=> sprintf(__('%1$s minutes','MailPress'),'15'), 
				1800 	=> sprintf(__('%1$s minutes','MailPress'),'30'), 
				3600 	=> sprintf(__('%1$s hour','MailPress')   ,'')     ); 

		$batch_send = get_option('MailPress_batch_send');
?>
		<div id='fragment-MailPress_batch_send'>
						<div>
							<form name='batch_send_form' action='' method='post' class='mp_settings'>
								<input type='hidden' name='formname' value='batch_send_form' />
								<table class='form-table'>
									<tr valign='top'>
										<th scope='row'>
											<?php _e('Max mails sent per batch','MailPress'); ?>
										</th>
										<td class='field'>
											<select name='batch_send[per_pass]'>
<?php MP_Admin::select_number(1,10,$batch_send['per_pass'],1);?>
<?php MP_Admin::select_number(11,100,$batch_send['per_pass'],10);?>
<?php MP_Admin::select_number(101,1000,$batch_send['per_pass'],100);?>
<?php MP_Admin::select_number(1001,10000,$batch_send['per_pass'],1000);?>
											</select>
										</td>
									</tr>
									<tr valign='top'>
										<th scope='row'>
											<?php _e('Max retries','MailPress'); ?>
										</th>
										<td class='field'>
											<select name='batch_send[max_retry]'  style='width:4em;'>
<?php MP_Admin::select_number(0,5,$batch_send['max_retry']);?>
											</select>
										</td>
									</tr>
									<tr valign='top'>
										<th scope='row'><?php _e('Submit batch with','MailPress'); ?></th>
										<td>
											<table class='general'>
												<tr>
													<td class='pr10'>
														<input value='wpcron' name='batch_send[batch_mode]' class='submit_batch tog' type='radio' <?php checked('wpcron',$batch_send['batch_mode']); ?> />
														&nbsp;&nbsp;
														<?php _e('WP_Cron','MailPress'); ?>
													</td>
													<td class='wpcron pr10 toggl2<?php if ('wpcron' != $batch_send['batch_mode']) echo ' hide'; ?>' style='padding-left:10px;vertical-align:bottom;'>
														<?php _e('Every','MailPress'); ?>
														&nbsp;&nbsp;
														<select name='batch_send[every]' id='every' >
<?php MP_Admin::select_option($xevery,$batch_send['every']);?>
														</select>
													</td>
												</tr>
												<tr>
													<td class='pr10'>
														<input value='other' name='batch_send[batch_mode]' class='submit_batch tog' type='radio' <?php checked('other',$batch_send['batch_mode']); ?> />
														&nbsp;&nbsp;
														<?php _e('Other','MailPress'); ?>
													</td>
													<td class='other pr10 toggl2<?php if ('other' != $batch_send['batch_mode']) echo ' hide'; ?>'>
														<?php _e('Using','MailPress'); ?> "<?php echo  MP_MailPress_batch_send_PATH . 'mp-includes/cron.php'; ?>"
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr valign='top'>
										<th scope='row'><?php _e('Logging','MailPress'); ?></th>
										<td>
<?php MP_Log::form('batch_send', $batch_send, __('Batch log','MailPress_batch_send'), __('(for <b>ALL</b> mails send through MailPress)','MailPress'), __('Number of Batch log files : ','MailPress_batch_send')); ?>
										</td>
									</tr>
								</table>
<?php mp_settings_save(); ?>
							</form>
						</div>
		</div>