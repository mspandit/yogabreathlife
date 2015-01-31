<?php
/*
Plugin Name: MailPress_batch_send 
Plugin URI: http://www.mailpress.org
Description: This is just an addon for MailPress to send mail in batch mode.
Author: Andre Renaut
Requires at least: 2.7
Version: 3.0.1
Author URI: http://www.mailpress.org
*/
class MailPress_batch_send 
{
	function MailPress_batch_send ()
	{
		define ('MailPress_batch_send_metakey', 	'_MailPress_batch_send');
// install
		add_action('activate_' . MP_MailPress_batch_send_FOLDER . '/MailPress_batch_send.php',	array(&$this,'install'));
// for settings
		add_filter('plugin_action_links', 				array(&$this,plugin_action_links), 10, 2 );

		add_action('MailPress_register_scripts', 			array(&$this,'register_scripts'));
		add_filter('MailPress_enqueue_scripts',			array(&$this,'enqueue_scripts'),8,1);

		add_action('MailPress_settings_extraform_update', 	array(&$this,'update'));
		add_action('MailPress_settings_extraform_tab', 		array(&$this,'tab'),8,1);
		add_action('MailPress_settings_extraform_div', 		array(&$this,'div'));

// for batch mode
		$batch_send_config 	= get_option('MailPress_batch_send');
		if ('wpcron' == $batch_send_config['batch_mode'])	add_action('MailPress_batch_send_schedule', 	array(&$this,'schedule'));

// for batch processing
		add_filter('MailPress_batch_send_status',			array(&$this,'status'),8,1);
		add_filter('MailPress_swift_send',				array(&$this,'swift_send'),8,2);
		add_action('mp_action_batchsend',				array(&$this,'process'));
	}

	function install() 
	{
		include ( MP_MailPress_batch_send_TMP . '/mp-admin/includes/install.php');
	}

	function plugin_action_links($links, $file)
	{
		static $this_plugin;
		if( !$this_plugin ) $this_plugin = plugin_basename(__FILE__);

		if( $file == $this_plugin ){
			$settings_link = '<a href="' . MailPress_settings . '#fragment-MailPress_batch_send">' . __('Settings') . '</a>';
			array_unshift ($links, $settings_link);
		}
		return $links;
	}

// for settings

	function register_scripts($x)
	{
		wp_register_script( 'mp-batchsend', 	'/' . MP_MailPress_batch_send_PATH . 'mp-admin/js/settings.js', array(), false, 1);
	}

	function enqueue_scripts($x)
	{
		$x[MailPress_page_settings][] = 'mp-batchsend';
		return $x;
	}

	function update()
	{
		if ($_POST['formname'] != 'batch_send_form') return;

		global $mp_general, $mp_tab;

		$mp_general['tab']	= $mp_tab =  'MailPress_batch_send';

		$batch_send	= $_POST['batch_send'];

		if (!add_option ('MailPress_batch_send', $batch_send, 'MailPress - batch_send config' )) update_option ('MailPress_batch_send', $batch_send);
		if (!add_option ('MailPress_general', $mp_general, 'MailPress - general settings' )) update_option ('MailPress_general', $mp_general);

		MP_Admin::message(__("'Batch' settings saved",'MailPress'));
	}

	function tab($tab)
	{
?>
			<li <?php if ($tab=='MailPress_batch_send') echo " class='ui-tabs-selected'"; ?>><a href='#fragment-MailPress_batch_send'><span class='button-secondary'><?php _e('Batch'    ,'MailPress'); ?></span></a></li>
<?php
	}

	function div()
	{
		include (MP_MailPress_batch_send_TMP . '/mp-admin/includes/settings.php');
	}

// for batch mode

	function schedule()
	{
		$batch_send_config = get_option('MailPress_batch_send');

		if ('wpcron' == $batch_send_config['batch_mode']) 
			if (!wp_next_scheduled( 'mp_action_batchsend' )) 
				wp_schedule_single_event(time()+$batch_send_config['every'], 'mp_action_batchsend');
	}

// for batch processing

	function status($x=false)
	{
		return 'unsent';
	}

	function swift_send($rc,$MP_Mail)
	{
		if ('MailPress_batch_send' == $MP_Mail->batch)
		{
			require_once MP_TMP . '/mp-includes/class/swift/Swift/Plugin/Decorator.php';
			$MP_Mail->swift->attachPlugin(new Swift_Plugin_Decorator($MP_Mail->row->toemail), 'decorator');

			$batch =& new Swift_BatchMailer($MP_Mail->swift);
			if (!$batch->send($MP_Mail->message, $MP_Mail->to , $MP_Mail->from))
			{
				$MP_Mail->swift->disconnect();
				return false;
			}
			return $batch->getFailedRecipients();
		}
		return $rc;
	}

