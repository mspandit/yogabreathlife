<?php

add_action('plugins_loaded', 			array('MP_Widget','init'));
add_action('mp_action_add_user_fo',	array('MP_Widget','mp_action_add_user_fo'));

class MP_Widget
{
	function init()
	{
		if ( function_exists('register_sidebar_widget') && function_exists('register_widget_control') )
		{
			$widget_ops = array('classname' => 'MailPress_widget');
			$control_ops = array('width' => 400, 'height' => 300);
			wp_register_sidebar_widget('mailpress', 'MailPress', array('MP_Widget','widget'), $widget_ops );
			wp_register_widget_control('mailpress', 'MailPress', array('MP_Widget','widget_control'), $control_ops );
		}
	}

	public static function form_defaults($options=false) 
	{
		if (!$options) $options = array();

		if (isset($options['jq'])) 		// shortcode API not accepting Caps in attributes
		{
			$options['jQ'] = $options['jq']; 
			unset ( $options['jq'] );
		}

		$defaults = array(	'jQ' 			=> false, 
						'urlsubmgt' 	=> false,
						'txtbutton' 	=> __('Subscribe','MailPress'),
						'txtsubmgt' 	=> __('Manage your subscription','MailPress'),
						'txtloading'	=> __('Loading...','MailPress'),

						'txtfield' 		=> __('Your email','MailPress'),
						'txtwait'		=> __('Waiting for ...','MailPress'),
						'txtwaitconf' 	=> __('Waiting for your confirmation','MailPress'),
						'txtallready' 	=> __('You have already subscribed','MailPress'),
						'txtvalidemail' 	=> __('Enter a valid email !','MailPress'),
						'txterrconf' 	=> __('ERROR. resend confirmation email failed','MailPress'),
						'txtdberror' 	=> __('ERROR in the database : subscriber not inserted','MailPress'), 

						'txtsubcomment' 	=> __("Subscribe to comments on this post",'MailPress')
					);

		$defaults = apply_filters('MailPress_form_defaults',$defaults);
		$options  = wp_parse_args( $options, $defaults );
		$options  = apply_filters('MailPress_form_options',$options);
		return $options;
	}

	public static function get_wp_user_unsubscribe_url()
	{
		$url = false;
		$email = MailPress::get_wp_user_email();
		if (!empty($email)) if (MP_User::get_key_by_email($email)) $url = MP_User::get_unsubscribe_url(MP_User::get_key_by_email($email) );
		return $url;
	}

