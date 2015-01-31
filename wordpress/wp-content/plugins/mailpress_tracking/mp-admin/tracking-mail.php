<?php
if ( !current_user_can('MailPress_tracking_mails') ) wp_die(__('You do not have sufficient permissions to access this page.'));

function mails_columns($id=true)
{
	$mails_columns = MP_Mail::manage_list_columns();
	$hidden = array();
	foreach ( $mails_columns as $mail_column_key => $column_display_name ) {
		if ( 'cb' === $mail_column_key ) $column_display_name = '';
		
		$class = " class=\"manage-column column-$mail_column_key\"";

		$style = '';
		if ( in_array($mail_column_key, $hidden) )
			$style = ' style="display:none;"';
?>
					<th scope="col" <?php if ($id) echo "id=\"$mail_column_key\""; echo $class; echo $style?>><?php echo $column_display_name; ?></th>
<?php	} 
}

		global $mp_screen;
		$mail = MP_Mail::get($_GET['id']);
		$title = __('Tracking','MailPress');
?>
<div class="wrap">
	<div class="icon32" id="icon-mailpress-mails"><br /></div>
	<h2><?php echo wp_specialchars( $title ); ?></h2>



		<table class='widefat'>
			<thead>
				<tr>
<?php mails_columns(); ?>
				  </tr>
			</thead>
			<tbody id='the-mail-list'>
<?php
		MP_Mail::get_row( $mail->id, array(), false, true);
?>
			</tbody>
		</table>

	<div id="dashboard-widgets-wrap">
		<div id='dashboard-widgets' class='metabox-holder'>
			<div id='side-info-column' class='inner-sidebar'>
				<?php do_meta_boxes( $mp_screen, 'side', $mail ); ?>
			</div>
			<div id='post-body' class='has-sidebar'>
				<div id='dashboard-widgets-main-content' class='has-sidebar-content'>
					<?php do_meta_boxes( $mp_screen, 'normal', $mail ); ?>
				</div>
			</div>
			<form style='display: none' method='get' action=''>
				<p>
					<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
					<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );  ?>
				</p>
			</form>
		</div>
		<div class="clear"></div>
	</div><!-- dashboard-widgets-wrap -->
</div>