<?php
if ( !empty($mailinglist_ID) ) 
{
	$heading 		= __('Edit Mailing list','MailPress');
	$submit_text 	= __('Edit Mailing list','MailPress');
	$form 		= "<form name='edit' id='edit' method='post' action='' class='validate'>";
	$action 		= 'edited';
	$nonce_action 	= 'update-mailinglist_' . $mailinglist_ID;
	do_action('MailPress_edit_mailinglist_form_pre', $mailinglist);
}
else
{
	$heading 		= __('Add Mailing list','MailPress');
	$submit_text 	= __('Add Mailing list','MailPress');
	$form 		= "<form name='add'  id='add'  method='post' action='' class='add:the-list: validate'>";
	$action 		= 'add';
	$nonce_action 	= 'update-mailinglist';
	do_action('MailPress_add_mailinglist_form_pre', $mailinglist);
}
?>
<div class='wrap'>
	<div id="icon-mailpress-users" class="icon32"><br /></div>
	<h2>
		<?php echo $heading; ?>
	</h2>
	<div id='ajax-response'></div>
	<?php echo $form; ?>
	<input type='hidden' name='action' value='<?php echo $action; ?>' />
	<input type='hidden' name='mailinglist_ID' value='<?php echo $mailinglist->term_id; ?>' />
<?php wp_nonce_field($nonce_action); ?>
	<table class='form-table'>
		<tr class='form-field form-required'>
			<th scope='row' valign='top'><label for='mailinglist_name'><?php _e('Mailing list Name','MailPress'); ?></label></th>
			<td><input name='mailinglist_name' id='mailinglist_name' type='text' value="<?php echo attribute_escape($mailinglist->name); ?>" size='40' aria-required='true' /><br />
            		<?php _e('The name is used to identify the mailing list almost everywhere.','MailPress'); ?>
			</td>
		</tr>
		<tr class='form-field'>
			<th scope='row' valign='top'><label for='mailinglist_nicename'><?php _e('Mailing list Slug','MailPress') ?></label></th>
			<td><input name='mailinglist_nicename' id='mailinglist_nicename' type='text' value="<?php echo attribute_escape(apply_filters('editable_slug', $mailinglist->slug)); ?>" size='40' /><br />
		            <?php _e('The &#8220;slug&#8221; is a unique id for the mailing list (not so friendly !). In case of conflict, new mailing list is not created or when updating, slug might be regenerated. It is usually all lowercase and contains only letters, numbers, and hyphens. It is never displayed.','MailPress'); ?>
			</td>
		</tr>
		<tr class='form-field'>
			<th scope='row' valign='top'><label for='mailinglist_parent'><?php _e('Mailing list Parent','MailPress') ?></label></th>
			<td>
	  			<?php mp_dropdown_mailinglists(array('hide_empty' => 0, 'name' => 'mailinglist_parent', 'orderby' => 'name', 'selected' => $mailinglist->parent, 'hierarchical' => true, 'show_option_none' => __('None','MailPress'))); ?><br />
				<?php _e("Mailing list can have a hierarchy. You might have a Rock'n roll mailing list, and under that have children mailing lists for Elvis and The Beatles. Totally optional !",'MailPress'); ?>
	  		</td>
		</tr>
		<tr class='form-field'>
			<th scope='row' valign='top'><label for='mailinglist_description'><?php _e('Description','MailPress') ?></label></th>
			<td><textarea name='mailinglist_description' id='mailinglist_description' rows='5' cols='50' style='width: 97%;'><?php echo wp_specialchars($mailinglist->description); ?></textarea><br />
				<?php _e('The description is not prominent by default.','MailPress'); ?>
			</td>
		</tr>
	</table>
	<p class='submit'><input type='submit' class='button' name='submit' value="<?php echo $submit_text ?>" /></p>
	<?php do_action('MailPress_edit_mailinglist_form', $mailinglist); ?>
	</form>
</div>