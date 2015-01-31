<?php
/*
Plugin Name: MailPress_tracking
Plugin URI: http://www.mailpress.org
Description: This is just an addon for MailPress to track the mails/users activity.
Author: Andre Renaut
Version: 3.0.1
Author URI: http://www.mailpress.org
*/

class MailPress_tracking
{
	function MailPress_tracking()
	{
		define ('MailPress_tracking_openedmail', 	'_MailPress_mail_opened');

		global $wpdb;
		$wpdb->mp_tracks = $wpdb->prefix . 'mailpress_tracks';

// for plugin
		add_filter('MailPress_tracking',				array(&$this,'is_tracking'),8,1);
		add_action('mp_action_tracking',				array(&$this,'tracking'),8,1);

// install
		register_activation_hook(MP_MailPress_tracking_FOLDER . '/MailPress_tracking.php',	array(&$this,'install'));

// plugins loaded
		add_action('MailPress_init', 					array(&$this,'init'));

// for role & capabilities
		add_filter('MailPress_capabilities',  			array(&$this,'capabilities'),1,1);

// for settings
		add_filter('plugin_action_links', 				array(&$this,plugin_action_links), 10, 2 );

		add_action('MailPress_settings_extraform_update', 	array(&$this,'update'));
		add_action('MailPress_settings_extraform_tab', 		array(&$this,'tab'),8,1);
		add_action('MailPress_settings_extraform_div', 		array(&$this,'div'));

// for referential integrity
		add_action('MailPress_delete_mail',  			array(&$this,'delete_mail'),1,1);
		add_action('MailPress_delete_user',  			array(&$this,'delete_user'),1,1);
	}

// for plugin
	function is_tracking($x=false)
	{
		return true;
	}

	function tracking()
	{
		$meta = MP_Mailmeta::get_by_id($_GET['mm']);

		if ($meta)
		{
			switch ($_GET['tg'])
			{
				case ('l') :
					self::save($meta);
				break;
				case ('o') :
					self::save($meta);
				break;
				default :
					$meta->meta_value = '404';
					self::save($meta);
				break;
			}
		}
	}

// install
	function install() 
	{
		include ( MP_MailPress_tracking_TMP . '/mp-admin/includes/install.php');
	}

//plugins loaded
	function init()
	{
// for mails list
		if ( current_user_can('MailPress_tracking_mails') )
		{
			add_filter('MailPress_screen_meta_screen',		array(&$this,'screen_meta_screen'),8,2);
			add_action('MailPress_screen_meta',				array(&$this,'screen_meta'),8,2);
			add_action('MailPress_register_styles', 			array(&$this,'register_styles'));
			add_action('MailPress_enqueue_styles', 			array(&$this,'enqueue_styles'));
			add_action('MailPress_register_scripts',  		array(&$this,'register_scripts'));
			add_filter('MailPress_enqueue_scripts',			array(&$this,'enqueue_scripts'),8,1);

			add_action('MailPress_manage_mails_custom_column',  	array(&$this,'manage_mails_custom_column'),1,3);
			add_filter('MailPress_manage_mails_columns',		array(&$this,'manage_mails_columns'),8,1);

			add_action('mp_mail_tracking',  				array(&$this,'mp_mail_tracking')); 
		}

// for user page
		if ( current_user_can('MailPress_tracking_users') )
			add_action('MailPress_user_boxes',  			array(&$this,'user_boxes'),1,2); 
	}

// for role & capabilities
	function capabilities($x) 
	{
		$x['MailPress_tracking_mails'] = array(	'name'  	=> __('View tracking','MailPress'),
            						'group' 	=> 'mails',
            						'menu'  	=> false
            					);
		$x['MailPress_tracking_users'] = array(	'name'  	=> __('View tracking','MailPress'),
            						'group' 	=> 'users',
            						'menu'  	=> false
            					);
		return $x;
	}

// for settings
	function plugin_action_links($links, $file)
	{
		static $this_plugin;
		if( !$this_plugin ) $this_plugin = plugin_basename(__FILE__);

		if( $file == $this_plugin ){
			$settings_link = '<a href="' . MailPress_settings . '#fragment-MailPress_tracking">' . __('Settings') . '</a>';
			array_unshift ($links, $settings_link);
		}
		return $links;
	}

	function update()
	{
		if ($_POST['formname'] != 'tracking_form') return;

		global $mp_general, $mp_tab;

		$mp_general['tab']	= $mp_tab =  'MailPress_tracking';

		if (isset($_POST['general']['gmapkey'])) $mp_general['gmapkey'] = $_POST['general']['gmapkey'];

		$tracking	= $_POST['tracking'];

		if (!add_option ('MailPress_tracking', $tracking, 'MailPress - tracking config' )) update_option ('MailPress_tracking', $tracking);
		if (!add_option ('MailPress_general', $mp_general, 'MailPress - general settings' )) update_option ('MailPress_general', $mp_general);

		MP_Admin::message(__("'Tracking' settings saved",'MailPress'));
	}

