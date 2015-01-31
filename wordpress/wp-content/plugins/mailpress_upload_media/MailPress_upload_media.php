<?php
/*
Plugin Name: MailPress_upload_media
Plugin URI: http://www.mailpress.org
Description: This is just an addon for MailPress to allow upload media button on MailPress Write
Author: Andre Renaut
Version: 3.0.1
Author URI: http://www.mailpress.org
*/

class MailPress_upload_media
{
	function MailPress_upload_media()
	{
		add_action('MailPress_register_scripts', 		array(&$this,'register_scripts'));
		add_filter('MailPress_enqueue_scripts',		array(&$this,'enqueue_scripts'),8,1);
		add_filter('MailPress_upload_media',		array(&$this,'upload_media'),8,1);
	}

	function register_scripts($x)
	{
		wp_register_script( 'mp-media-upload', 	'/' . MP_MailPress_upload_media_PATH . 'js/mail_new.js', array(), false, 1);
	}

	function enqueue_scripts($x)
	{
		$x[MailPress_page_write][] = 'mp-media-upload';
		$x[MailPress_page_write][] = 'media-upload';
		return $x;
	}

	function upload_media($x)
	{
		return true;
	}
}

define ('MP_MailPress_upload_media_FOLDER', 	basename(dirname(__FILE__)));
define ('MP_MailPress_upload_media_PATH', 	'wp-content/plugins/' . MP_MailPress_upload_media_FOLDER . '/' );

$MailPress_upload_media = new MailPress_upload_media();
?>