<?php
function mp_mail_list_autoresponders( $meta ) 
{
	if ( ! $meta ) 
	{
        $meta = array();
		echo '
<table id="list-table" style="display: none;">
	<thead>
	<tr>
		<th class="left">' . __( 'Autoresponder' ) . '</th>
		<th>' . __( 'Schedule','MailPress' ) . '</th>
	</tr>
	</thead>
	<tbody id="the-arlist" class="list:mailautoresponder">
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
		<th class="left"><?php _e( 'Autoresponder','MailPress' ) ?></th>
		<th><?php _e( 'Schedule','MailPress' ) ?></th>
	</tr>
	</thead>
	<tbody id='the-arlist' class='list:mailautoresponder'>
<?php
	foreach ( $meta as $entry )
		echo mp_mail_list_autoresponder_row( $entry, $count );
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
function mp_mail_list_autoresponder_row( $entry, &$count ) 
{
	static $update_nonce = false;
	if ( !$update_nonce ) $update_nonce = wp_create_nonce( 'add-mailautoresponder' );

	$r = '';
	++ $count;

	if ( $count % 2 )	$style = 'alternate';
	else			$style = '';

	$entry['mmeta_id'] 	= (int) $entry['mmeta_id'];

	$delete_nonce 		= wp_create_nonce( 'delete-mailautoresponder_' . $entry['mmeta_id'] );

	$autoresponders = MailPress_autoresponder::get_all();
	foreach( $autoresponders as $autoresponder )
	{
		$_autoresponders[$autoresponder->term_id] = apply_filters( 'term_name', $autoresponder->name );
	}
	$r .= "
		<tr id='mailautoresponder-{$entry['mmeta_id']}' class='$style'>
			<td class='left'>
				<select id='mailautoresponder[" . $entry['mmeta_id'] . "][key]' name='mailautoresponder[" . $entry['mmeta_id'] . "][key]' tabindex='7'>
" . MP_Admin::select_option($_autoresponders,$entry['term_id'],false) . "
				</select>
				<div class='submit'>
					<input name='deletemailautoresponder[{$entry['mmeta_id']}]' type='submit' class='delete:the-arlist:mailautoresponder-{$entry['mmeta_id']}::_ajax_nonce=$delete_nonce deletemailautoresponder' tabindex='6' value='".attribute_escape(__( 'Delete' ))."' />
					<input name='updatemailautoresponder' type='submit' tabindex='6' value='".attribute_escape(__( 'Update' ))."' class='add:the-arlist:mailautoresponder-{$entry['mmeta_id']}::_ajax_nonce=$update_nonce updatemailautoresponder' />
				" . wp_nonce_field( 'change-mailautoresponder', '_ajax_nonce', false, false ) . "
				</div>
			</td>
			<td style='vertical-align:top;'>
				<table style='border:none;margin:8px 0 8px 8px;width:95%;'>
					<tbody>
						<tr>
							<td class='arschedule'>
								" . __('Month','MailPress') . "<br />
								<select style='width:auto;margin:0;padding:0;' name='mailautoresponder[" . $entry['mmeta_id'] . "][value][MM]' >
" . MP_Admin::select_number(0,12,substr($entry['schedule'],0,2),1,false) . "
								</select>
							</td>
							<td class='arschedule'>
								" . __('Day','MailPress') . "<br />
								<select style='width:auto;margin:0;padding:0;' name='mailautoresponder[" . $entry['mmeta_id'] . "][value][DD]' >
" . MP_Admin::select_number(0,31,substr($entry['schedule'],2,2),1,false) . "
								</select>
							</td>
							<td class='arschedule'>
								" . __('Hour','MailPress') . "<br />
								<select style='width:auto;margin:0;padding:0;' name='mailautoresponder[" . $entry['mmeta_id'] . "][value][HH]' >
" . MP_Admin::select_number(0,23,substr($entry['schedule'],4,2),1,false) . "
								</select>
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
		";
	return $r;
}

/**
 * {@internal Missing Short Description}}
 *
 * @since unknown
 */
function mp_mail_autoresponder_form() {
	global $wpdb;

	$autoresponders = MailPress_autoresponder::get_all();
	foreach( $autoresponders as $autoresponder )
	{
		$_autoresponders[$autoresponder->term_id] = apply_filters( 'term_name', $autoresponder->name );
	}
	if (empty($_autoresponders))
	{
?>
<p><strong><?php _e( 'No autoresponder', 'MailPress') ?></strong></p>
<?php
		return;
	}
?>
<p><strong><?php _e( 'Link to :', 'MailPress') ?></strong></p>
<table id="newar">
	<thead>
		<tr>
			<th class="left"><label for="autoresponderselect"><?php _e( 'Autoresponder','MailPress' ) ?></label></th>
			<th><label for="metavalue"><?php _e( 'Schedule','MailPress' ) ?></label></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td id="newarleft" class="left">
				<select id="autoresponderselect" name="autoresponderselect" tabindex="7">
<?php MP_Admin::select_option($_autoresponders,false); ?>
				</select>
			</td>
			<td style='vertical-align:top;'>
				<table style='border:none;margin:8px 0 8px 8px;width:95%;'>
					<tbody>
						<tr>
							<td class='arschedule'>
								<?php _e('Month','MailPress');?><br />
								<select style='width:auto;margin:0;padding:0;' name="autoresponder[schedule][MM]" >
<?php MP_Admin::select_number(0,12,$month); ?>
								</select>
							</td>
							<td class='arschedule'>
								<?php _e('Day','MailPress');?><br />
								<select style='width:auto;margin:0;padding:0;' name="autoresponder[schedule][DD]" >
<?php MP_Admin::select_number(0,31,$days); ?>
								</select>
							</td>
							<td class='arschedule'>
								<?php _e('Hour','MailPress');?><br />
								<select style='width:auto;margin:0;padding:0;' name="autoresponder[schedule][HH]" >
<?php MP_Admin::select_number(0,23,$hours); ?>
								</select>
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan='2' class='submit'>
					<input type="submit" id="addmetasub" name="addmailautoresponder" class="add:the-arlist:newar" tabindex="9" value="<?php _e( 'Add','MailPress' ) ?>" />
					<?php wp_nonce_field( 'add-mailautoresponder', '_ajax_nonce', false ); ?>
			</td>
		</tr>
	</tbody>
</table>
<?php

}
?>
