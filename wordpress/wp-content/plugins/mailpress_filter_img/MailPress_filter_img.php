<?php
/*
Plugin Name: MailPress_filter_img
Plugin URI: http://www.mailpress.org
Description: This is just an addon for MailPress to filter ALL html img tags before mailing them.
Author: Andre Renaut
Version: 3.0.1
Author URI: http://www.mailpress.org
*/

class MailPress_filter_img
{
	function MailPress_filter_img()
	{
// for settings
		add_filter('plugin_action_links', 				array(&$this,plugin_action_links), 10, 2 );

		add_action('MailPress_register_styles', 			array(&$this,'register_styles'));
		add_filter('MailPress_enqueue_styles',			array(&$this,'enqueue_styles'),8,1);

		add_action('MailPress_settings_extraform_update', 	array(&$this,'update'));
		add_action('MailPress_settings_extraform_tab', 		array(&$this,'tab'),8,1);
		add_action('MailPress_settings_extraform_div', 		array(&$this,'div'));
// for filtering img html tag
		add_filter('MailPress_process_img_url',			array(&$this,'process_img_url'),8,1);
		add_filter('MailPress_process_img',				array(&$this,'process_img'),8,1);
	}

// for settings

	function plugin_action_links($links, $file)
	{
		static $this_plugin;
		if( !$this_plugin ) $this_plugin = plugin_basename(__FILE__);

		if( $file == $this_plugin ){
			$settings_link = '<a href="' . MailPress_settings . '#fragment-MailPress_filter_img">' . __('Settings') . '</a>';
			array_unshift ($links, $settings_link);
		}
		return $links;
	}

	function register_styles() 
	{
		wp_register_style ( 'MailPress_filter_img', 	get_option('siteurl') . '/' . MP_MailPress_filter_img_PATH . 'mp-admin/css/settings.css' );
	}

 	function enqueue_styles($x) 
	{
		$x [MailPress_page_settings][] = 'MailPress_filter_img';
		return $x;
	}

	function update()
	{
		if ($_POST['formname'] != 'filter_img_form') return;

		global $mp_general, $mp_tab;

		$mp_general['tab'] = $mp_tab = 'MailPress_filter_img';

		$filter_img	= $_POST['filter_img'];

		if (!add_option ('MailPress_filter_img', $filter_img, 'MailPress - filter_img config' )) update_option ('MailPress_filter_img', $filter_img);
		if (!add_option ('MailPress_general', $mp_general, 'MailPress - general settings' )) update_option ('MailPress_general', $mp_general);

		MP_Admin::message(__("'Image filter' settings saved",'MailPress'));
	}

	function tab($tab)
	{
?>
			<li <?php if ($tab=='MailPress_filter_img') echo " class='ui-tabs-selected'"; ?>><a href='#fragment-MailPress_filter_img'><span class='button-secondary'><?php _e('Image filter'    ,'MailPress'); ?></span></a></li>
<?php
	}

	function div()
	{
		include (MP_MailPress_filter_img_TMP . '/mp-admin/includes/settings.php');
	}

// for filtering img html tag

	function process_img_url($bool)
	{
// defaults
		$filter_img	= get_option('MailPress_filter_img');

		if (isset($filter_img['keepurl'])) return true;
		return false;
	}

	function process_img($img)
	{
		$wstyle 	= $inline_style = $default_style = array();
		$wattr 	= $inline_attr  = $default_attr  = array();

// defaults

		$filter_img	= get_option('MailPress_filter_img');

		if (isset($filter_img['align']))
		{
			if ('center' == $filter_img['align']) 	$default_attr['align'] = 'middle';
			elseif ('none' != $filter_img['align']) 	$default_attr['align'] = $filter_img['align'];
		}

		if (!empty($filter_img['extra_style']))
		{
		 	$x = self::retrieve_styles(stripslashes($filter_img['extra_style']));
			foreach($x as $k => $v) $default_style[$k] = $v;
		}

// inline

		$x 		= self::retrieve_attributes($img);
		foreach($x as $k => $v)
		{
			switch ($k)
			{
				case 'style' :
					$inline_style = self::retrieve_styles($v);
				break;
				case 'class' :
					$inline_attr[$k] = $v;
					if (false !== stripos($v,'left'))  $wstyle['float'] = 'left';
					if (false !== stripos($v,'right')) $wstyle['float'] = 'right';
				break;
				default :
					$inline_attr[$k] = $v;
				break;				
			}
		}

		$inline_attr  = array_merge($wattr ,$default_attr ,$inline_attr );
		$inline_style = array_merge($wstyle,$default_style,$inline_style);

// solve possible conflicts between align and float
		if (isset($inline_attr['align']) && isset($inline_style['float'])) unset($inline_attr['align']);		

// format style
		$wstyle = '';
		$quote = '"';
		foreach ($inline_style as $k => $v)
		{
			if (false !== strpos($v,'"')) $quote = "'";
			$wstyle .= $k . ':' . $v . ';';
		}

		$wimg = '<img';
// format attributes
		foreach ($inline_attr as $k => $v) $wimg .= ' ' . $k . '="' . $v . '"';
		$wimg .= ' style=' . $quote . $wstyle . $quote;
		$wimg .= ' />';
		$wimg = "<!-- MailPress_filter_img start -->\n" . $wimg .  "\n<!-- MailPress_filter_img end -->" ;

		return $wimg;
	}

	function retrieve_attributes($img)
	{
		if (empty($img)) return array();

		$w = str_ireplace('<img ','',$img);
		$w = str_ireplace('/>','',$w);
		$w = trim($w);
		do {$w = str_ireplace('  ',' ',$w,$count);} while ($count>0);
		do {$w = str_ireplace(' =','=',$w,$count);} while ($count>0);
		do {$w = str_ireplace('= ','=',$w,$count);} while ($count>0);

		if ('' == $w) return array();

		do
		{
			$att 		= strpos($w,'=');
			$key   	= substr($w,0,$att);
			$quote 	= substr($w,$att+1,1);
			if ("'" != $quote) if ('"' != $quote) $quote=false;
			$start 	= ($quote) ? 1 : 0;
			$end 		= ($quote) ? strpos($w,$quote,$att+1+$start) : strpos($w,' ') ;
			$val 		= substr($w,$att+1+$start,$end-($att+1+$start));

			$x[trim($key)]=trim($val);

			$w = trim(substr($w,$end+1));
		} while ('' != $w);

		return $x;
	}

	function retrieve_styles($style)
	{
		if (empty($style)) return array();

		$w = explode(';',$style);
		foreach ($w as $v)
		{
			if ($v)
			{
				$zs = explode(':',$v);
				$x[trim($zs[0])] = trim($zs[1]);
			}
		}

		return $x;
	}
}

define ('MP_MailPress_filter_img_FOLDER', basename(dirname(__FILE__)));
define ('MP_MailPress_filter_img_PATH', 	'wp-content/plugins/' . MP_MailPress_filter_img_FOLDER . '/' );
define ('MP_MailPress_filter_img_TMP', 	dirname(__FILE__));

$MailPress_filter_img = new MailPress_filter_img();
?>