<?php
		global $mp_general;
		global $wp_roles;

		$capabilities = MP_Admin::capabilities();
		$capability_groups = MP_Admin::capability_groups();
		$grouping_cap = array();
		foreach ($capabilities as $capability => $v)	$grouping_cap[$v['group']] [$capability] = null;
?>
		<div id='fragment-MailPress_roles_and_capabilities'>
				<form name='roles_and_capabilities_form' action='' method='post' class='mp_settings'>
					<input type='hidden' name='formname' value='roles_and_capabilities_form' />
					<table class='form-table'>
<?php
foreach($wp_roles->role_names as $role => $name)
{
	if ('administrator' == $role) continue;
	$name = translate_with_context($name);
	$rcs = get_option('MailPress_r&c_' . $role);
?>
						<tr class='rc_role'>
							<th scope='row' style='width:100px;'><?php echo $name; ?></th>
							<td style='padding:0;'>
								<table>
<?php
	$col = 6;
	foreach ($capability_groups as $group => $groupname)
	{
		if (!isset($grouping_cap[$group])) continue;
		echo "<tr><td  class='capacity' colspan='" . ($col+1) . "'><i>$groupname</i></td></tr>";
		$i = 0;
		foreach ($grouping_cap[$group] as $capability => $v)
		{
			$capname = $capabilities[$capability]['name'];
						
			if (0 == $i)	echo "<tr><td class='capacity' style='width:10px'></td>\n";
			
			
?>
										<td class='capacity'>
											<input name='cap[<?php echo $role; ?>][<?php echo $capability; ?>]' type='checkbox'<?php echo (isset($rcs[$capability])) ? " checked='checked'" : ''; ?> />
											<span id='<?php echo $role . '_' . $capability; ?>' class='<?php echo (isset($rcs[$capability])) ? 'crok' : 'crko'; ?>'><?php echo $capname; ?></span>
										</td>
<?php
			$i++;
			if (intval($i/$col) == ( $i/$col)) {$i = 0; echo "</tr>\n"; }
		}
		$tr = false;
		while (intval($i/$col) != ($i/$col)) { echo "<td style='border-bottom:none;'></td>\n"; $tr = true; $i++;}
		if ($tr) echo "</tr>\n";
	}
?>								
								</table>
							</td>
						</tr>
<?php
}
?>
					</table>
<?php mp_settings_save(); ?>
				</form>
		</div>