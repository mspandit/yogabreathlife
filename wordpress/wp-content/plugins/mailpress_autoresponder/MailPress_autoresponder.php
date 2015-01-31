<?php
/*
Plugin Name: MailPress_autoresponder
Plugin URI: http://www.mailpress.org
Description: This is just an addon for MailPress to manage autoresponders (based on wp-cron)
Author: Andre Renaut
Version: 3.0.1
Author URI: http://www.mailpress.org
*/

class MailPress_autoresponder
{
	function MailPress_autoresponder()
	{
		global $mp_general;

		define ('MailPress_page_autoresponders',	'mailpress_autoresponders');

// for taxonomy
		define ('MailPress_taxonomy_autoresponder', 'MailPress_autoresponder');
		register_taxonomy(MailPress_taxonomy_autoresponder,'MailPress_autoresponder',array('update_count_callback' => array(&$this,'_update_count')));
		add_filter('MailPress_autoresponder_add_slug', 		array(&$this,'add_slug'),8,2);
		add_filter('MailPress_autoresponder_remove_slug',	array(&$this,'remove_slug'),8,2);

// for init
		add_action('MailPress_init', 					array(&$this,'init'));

// for role & capabilities
		add_filter('MailPress_capabilities',  			array(&$this,'capabilities'),1,1);

// for javascript plugin conflicts
		add_filter('MailPress_deregister_scripts', 		array(&$this,'deregister_scripts'), 10, 1 );

// for tracking events to autorespond to
		include(MP_MailPress_autoresponder_TMP . "/mp-includes/options.php");
		$x = array();
		$autoresponders = self::get_all();
		foreach( $autoresponders as $autoresponder )
		{
			if (!isset($autoresponder->description['active'])) continue;
			$id = $autoresponder->description['event'];
			if (isset($mp_autoresponder_registered_events[$id]))
			{
				$event 	= $mp_autoresponder_registered_events[$id]['event'];
				$callback 	= $mp_autoresponder_registered_events[$id]['callback'];
				$x[$event] 	= $callback;
			}
		}
		foreach($x as $e => $c) add_action($e, $c, 8, 2);  // 2 arguments : mp_user_id, event

// for autoresponder
		add_action('mp_autoresponder_process',			array(&$this,'process'));
	}

// for autoresponders
	function &get_all($args = '')
	{
		$defaults = array('hide_empty' => 0, 'hierarchical' => 0, 'child_of' => '0', 'parent' => '');
		$args = wp_parse_args($args, $defaults);
		$autoresponders = get_terms(MailPress_taxonomy_autoresponder, $args);
		if (empty($autoresponders)) return array();
		foreach ($autoresponders as $k => $autoresponder) if (!is_array($autoresponders[$k]->description)) $autoresponders[$k]->description = unserialize($autoresponder->description);
		return $autoresponders;
	}

	function &get($term_id, $output = OBJECT, $filter = 'raw') 
	{
		$autoresponder = get_term($term_id, MailPress_taxonomy_autoresponder, $output, $filter);
		if ( is_wp_error( $autoresponder ) )	return false;
		if (!is_array($autoresponder->description)) $autoresponder->description = unserialize($autoresponder->description);
		return $autoresponder;
	}

	function get_all_by_mail_id($mail_id)
	{
		$_autoresponders = array();
		$autoresponders = self::get_all();

		foreach( $autoresponders as $autoresponder )
		{
			$metakey = '_MailPress_autoresponder_' . $autoresponder->term_id;
			$metadata = MP_Mailmeta::has($mail_id,$metakey);
			if ($metadata) foreach ($metadata as $entry) 	$_autoresponders[] = 	array('term_id' 	=> $autoresponder->term_id, 
																'mmeta_id' 	=> $entry['mmeta_id'], 
																'mail_id' 	=> $mail_id, 
																'schedule' 	=> $entry['meta_value']
															);
		}

		usort($_autoresponders,array('MailPress_autoresponder','usort'));
		return $_autoresponders;
	}