	function process()
	{
		if (function_exists('ignore_user_abort')) 	ignore_user_abort(1);
		if (function_exists('set_time_limit')) 		if( !ini_get('safe_mode') ) set_time_limit(0);

		self::update_batch_env();

		extract(self::select_mail());

		if ($send) 	self::batch($mail, $mailmetas);
		else		self::alldone();

		self::update_batch_env(!$send);
	}

	function update_batch_env($done=true)
	{
		global $wpdb;
		$status 			= self::status();
		$batch_send_config 	= get_option('MailPress_batch_send');
		
		$query = "SELECT id, toemail FROM $wpdb->mp_mails WHERE status = '$status' ;";
		$mails = $wpdb->get_results($query);

		if ($mails)
		{
			foreach ($mails as $mail)
			{
				$mailmetas 		= MP_Mailmeta::get( $mail->id ,MailPress_batch_send_metakey);
				if ($mailmetas)
				{
					switch (true)
					{
						case ($mailmetas['try']  == $mailmetas['max_try'] ) :
							self::update_mail($mail->id,count($mailmetas['failed']));
						break;
						case ($mailmetas['sent'] == $mailmetas['count']) :
							self::update_mail($mail->id,count($mailmetas['failed']));
						break;
					}
				}
				else
				{
					$mailmetas['per_pass'] 	= $batch_send_config['per_pass'];
					$mailmetas['max_try']	= $batch_send_config['max_retry'] + 1;
					$mailmetas['try'] 	= 0;
					$mailmetas['pass'] 	= 0;

					$mailmetas['processed'] = 0;
					$mailmetas['count'] 	= (unserialize($mail->toemail)) ? $count = count(unserialize($mail->toemail)) : 1;
					$mailmetas['sent'] 	= 0;
					$mailmetas['failed'] 	= array();
					MP_Mailmeta::update( $mail->id, MailPress_batch_send_metakey, $mailmetas );
				}		
			}
		}
		if (!$done) self::schedule();
	}

	function update_mail($id,$failed)
	{
		global $wpdb;
				
		$query = "UPDATE $wpdb->mp_mails SET status = 'sent' WHERE id = $id";
		$x = $wpdb->query( $query );
		if (!failed) MP_Mailmeta::delete( $id ,MailPress_batch_send_metakey);
	}

	function select_mail()
	{
		global $wpdb;
		$status 			= self::status();
		$send = $mail = $mailmetas = false;
		$current_mail = '';

		$query = "SELECT * FROM $wpdb->mp_mails WHERE status = '$status' ;";
		$mails = $wpdb->get_results($query);

		if ($mails)
		{
			foreach ($mails as $mail)
			{
				$mailmetas 		= MP_Mailmeta::get( $mail->id ,MailPress_batch_send_metakey);

				if ($mailmetas['count'] == $mailmetas['sent']) continue;

				$send = true;

				if (empty($current_mail))
				{
					$current_mail 	= $mail;
					$current_mailmetas= $mailmetas;
				}

				if ($mailmetas['try'] < $current_mailmetas['try'])
				{
					$current_mail 	= $mail;
					$current_mailmetas= $mailmetas;
				}
			}
			$mail 	= $current_mail;
			$mailmetas	= $current_mailmetas;
		}
		return array('mail' => $mail, 'mailmetas' => $mailmetas, 'send' => $send);
	}

	function logging($x) {
		$y = false;
		$z = '';
		foreach ($x as $k => $v)
		{
			if ($y) $z .=', ';
			if ($k != 'failed') 	$z .= " $k : $v";
			else				$z .= " $k : " . count($v);
			$y = true;
		}
		return $z;
	}

