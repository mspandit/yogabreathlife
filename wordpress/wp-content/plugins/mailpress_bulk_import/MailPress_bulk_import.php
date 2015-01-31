<?php
/*
Plugin Name: MailPress_bulk_import
Plugin URI: http://www.mailpress.org
Description: This is just an addon for MailPress to import users
Author: Daniel Caleb & Andre Renaut
Version: 3.0.1
Author URI: http://www.mailpress.org http://galerie-eigenheim.de
*/

class MailPress_bulk_import
{
	function MailPress_bulk_import()
	{
		add_action('MailPress_users_addon',  		array(&$this,'form'),1,1);
		add_action('MailPress_users_addon_update', 	array(&$this,'process_form'));
	}

	function form($url_parms)
	{
?>
<!-- MailPress_bulk_import -->
<form id='bulk-add' action='' method='post' style="z-index:2000">
	<input type='text'   name='emails'   value='' id='emails' onclick="document.getElementById('bulk-add').style.width='90%';document.getElementById('emails').style.width='70%';document.getElementById('radios').style.display='block';" />
	<input type='submit' name='bulk_add' value='<?php _e('Bulk Add','MailPress' ); ?>' class='button' />
	<div id="radios" style="display:none">
		<input type='radio' name='activate' value='activate' /><?php _e('Activate','MailPress'); ?>
		<input type='radio' name='activate' value='waiting' checked='checked' /> <?php _e('Require Authorization','MailPress'); ?>
		<span style="color:#f00;padding-left:50px;">
			<?php _e('Comma separated [,]','MailPress'); ?>
		</span>
	</div>
	<input type='hidden' name='mode' value='<?php echo $url_parms['mode']; ?>' />
	<input type='hidden' name='status' value='<?php echo $url_parms['status']; ?>' />
</form>
<br />
<!-- MailPress_bulk_import -->
<?php
	}

	function process_form()
	{
		if (!(isset($_POST['bulk_add']))) return;
		if ((empty($_POST['emails']))) return;

		global $wpdb;

		$count_records = $count_emails = $count_users = 0;

		$count_records 	= count(explode(',', $_POST['emails']));

		$count_emails	= self::bulk_users($_POST['emails'],$_POST['activate']);

		$count_users 	= $wpdb->get_var("SELECT count(*)    FROM $wpdb->mp_users;");

		$m1 = sprintf( __ngettext( __('%s email', 'MailPress'), __('%s emails', 'MailPress'), $count_emails ), $count_emails );
		$m2 = sprintf( __ngettext( __('%s record', 'MailPress'), __('%s records', 'MailPress'), $count_records ), $count_records );
		$m3 = sprintf( __ngettext( __('%s user', 'MailPress'), __('%s users', 'MailPress'), $count_users ), $count_users );
		$m4 = sprintf(__('%1$s from %2$s added. Now there are a total of %3$s','MailPress'),$m1,$m2,$m3);
		MP_Admin::message($m4);
	}

	function bulk_users($mails,$type)
	{
		$count = 0;
		$email_array 	= explode(',', $mails);

		foreach ($email_array as $email) 
		{
			$email = trim($email);

			if ( MailPress::is_email($email) && (!MP_User::get_status_by_email($email)))
			{
				if ('activate' == $type) 
				{
				 	$key = md5(uniqid(rand(),1));	
					MP_User::insert($email,$key,'active');
					$count++;
				}
				else
				{
					$return = MP_User::add($email);
					if ($return['result']) $count++;
				}
			}
		}
		return $count;
	}
}
$MailPress_bulk_import = new MailPress_bulk_import();
?>