	function tab($tab)
	{
?>
			<li <?php if ($tab=='MailPress_tracking') echo " class='ui-tabs-selected'"; ?>><a href='#fragment-MailPress_tracking'><span class='button-secondary'><?php _e('Tracking'    ,'MailPress'); ?></span></a></li>
<?php
	}

	function div()
	{
		include (MP_MailPress_tracking_TMP . '/mp-admin/includes/settings.php');
	}

// for mails list
	function screen_meta_screen($screen,$page)
	{
		global $mp_screen;

		$mp_screen = $screen;

		if (isset($_GET['action']) && ($_GET['action'] == 'tracking'))
		{
			switch ($page)
			{
				case MailPress_page_mail:
					$mp_screen = 'mailpress_tracking';
				break;
			}
		}

		return $mp_screen;
	}

	function screen_meta($page,$mp_screen)
	{
		if (isset($_GET['action']) && ($_GET['action'] == 'tracking'))
		{
			switch ($page)
			{
				case MailPress_page_mail:
					$mail = MP_Mail::get($_GET['id']);
					$tracking = get_option('MailPress_tracking');
					if (!is_array($tracking)) return;
					include ('mp-includes/tracking-reports.php');
					if ($tracking)
					{
						foreach($tracking as $k => $v)
						{
							if (!isset($tracking_reports['mail'][$k])) continue;
							include("mp-includes/$k/$k.php");
							add_meta_box('tracking'.$k.'div', $tracking_reports['mail'][$k]['title'] , "MailPress_tracking_$k", 	$mp_screen, 'normal', '');
						}
					}
					$help	= sprintf(__('<a href="%1$s" target="_blank">Documentation</a>','MailPress'),MailPress_help_url);
					$help	.= '<br />' . sprintf(__('<a href="%1$s" target="_blank">Support Forum</a>','MailPress'),'http://groups.google.com/group/mailpress');
					add_contextual_help($mp_screen, $help);
				break;
			}
		}
	}

	function register_styles() 
	{
		wp_register_style ( 'mp_tracking_mails', 	get_option('siteurl') . '/' . MP_MailPress_tracking_PATH . 'mp-admin/css/mails.css');
	}

 	function enqueue_styles($x) 
	{
		$x [MailPress_page_mails][] = 'mp_tracking_mails';
		if (isset($_GET['action']) && ($_GET['action'] == 'tracking'))
			$x [MailPress_page_mail][] = 'dashboard';
			$x [MailPress_page_mail][] = 'thickbox';
			$x [MailPress_page_mail][] = MailPress_page_mails;
			$x [MailPress_page_mail][] = 'mp_tracking_mails';
		return $x;
	}

	function register_scripts($x)
	{
		global $mp_screen;
		wp_register_script( 'mp-tracking-mail', 	      '/' . MP_MailPress_tracking_PATH . 'mp-admin/js/tracking-mail.js', array('jquery-ui-tabs', 'postbox', 'thickbox'), false, 1);
		wp_localize_script( 'mp-tracking-mail', 		'admintrackingL10n', array('pending' => __('%i% pending'),
														'screen' => $mp_screen ) );
	}

	function enqueue_scripts($x)
	{
		if (isset($_GET['action']) && ($_GET['action'] == 'tracking'))
		{
			$x[MailPress_page_mail][] = 'mp-tracking-mail';
		}
		return $x;
	}

	function manage_mails_columns($x)
	{
		$date = array_pop($x);
		$x['tracking_openrate']	=  __('Open rate','MailPress');
		$x['tracking_clicks']	=  __('Clicks','MailPress');
		$x['date']		= $date;
		return $x;
	}

	function manage_mails_custom_column($column_name,$mail,$url_parms)
	{
		global $wpdb;
		switch ($column_name)
		{
			case 'tracking_openrate' :
				if ('sent' == $mail->status)
				{
					$total = (MailPress::is_email($mail->toemail)) ? 1 : count(unserialize($mail->toemail));
					$query = "SELECT DISTINCT user_id FROM $wpdb->mp_tracks WHERE mail_id = " . $mail->id . " AND track = '" . MailPress_tracking_openedmail . "' ;";
					$result = $wpdb->get_results($query);
					if ($result) printf("%01.2f %%",100 * count($result)/$total );
				}
			break;
			case 'tracking_clicks' :
				$query = "SELECT count(*) FROM $wpdb->mp_tracks WHERE mail_id = " . $mail->id . " AND track <> '" . MailPress_tracking_openedmail . "' ;";
				$result = $wpdb->get_var($query);
				if ($result) echo "<div class='num post-com-count-wrapper'><a class='post-com-count'><span class='comment-count'>$result</span></a></div>";
			break;
		}
	}