	public static function form($options=false) 
	{
		global $user_ID;
		$email = $message = $widget_title = '';
		if (!$options) $options = array();

		$options  = self::form_defaults($options);

		switch (true)
		{
			case (isset($_POST['MailPress_submit'])) :
// ajax not available !
				$bots_useragent = array('googlebot', 'google', 'msnbot', 'ia_archiver', 'lycos', 'jeeves', 'scooter', 'fast-webcrawler', 'slurp@inktomi', 'turnitinbot', 'technorati', 'yahoo', 'findexa', 'findlinks', 'gaisbo', 'zyborg', 'surveybot', 'bloglines', 'blogsearch', 'ubsub', 'syndic8', 'userland', 'gigabot', 'become.com');
				$useragent = $_SERVER['HTTP_USER_AGENT'];
				foreach ($bots_useragent as $bot) if (stristr($useragent, $bot) !== false) return false;				// goodbye bot !

				$email = ( isset($_POST['email']) ) ? $_POST['email'] : '';									//has the user entered an email 

				if ( '' == $email || $options['txtfield'] == $email ) 
				{																		// check for bot
					$message = "<span class='error'>" . $options['txtwait'] . "</span>";
					$email = $options['txtfield'];
				}
				else
				{
					$add = MP_User::add($email);
					$shortcode_message = apply_filters('MailPress_form_submit','',$email);
					$message = ($add['result']) ? "<span class='success'>" . $add['message'] . $shortcode_message . "</span><br />" : "<span class='error'>" . $add['message']  . $shortcode_message . "</span><br />";
					$email   = ($add['result']) ? $email : $options['txtfield'];
					if ($add['result']) do_action('MailPress_form_user_added',$email,$options);
				}
			break;
			case ($user_ID != 0 && is_numeric($user_ID) ) :
// user connected, so populate the email field if not already a subscriber !
				$user = get_userdata($user_ID);
				$email = $user->user_email;
				if ( MP_User::is_user($email,$user_ID) ) $email = ''; 
			break;
			default :
// user as already commented, so populate the email field if not already a subscriber !
				$email  = $_COOKIE['comment_author_email_' . COOKIEHASH];
				if ( MP_User::get_status_by_email($email) == 'active' ) $email='';
			break;
		}

		if ('' == $email) $email = $options['txtfield'];

?>
<!-- start of code generated by MailPress -->
<style type='text/css'> div#MailPress div#mp-container, div#MailPress div#mp-formdiv {position:relative;} div#MailPress div#mp-loading, div#MailPress div#mp-message {position:absolute;opacity:0;} div#MailPress div#mp-loading, div#MailPress div#mp-message {filter:alpha(opacity=0);}</style>

<?php if (!$options['jQ']) : ?><script type='text/javascript' src='<?php echo get_option('siteurl'); ?>/wp-includes/js/jquery/jquery.js?ver=1.2.3'></script><?php endif; ?>
<script type='text/javascript'> var mp_url = '<?php echo get_option('siteurl'); ?>/<?php echo MP_PATH; ?>mp-includes/action.php'; </script>
<script type='text/javascript' src='<?php echo get_option('siteurl'); ?>/<?php echo MP_PATH; ?>mp-includes/js/form.js'></script>
<div id='MailPress'>
	<div id='mp-container'>
		<div id='mp-message'></div>
		<div id='mp-loading'>
			<img src='<?php echo get_option('siteurl'); ?>/<?php echo MP_PATH; ?>mp-includes/images/loading.gif' alt='<?php  echo $options['txtloading']; ?>' title='<?php  echo $options['txtloading']; ?>' />
			<?php  echo $options['txtloading']; ?>
		</div>
		<div id='mp-formdiv'>
			<?php if ('' != $message) echo $message; ?>
			<form id='mp-form' method='post' action=''>
				<input 					type='hidden' 			name='action' 		value='add_user_fo' />
<!--				<input class='MailPressFormEmail' 	type='text'   			name='email'  		value='<?php echo $email; ?>' size='25' /> -->
				<input class='MailPressFormEmail' 	type='text'   			name='email'  		value="<?php echo $email; ?>" size='25' onfocus="if(this.value=='<?php echo js_escape($options['txtfield']); ?>') this.value='';" onblur="if(this.value=='') this.value='<?php echo js_escape($email); ?>';" />
<?php do_action('MailPress_form',$email,$options); ?>
				<input class='MailPressFormSubmit'	type='submit' id='mp_submit'  	name='MailPress_submit' value="<?php echo apply_filters('Mailpress_input_text',$options['txtbutton']); ?>" />
			</form>
		</div>
	</div>
<?php 
$url = ($options['urlsubmgt']) ? self::get_wp_user_unsubscribe_url() : false;
if ($url) :
?>
	<div id='mp-urlsubmgt'><a href='<?php echo $url; ?>'><?php echo apply_filters('Mailpress_input_text',$options['txtsubmgt']); ?></a></div>
<?php
endif;
?>
<?php do_action('MailPress_form_div_misc',$email,$options); ?>
</div>
<!-- end of code generated by MailPress -->
<?php
	}

