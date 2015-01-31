<?php
		global $mp_general;
?>
		<div id='fragment-MailPress_newsletter_categories'>
			<div>
				<form name='categories_newsletters_form' action='' method='post' class='mp_settings'>
					<input type='hidden' name='formname' value='categories_newsletters_form' />
					<table class='form-table'>
						<tr valign='top'>
							<th scope='row'>
								<?php _e("Allow subscriptions to",'MailPress'); ?>
							</th>
							<td class='field' style='width:20%;vertical-align:bottom;'>
								<?php _e("Per post",'MailPress'); ?>
							</td>
							<td class='field' style='width:20%;vertical-align:bottom;'>
								<?php _e("Daily",'MailPress'); ?>
							</td>
							<td class='field' style='width:20%;vertical-align:bottom;'>
								<?php _e("Weekly",'MailPress'); ?>
							</td>
							<td class='field' style='width:20%;vertical-align:bottom;'>
								<?php _e("Monthly",'MailPress'); ?>
							</td>
						</tr>
<?php
	$col = 4;
	$item  = 1;
	$row = $col * $item;
	$i = $j = $td = $tr = 0;

	global $mp_registered_newsletters;
	foreach ($mp_registered_newsletters as $mp_registered_newsletter)
	{
		if (intval ($i/$row) == $i/$row ) 
		{
			$tr = true; 
			$td = 0;
			$blog = (isset($mp_registered_newsletter['params']['catname'])) ? '' : '_blog' ;
			$th =  (isset($mp_registered_newsletter['params']['catname'])) ? "<tr valign='top'><th scope='row'>" . $mp_registered_newsletter['params']['catname'] . "</th>\n" : "<tr valign='top' class='bkgndc bd1sc'><th scope='row'>" . __("** Blog **",'MailPress') . "</th>\n";
			echo "\t\t\t\t\t\t" . $th;
		}
		if (intval ($j/$item) == $j/$item ) { echo "\t\t\t\t\t\t\t" . "<td class='field' style=''>\n"; ++$td; }

		$default_style   = ($mp_general['newsletters'][$mp_registered_newsletter['id']]==true) ? '' : " style='display:none;'";
		$default_checked = (!$mp_registered_newsletter['in']) ? " checked='checked'" : '';
		$default_checked = (!empty($default_style)) ? '' : $default_checked;
?>
								<input class='newsletter' id='newsletter_<?php echo $mp_registered_newsletter['id'].$blog; ?>' name='general[newsletters][<?php echo $mp_registered_newsletter['id']; ?>]' type='checkbox' <?php echo( ($mp_general['newsletters'][$mp_registered_newsletter['id']]==true) ? "checked='checked'" : ''); ?> />
								&nbsp;
								<span id='span_default_newsletter_<?php echo $mp_registered_newsletter['id'].$blog; ?>'<?php echo $default_style; ?>>
									<br />
									<input  id='default_newsletter_<?php echo $mp_registered_newsletter['id'].$blog; ?>' name='general[default_newsletters][<?php echo $mp_registered_newsletter['id']; ?>]' type='checkbox'<?php echo "$default_checked"; ?> />
									&nbsp;<?php _e('default','MailPress'); ?>
								</span>
<?php
		$j++;
		if (intval ($j/$item) == $j/$item )  echo "\t\t\t\t\t\t\t" . "</td>\n";
		$i++;
		if (intval ($i/$row) == $i/$row ) { echo "\t\t\t\t\t\t" . "</tr>\n"; $tr = false; }
	}
	if (intval ($j/$item) != $j/$item )
	{
		echo "\t\t\t\t\t\t\t</td>\n"; 
		while ($td < $item) {echo "\t\t\t\t\t\t\t" . "<td></td>\n"; ++$td;}
	}
	if (intval ($i/$row) != $i/$row)  echo "\t\t\t\t\t\t" . "</tr>\n";
?>
					</table>
<?php mp_settings_save(); ?>
				</form>
			</div>
		</div>