<?php
/*
Plugin Name: MailPress_reset
Plugin URI: http://www.mailpress.org
Description: *** ACTIVATING this plugin WILL immediately DELETE ALL MailPress related DATA in the WordPress & MailPress tables! ***
Author: Andre Renaut
Version: 3.0.1
Author URI: http://www.mailpress.org
*/

class MailPress_reset
{
	function MailPress_reset()
	{
		global $wpdb;
// for mysql
		$wpdb->mp_users     = $wpdb->prefix . 'MailPress_users';
		$wpdb->mp_stats     = $wpdb->prefix . 'MailPress_stats';
		$wpdb->mp_mails     = $wpdb->prefix . 'MailPress_mails';
		$wpdb->mp_usermeta  = $wpdb->prefix . 'MailPress_usermeta';
		$wpdb->mp_mailmeta  = $wpdb->prefix . 'MailPress_mailmeta';
		$wpdb->mp_tracks    = $wpdb->prefix . 'mailpress_tracks';

// taxonomies
		$taxonomies = array('MailPress_mailing_list', 'MailPress_autoresponder');
		foreach($taxonomies as $taxonomy)
		{
			$queries[] = "DELETE FROM $wpdb->term_taxonomy WHERE term_taxonomy_id IN (SELECT term_taxonomy_id FROM $wpdb->term_taxonomy WHERE taxonomy = '$taxonomy');";
			$queries[] = "DELETE FROM $wpdb->term_relationships WHERE term_id IN (SELECT term_id FROM $wpdb->term_taxonomy WHERE taxonomy = '$taxonomy');";
			$queries[] = "DELETE FROM $wpdb->term_taxonomy WHERE taxonomy = '$taxonomy';";
		}
// postmeta
		$queries[] = "DELETE FROM $wpdb->postmeta WHERE meta_key like '%_MailPress%';";		
		$queries[] = "DELETE FROM $wpdb->postmeta WHERE meta_key like '%_mailpress%';";
// usermeta
		$queries[] = "DELETE FROM $wpdb->usermeta WHERE meta_key like '%_MailPress%';";		
		$queries[] = "DELETE FROM $wpdb->usermeta WHERE meta_key like '%_mailpress%';";
// options
		$queries[] = "DELETE FROM $wpdb->options WHERE option_name like 'MailPress%';";		
		$queries[] = "DELETE FROM $wpdb->options WHERE option_name like 'mailpress%';";
// mailpress tables
		$queries[] = "DELETE FROM $wpdb->mp_stats;";
		$queries[] = "DELETE FROM $wpdb->mp_mails;";
		$queries[] = "DELETE FROM $wpdb->mp_mailmeta;";		
		$queries[] = "DELETE FROM $wpdb->mp_users;";		
		$queries[] = "DELETE FROM $wpdb->mp_usermeta;";
		$queries[] = "DELETE FROM $wpdb->mp_tracks;";	

// erase logs
		$ftmplt = 'MP_Log_';
		$path = '../wp-content/plugins/mailpress/tmp';
		if (is_dir($path) && ($l = opendir($path))) 
		{
			while (($file = readdir($l)) !== false) 
			{
		      	switch (true)
				{
					case ($file  == '.') :
					break;
					case ($file  == '..') :
					break;
					case (strstr($file,$ftmplt)) :
						@unlink($path . '/' . $file);
					break;
				}
			}
			closedir($l);
		}
		foreach($queries as $query) $wpdb->query($query);
	}
}
$MailPress_reset = new MailPress_reset();
?>