	public static function mp_action_add_user_fo() {

		$bots_useragent = array('googlebot', 'google', 'msnbot', 'ia_archiver', 'lycos', 'jeeves', 'scooter', 'fast-webcrawler', 'slurp@inktomi', 'turnitinbot', 'technorati', 'yahoo', 'findexa', 'findlinks', 'gaisbo', 'zyborg', 'surveybot', 'bloglines', 'blogsearch', 'ubsub', 'syndic8', 'userland', 'gigabot', 'become.com');
		$useragent = $_SERVER['HTTP_USER_AGENT'];
		foreach ($bots_useragent as $bot) if (stristr($useragent, $bot) !== false) return false;				// goodbye bot !

		$defaults = self::form_defaults();
		$email = ( isset($_POST['email']) ) ? $_POST['email'] : '';									//has the user entered an email 

		if ( '' == $email || $defaults['txtfield'] == $email ) 
		{																		// check for bot
			$message = "<span class='error'>" . $defaults['txtwait'] . "</span>";
			$email = $defaults['txtfield'];
		}
		else
		{
			$add = MP_User::add($email);
			$shortcode_message = apply_filters('MailPress_form_submit','',$email);
			$message = ($add['result']) ? "<span class='success'>" . $add['message'] . $shortcode_message . "</span>" : "<span class='error'>" . $add['message'] . $shortcode_message . "</span>";
			$email   = ($add['result']) ? $email : $defaults['txtfield'];
			if ($add['result']) do_action('MailPress_form_user_added_ajax',$email,$options);
		}
		ob_end_clean();
		header('Content-Type: text/xml');
		echo "<?xml version='1.0' standalone='yes'?><wp_ajax><message><![CDATA[$message]]></message><email><![CDATA[$email]]></email></wp_ajax>";
		die();
	}

////	widget ////
	function widget($args) {
		extract($args);

		$options = get_option('MailPress_widget');

		$title = empty($options['title']) ? '' : apply_filters('widget_title', $options['title']);

		echo $before_widget;
		echo $before_title . stripslashes($title) . $after_title;
		self::form($options); 
		echo $after_widget;
	}

	function widget_control() {
		$options = $newoptions = get_option('MailPress_widget');
		if ( $_POST["MailPress-submit"] ) {
			$newoptions['title'] 		= $_POST["MailPress-title"];
			$newoptions['txtbutton'] 	= $_POST["MailPress-txtbutton"];
			$newoptions['txtsubmgt'] 	= $_POST["MailPress-txtsubmgt"];
			$newoptions['jQ']    		= $_POST["MailPress-jQ"];
			$newoptions['urlsubmgt']    	= $_POST["MailPress-urlsubmgt"];
			if ( empty($newoptions['title']) ) 		$newoptions['title'] = '';
			if ( empty($newoptions['txtbutton']) ) 	unset($newoptions['txtbutton']);
			if ( empty($newoptions['txtsubmgt']) ) 	unset($newoptions['txtsubmgt']);
			if ( empty($newoptions['jQ']) )    		$newoptions['jQ'] = false;
			if ( empty($newoptions['urlsubmgt']) )    $newoptions['urlsubmgt'] = false;
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			if (!update_option('MailPress_widget', $options)) add_option('MailPress_widget', $options);
		}

		$options = self::form_defaults($options);

?>
<script type="text/javascript">
	jQuery(document).ready( function() {
		jQuery('input#MailPress-urlsubmgt').click( function() {
			var checked = jQuery(this).attr('checked');
			if (!checked) jQuery('input#MailPress-txtsubmgt').addClass('hidden');
			else jQuery('input#MailPress-txtsubmgt').removeClass('hidden');
		})
	});
</script>
<p>
	<label for="MailPress-title">
		<?php _e('Title:'); ?> 
		<input class='widefat' id="MailPress-title" name="MailPress-title" type="text" value="<?php echo str_replace('"',"&QUOT;",stripslashes($options['title'])); ?>" />
	</label>
	<br /><br />
	<label for="MailPress-txtbutton">
		<?php _e('Button:'); ?> 
		<input class='widefat' id="MailPress-txtbutton" name="MailPress-txtbutton" type="text" value="<?php echo str_replace('"',"&QUOT;",stripslashes($options['txtbutton'])); ?>" />
	</label>
	<br /><br />
	<label for="MailPress-jQ">
		<input id="MailPress-jQ" name="MailPress-jQ" <?php checked($options['jQ'],true); ?> type="checkbox"> <?php _e('jQuery already loaded','MailPress'); ?> 
	</label>
	<br /><br />
	<label for="MailPress-urlsubmgt">
		<input id="MailPress-urlsubmgt" name="MailPress-urlsubmgt" <?php checked($options['urlsubmgt'],true); ?> type="checkbox"> <?php _e("\"Manage your subscription\" link ?",'MailPress'); ?>
	</label>
	<label for="MailPress-txtsubmgt">
		<input class='widefat<?php if(!$options['urlsubmgt']) echo ' hidden'; ?>' id="MailPress-txtsubmgt" name="MailPress-txtsubmgt" type="text" value="<?php echo str_replace('"',"&QUOT;",stripslashes($options['txtsubmgt'])); ?>" />
	</label>
</p>
<input type="hidden" id="MailPress-submit" name="MailPress-submit" value="1" />
<?php
	}
}
?>