	function get_by_mail_id($mail_id, $term_id)
	{
		$_autoresponder = array();
		$autoresponder = self::get( $term_id);

		$metadata = MP_Mailmeta::has($mail->id,'_MailPress_autoresponder_' . $autoresponder->term_id);
		if ($metadata) foreach ($metadata as $entry) 		$_autoresponder = 	array('term_id' 	=> $term_id, 
																'mmeta_id' 	=> $entry['mmeta_id'], 
																'mail_id' 	=> $mail_id, 
																'schedule' 	=> $entry['meta_value']
															);

		usort($_autoresponder,array('MailPress_autoresponder','usort'));
		return $_autoresponder;
	}

	function get_by_mmeta_id($mid)
	{
		$entry = MP_Mailmeta::get_by_id( $mid );

		$term_id = str_replace('_MailPress_autoresponder_','',$entry->meta_key);

		return 												array('term_id' 	=> $term_id, 
																'mmeta_id' 	=> $entry->mmeta_id, 
																'mail_id' 	=> $entry->mail_id,
																'schedule' 	=> $entry->meta_value
															);
	}

	function get_all_mails($term_id)
	{
		global $wpdb;
		$query = "SELECT * FROM $wpdb->mp_mailmeta WHERE meta_key = '_MailPress_autoresponder_$term_id' ORDER BY meta_value;";
		$metadata = $wpdb->get_results($query);
		if (!$metadata) return array();
		foreach ($metadata as $entry) 				$_mails[] = 		array('term_id' 	=> $term_id, 
																'mmeta_id' 	=> $entry->mmeta_id,
																'mail_id' 	=> $entry->mail_id,
																'schedule' 	=> $entry->meta_value
															);
		return $_mails;
	}

	function mail_is($mail_id)
	{
		$autoresponders = self::get_all();

		foreach( $autoresponders as $autoresponder )
		{
			$metakey = '_MailPress_autoresponder_' . $autoresponder->term_id;
			$metadata = MP_Mailmeta::has($mail_id,$metakey);
			if ($metadata) return true;
		}
		return false;
	}

	function usort($x,$y)
	{
		$a = $x['term_id'] . '  ' . $x['schedule'];
		$b = $y['term_id'] . '  ' . $y['schedule'];

	    if ($a == $b) return 0;
	    return ($a < $b) ? -1 : 1;
	}