	function mp_mail_tracking()
	{							
		include('mp-admin/tracking-mail.php');
	}

// for user page
	function user_boxes($mp_user_id,$mp_screen)
	{
		$tracking = get_option('MailPress_tracking');
		if (!is_array($tracking)) return;
		include ('mp-includes/tracking-reports.php');
		foreach($tracking as $k => $v)
		{
			if (!isset($tracking_reports['user'][$k])) continue;
			add_meta_box($k . 'div', $tracking_reports['user'][$k]['title'] , "MailPress_tracking_$k", $mp_screen, 'advanced', 'low');
			include("mp-includes/$k/$k.php");
		}
	}

// for referential integrity
	function delete_mail($mail_id)
	{
		global $wpdb;
		$query = "DELETE FROM $wpdb->mp_tracks WHERE mail_id = $mail_id; ";
		$wpdb->query($query);
		$query = "DELETE FROM $wpdb->mp_usermeta WHERE meta_key = '_MailPress_mail_sent' AND meta_value = $mail_id;";
		$wpdb->query($query);
	}

	function delete_user($mp_user_id)
	{
		global $wpdb;
		$query = "DELETE FROM $wpdb->mp_tracks WHERE user_id = $mp_user_id; ";
		$wpdb->query($query);
	}

// for reports
	function translate_track($track, $mail_id)
	{
		switch ($track)
		{
			case '{{subscribe}}' :
				return __('subscribe','MailPress');
			break;
			case '{{unsubscribe}}' :
				return __('unsubscribe','MailPress');
			break;
			case '{{viewhtml}}' :
				return __('view html','MailPress');
			break;
			case MailPress_tracking_openedmail :
				return __('mail opened','MailPress');
			break;
			default :
				$url = MP_User::get_subscribe_url('#µ$&$µ#');
				$url = str_replace('#µ$&$µ#','',$url);
				if (stripos($track,$url) !== false) {return __('subscribe','MailPress');}
				$url = MP_User::get_unsubscribe_url('#µ$&$µ#');
				$url = str_replace('#µ$&$µ#','',$url);
				if (stripos($track,$url) !== false) {return __('unsubscribe','MailPress');}
				$url = MP_User::get_view_url('#µ$&$µ#',$mail_id);
				$url = str_replace('#µ$&$µ#&id=' . $mail_id,'',$url);
				if (stripos($track,$url) !== false) {return __('view html','MailPress');}
			break;
		}
		global $wpdb;
		$title = $wpdb->get_var("SELECT post_title FROM $wpdb->posts WHERE guid = '$track';");
		$title = $display_title = ($title) ? $title : $track;
		$display_title = (substr($display_title,0,7) == 'http://') ? substr($display_title,7) : $display_title;
		$display_title = (substr($display_title,0,8) == 'https://') ? substr($display_title,8) : $display_title;
		$display_title = (strlen($display_title) > 20) ? substr($display_title, 0, 18) . '...' : $display_title;
		return "<a href='$track' title='$title'>$display_title</a>";
	}

	function get_geoip($ip)
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

// prepare mail for tracking
	public static function prepare($mail_id, $toemail, $plaintext, $html, $sepb='{{' ,$sepa='}}')
	{
		foreach($toemail as $email => $rep)
		{
			if (isset($rep['{{mp_confkey}}']))
			{
				MP_Usermeta::add(MP_User::get_id($rep['{{mp_confkey}}']), '_MailPress_mail_sent', $mail_id);
			}
		}

		$output = preg_match_all('/<a.+href=[\'""]([^\'""]+)[\'""].*>([^\'""]+)<\/a>/i', $html, $matches, PREG_SET_ORDER);

		if ($matches)
		{
			foreach ($matches as $match)
			{
				if (strpos($match[1], 'mailto:') !== false) continue;
				$mmeta_id = self::get_mmid($mail_id, '_MailPress_mail_link', $match[1]);
				$y[$sepb . "_MailPress_mail_link_$mmeta_id" . $sepa] = get_option('siteurl') . '/' . MP_PATH . "mp-includes/action.php?tg=l&mm=$mmeta_id";

				$search = $match[1];
				$replace = "{{_MailPress_mail_link_$mmeta_id}}";
				$subject = $match[0];
				$count = 1;
				$x = self::str_replace_count($search, $replace . '&co=h&us={{mp_confkey}}', $subject, $count);
				$html = str_ireplace($subject, $x, $html);
				$plaintext = str_ireplace($search, $replace . '&co=p&us={{mp_confkey}}', $plaintext);
			}
			$mmeta_id = self::get_mmid($mail_id,MailPress_tracking_openedmail, MailPress_tracking_openedmail);
			$html = str_ireplace('</body>', "\n<img src='{{" . MailPress_tracking_openedmail . "_$mmeta_id}}&co=h&us={{mp_confkey}}' alt='' style='margin:0;padding:0;border:none;' /></body>", $html);
			$y[$sepb . MailPress_tracking_openedmail . "_$mmeta_id" . $sepa] = get_option('siteurl') . '/' . MP_PATH . "mp-includes/action.php?tg=o&mm=$mmeta_id";

			foreach($toemail as $email => $z) foreach($y as $k => $v) $toemail[$email][$k] = $v;
		}
		return array('toemail' => $toemail, 'plaintext' => $plaintext, 'html' => $html);
	}