	function batch($mail, $mailmetas)
	{
		$rc = true;

// MP_Log
$trace = new MP_Log('mp_action_batchsend',ABSPATH . MP_PATH,MP_MailPress_batch_send_FOLDER,false,'MailPress_batch_send');

$trace->log('');
$x = "*** START OF PROCESSING *** recipients mail : $mail->id";
$trace->log($x);
$x = self::logging($mailmetas);
$trace->log($x);

		$recipients = unserialize($mail->toemail);
		$batch = 'MailPress_batch_send';

		if ($mailmetas['sent'] != $mailmetas['count'])
		{
			if (0 == $mailmetas['try'])
			{
				$offset 	= $mailmetas['pass'] * $mailmetas['per_pass'];
				$length 	= $mailmetas['per_pass'];

				$toemail 	= array_slice($recipients,$offset,$length,true);
				$mailmetas['processed'] += count($toemail);
			}
			else
			{
				$offset 	= (isset($mailmetas['offset'])) ? $mailmetas['offset'] : 0;
				$length 	= $mailmetas['per_pass'];

				$j = 0;
				$i = 0;

				if (count($mailmetas['failed']) > 0)
				{
					foreach ($mailmetas['failed'] as $k => $v)
					{
						$i++;
						if ($i < $offset) continue;
						if ($j < $length)
						{
							$toemail[$k] = $recipients [$k];
							$j++;
						}
						else break;
					}
				}
				else	$toemail 	= array_slice($recipients,$offset,$length,true);

				$mailmetas['processed'] = $mailmetas['sent'] + $offset + count($toemail);
			}

			if (0 == count($toemail))
			{
				$mailmetas['try']++;
				$mailmetas['processed'] = 0;
				$mailmetas['pass'] = 0;
				$mailmetas['offset'] = 0;

$trace->log(self::logging(array_merge(array(">> WARNING >>" => 'No more recipient','start offset'=>$offset,'start length'=>$length),$mailmetas)));
/*
$x = self::logging(array(">> WARNING >>" => 'No more recipient','start offset'=>$offset,'start length'=>$length));
$trace->log($x);
$x = self::logging($mailmetas);
$trace->log($x);
*/
			}
			else
			{
$trace->log(self::logging(array_merge(array(">> PROCESSING >>" => '','start offset' => $offset, 'start length' => $length) ,$mailmetas)));
/*
$x = self::logging( array(">> PROCESSING >>" => '','start offset' => $offset, 'start length' => $length) );
$trace->log($x);
$x = self::logging($mailmetas);
$trace->log($x,$trace->levels[512]);
*/

				$w_mail = new MP_Mail(MP_MailPress_batch_send_FOLDER);
				$w_mail->trace = $trace;
				$w_mail->batch = $batch;
				$w_mail->row   = $mail;
				$w_mail->row->toemail = $toemail;

				$swiftfailed = $w_mail->swift_processing();

				if (!is_array($swiftfailed))
				{
					$rc = $swiftfailed;
					$swiftfailed = array();
				}

				if ($rc)
				{
					$failed = $successfull = array();
					foreach ($swiftfailed as $k) $failed[$k] = null;
					$successfull = array_diff_key($toemail,$failed);

					foreach ($successfull as $k => $v) 
					{
						unset($mailmetas['failed'][$k]);
						$mailmetas['sent']++ ;
					}
					foreach ($failed as $k) 
					{
						if (!isset($mailmetas['failed'][$k])) $mailmetas['failed'][$k] = null;
						if (0 != $mailmetas['try']) $mailmetas['offset']++;
					}
				}
			}
			$mailmetas['pass']++;
			if ($mailmetas['processed'] >= $mailmetas['count']) 
			{
				$mailmetas['try']++;
				$mailmetas['processed'] = 0;
				$mailmetas['pass'] = 0;
				$mailmetas['offset'] = 0;
			}
		}

		if ($mailmetas['sent'] == $mailmetas['count']) $mailmetas['try'] = $mailmetas['max_try'];

		MP_Mailmeta::update( $mail->id, MailPress_batch_send_metakey, $mailmetas );

$x = "*** END OF PROCESSING *** recipients mail : $mail->id";
$trace->log($x);
$x = self::logging($mailmetas);
$trace->log($x);
$trace->log('');

// MP_Log
$trace->end($rc);
	}

	function alldone()
	{
// MP_Log
$trace = new MP_Log('mp_action_batchsend',ABSPATH . MP_PATH,MP_MailPress_batch_send_FOLDER,false,'MailPress_batch_send');
$trace->log("::: ALL MAIL PROCESSED :::");
// MP_Log
$trace->end(true);
	}
}
if (class_exists('MailPress'))
{
	define ('MP_MailPress_batch_send_FOLDER', 	basename(dirname(__FILE__)));
	define ('MP_MailPress_batch_send_PATH', 		'wp-content/plugins/' . MP_MailPress_batch_send_FOLDER . '/' );
	define ('MP_MailPress_batch_send_TMP', 		dirname(__FILE__));

	$MailPress_batch_send = new MailPress_batch_send();
}
?>