	function delete($term_id)
	{
		global $wpdb;

		wp_delete_term( $term_id, MailPress_taxonomy_autoresponder);

		$meta_key = '_MailPress_autoresponder_' . $term_id;

		$query = "DELETE FROM $wpdb->mp_mailmeta WHERE meta_key = '$meta_key';";
		$wpdb->query($query);
		$query = "DELETE FROM $wpdb->mp_usermeta WHERE meta_key = '$meta_key';";
		$wpdb->query($query);
	}

// for taxonomy
	function _update_count( $autoresponders )	{}
	function add_slug( $slug , $taxonomy = MailPress_taxonomy_autoresponder )
	{
		if ( MailPress_taxonomy_autoresponder != $taxonomy ) return $slug;
		return '_' . MailPress_taxonomy_autoresponder . '_' . $slug;
	}
	function remove_slug( $slug , $term_id = '' , $taxonomy = MailPress_taxonomy_autoresponder , $context = '' )
	{
		if ( MailPress_taxonomy_autoresponder != $taxonomy ) return $slug;
		return str_ireplace('_' . MailPress_taxonomy_autoresponder . '_','',$slug);
	}

// for plugin
	function init()
	{
		if (!current_user_can('MailPress_manage_autoresponders')) return;

		global $mp_general;
		$file= ($mp_general['menu']) ? 'admin.php' : 'tools.php';
		define ('MailPress_autoresponders', $file . '?page=' . MailPress_page_autoresponders);

// for admin
		add_action('MailPress_register_styles', 			array(&$this,'register_styles'));
		add_filter('MailPress_enqueue_styles',			array(&$this,'enqueue_styles'),8,1);
		add_action('MailPress_register_scripts',  		array(&$this,'register_scripts'));
		add_filter('MailPress_enqueue_scripts',			array(&$this,'enqueue_scripts'),8,1);

// for admin autoresponders
		add_action('MailPress_mp_redirect',  			array(&$this,'mp_redirect'),1,1);
		add_filter('MailPress_title',					array(&$this,'title'),8,1);
		add_action('MailPress_screen_meta',				array(&$this,'screen_meta'),8,2);
		add_filter('MailPress_screen_meta_screen',		array(&$this,'screen_meta_screen'),8,2);
		add_filter('MailPress_manage_columns_prefs',		array(&$this,'manage_list_columns'),8,1);

// for settings
		add_filter('plugin_action_links', 				array(&$this,plugin_action_links), 10, 2 );

		add_action('MailPress_settings_extraform_update', 	array(&$this,'update'));
		add_action('MailPress_settings_extraform_tab', 		array(&$this,'tab'),8,1);
		add_action('MailPress_settings_extraform_div', 		array(&$this,'div'));

// for meta box in write page
		add_action('MailPress_mailnew_boxes',			array(&$this,'mail_boxes'),8,2);

		add_action('mp_action_add-mailautoresponder',		array(&$this,'mp_action_add_mailautoresponder'));
		add_action('mp_action_delete-mailautoresponder',	array(&$this,'mp_action_delete_mailautoresponder'));

// for mails list
		add_action('MailPress_mails_list_icon', 			array(&$this,'mails_list_icon'),8,1);
		//add_action('MailPress_mails_list_status', 		array(&$this,'mails_list_status'),8,2);
	}

// for role & capabilities
	function mailpress_autoresponders() {include (MP_MailPress_autoresponder_TMP . '/mp-admin/autoresponders.php');}
	function capabilities($x) 
	{
		global $mp_general;
		$m = (isset($mp_general['menu'])) ? true : false;
		$pu = 'tools.php';

		$x['MailPress_manage_autoresponders'] = array(	'name'  => __('Autoresponders','MailPress'),
										'group' => 'mails',
										'menu'  => 99,

										'parent'		=> ($m) ? false : $pu,
										'page_title'	=> __('MailPress Autoresponders','MailPress'),
										'menu_title'   	=> __('Autoresponders','MailPress'),
										'page'  		=> MailPress_page_autoresponders,
										'func'  		=> array(&$this,MailPress_page_autoresponders)
									);
		return $x;
	}

// for admin

	function register_styles() 
	{
		wp_register_style ( MailPress_page_autoresponders, 	get_option('siteurl') . '/' . MP_MailPress_autoresponder_PATH . 'mp-admin/css/autoresponders.css', array('thickbox'), false, 1);
		wp_register_style ( 'MailPress_autoresponder', 		get_option('siteurl') . '/' . MP_MailPress_autoresponder_PATH . 'mp-admin/css/mail_new.css', array(), false, 1);
	}

 	function enqueue_styles($x) 
	{
		$x [MailPress_page_autoresponders][] 	= MailPress_page_autoresponders;
		$x [MailPress_page_write][] 			= 'MailPress_autoresponder';
		return $x;
	}

	function register_scripts() 
	{
		global $mp_screen;
		
		wp_register_script( MailPress_page_autoresponders,      '/' . MP_MailPress_autoresponder_PATH . 'mp-admin/js/autoresponders.js', array('mp-lists','thickbox'), false, 1);
		wp_localize_script( MailPress_page_autoresponders, 	'adminautorespondersL10n', array('pending' => __('%i% pending'),
																   'screen'  => $mp_screen ) );
		wp_register_script( 'mp-mail-autoresponders', '/' . MP_MailPress_autoresponder_PATH . 'mp-admin/js/mail_new.js', array('mp-lists'), false, 1);
	}

	function enqueue_scripts($x)
	{
		$x[MailPress_page_autoresponders][]  = MailPress_page_autoresponders ;
		$x[MailPress_page_write][] = 'mp-mail-autoresponders';
		return $x;
	}

