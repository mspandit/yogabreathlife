<?php
		$default_mailinglist	= get_option('MailPress_default_mailinglist');
		$mailinglist		= get_option('MailPress_mailinglist');
		$class= (isset($mailinglist['show_mailinglists'])) ? '' : ' hidden';
?>
		<div id='fragment-MailPress_mailinglist'>
			<div>
				<form name='mailinglist_form' action='' method='post'>
					<input type='hidden' name='formname' value='mailinglist_form' />
					<table class='form-table'>
						<tr valign='top'>
							<th scope='row'>
								<?php _e('Default Mailing list','MailPress'); ?>
							</th>
							<td class='field'>
<?php
		$dropdown_options = array('hide_empty' => 0, 'hierarchical' => true, 'show_count' => 0, 'orderby' => 'name', 'selected' => $default_mailinglist, 'name' => 'default_mailinglist' );
		mp_dropdown_mailinglists($dropdown_options);
?>
							</td>
						</tr>
						<tr valign='top'>
							<th scope='row'>
								<?php _e('Allow subscriptions to','MailPress'); ?>
							</th>
							<td class='field'>
								<table class='general'>
									<tr>
										<td class='field' style='width:150px;vertical-align:top;'>
											<?php _e("Mailing lists",'MailPress'); ?>
										</td>
										<td class='field' >
											<input id='show_mailinglists' type='checkbox' name='mailinglist[show_mailinglists]'<?php checked($mailinglist['show_mailinglists'],'on'); ?> />
											<table id='mailinglists' class='general<?php echo $class; ?>'>
												<tr>
													<td style='width:50px;vertical-align:top;'>&nbsp;</td>
													<td>
<?php
		$default_mailing_list = get_option('MailPress_default_mailinglist');
		$mls = apply_filters('MailPress_mailing_lists',array());
		foreach ($mls as $k => $v)
		{
			$x = str_replace('MailPress_mailing_list~','',$k,$count);
			if (0 == $count) 	continue;
			if (empty($x)) 	continue;
			if ($x == $default_mailing_list) 	continue;
?>
														<input type='checkbox' name='mailinglist[display_mailinglists][<?php echo $x; ?>]'<?php checked($mailinglist['display_mailinglists'][$x],'on'); ?> />&nbsp;&nbsp;<?php echo $v; ?><br />
<?php
		}
?>
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
<?php mp_settings_save(); ?>
				</form>
			</div>
		</div>