<?php

function mp_mail_list_meta( $meta ) 
{
	if ( ! $meta ) 
	{
		echo '
<table id="list-table" style="display: none;">
	<thead>
	<tr>
		<th class="left">' . __( 'Name' ) . '</th>
		<th>' . __( 'Value' ) . '</th>
	</tr>
	</thead>
	<tbody id="the-list" class="list:mailmeta">
	<tr><td></td></tr>
	</tbody>
</table>'; //TBODY needed for list-manipulation JS
		return;
	}
	$count = 0;
?>
<table id="list-table">
	<thead>
	<tr>
		<th class="left"><?php _e( 'Name' ) ?></th>
		<th><?php _e( 'Value' ) ?></th>
	</tr>
	</thead>
	<tbody id='the-list' class='list:mailmeta'>
<?php
	foreach ( $meta as $entry )
		echo mp_mail_list_meta_row( $entry, $count );
?>
	</tbody>
</table>
<?php
}

/**
 * {@internal Missing Short Description}}
 *
 * @since unknown
 *
 * @param unknown_type $entry
 * @param unknown_type $count
 * @return unknown
 */
function mp_mail_list_meta_row( $entry, &$count ) 
{
	static $update_nonce = false;
	if ( !$update_nonce ) $update_nonce = wp_create_nonce( 'add-mailmeta' );

	$r = '';
	++ $count;

	if ( $count % 2 )	$style = 'alternate';
	else			$style = '';
	
	if ('_' == $entry['meta_key'] { 0 } ) $style .= ' hidden';

	if ( is_serialized( $entry['meta_value'] ) ) 
	{
		if ( is_serialized_string( $entry['meta_value'] ) ) 
		{
			$entry['meta_value'] = maybe_unserialize( $entry['meta_value'] );
		}
		else
		{
			--$count;
			return;
		}
	}

	$entry['meta_key'] 	= apply_filters('MailPress_input_text',$entry['meta_key']);
	$entry['meta_value'] 	= apply_filters('MailPress_input_text',$entry['meta_value']); // using a <textarea />
	$entry['mmeta_id'] 	= (int) $entry['mmeta_id'];

	$delete_nonce 		= wp_create_nonce( 'delete-mailmeta_' . $entry['mmeta_id'] );

	$r .= "\n\t<tr id='mailmeta-{$entry['mmeta_id']}' class='$style'>";
	$r .= "\n\t\t<td class='left'><label class='hidden' for='mailmeta[{$entry['mmeta_id']}][key]'>" . __( 'Key' ) . "</label><input name='mailmeta[{$entry['mmeta_id']}][key]' id='mailmeta[{$entry['mmeta_id']}][key]' tabindex='6' type='text' size='20' value='{$entry['meta_key']}' />";

	$r .= "\n\t\t<div class='submit'><input name='deletemailmeta[{$entry['mmeta_id']}]' type='submit' ";
	$r .= "class='delete:the-list:mailmeta-{$entry['mmeta_id']}::_ajax_nonce=$delete_nonce deletemailmeta' tabindex='6' value='".attribute_escape(__( 'Delete' ))."' />";
	$r .= "\n\t\t<input name='updatemailmeta' type='submit' tabindex='6' value='".attribute_escape(__( 'Update' ))."' class='add:the-list:mailmeta-{$entry['mmeta_id']}::_ajax_nonce=$update_nonce updatemailmeta' /></div>";
	$r .= wp_nonce_field( 'change-mailmeta', '_ajax_nonce', false, false );
	$r .= "</td>";

	$r .= "\n\t\t<td><label class='hidden' for='mailmeta[{$entry['mmeta_id']}][value]'>" . __( 'Value' ) . "</label><textarea name='mailmeta[{$entry['mmeta_id']}][value]' id='mailmeta[{$entry['mmeta_id']}][value]' tabindex='6' rows='2' cols='30'>{$entry['meta_value']}</textarea></td>\n\t</tr>";
	return $r;
}

/**
 * {@internal Missing Short Description}}
 *
 * @since unknown
 */
function mp_mail_meta_form() {
	global $wpdb;
	$keys = $wpdb->get_col( "
		SELECT meta_key FROM $wpdb->mp_mailmeta GROUP BY meta_key ORDER BY meta_key ASC LIMIT 30" );
	foreach ($keys as $k => $v)
	{
		if ($keys[$k][0] == '_') unset($keys[$k]);
		if ('batch_send' == $v)  unset($keys[$k]);
	}
?>
<p><strong><?php _e( 'Add new custom field:' ) ?></strong></p>
<table id="newmeta">
<thead>
<tr>
<th class="left"><label for="metakeyselect"><?php _e( 'Name' ) ?></label></th>
<th><label for="metavalue"><?php _e( 'Value' ) ?></label></th>
</tr>
</thead>

<tbody>
<tr>
<td id="newmetaleft" class="left">
<?php if ( $keys ) { ?>
<select id="metakeyselect" name="metakeyselect" tabindex="7">
<option value="#NONE#"><?php _e( '- Select -' ); ?></option>
<?php

	foreach ( $keys as $key ) {
		$key = apply_filters('MailPress_input_text',$key);
		echo "\n<option value=\"$key\">$key</option>";
	}
?>
</select>
<input class="hide-if-js" type="text" id="metakeyinput" name="metakeyinput" tabindex="7" value="" />
<a href="#postcustomstuff" class="hide-if-no-js" onclick="jQuery('#metakeyinput, #metakeyselect, #enternew, #cancelnew').toggle();return false;">
<span id="enternew"><?php _e('Enter new'); ?></span>
<span id="cancelnew" class="hidden"><?php _e('Cancel'); ?></span></a>
<?php } else { ?>
<input type="text" id="metakeyinput" name="metakeyinput" tabindex="7" value="" />
<?php } ?>
</td>
<td><textarea id="metavalue" name="metavalue" rows="2" cols="25" tabindex="8"></textarea></td>
</tr>

<tr><td colspan="2" class="submit">
<input type="submit" id="addmetasub" name="addmailmeta" class="add:the-list:newmeta" tabindex="9" value="<?php _e( 'Add Custom Field' ) ?>" />
<?php wp_nonce_field( 'add-mailmeta', '_ajax_nonce', false ); ?>
</td></tr>
</tbody>
</table>
<?php

}
?>