	function deregister_scripts($x)
	{
		$x[] = MailPress_page_autoresponders;
		return $x;
	}

// for settings
	function plugin_action_links($links, $file)
	{
		static $this_plugin;
		if( !$this_plugin ) $this_plugin = plugin_basename(__FILE__);

		if( $file == $this_plugin ){
			$settings_link = '<a href="' . MailPress_settings . '#fragment-MailPress_autoresponder">' . __('Settings') . '</a>';
			array_unshift ($links, $settings_link);
		}
		return $links;
	}

	function update()
	{
		if ($_POST['formname'] != 'autoresponder_form') return;

		global $mp_general, $mp_tab;

		$mp_general['tab']	= $mp_tab =  'MailPress_autoresponder';

		$autoresponder	= $_POST['autoresponder'];

		if (!add_option ('MailPress_autoresponder', $autoresponder, 'MailPress - import config' )) update_option ('MailPress_autoresponder', $autoresponder);
		if (!add_option ('MailPress_general', $mp_general, 'MailPress - general settings' )) update_option ('MailPress_general', $mp_general);

		MP_Admin::message(__("'Autoresponder' settings saved",'MailPress'));
	}

	function tab($tab)
	{
?>
			<li <?php if ($tab=='MailPress_autoresponder') echo " class='ui-tabs-selected'"; ?>><a href='#fragment-MailPress_autoresponder'><span class='button-secondary'><?php echo(trim(__('Autoresponders'    ,'MailPress'))); ?></span></a></li>
<?php
	}

	function div()
	{
		include (MP_MailPress_autoresponder_TMP . '/mp-admin/includes/settings.php');
	}

// for admin autoresponders
	function mp_redirect($page) 
	{
		global $action;
		switch (true)
		{
			case (MailPress_page_autoresponders == $page) :

				wp_reset_vars( array('action') );

				if ( isset($_GET['deleteit']) ) $action = 'bulk-delete';
				switch($action) 
				{
					case 'add':
						$_POST['slug'] = apply_filters('MailPress_autoresponder_add_slug', (empty($_POST['slug'])) ? $_POST['name'] : $_POST['slug'], MailPress_taxonomy_autoresponder );
						$_POST['description'] = mysql_real_escape_string(serialize($_POST['autoresponder']['description']));

						$ret = wp_insert_term($_POST['name'], MailPress_taxonomy_autoresponder, $_POST);

						$x = MailPress_autoresponders . "&message=";
						$x   .= ( $ret && !is_wp_error( $ret ) ) ? 1 : 4;
						$x   .= '#add';
						wp_redirect($x);
						exit;
					break;

					case 'delete':
						$id = $_GET['id'];
						self::delete($id);
						wp_redirect(MailPress_autoresponders . '&message=2');
						exit;
					break;

					case 'bulk-delete':

						foreach ( (array) $_GET['delete_autoresponders'] as $id ) 
						{
							self::delete($id);
						}

						$location = MailPress_autoresponders ;
						if ( $referer = wp_get_referer() ) {
							if ( false !== strpos($referer, MailPress_autoresponders) )
								$location = $referer;
						}
						$location = add_query_arg('message', 6, $location);

						wp_redirect($location);
						exit;
					break;

					case 'edited':
						if (isset($_POST['cancel'])) 
						{
							unset($_GET['action']);
							wp_redirect(MailPress_autoresponders);
							exit;
						}
						else
						{
							$_POST['slug'] = apply_filters('MailPress_autoresponder_add_slug', (empty($_POST['slug'])) ? $_POST['name'] : $_POST['slug'], MailPress_taxonomy_autoresponder );
							$_POST['description'] = mysql_real_escape_string(serialize($_POST['autoresponder']['description']));
	
							$e = wp_update_term($_POST['id'],   MailPress_taxonomy_autoresponder, $_POST);
							$x = ( !is_wp_error($e)) ? 3 : 5 ;

							wp_redirect(MailPress_autoresponders . "&message=$x");
							exit;
						}
					break;

					default:

					if ( !empty($_GET['_wp_http_referer']) ) 
					{
						wp_redirect(remove_query_arg(array('_wp_http_referer', '_wpnonce'), stripslashes($_SERVER['REQUEST_URI'])));
						exit;
					}
				}
			break;
		}
	}

