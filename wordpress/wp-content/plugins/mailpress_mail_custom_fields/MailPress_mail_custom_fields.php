<?php
/*
Plugin Name: MailPress_mail_custom_fields
Plugin URI: http://www.mailpress.org
Description: This is just an addon for MailPress to edit MailPress mail custom fields.
Author: Andre Renaut
Version: 3.0.1
Author URI: http://www.mailpress.org
*/

class MailPress_mail_custom_fields
{
	function MailPress_mail_custom_fields()
	{
// plugins loaded
		add_action('MailPress_init',				array(&$this,'init'));

// for role & capabilities
		add_filter('MailPress_capabilities',  		array(&$this,'capabilities'),1,1);
	}

//plugins loaded
	function init()
	{
		if ( !current_user_can('MailPress_mail_custom_fields') ) return;

		add_action('mp_action_add-mailmeta',		array(&$this,'mp_action_add_mailmeta'));
		add_action('mp_action_delete-mailmeta',		array(&$this,'mp_action_delete_mailmeta'));

		add_action('MailPress_register_styles', 		array(&$this,'register_styles'));
		add_filter('MailPress_enqueue_styles',		array(&$this,'enqueue_styles'),8,1);
		add_action('MailPress_register_scripts', 		array(&$this,'register_scripts'));
		add_filter('MailPress_enqueue_scripts',		array(&$this,'enqueue_scripts'),8,1);

		add_action('MailPress_mailnew_boxes',		array(&$this,'mail_boxes'),8,2);
		add_action('MailPress_update_mail_meta_box',	array(&$this,update_mail_meta_box));
	}

// for role & capabilities
	function capabilities($x) 
	{
		global $mp_general;

		$x['MailPress_mail_custom_fields'] = array(	'name'  => __('Custom fields','MailPress'),
										'group' => 'mails'
							);
		return $x;
	}

// for write page
	function register_styles() 
	{
		wp_register_style ( 'MailPress_mail_custom_fields', 	get_option('siteurl') . '/' . MP_MailPress_mail_custom_fields_PATH . 'mp-admin/css/mail_new.css' );
	}

 	function enqueue_styles($x) 
	{
		$x [MailPress_page_write][] = 'MailPress_mail_custom_fields';
		return $x;
	}

	function register_scripts() 
	{
		wp_register_script( 'mp-mail-customfields', '/' . MP_MailPress_mail_custom_fields_PATH . 'mp-admin/js/mail_new.js', array('mp-lists'), false, 1);
	}

	function enqueue_scripts($x)
	{
		$x[MailPress_page_write][] = 'mp-mail-customfields';
		return $x;
	}

	function mail_boxes($mail_id_id,$mp_screen)
	{
		add_meta_box('mp_mailcustom', __('Custom Fields'), array(&$this,'mp_mail_custom_meta_box'), $mp_screen, 'normal', 'core');
	}

	function mp_mail_custom_meta_box($mail)
	{
?>
<div id="postcustomstuff">
	<div id="ajax-response"></div>
<?php
$metadata = MP_Mailmeta::has($mail->id);
mp_mail_list_meta($metadata);
mp_mail_meta_form();
?>
</div>
<p><?php _e('Custom fields can be used to add extra metadata to a mail that you can <a href="http://www.mailpress.org" target="_blank">use in your mail</a>.','MailPress'); ?></p>
<?php
	}

	function update_mail_meta_box()
	{
		$mail_id = $_POST['id'];
		switch (true)
		{
			case isset($_POST['addmeta']) :
				self::add_meta($mail_id);
			break;
			case isset($_POST['updatemeta']) :
				foreach ($_POST['meta'] as $mmeta_id => $meta)
				{
					$meta_key = $meta['key'];
					$meta_value = $meta['value'];
					MP_Mailmeta::update_by_id($mmeta_id , $meta_key, $meta_value);
				}
			break;
			case isset($_POST['deletemeta']) :
				foreach ($_POST['deletemeta'] as $mmeta_id => $x)
				{
					MP_Mailmeta::delete_by_id( $mmeta_id );
				}
			break;
		}
	}

