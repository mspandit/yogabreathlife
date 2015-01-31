<?php
/*
Plugin Name: MailPress_sync_wordpress_user
Plugin URI: http://www.mailpress.org
Description: This is just an add-on for MailPress to synchronise with WordPress users
Author: Andre Renaut
Version: 3.0.1
Author URI: http://www.mailpress.org
*/

class MailPress_sync_wordpress_user
{
	function MailPress_sync_wordpress_user()
	{
		define ('MailPress_page_subscriptions',	'mailpress_subscriptions');
// for plugin
		add_action('activate_' . basename(dirname(__FILE__)) . '/MailPress_sync_wordpress_user.php',	array(&$this,'init'));
		add_filter('plugin_action_links', 				array(&$this,plugin_action_links), 10, 2 );
// for role & capabilities
		add_filter('MailPress_capabilities',  			array(&$this,'capabilities'),1,1);
		if (!class_exists('MailPress_roles_and_capabilities'))add_action('MailPress_roles_and_capabilities', 	array(&$this,'roles_and_capabilities'));
// for settings
		add_action('MailPress_settings_extraform_update', 	array(&$this,'settings_update'));
		add_action('MailPress_settings_extraform_tab', 		array(&$this,'tab'),8,1);
		add_action('MailPress_settings_extraform_div', 		array(&$this,'div'));

// registering user
		add_action('user_register',  					array(&$this,'register'),1,1);
// editing user
		add_action('profile_update',  				array(&$this,'update'),1,1);
// deleting user
		add_action('delete_user',  					array(&$this,'delete'),1,1);
// for register form
		$settings = get_option('MailPress_sync_wordpress_user');
		if (isset($settings['register_form']))			add_action('register_form', 				array(&$this,'register_form'));

// for mp_user
		add_action('MailPress_register_styles', 			array(&$this,'register_styles'));
		add_filter('MailPress_enqueue_styles',			array(&$this,'enqueue_styles'),8,1);
		add_action('MailPress_user_boxes',  			array(&$this,'user_boxes'),1,2); 

		add_action('MailPress_insert_user',  			array(&$this,'mp_insert_user'),1,1);
		add_action('MailPress_delete_user',  			array(&$this,'mp_delete_user'),1,1);

// for javascript plugin conflicts
		add_filter('MailPress_deregister_scripts', array(&$this,'deregister_scripts'), 10, 1 );
	}

// for plugin
	function init()
	{
		$users = self::get_wp_users();
		if ($users) foreach($users as $user) self::sync($user);
	}

	function plugin_action_links($links, $file)
	{
		static $this_plugin;
		if( !$this_plugin ) $this_plugin = plugin_basename(__FILE__);

		if( $file == $this_plugin ){
			$settings_link = '<a href="' . MailPress_settings . '#fragment-MailPress_sync_wordpress_user">' . __('Settings') . '</a>';
			array_unshift ($links, $settings_link);
		}
		return $links;
	}

// for role & capabilities

	function mailpress_subscriptions() { include (MP_MailPress_sync_wordpress_user_TMP . '/mp-admin/subscriptions.php'); }

	function capabilities($x) 
	{
		$pu = ( current_user_can('edit_users') ) ? 'users.php' : 'profile.php';

		$x['MailPress_manage_subscriptions'] = array(	'name'  => __('Your Subscriptions','MailPress'),
										'group' => 'admin',
										'menu'  => 33,
	
										'parent'		=> $pu,
										'page_title'	=> __('MailPress - Subscriptions','MailPress'),
										'menu_title'   	=> __('Your Subscriptions','MailPress'),
										'page'  		=> MailPress_page_subscriptions,
										'func'  		=> array(&$this,MailPress_page_subscriptions)
									);
		return $x;
	}

	function roles_and_capabilities()
	{
		global $wp_roles;
		foreach($wp_roles->role_names as $role => $name)
		{
			if ('administrator' == $role) continue;
			$r = get_role($role);
			$r->add_cap('MailPress_manage_subscriptions');
		}
	}

// for settings

	function settings_update()
	{
		if ($_POST['formname'] != 'sync_wordpress_user_form') return;

		global $mp_general, $mp_tab;

		$mp_general['tab']	= $mp_tab =  'MailPress_sync_wordpress_user';

		$sync_wordpress_user	= $_POST['sync_wordpress_user'];

		if (!add_option ('MailPress_sync_wordpress_user', $sync_wordpress_user, 'MailPress - sync_wordpress_user config' )) update_option ('MailPress_sync_wordpress_user', $sync_wordpress_user);
		if (!add_option ('MailPress_general', $mp_general, 'MailPress - general settings' )) update_option ('MailPress_general', $mp_general);

		MP_Admin::message(__("'Sync WordPress user' settings saved",'MailPress'));
	}

