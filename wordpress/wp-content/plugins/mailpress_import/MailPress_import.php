<?php
/*
Plugin Name: MailPress_import
Plugin URI: http://www.mailpress.org
Description: This is just an addon for MailPress to provide an import API for files.
Author: Andre Renaut
Version: 3.0.1
Author URI: http://www.mailpress.org
*/

class MailPress_import
{
	function MailPress_import()
	{
		global $mp_general;
		$file = (isset($mp_general['menu'])) ? 'admin.php' : 'tools.php';

// for plugin
		define ('MailPress_page_import',	'mailpress_import');
		define ('MailPress_import',	$file . '?page=' . MailPress_page_import);

// for role & capabilities
		add_filter('MailPress_capabilities',  			array(&$this,'capabilities'),1,1);
// for settings
		add_filter('plugin_action_links', 				array(&$this,plugin_action_links), 10, 2 );

		add_action('MailPress_settings_extraform_update', 	array(&$this,'update'));
		add_action('MailPress_settings_extraform_tab', 		array(&$this,'tab'),8,1);
		add_action('MailPress_settings_extraform_div', 		array(&$this,'div'));
// for admin
		add_action('MailPress_screen_meta',				array(&$this,'screen_meta'),8,2);
		add_filter('MailPress_screen_meta_screen',		array(&$this,'screen_meta_screen'),8,2);

// for javascript plugin conflicts
		add_filter('MailPress_deregister_scripts', array(&$this,'deregister_scripts'), 10, 1 );

	}

// for role & capabilities

	function mailpress_import2() 		{ include (MP_MailPress_import_TMP . '/mp-admin/import.php'); }

	function capabilities($x) 
	{
		global $mp_general;
		$m = (isset($mp_general['menu'])) ? true : false;

		$x['MailPress_import'] = array(	'name'  => __('Import','MailPress'),
								'group' => 'admin',
								'menu'  => 50,

								'parent'		=> ($m) ? false : 'tools.php',
								'page_title'	=> __('MailPress Import','MailPress'),
								'menu_title'   	=> ($m) ? __('Import','MailPress') : __('MailPress Import','MailPress'),
								'page'  		=> MailPress_page_import,
								'func'  		=> array(&$this,MailPress_page_import . '2')
							);
		return $x;
	}

// for settings

	function plugin_action_links($links, $file)
	{
		static $this_plugin;
		if( !$this_plugin ) $this_plugin = plugin_basename(__FILE__);

		if( $file == $this_plugin ){
			$settings_link = '<a href="' . MailPress_settings . '#fragment-MailPress_import">' . __('Settings') . '</a>';
			array_unshift ($links, $settings_link);
		}
		return $links;
	}

	function update()
	{
		if ($_POST['formname'] != 'import_form') return;

		global $mp_general, $mp_tab;

		$mp_general['tab']	= $mp_tab =  'MailPress_import';

		$import	= $_POST['import'];

		if (!add_option ('MailPress_import', $import, 'MailPress - import config' )) update_option ('MailPress_import', $import);
		if (!add_option ('MailPress_general', $mp_general, 'MailPress - general settings' )) update_option ('MailPress_general', $mp_general);

		MP_Admin::message(__("'Import' settings saved",'MailPress'));
	}

	function tab($tab)
	{
?>
			<li <?php if ($tab=='MailPress_import') echo " class='ui-tabs-selected'"; ?>><a href='#fragment-MailPress_import'><span class='button-secondary'><?php echo(trim(__('Import '    ,'MailPress'))); ?></span></a></li>
<?php
	}

	function div()
	{
		include (MP_MailPress_import_TMP . '/mp-admin/includes/settings.php');
	}

// screen_meta

	function screen_meta($page,$mp_screen)
	{
		switch ($page)
		{
			case MailPress_page_import :

				$help	= sprintf(__('<a href="%1$s" target="_blank">Documentation</a>','MailPress'),MailPress_help_url);
				$help	.= '<br />' . sprintf(__('<a href="%1$s" target="_blank">Support Forum</a>','MailPress'),'http://groups.google.com/group/mailpress');
				add_contextual_help($mp_screen, $help);
			break;
		}
	}