	function title($x)
	{
		$x[MailPress_page_autoresponders]     	= __('MailPress Autoresponders','MailPress');
		$x[MailPress_page_autoresponders . 'id']	= __('Edit Autoresponder','MailPress');
		return $x;
	}

	function screen_meta($page,$mp_screen)
	{
		switch ($page)
		{
			case MailPress_page_autoresponders :
				add_filter('manage_' . $mp_screen . '_columns',array('MailPress_autoresponder','manage_list_columns'));

				$help	= sprintf(__('<a href="%1$s" target="_blank">Documentation</a>','MailPress'),MailPress_help_url);
				$help	.= '<br/>' . sprintf(__('<a href="%1$s" target="_blank">Support Forum</a>','MailPress'),'http://groups.google.com/group/mailpress');
				add_contextual_help($mp_screen, $help);
			break;
		}
	}

	function screen_meta_screen($screen,$page)
	{
		global $mp_screen;

		$mp_screen = $screen;

		switch ($page)
		{
			case MailPress_page_autoresponders :
				$mp_screen = MailPress_page_autoresponders;
			break;
		}
		return $mp_screen;
	}

	function row( $autoresponder, $class = '', $page = 1 ) 
	{
		global $mp_screen;
		include(MP_MailPress_autoresponder_TMP . "/mp-includes/options.php");

		$page= ($page > 1) ? "&apage=$page" : "";

		$name = apply_filters( 'term_name', $autoresponder->name );
		$edit_link = MailPress_autoresponders .  "&amp;action=edit&amp;id=$autoresponder->term_id" . $page;
		$out = '';
		$out .= '<tr id="autoresponder-' . $autoresponder->term_id . '"' . $class . '>';

		$columns = self::manage_list_columns();
		$hidden = (array) get_user_option( "manage" . $mp_screen . "columnshidden" );

		foreach ( $columns as $column_name => $column_display_name ) 
		{
			$class = "class=\"$column_name column-$column_name\"";

			$style = '';
			if ( in_array($column_name, $hidden) )
				$style = ' style="display:none;"';

			$attributes = "$class$style";

			switch ($column_name) {
				case 'cb':
					if ($default_ID == $autoresponder->term_id )
						$out .= '<th scope="row" class="check-column"></th>';
					else
						$out .= '<th scope="row" class="check-column"> <input type="checkbox" name="delete_autoresponders[]" value="' . $autoresponder->term_id . '" /></th>';
					break;
				case 'name':
					$out .= '<td ' . $attributes . '><strong><a class="row-title" href="' . $edit_link . '" title="' . attribute_escape(sprintf(__('Edit "%s"'), $name)) . '">' . $name . '</a></strong><br />';
					$actions = array();
					$actions['edit'] = '<a href="' . $edit_link . '">' . __('Edit') . '</a>';
					if ($default_ID != $autoresponder->term_id )
						$actions['delete'] = "<a class='submitdelete' href='" . wp_nonce_url(MailPress_autoresponders .  "&amp;action=delete&amp;id=$autoresponder->term_id" . $page, 'delete-autoresponder_' . $autoresponder->term_id) . "' onclick=\"if ( confirm('" . js_escape(sprintf(__("You are about to delete this autoresponder '%s'\n 'Cancel' to stop, 'OK' to delete."), $name )) . "') ) { return true;}return false;\">" . __('Delete') . "</a>";
					$action_count = count($actions);
					$i = 0;
					$out .= '<div class="row-actions">';
					foreach ( $actions as $action => $link ) {
						++$i;
						( $i == $action_count ) ? $sep = '' : $sep = ' | ';
						$out .= "<span class='$action'>$link$sep</span>";
					}
					$out .= '</div>';
					$out .= '<div class="hidden" id="inline_' . $autoresponder->term_id . '">';
					$out .= '<div class="name">' . $autoresponder->name . '</div>';
					$out .= '<div class="slug">' . $autoresponder->slug . '</div></div></td>';
					break;
				case 'active':
					$x = (isset($autoresponder->description['active'])) ? __('Yes','MailPress') : __('No','MailPress');
					$out .= "<td $attributes>" . $x . "</td>";
					break;
				case 'desc':
					$out .= "<td $attributes>" . stripslashes($autoresponder->description['desc']) . "</td>";
					break;
				case 'event':
					$out .= "<td $attributes>" . $mp_autoresponder_registered_events[$autoresponder->description['event']]['desc'] . "</td>";
					break;
			}
		}
		$out .= '</tr>';

		return $out;
	}

