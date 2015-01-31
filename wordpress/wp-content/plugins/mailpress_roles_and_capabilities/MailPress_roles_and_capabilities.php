<?php
/*
Plugin Name: MailPress_roles_and_capabilities
Plugin URI: http://www.mailpress.org
Description: This is just an addon for MailPress to manage roles & capabilities.
Author: Andre Renaut
Version: 3.0.1
Author URI: http://www.mailpress.org
*/

class MailPress_roles_and_capabilities
{
	function MailPress_roles_and_capabilities()
	{
// for install
		add_action('activate_' . MP_MailPress_roles_and_capabilities_FOLDER . '/MailPress_roles_and_capabilities.php',	array(&$this,'install'));
		add_filter('plugin_action_links', 				array(&$this,plugin_action_links), 10, 2 );

// for role & capabilities
		add_action('MailPress_roles_and_capabilities',		array(&$this,'roles_and_capabilities'));

// for settings
		add_filter('MailPress_force_general_menu',		array(&$this,'force_general_menu'),8,1);
		add_action('MailPress_register_styles', 			array(&$this,'register_styles'));
		add_filter('MailPress_enqueue_styles',			array(&$this,'enqueue_styles'),8,1);
		add_action('MailPress_register_scripts', 			array(&$this,'register_scripts'));
		add_filter('MailPress_enqueue_scripts',			array(&$this,'enqueue_scripts'),8,1);

		add_action('MailPress_settings_extraform_update', 	array(&$this,'update'));
		add_action('MailPress_settings_extraform_tab', 		array(&$this,'tab'),8,1);
		add_action('MailPress_settings_extraform_div', 		array(&$this,'div'));
//for ajax
		add_action('mp_action_r_and_c',				array(&$this,'mp_action_r_and_c'));
	}

// for install
	function install()
	{
		global $mp_general;
		$mp_general		= get_option('MailPress_general');
		$mp_general['menu'] = 'on';
		update_option ('MailPress_general', $mp_general);
	}

	function plugin_action_links($links, $file)
	{
		static $this_plugin;
		if( !$this_plugin ) $this_plugin = plugin_basename(__FILE__);

		if( $file == $this_plugin ){
			$settings_link = '<a href="' . MailPress_settings . '#fragment-MailPress_roles_and_capabilities">' . __('Settings') . '</a>';
			array_unshift ($links, $settings_link);
		}
		return $links;
	}

// for role & capabilities
	function roles_and_capabilities()
	{
		global $wp_roles;
		$capabilities = MP_Admin::capabilities();

		foreach($wp_roles->role_names as $role => $name)
		{
			if ('administrator' == $role) continue;

			$r = get_role($role);
			$rcs = get_option('MailPress_r&c_' . $role);

			foreach ($capabilities as $capability => $v)
			{
				if (isset($rcs[$capability])) 	$r->add_cap($capability);
				else						$r->remove_cap($capability);
			}
		}
	}

// for settings
	function force_general_menu($x)
	{
		return 'MailPress_roles_and_capabilities';
	}

	function register_styles() 
	{
		$pathcss 		= MP_MailPress_roles_and_capabilities_TMP . '/mp-admin/css/colors-' . get_user_option('admin_color') . '.css';
		$css_url 		= get_option('siteurl') . '/' . MP_MailPress_roles_and_capabilities_PATH . 'mp-admin/css/colors-' . get_user_option('admin_color') . '.css';
		$css_url_default 	= get_option('siteurl') . '/' . MP_MailPress_roles_and_capabilities_PATH . 'mp-admin/css/colors-fresh.css';
		$css_url = (is_file($pathcss)) ? $css_url : $css_url_default;

		wp_register_style ( 'MailPress_roles_and_capabilities' . 'color', 	$css_url );
		wp_register_style ( 'MailPress_roles_and_capabilities', 			get_option('siteurl') . '/' . MP_MailPress_roles_and_capabilities_PATH . 'mp-admin/css/settings.css', array('MailPress_roles_and_capabilities' . 'color'));
	}

 	function enqueue_styles($x) 
	{
		$x [MailPress_page_settings][] = 'MailPress_roles_and_capabilities';
		return $x;
	}

	function register_scripts($x)
	{
		wp_register_script( 'mp-r&c', 	'/' . MP_MailPress_roles_and_capabilities_PATH . 'mp-admin/js/settings.js', array('jquery'), false, 1);
		wp_localize_script( 'mp-r&c', 	'settingsL10n', array( 'requestFile' => get_option('siteurl') . '/' . MP_PATH . 'mp-includes/action.php' ) );
	}

	function enqueue_scripts($x)
	{
		$x[MailPress_page_settings][] = 'mp-r&c';
		return $x;
	}


	function update()
	{
		if ($_POST['formname'] != 'roles_and_capabilities_form') return;

		global $mp_general, $mp_tab;

		$mp_general['tab']	= $mp_tab =  'MailPress_roles_and_capabilities';

		global $wp_roles;
		foreach($wp_roles->role_names as $role => $name)
		{
			if ('administrator' == $role) continue;
			$rcs	= $_POST['cap'][$role];
			if (!add_option ('MailPress_r&c_' . $role, $rcs, 'MailPress - roles and capabilities config' )) update_option ('MailPress_r&c_' . $role, $rcs);
		}
		if (!add_option ('MailPress_general', $mp_general, 'MailPress - general settings' )) update_option ('MailPress_general', $mp_general);

		MP_Admin::message(__("'Roles and capabilities' settings saved",'MailPress'));
	}

	function tab($tab)
	{
?>
			<li <?php if ($tab=='MailPress_roles_and_capabilities') echo " class='ui-tabs-selected'"; ?>><a href='#fragment-MailPress_roles_and_capabilities'><span class='button-secondary'><?php _e('R&amp;C','MailPress'); ?></span></a></li>
<?php
	}

	function div()
	{

		include (MP_MailPress_roles_and_capabilities_TMP . '/mp-admin/includes/settings.php');
	}

	function mp_action_r_and_c()
	{
		$rcs_option = 'MailPress_r&c_' . $_POST['role'];
		$r = get_role($_POST['role']);

		$rcs = get_option($rcs_option);
		if (empty($rcs)) $rcs = array();

		if ($_POST['add'])
		{
			$rcs[$_POST['capability']] = 'on';
			if ($r) $r->add_cap($_POST['capability']);
		}
		else
		{
			unset ($rcs[$_POST['capability']] );
			if ($r) $r->remove_cap($_POST['capability']);
		}
		if (!add_option ($rcs_option, $rcs, 'MailPress - roles and capabilities config' )) update_option ($rcs_option, $rcs);

		die(1);
	}
}

define ('MP_MailPress_roles_and_capabilities_FOLDER', basename(dirname(__FILE__)));
define ('MP_MailPress_roles_and_capabilities_PATH', 	'wp-content/plugins/' . MP_MailPress_roles_and_capabilities_FOLDER . '/' );
define ('MP_MailPress_roles_and_capabilities_TMP', 	dirname(__FILE__));

$MailPress_roles_and_capabilities = new MailPress_roles_and_capabilities();
?>