	function screen_meta_screen($screen,$page)
	{
		global $mp_screen;

		$mp_screen = $screen;

		switch ($page)
		{
			case MailPress_page_import :
				$mp_screen = MailPress_page_import;
			break;
		}
		return $mp_screen;
	}

// list of importers

	function manage_list_columns() {
		$importers_columns = array(	'name' 	=> __('Name','MailPress'),
							'desc'	=> __('Description','MailPress'));
		$importers_columns = apply_filters('MailPress_manage_importers_columns', $importers_columns);
		return $importers_columns;
	}


// import API

	function sync_mailinglist($mailinglist,$trace=false) 
	{
		if ($id = get_mailinglist_ID('MailPress_import_' . $mailinglist))
		{
			$x = "mailing list found :  [$id] => $mailinglist";
			if ($trace)	$trace->log($x);
			else 		echo $x . '<br />';
			return $id;
		}

		if ($id = mp_insert_mailinglist(array('mailinglist_name'=>'MailPress_import_' . $mailinglist)))
		{
			$x = "mailing list inserted :  [$id] => $mailinglist";
			if ($trace)	$trace->log($x);
			else 		echo $x . '<br />';
			return $id;
		}

		return false;
	}

	function sync_mp_user($email,$trace=false,$status = 'active')
	{
		if ( !MailPress::is_email($email))
		{
			$x = '>>>>' .  $email . ' not an email***';
			if ($trace)	$trace->log($x);
			else 		echo $x . '<br />';
		 	return false;
		}
		if (MP_User::get_status_by_email($email))
		{
			$x = '>>>>' .  $email . ' already exists but processed if extra work to do';
			if ($trace)	$trace->log($x);
			else 		echo $x . '<br />';
		}
		else
		{
		 	$key = md5(uniqid(rand(),1));	
			MP_User::insert($email,$key,$status);
			$x = '>>>>' .  $email . ' inserted ';
			if ($trace)	$trace->log($x);
			else 		echo $x . '<br />';
		}
		return MP_User::get_id_by_email($email);
	}

	function sync_mp_usermeta($mp_user_id,$meta_key,$meta_value,$trace=false)
	{
		MP_Usermeta::update( $mp_user_id, $meta_key, $meta_value ) ;
		$x = "user [$mp_user_id]=> update of meta data key=>\"$meta_key\" data=>\"$meta_value\"";
		if ($trace)	$trace->log($x);
		else 		echo $x . '<br />';
	}

	function sync_mp_user_mailinglist($mp_user_id,$mailinglist_ID,$email='',$mailinglist='',$trace=false) 
	{
		$user_mailinglists = MailPress_mailing_lists::get_user_mailinglists($mp_user_id);
		if (in_array($mailinglist_ID,$user_mailinglists))
		{
			$x = "user [$mp_user_id]=> $email is already in mailing list [$mailinglist_ID] => $mailinglist";
			if ($trace)	$trace->log($x);
			else 		echo $x . '<br />';
		}
		else
		{
			array_push($user_mailinglists,$mailinglist_ID);
			MailPress_mailing_lists::set_user_mailinglists( $mp_user_id, $user_mailinglists);
			$x = "user [$mp_user_id]=> $email is inserted in mailing list [$mailinglist_ID] => $mailinglist";
			if ($trace)	$trace->log($x);
			else 		echo $x . '<br />';
		}
	}

	function deregister_scripts($x)
	{
		$x[] = MailPress_page_import;
		return $x;
	}
}

define ('MP_MailPress_import_FOLDER', 	basename(dirname(__FILE__)));
define ('MP_MailPress_import_PATH', 	'wp-content/plugins/' . MP_MailPress_import_FOLDER . '/' );
define ('MP_MailPress_import_TMP', 	dirname(__FILE__));

$MailPress_import = new MailPress_import();
?>