	function tab($tab)
	{
?>
			<li <?php if ($tab=='MailPress_sync_wordpress_user') echo " class='ui-tabs-selected'"; ?>><a href='#fragment-MailPress_sync_wordpress_user'><span class='button-secondary'><?php _e('Sync WP user'    ,'MailPress'); ?></span></a></li>
<?php
	}

	function div()
	{
		include (MP_MailPress_sync_wordpress_user_TMP . '/mp-admin/includes/settings.php');
	}

// registering user

	function register($wp_user_id)
	{
		$wp_user = self::get_wp_user($wp_user_id);
		if ($wp_user) self::sync($wp_user);

		$settings = get_option('MailPress_sync_wordpress_user');
		if (isset($settings['register_form']))
		{
			$user 	= get_userdata($wp_user_id);
			$email 	= $user->user_email;
			$mp_user_id	= MP_User::get_id_by_email($email);

			MP_Newsletter::update_mp_user_newsletters($mp_user_id);
		}
	}

// editing user

	function update($wp_user_id)
	{
		$wp_user = self::get_wp_user($wp_user_id);
		if ($wp_user)
		{
			$oldid = get_usermeta( $wp_user->ID, '_MailPress_sync_wordpress_user');
			if ($oldid)
			{
				$oldemail = MP_User::get_email($oldid);
				if ($oldemail == $wp_user->user_email) return true;
				else
				{
					self::sync($wp_user);
					$newid =  MP_User::get_id_by_email($wp_user->user_email);

					if (MP_User::has_subscribed_to_comments($oldid))	self::sync_comments($oldid,$newid);
					$count = self::count_emails($oldemail);
					if (0 == $count)							MP_User::delete($oldid);
				}
			}
			else
			{
				self::register($wp_user_id);
			}
		}
	}

// deleting user

	function delete($wp_user_id)
	{
		$wp_user = self::get_wp_user($wp_user_id);
		if ($wp_user) 
		{
			$id = get_usermeta( $wp_user->ID, '_MailPress_sync_wordpress_user');
			if ($id)
			{
				$email = MP_User::get_email($id);
				if ($email)
				{
					$count = self::count_emails($email);
					if ((1 == $count) && !MP_User::has_subscribed_to_comments($id)) MP_User::delete($id);
				}
			}
		}
		return true;
	}

// for register form

	function register_form()
	{
		if ($checklist_newsletters = MP_Newsletter::checklist_mp_user_newsletters())
		{
?>
	<p>
		<label>
			<?php _e('Newsletters','MailPress'); ?>
			<br />
			<span style='color:#777;font-weight:normal;'>
				<?php echo $checklist_newsletters; ?>
			</span>
		</label>
	</p>
<?php 
		}
		do_action('MailPress_register_form');
?>
	<br />
<?php
	}

// for mp_user

	function register_styles() 
	{
		wp_register_style ( 'MailPress_sync_wordpress_user', 	get_option('siteurl') . '/' . MP_MailPress_sync_wordpress_user_PATH . 'mp-admin/css/user.css' );
	}

 	function enqueue_styles($x) 
	{
		$x [MailPress_page_user][] = 'MailPress_sync_wordpress_user';
		return $x;
	}

	function user_boxes($mp_user_id,$mp_screen)
	{
		add_meta_box('syncwordpressdiv', __('WordPress sync','MailPress') , array(&$this,'meta_box'), $mp_screen, 'advanced', 'low');
	}

	function meta_box($mp_user)
	{
		$wp_users = self::get_wp_users_by_mp_user_id( $mp_user->id );
		if ($wp_users)
		{
?>
<div id="user-syncwordpress">
	<table class='form-table'>
<?php
			$header = true;
			foreach ($wp_users as $wp_user)
			{
				$wp_user = get_userdata($wp_user->ID);
				if (empty($wp_user->first_name) && empty($wp_user->last_name) && empty($wp_user->nickname)) continue;
?>
		<tr>
			<td style='border-bottom:none;padding:5px;' class='side-info-hide'>
				<label>
					<?php printf(__('WP User # %1$s','MailPress'), $wp_user->ID); ?>
				</label>
			</td>
			<td style='border-bottom:none;line-height:0.8em;padding:5px;'>
				<table>
<?php
			if ($header)
			{
				$header = false;
?>
					<tr>
						<td style='border-bottom:none;line-height:0.8em;padding:0px;' class='side-info-hide'>
<b><?php _e('First name') ?></b>
						</td>
						<td style='border-bottom:none;line-height:0.8em;padding:0px;' class='side-info-hide'>
<b><?php _e('Last name') ?></b>
						</td>
						<td style='border-bottom:none;line-height:0.8em;padding:0px;'>
<b><?php _e('Nickname') ?></b>
						</td>
					</tr>
<?php
			}
?>
					<tr>
						<td style='border-bottom:none;line-height:0.8em;padding:0px;' class='side-info-hide'>
							<input style='padding:3px;margin:0 10px 0 0;width:170px;' type='text' disabled='disabled' value="<?php echo $wp_user->first_name ?>" />
						</td>
						<td style='border-bottom:none;line-height:0.8em;padding:0px;' class='side-info-hide'>
							<input style='padding:3px;margin:0 10px 0 0;width:170px;' type='text' disabled='disabled' value="<?php echo $wp_user->last_name ?>" />
						</td>
						<td style='border-bottom:none;line-height:0.8em;padding:0px;'>
							<input style='padding:3px;margin:0 10px 0 0;width:170px;' type='text' disabled='disabled' value="<?php echo $wp_user->nickname ?>" />
						</td>
					</tr>
				</table>
			</td>
		</tr>
<?php
		}
?>
	</table>
</div>
<?php
		}
		else 
			printf(__('%1$s is not a WordPress user','MailPress'),$mp_user->email);
	}