	function mp_action_add_mailmeta()
	{
		if ( !current_user_can( 'MailPress_mail_custom_fields') )	die('-1');

		check_ajax_referer( 'add-mailmeta' );

		$c = 0;
		$uid = (int) $_POST['mail_id'];
		if ($uid === 0) die('0');

		if ( isset($_POST['metakeyselect']) || isset($_POST['metakeyinput']) ) 
		{
			if ( '#NONE#' == $_POST['metakeyselect'] && empty($_POST['metakeyinput']) ) 	die('1');
			if ( !$mid = self::add_meta( $uid ) ) 							die('0');

			$meta = MP_Mailmeta::get_by_id( $mid );
			$uid = (int) $meta->mail_id;
			$meta = get_object_vars( $meta );
			$x = new WP_Ajax_Response( array(
				'what' => 'mailmeta',
				'id' => $mid,
				'data' => mp_mail_list_meta_row( $meta, $c ),
				'position' => 1,
				'supplemental' => array('mail_id' => $uid)
			) );
		}
		else
		{
			$mid   = (int) array_pop(array_keys($_POST['mailmeta']));
			$key   = $_POST['mailmeta'][$mid]['key'];
			$value = $_POST['mailmeta'][$mid]['value'];

			if ( !$meta = MP_Mailmeta::get_by_id( $mid ) )			die('0');
			if ( !MP_Mailmeta::update_by_id($mid , $key, $value) )	die('1'); // We know meta exists; we also know it's unchanged (or DB error, in which case there are bigger problems).
			$meta = MP_Mailmeta::get_by_id( $mid );
			$x = new WP_Ajax_Response( array(
				'what' => 'mailmeta',
				'id' => $mid, 'old_id' => $mid,
				'data' => mp_mail_list_meta_row( array(
					'meta_key' => $meta->meta_key,
					'meta_value' => $meta->meta_value,
					'mmeta_id' => $mid
				), $c ),
				'position' => 0,
				'supplemental' => array('mail_id' => $meta->mail_id)
			) );
		}
		$x->send();
	}

	function add_meta($mail_id)
	{
		$mail_id = (int) $mail_id;

		$protected = array( '_MailPress_attached_file', '_MailPress_batch_send', '_MailPress_mail_link', '_MailPress_mail_opened', '_MailPress_mail_revisions', '_edit_lock','_edit_last' );

		$metakeyselect 	= isset($_POST['metakeyselect']) ? trim( $_POST['metakeyselect'] ) : '';
		$metakeyinput 	= isset($_POST['metakeyinput']) ?  trim( $_POST['metakeyinput'] )  : '';
		$metavalue 		= isset($_POST['metavalue']) ? maybe_serialize( stripslashes( trim( $_POST['metavalue'] ) ) ) : '';

		if ( ('0' === $metavalue || !empty ( $metavalue ) ) && ((('#NONE#' != $metakeyselect) && !empty ( $metakeyselect) ) || !empty ( $metakeyinput) ) ) 
		{
			// We have a key/value pair. If both the select and the
			// input for the key have data, the input takes precedence:

			if ('#NONE#' != $metakeyselect)		$metakey = $metakeyselect;
			if ( $metakeyinput)				$metakey = $metakeyinput; // default
			if ( in_array($metakey, $protected) )	return false;

			return MP_Mailmeta::add( $mail_id, $metakey, $metavalue );
		}
		return false;
	}

	function mp_action_delete_mailmeta()
	{
		if ( !current_user_can( 'MailPress_mail_custom_fields') )			die('-1');

		$id = isset($_POST['id'])? (int) $_POST['id'] : 0;
		check_ajax_referer( "delete-mailmeta_$id" );

		if ( !$meta = MP_Mailmeta::get_by_id( $id ) ) 				die('1');
		if ( MP_Mailmeta::delete_by_id( $meta->mmeta_id ) )	die('1');
		die('0');
	}
}

define ('MP_MailPress_mail_custom_fields_FOLDER', 	basename(dirname(__FILE__)));
define ('MP_MailPress_mail_custom_fields_PATH', 	'wp-content/plugins/' . MP_MailPress_mail_custom_fields_FOLDER . '/' );
define ('MP_MailPress_mail_custom_fields_TMP', 		dirname(__FILE__));

require MP_MailPress_mail_custom_fields_TMP . "/mp-admin/includes/template.php";

$MailPress_mail_custom_fields = new MailPress_mail_custom_fields();
?>