	function manage_list_columns() 
	{
		$autoresponders_columns = array(	'cb' 		=> '<input type="checkbox" />',
								'name' 	=> __('Name','MailPress'),
								'active'	=> __('Active','MailPress'),
								'desc'	=> __('Description','MailPress'),
								'event' 	=> __('Event','MailPress'));
		return $autoresponders_columns;
	}

// for meta box in write page
	function mail_boxes($mail_id,$mp_screen)
	{
		add_meta_box('mp_mail_autoresponder', __('Autoresponders','MailPress'), array(&$this,'mp_mail_autoresponders_meta_box'), $mp_screen, 'normal', 'core');
	}

	function mp_mail_autoresponders_meta_box($mail)
	{
?>
<div id="postcustomstuff">
	<div id="ajar-response"></div>
<?php
$metadata = self::get_all_by_mail_id($mail->id);
mp_mail_list_autoresponders($metadata);
mp_mail_autoresponder_form();
?>
</div>
<?php
	}

	function mp_action_add_mailautoresponder()
	{
		if ( !current_user_can( 'MailPress_manage_autoresponders') )	die('-1');

		//check_ajax_referer( 'add-mailmeta' );

		$c = 0;
		$obj_id = (int) $_POST['mail_id'];
		if ($obj_id === 0) die('0');

		if ( isset($_POST['autoresponderselect']) || isset($_POST['autoresponder']['schedule']) ) 
		{
			if ( !$mid = self::add_meta( $obj_id ) ) 							die('0');

			$meta = MP_Mailmeta::get_by_id( $mid );
			$obj_id = (int) $meta->mail_id;
			$meta = get_object_vars( $meta );
			$x = new WP_Ajax_Response( array(
				'what' => 'mailautoresponder',
				'id' => $mid,
				'data' => mp_mail_list_autoresponder_row( self::get_by_mmeta_id($mid), $c ),
				'position' => 1,
				'supplemental' => array('mail_id' => $obj_id)
			) );
		}
		else
		{
			$mid   = (int) array_pop(array_keys($_POST['mailautoresponder']));
			$key   = '_MailPress_autoresponder_' . $_POST['mailautoresponder'][$mid]['key'];
			if (isset($_POST['mailautoresponder'][$mid]['value'])) foreach ($_POST['mailautoresponder'][$mid]['value'] as $k => $v) if ($v <10) $_POST['mailautoresponder'][$mid]['value'][$k] = '0' . $v;
			$value = implode('',$_POST['mailautoresponder'][$mid]['value']);

			if ( !$meta = MP_Mailmeta::get_by_id( $mid ) )			die('0');
			if ( !MP_Mailmeta::update_by_id($mid , $key, $value) )	die('1'); // We know meta exists; we also know it's unchanged (or DB error, in which case there are bigger problems).
			$meta = MP_Mailmeta::get_by_id( $mid );
			$x = new WP_Ajax_Response( array(
				'what' => 'mailautoresponder',
				'id' => $mid, 'old_id' => $mid,
				'data' => mp_mail_list_autoresponder_row( self::get_by_mmeta_id($mid), $c ),
				'position' => 0,
				'supplemental' => array('mail_id' => $meta->mail_id)
			) );
		}
		$x->send();
	}

