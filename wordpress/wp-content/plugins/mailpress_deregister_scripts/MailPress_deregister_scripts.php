<?php
/*
Plugin Name: MailPress_deregister_scripts
Plugin URI: http://www.mailpress.org
Description: This is just an add-on for MailPress to remove unwelcomed scripts from MailPress Pages
Author: Andre Renaut
Version: 3.0.1
Author URI: http://www.mailpress.org
*/

class MailPress_deregister_scripts
{
	function MailPress_deregister_scripts()
	{
		add_action('wp_print_scripts',  array(&$this,'deregister_scripts'),1);
	}

	function deregister_scripts()
	{
		$pages = apply_filters('MailPress_deregister_scripts',array());
		$page = MP_Admin::get_page();
		$y = '';

		if (in_array($page,$pages))
		{
			$file	= MP_MailPress_deregister_scripts_TMP . '/scripts.xml';

			if (file_exists($file))
			{
				echo "\n<!-- MailPress_deregister_scripts : ";
				$x = file_get_contents($file);
				if ($x)
				{
					$xml = new SimpleXMLElement ( $x );
					foreach ($xml->script as $script)
					{
						wp_deregister_script($script);
						$y .= (!empty($y)) ? ", $script" : $script;
					}
				}
				echo "$y -->\n";
			}
		}
	}
}

if (class_exists('MailPress'))
{
	define ('MP_MailPress_deregister_scripts_FOLDER', 	basename(dirname(__FILE__)));
	define ('MP_MailPress_deregister_scripts_PATH', 	'wp-content/plugins/' . MP_MailPress_deregister_scripts_FOLDER . '/' );
	define ('MP_MailPress_deregister_scripts_TMP', 	dirname(__FILE__));

	$MailPress_deregister_scripts = new MailPress_deregister_scripts();
}
?>