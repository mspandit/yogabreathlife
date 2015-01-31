<?php
/*
Plugin Name: MailPress_connection_phpmail 
Plugin URI: http://www.mailpress.org
Description: This is just an addon for MailPress to replace default SMTP connection by native php mail connection.
Author: Andre Renaut
Version: 3.0.1
Author URI: http://www.mailpress.org
*/

class MailPress_connection_phpmail 
{
	function MailPress_connection_phpmail ()
	{
// for settings
		add_filter('plugin_action_links', 				array(&$this,plugin_action_links), 10, 2 );
		add_action('MailPress_settings_extraform_update', 	array(&$this,'update'));
// for connection type & settings
		add_filter('MailPress_Swift_Connection_type',		array(&$this,'Swift_Connection_type'),8,1);
		add_filter('MailPress_Swift_Connection_settings',	array(&$this,'Swift_Connection_settings'),8,1);
// for connection 
		add_filter('MailPress_Swift_Connection_PHP_MAIL', 	array(&$this,'connect'),8,1);
	}

	function plugin_action_links($links, $file)
	{
		static $this_plugin;
		if( !$this_plugin ) $this_plugin = plugin_basename(__FILE__);

		if( $file == $this_plugin ){
			$settings_link = '<a href="' . MailPress_settings . '#fragment-2">' . __('Settings') . '</a>';
			array_unshift ($links, $settings_link);
		}
		return $links;
	}

	function update()
	{
		if ($_POST['formname'] != 'connection_phpmail_form') return;

		global $mp_general, $mp_tab;

		$mp_general['tab']	= $mp_tab =  1;

		$connection_phpmail	= $_POST['connection_phpmail'];

		if (!add_option ('MailPress_connection_phpmail', $connection_phpmail, 'MailPress - connection_phpmail config' )) update_option ('MailPress_connection_phpmail', $connection_phpmail);
		if (!add_option ('MailPress_general', $mp_general, 'MailPress - general settings' )) update_option ('MailPress_general', $mp_general);

		MP_Admin::message(__("'PHP MAIL' settings saved",'MailPress'));
	}

	function Swift_Connection_type($x)
	{
		return 'PHP_MAIL';
	}

	function Swift_Connection_settings($x)
	{
		return ABSPATH . MP_MailPress_connection_phpmail_PATH . 'mp-admin/includes/settings.php';
	}

	function connect($x)
	{
		require_once MP_TMP . "/mp-includes/class/swift/Swift/Connection/NativeMail.php";

		$phpmail_settings = get_option('MailPress_connection_phpmail');

		$conn = new Swift_Connection_NativeMail($phpmail_settings['addparm']);

		return $conn;
	}
}

define ('MP_MailPress_connection_phpmail_FOLDER', 	basename(dirname(__FILE__)));
define ('MP_MailPress_connection_phpmail_PATH', 	'wp-content/plugins/' . MP_MailPress_connection_phpmail_FOLDER . '/' );
define ('MP_MailPress_connection_phpmail_TMP', 		dirname(__FILE__));

$MailPress_connection_phpmail = new MailPress_connection_phpmail();
?>