	function add_meta($mail_id)
	{
		$mail_id = (int) $mail_id;
		if (isset($_POST['autoresponder']['schedule'])) foreach ($_POST['autoresponder']['schedule'] as $k => $v) if ($v <10) $_POST['autoresponder']['schedule'][$k] = '0' . $v;

		$metakey 	= isset($_POST['autoresponderselect']) ? '_MailPress_autoresponder_' . trim( $_POST['autoresponderselect'] ) : '';
		$metavalue 	= isset($_POST['autoresponder']['schedule']) ? implode('',$_POST['autoresponder']['schedule']) : '';

		if ( !empty($metavalue)  && !empty ($metakey) ) 
		{
			// We have a key/value pair. If both the select and the
			// input for the key have data, the input takes precedence:

			return MP_Mailmeta::add( $mail_id, $metakey, $metavalue );
		}
		return false;
	}

	function mp_action_delete_mailautoresponder()
	{
		if ( !current_user_can( 'MailPress_manage_autoresponders') )			die('-1');

		$id = isset($_POST['id'])? (int) $_POST['id'] : 0;
		//check_ajax_referer( "delete-mailmeta_$id" );

		if ( !$meta = MP_Mailmeta::get_by_id( $id ) ) 				die('1');
		if ( MP_Mailmeta::delete_by_id( $meta->mmeta_id ) )	die('1');
		die('0');
	}

// for mails list
	function mails_list_icon($mail_id)
	{
		if (!self::mail_is($mail_id)) return;
?>
			<img class='attach' alt="<?php _e('Autoresponder','MailPress'); ?>" title="<?php _e('Autoresponder','MailPress'); ?>" src='<?php echo get_option('siteurl') . '/' . MP_MailPress_autoresponder_PATH; ?>/mp-includes/images/autoresponder.png' />
<?php
	}

	function mails_list_status($s, $mail_id)
	{
		if (self::mail_is($mail_id)) return $s . ' + @';
		return $s;
	}

// for tracking events to autorespond to
	function start_user_autoresponder($mp_user_id, $event)
	{
		$x = array();
		include(MP_MailPress_autoresponder_TMP . "/mp-includes/options.php");
		$autoresponders = self::get_all();

		foreach( $autoresponders as $autoresponder )
		{
			if (!isset($autoresponder->description['active'])) continue;
			foreach($mp_autoresponders_by_event[$event] as $k => $v)
			{
				if ($k != $autoresponder->description['event']) continue;
				$_mails = self::get_all_mails($autoresponder->term_id);

				if (isset($_mails[0]))
				{
					$term_id = $autoresponder->term_id;

					$time = time();
					$schedule = self::schedule($time,$_mails[0]['schedule']);
					$umeta_id = MP_Usermeta::add($mp_user_id, '_MailPress_autoresponder_' . $term_id, $time);

					$trace = new MP_Log('MP_Autoresponder_' . $term_id,ABSPATH . MP_PATH,MP_MailPress_autoresponder_FOLDER,false,'MailPress_autoresponder');
					$trace->log("*** START PROCESSING *** event : $event, mp_user_id : $mp_user_id");

					wp_schedule_single_event($schedule, 'mp_autoresponder_process', 	array('args' => array('umeta_id' => $umeta_id, 'mail_order'=> 0 )));

					$trace->log("***                  *** autoresponder_id : $term_id, umeta_id : $umeta_id, mail_order : 0");
					$trace->log("***                  *** first mail scheduled on : " . date('Y-m-d H:i:s',$schedule) );
					$trace->log("***  END PROCESSING  ***");
					$trace->end(true);
				}
			}
		}
	}

	function schedule($time,$schedule)
	{
		$Y = date('Y',$time);
		$M = date('n',$time) + substr($schedule,0,2);
		$D = date('j',$time) + substr($schedule,2,2);
		$H = date('G',$time) + substr($schedule,4,2);
		$Mn =  date('i',$time);
		$S =  date('s',$time);
		$U =  date('u',$time);

		return mktime($H,$Mn,$S,$M,$D,$Y);
	}

