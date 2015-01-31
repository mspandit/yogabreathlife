<?php
/*
Plugin Name: MailPress_view_logs
Plugin URI: http://www.mailpress.org
Description: This is just an add-on for MailPress to view logs
Author: Andre Renaut
Version: 3.0.1
Author URI: http://www.mailpress.org
*/

class MailPress_view_logs
{
	function MailPress_view_logs()
	{
		global $mp_general;
		$file = (isset($mp_general['menu'])) ? 'admin.php' : 'import.php';

// for plugin
		define ('MailPress_page_view_logs',	'mailpress_viewlogs');
		define ('MailPress_view_logs',	$file . '?page=' . MailPress_page_view_logs);

// for role & capabilities
		add_filter('MailPress_capabilities',  		array(&$this,'capabilities'),1,1);
// for view logs page
		add_action('MailPress_mp_redirect',  		array(&$this,'mp_redirect'),1,1);
		add_action('MailPress_register_scripts',  	array(&$this,'register_scripts'));
		add_filter('MailPress_enqueue_scripts',		array(&$this,'enqueue_scripts'),8,1);

// for javascript plugin conflicts
		add_filter('MailPress_deregister_scripts', array(&$this,'deregister_scripts'), 10, 1 );
	}

// for role & capabilities

	function mailpress_viewlogs() {include (MP_MailPress_view_logs_TMP . '/mp-admin/viewlogs.php');}

	function capabilities($x) 
	{
		global $mp_general;
		$m = (isset($mp_general['menu'])) ? true : false;

		$x['MailPress_view_logs'] = array(	'name'  => __('Logs','MailPress'),
								'group' => 'admin',
								'menu'  => 55,

								'parent'		=> ($m) ? false : 'tools.php',
								'page_title'	=> __('MailPress View logs','MailPress'),
								'menu_title'   	=> ($m) ?  __('Logs','MailPress') : __('MailPress Logs','MailPress'),
								'page'  		=> MailPress_page_view_logs,
								'func'  		=> array(&$this,MailPress_page_view_logs)
							);
		return $x;
	}

// for view logs page

	public static function mp_redirect($page) 
	{ 
		switch (true)
		{
			case ((MailPress_page_view_logs == $page) && !empty( $_GET['delete'] )) :		// MANAGING CHECKBOX REQUESTS
				global $wpdb;
				$ftmplt	= (isset($wpdb->blogid)) ? 'MP_Log_' . $wpdb->blogid . '_mailpress_' : 'MP_Log_mailpress_' ;
				$path 	= '../' . self::get_path();

				$url_parms = MP_Admin::get_url_parms();
				$deleted = 0;
				foreach ($_GET['delete'] as $file)
				{							
					switch (true)
					{
						case ( isset( $_GET['deleteit'] )):
							@unlink($path . '/' . $file);
							$deleted++;
						break;
					}
				}
				$redirect_to  = MailPress_view_logs;
				$redirect_to .= ($deleted) 		? '&deleted=' 	. $deleted : '';
				$redirect_to = MP_Admin::url($redirect_to ,false,$url_parms);
				wp_redirect( $redirect_to );
			break;
		}
	}

	function register_scripts() 
	{
		global $mp_screen;

		wp_register_script( MailPress_page_view_logs, 	'/' . MP_MailPress_view_logs_PATH . 'mp-admin/js/viewlogs.js', array('mp-lists'), false, 1);
		wp_localize_script( MailPress_page_view_logs, 	'adminfilesL10n',  array('pending' => __('%i% pending'),
														 'screen'  => $mp_screen ) );
	}

	function enqueue_scripts($x)
	{
		$x[MailPress_page_view_logs][]  = MailPress_page_view_logs;

		return $x;
	}

// for path
	function get_path() 
	{
		return MP_PATH . 'tmp';
	}


	function deregister_scripts($x)
	{
		$x[] = MailPress_page_view_logs;
		return $x;
	}
}

define ('MP_MailPress_view_logs_FOLDER', 	basename(dirname(__FILE__)));
define ('MP_MailPress_view_logs_PATH', 	'wp-content/plugins/' . MP_MailPress_view_logs_FOLDER . '/' );
define ('MP_MailPress_view_logs_TMP', 	dirname(__FILE__));

$MailPress_view_logs = new MailPress_view_logs();
?>