	function mp_insert_user($mp_user_id)
	{
		$mp_email	= MP_User::get_email($mp_user_id);
		$wp_users  	= self::get_wp_users_by_email($mp_email);
		if (is_array($wp_users)) foreach ($wp_users as $wp_user) update_usermeta( $wp_user->ID, '_MailPress_sync_wordpress_user' , $mp_user_id);
	}

	function mp_delete_user($mp_user_id)
	{
		global $wpdb;
		$query = "DELETE FROM $wpdb->usermeta WHERE meta_key = '_MailPress_sync_wordpress_user' AND meta_value = '$mp_user_id';";
		$results = $wpdb->query( $query );
	}

// generic functions

	function get_wp_users()
	{
		global $wpdb;
		$query = "SELECT ID, user_email FROM $wpdb->users";
		return $wpdb->get_results( $query );
	}

	function get_wp_user($wp_user_id)
	{
		global $wpdb;
		return $wpdb->get_row($wpdb->prepare("SELECT ID, user_email FROM $wpdb->users WHERE ID = %d ", $wp_user_id));
	}

	function get_wp_users_by_mp_user_id($mp_user_id)
	{
		global $wpdb;
		$query = $wpdb->prepare("SELECT DISTINCT ID, user_email FROM $wpdb->users a, $wpdb->usermeta b WHERE a.ID = b.user_id AND b.meta_key = '_MailPress_sync_wordpress_user' AND b.meta_value = '%d'", $mp_user_id);
		return $wpdb->get_results( $query );
	}

	function get_wp_users_by_email($email)
	{
		global $wpdb;
		$email = trim($email);
		if (!MailPress::is_email($email)) return false;

		$query = "SELECT ID FROM $wpdb->users WHERE user_email = '$email'";
		return $wpdb->get_results( $query );
	}

	function count_emails($email)
	{
		global $wpdb;
		return $wpdb->get_var("SELECT count(*) FROM $wpdb->users WHERE user_email = '$email'");
	}

	function sync_comments($oldid,$newid)
	{
		global $wpdb;
		$query = "UPDATE $wpdb->postmeta SET meta_value = '$newid' WHERE meta_key = '_MailPress_subscribe_to_comments_' AND meta_value = '$oldid';";
		return $wpdb->query( $query );
	}

	function sync($wp_user)
	{

 // Already a MailPress user ?

		$id = get_usermeta( $wp_user->ID, '_MailPress_sync_wordpress_user');
		if ($id)
		{
			if (MP_User::get_email($id) == $wp_user->user_email) return true;
		}

// Mail already in MailPress table ?

		$id =  MP_User::get_id_by_email($wp_user->user_email);
		if ($id) 
		{
			update_usermeta( $wp_user->ID, '_MailPress_sync_wordpress_user' , $id );
			MP_User::set_status($id, 'active');
			return true;										  
		}

// so insert !

		return self::insert($wp_user);
	}

	function insert($wp_user, $type = 'activate')
	{
		if ( !MailPress::is_email($wp_user->user_email) )					return false; // not an email
		else
		{
			if ('activate' == $type) 
			{
			 	$key = md5(uniqid(rand(),1));	
				if (!MP_User::insert($wp_user->user_email,$key,'active'))	return false; // user not inserted
			}
			else
			{
				$return = MP_User::add($wp_user->user_email);
				if (!$return['result']) 						return false; // user not inserted
			}
		}
		$id = MP_User::get_id_by_email($wp_user->user_email);
		update_usermeta( $wp_user->ID, '_MailPress_sync_wordpress_user' , $id );
		return true;
	}


	function deregister_scripts($x)
	{
		$x[] = MailPress_page_subscriptions;
		return $x;
	}
}

define ('MP_MailPress_sync_wordpress_user_FOLDER', 	basename(dirname(__FILE__)));
define ('MP_MailPress_sync_wordpress_user_PATH', 	'wp-content/plugins/' . MP_MailPress_sync_wordpress_user_FOLDER . '/' );
define ('MP_MailPress_sync_wordpress_user_TMP', 	dirname(__FILE__));

$MailPress_sync_wordpress_user = new MailPress_sync_wordpress_user();
?>