	function process($args)
	{
		if (function_exists('ignore_user_abort')) 	ignore_user_abort(1);
		if (function_exists('set_time_limit')) 		if( !ini_get('safe_mode') ) set_time_limit(0);

		extract($args);		// $umeta_id, $mail_order

		$meta = MP_Usermeta::get_by_id($umeta_id);
		$term_id 	= (!$meta) ? 'default' : str_replace('_MailPress_autoresponder_','',$meta->meta_key);

		$trace = new MP_Log('MP_Autoresponder_' . $term_id,ABSPATH . MP_PATH,MP_MailPress_autoresponder_FOLDER,false,'MailPress_autoresponder');
		$trace->log("*** START PROCESSING *** umeta_id : $umeta_id, mail_order : $mail_order");
		$trace->end(self::send($args, $trace));
	}

	function send($args, $trace)
	{
		if (function_exists('ignore_user_abort')) 	ignore_user_abort(1);
		if (function_exists('set_time_limit')) 		if( !ini_get('safe_mode') ) set_time_limit(0);

		extract($args);		// $umeta_id, $mail_order

		$meta = MP_Usermeta::get_by_id($umeta_id);
		if (!$meta)
		{
			$trace->log("***      WARNING     *** Unable to read table usermeta for id : $umeta_id");
			$trace->log("***  END PROCESSING  ***");
			return false;
		}

		$mp_user_id = $meta->user_id;
		$term_id 	= str_replace('_MailPress_autoresponder_','',$meta->meta_key);
		$time		= $meta->meta_value;

		$trace->log("***                  *** autoresponder_id : $term_id, mp_user_id : $mp_user_id");

		$autoresponder = self::get($term_id);
		if (!isset($autoresponder->description['active']))
		{
			$trace->log("***      WARNING     *** Autoresponder :  $term_id is inactive");
			$trace->log("***  END PROCESSING  ***");
			return false;
		}

		$mp_user = MP_User::get($mp_user_id);
		if (!$mp_user)
		{
			$trace->log("***      WARNING     *** mp_user_id : $mp_user_id is not found");
			$trace->log("***  END PROCESSING  ***");
			return false;
		}

		$_mails = self::get_all_mails($term_id);
		if (!$_mails)
		{
			$trace->log("***      WARNING     *** Autoresponder :  $term_id has no mails");
			$trace->log("***  END PROCESSING  ***");
			return false;
		}
		if (!isset($_mails[$mail_order]))
		{
			$trace->log("***      WARNING     *** mail_order : $mail_order NOT in mails to be processed");
			$trace->log("***  END PROCESSING  ***");
			return false;
		}

		$_mail = $_mails[$mail_order];

		$draft = MP_Mail::get($_mail['mail_id']);
		if (!$draft)
		{
			$trace->log("***        INFO      *** mail_id : " . $_mail['mail_id'] . " NOT in mail table, skip to next mail/schedule if any");
		}

		if (!MP_Mail::send_draft($_mail['mail_id'],false,$mp_user->email,MP_Mail::display_name($mp_user->email)))
		{
			$trace->log("***        INFO      *** Sending mail_id : " . $_mail['mail_id'] . " failed, skip to next mail/schedule if any");
		}
		else
		{
			$trace->log("***        INFO      *** Sending mail_id : " . $_mail['mail_id'] . " successfull !");
		}

		$mail_order++;
		if (!isset($_mails[$mail_order]))
		{
			$trace->log("***  END PROCESSING  *** last mail processed");
			return true;
		}

		$schedule = self::schedule($time,$_mails[$mail_order]['schedule']);
		wp_schedule_single_event($schedule, 'mp_autoresponder_process', 				array('args' => array('umeta_id' => $umeta_id, 'mail_order'=> $mail_order)));
		$trace->log("***  END PROCESSING  *** next mail to be processed : $mail_order scheduled on : " . date('Y-m-d H:i:s',$schedule) );
		return true;
	}
}

define ('MP_MailPress_autoresponder_FOLDER', 	basename(dirname(__FILE__)));
define ('MP_MailPress_autoresponder_PATH', 	'wp-content/plugins/' . MP_MailPress_autoresponder_FOLDER . '/' );
define ('MP_MailPress_autoresponder_TMP', 	dirname(__FILE__));

require MP_MailPress_autoresponder_TMP . "/mp-admin/includes/template.php";

$MailPress_autoresponder = new MailPress_autoresponder();
?>