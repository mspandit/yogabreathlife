<?php
/*
Plugin Name: MailPress_extra_form_mail_new
Plugin URI: http://www.mailpress.org
Description: This is just an addon for MailPress to change the MailPress theme on MailPress Write panel
Author: Andre Renaut
Version: 3.0.1
Author URI: http://www.mailpress.org
*/

class MailPress_extra_form_mail_new
{
	function MailPress_extra_form_mail_new()
	{
		add_action('MailPress_mailnew_boxes',array(&$this,'mailnew_boxes'),8,2);
	}

	function mailnew_boxes($id, $mp_screen)
	{
		add_meta_box('extra_form_mail_new_theme', __('Change Theme','MailPress'), array(&$this,'change_theme_boxes'), $mp_screen, 'side', 'core');
	}

	function change_theme_boxes($draft)
	{
		$th = new MP_Themes();
		$themes = $th->themes;
		$current_theme = $themes[$th->current_theme]['Template']; 

		$xtheme = array();
		foreach ($themes as $theme)
		{
			if ( 'plaintext' == $theme['Template'] ) continue;

			$xtheme[$theme['Template']] = $theme['Template'];
		}
?>
	<p id='MailPress_extra_form_mail_new'>
<?php printf(__('Current theme is : %s','MailPress'),$current_theme); ?>
		<br class='clear' />
			<input type='hidden' name='CurrentTheme' value="<?php echo $current_theme; ?>" />
			<select name='Theme'>
<?php MP_Admin::select_option($xtheme,$current_theme);?>
			</select>
		<br class='clear' />
	</p>
<?php
	}
}
$MailPress_extra_form_mail_new = new MailPress_extra_form_mail_new();
?>