	public static function str_replace_count($search,$replace,$subject,$times=1) 
	{
		$subject_original=$subject;

		$len=strlen($search);
		$pos=0;
		for ($i=1;$i<=$times;$i++) 
		{
			$pos=strpos($subject,$search,$pos);
			if($pos!==false) 
			{
				$subject=substr($subject_original,0,$pos);
				$subject.=$replace;
				$subject.=substr($subject_original,$pos+$len);
				$subject_original=$subject;
			}
			else
			{
				break;
			}
		}
		return($subject);
	}

	public static function get_mmid($mail_id, $meta_key, $meta_value)
	{
		global $wpdb;
		$mmeta_id = $wpdb->get_var("SELECT mmeta_id FROM $wpdb->mp_mailmeta WHERE mail_id = $mail_id AND meta_key = '$meta_key' AND meta_value = '$meta_value';");
		if ($mmeta_id) return $mmeta_id;
		return MP_Mailmeta::add( $mail_id, $meta_key, $meta_value);
	}

// save tracking
	public static function save($meta)
	{
		global $wpdb;

		$now	  	= date('Y-m-d H:i:s');

		$mp_user_id = MP_User::get_id($_GET['us']);

		$context 	= ('h' == $_GET['co']) ? 'html' : 'plaintext';

		$mail_id	= $meta->mail_id;
		$mmeta_id	= $meta->mmeta_id;
		$track	= mysql_real_escape_string($meta->meta_value);

		$ip		= mysql_real_escape_string(trim($_SERVER['REMOTE_ADDR']));
		$agent	= mysql_real_escape_string(trim($_SERVER['HTTP_USER_AGENT']));
		$referrer   = mysql_real_escape_string(trim($_SERVER['HTTP_REFERER']));

		$open_mmeta_id 	= (MailPress_tracking_openedmail == $meta->meta_value) ? $mmeta_id : self::get_mmid($mail_id, MailPress_tracking_openedmail, MailPress_tracking_openedmail);
		$query 		= "SELECT count(*) FROM $wpdb->mp_tracks WHERE user_id = $mp_user_id AND mail_id = $mail_id AND mmeta_id = $open_mmeta_id ;";
		$opened_mail	= $wpdb->get_var($query);

		if ((MailPress_tracking_openedmail == $meta->meta_value) && ($opened_mail)) return;

		$query = "INSERT INTO $wpdb->mp_tracks (user_id, mail_id, mmeta_id, track, context, ip, agent, referrer, tmstp) VALUES ($mp_user_id, $mail_id, $mmeta_id, '$track', '$context', '$ip', '$agent', '$referrer', '$now');";
		$wpdb->query( $query );

		if (MailPress_tracking_openedmail == $meta->meta_value) $opened_mail = true;
		if ($opened_mail) return;

		$query = "INSERT INTO $wpdb->mp_tracks (user_id, mail_id, mmeta_id, track, context, ip, agent, referrer, tmstp) VALUES ($mp_user_id, $mail_id, $open_mmeta_id, '" . MailPress_tracking_openedmail . "', '$context', '$ip', '$agent', '$referrer', '$now');";
		$wpdb->query( $query );
	}
}
if (class_exists('MailPress'))
{
	define ('MP_MailPress_tracking_FOLDER', 	basename(dirname(__FILE__)));
	define ('MP_MailPress_tracking_PATH', 	'wp-content/plugins/' . MP_MailPress_tracking_FOLDER . '/' );
	define ('MP_MailPress_tracking_TMP', 	dirname(__FILE__));

	$MailPress_tracking = new MailPress_tracking();
}
?>