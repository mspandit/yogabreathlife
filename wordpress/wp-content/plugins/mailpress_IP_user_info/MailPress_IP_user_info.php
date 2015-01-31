<?php
/*
Plugin Name: MailPress_IP_user_info
Plugin URI: http://www.mailpress.org
Description: This is just an addon for MailPress to have info from ip and infosniper.net
Author: Andre Renaut
Version: 3.0.1
Author URI: http://www.mailpress.org
*/

class MailPress_IP_user_info
{
	function MailPress_IP_user_info()
	{
		add_filter('plugin_action_links', 		array(&$this,'plugin_action_links'), 10, 2 );

		add_filter('admin_xml_ns', 			array(&$this,'admin_xml_ns'));

		add_action('MailPress_register_scripts',  array(&$this,'register_scripts'));
		add_filter('MailPress_enqueue_scripts',	array(&$this,'enqueue_scripts'),8,1);

		add_action('MailPress_settings_general',	array(&$this,'gmap_key'));
		add_action('MailPress_user_boxes',		array(&$this,'user_boxes'),8,2);
	}

	function plugin_action_links($links, $file)
	{
		static $this_plugin;
		if( !$this_plugin ) $this_plugin = plugin_basename(__FILE__);

		if( $file == $this_plugin ){
			$settings_link = '<a href="' . MailPress_settings . '#fragment-1">' . __('Settings') . '</a>';
			array_unshift ($links, $settings_link);
		}
		return $links;
	}

	function admin_xml_ns()
	{
		$page = MP_Admin::get_page();
		if ($page != MailPress_page_user) return;
		echo "xmlns:v=\"urn:schemas-microsoft-com:vml\"";
	}

	function register_scripts() 
	{
		global $mp_screen, $mp_general;

		$color 	= ('fresh' == get_user_option('admin_color')) ? '' : 'b';
		$pathimg 	= MP_MailPress_IP_user_info_TMP . '/mp-admin/images/controlmap' . $color . '.png';
		$color 	= (is_file($pathimg)) ? $color : '';

		wp_register_script( 'gmap', 	'http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=false&amp;key=' . $mp_general['gmapkey'], array(), false, 1);

		wp_register_script( 'mailpress_IP_user_info',      '/' . MP_MailPress_IP_user_info_PATH . 'mp-admin/js/user.js', array('gmap'), false, 1);
		wp_localize_script( 'mailpress_IP_user_info', 	'mailpress_IP_user_infoL10n', array( 'url' 	=> get_option( 'siteurl' ) . '/' . MP_MailPress_IP_user_info_PATH . 'mp-admin/images/',
																 'color'	=> $color,
																 'zoomwide' => js_escape(__('zoom -','MailPress')),
																 'zoomtight'=> js_escape(__('zoom +','MailPress')),
																 'center' 	=> js_escape(__('center','MailPress')),
																 'changemap'=> js_escape(__('change map','MailPress')) ) );
	}

	function enqueue_scripts($x)
	{
		$x[MailPress_page_user][] = 'mailpress_IP_user_info';

		return $x;
	}

	function gmap_key()
	{
		global $mp_general;
?>
									<tr>
										<th scope='row'><a target='_blank' style='color:#333;' href='http://www.google.com/apis/maps/signup.html'><?php _e('Google Map API Key','MailPress'); ?></a></th>
										<td>
											<input type="text" size="90"  name="general[gmapkey]" value="<?php echo $mp_general['gmapkey']; ?>" />
										</td>
									</tr>
<?php
	}

	function user_boxes($mp_user_id,$mp_screen)
	{
		add_meta_box('IP_info', __('IP info','MailPress'), array(&$this,'IP_info_meta_box'), $mp_screen, 'side', 'core');
	}

	function IP_info_meta_box($mp_user)
	{
		global $mp_general;
		$Gkey = $mp_general['gmapkey'];
		$skip = array('hostname', 'countrycode', 'countryflag', 'areacode', 'dmacode', 'queries');

		$ip = MP_User::get_user_author_IP();

		$x  = self::get_IP_user_info($ip);
?>
<div>
<?php
		if ($x)
		{
			$xml = new SimpleXMLElement ( $x );
			foreach ($xml->result[0] as $k => $v)
			{
				if (trim($v) == 'Quota exceeded') 
				{ 
					echo "<p style='margin:3px;'><b>" . __('** Quota exceeded for **','MailPress') . "</b></p>"; 
					break;
				}

				if ($v == 'n/a') continue;

				if (in_array($k,$skip)) continue;

				if (in_array($k,array('latitude','longitude'))) {$$k = $v; continue;}

				$datas .= "<p style='margin:3px;'><b>$k</b> : $v</p>";
			}
			if ($datas) echo "<div>$datas<p style='margin:3px;'><i><small>data from <a href='http://www.infosniper.net' target='_blank'>http://www.infosniper.net</a></small></i></p></div>";
			if ($latitude && $longitude && $Gkey)
			{
				$mapdiv = 'IPuserinfo_map';
?>
<script type="text/javascript">
jQuery(document).ready( function() { if (GBrowserIsCompatible()) IPuserinfo(<?php echo $latitude; ?>,<?php echo $longitude; ?>,'<?php echo $mapdiv; ?>'); } );
</script>
<?php
				echo "<br /><div id='$mapdiv' style='overflow:hidden;'></div><div></div>";

			}
		}
?>
</div>
<?php	
	}

	function get_IP_user_info($ip)
	{
		$xml 		= '';
		$ttl 		= 30*24*60*60; 									// keep it 30 days
		$ip_url 	= "http://www.infosniper.net/xml.php?ip_address=$ip";
		$cache	= '../' . MP_PATH . 'tmp/' . md5($ip_url) . '.spc';

		if (file_exists($cache) && (time() - $ttl < filemtime($cache)))	$file = $cache;
		else											$file = $ip_url;

		$xml = @file_get_contents($file);

		if (($file == $ip_url) && (strpos($xml, 'Quota exceeded') === false) && (!empty($xml))) file_put_contents($cache,$xml);

		return $xml;
	}
}
if (class_exists('MailPress'))
{
	define ('MP_MailPress_IP_user_info_FOLDER', 	basename(dirname(__FILE__)));
	define ('MP_MailPress_IP_user_info_PATH', 	'wp-content/plugins/' . MP_MailPress_IP_user_info_FOLDER . '/' );
	define ('MP_MailPress_IP_user_info_TMP', 	dirname(__FILE__));

	$MailPress_IP_user_info = new MailPress_IP_user_info();
}
?>