<?php
/*
Plugin Name: MailPress_mailing_lists
Plugin URI: http://www.mailpress.org
Description: This is just an addon for MailPress to manage mailing lists
Author: Andre Renaut
Version: 3.0.1
Author URI: http://www.mailpress.org
*/

class MailPress_mailing_lists
{
	function MailPress_mailing_lists()
	{
		global $mp_general;

		define ('MailPress_page_mailinglists',	'mailpress_mailinglists');

// for taxonomy
		global $wp_taxonomies;
		define ('MailPress_taxonomy_mailing_lists', 'MailPress_mailing_list');
		$wp_taxonomies[MailPress_taxonomy_mailing_lists] = (object) array('name' => MailPress_taxonomy_mailing_lists, 'object_type' => 'MailPress_user', 'hierarchical' => true , 'update_count_callback' => array(&$this,'_update_user_mailing_lists_count'));

		add_filter('pre_term_slug', 					array(&$this,'add_slug_MailPress_mailing_lists'),8,2);
		add_filter('edit_term_slug', 					array(&$this,'remove_slug_MailPress_mailing_lists'),8,2);
		add_filter('term_slug_rss', 					array(&$this,'remove_slug_MailPress_mailing_lists'),8,1);
		add_filter('term_slug', 					array(&$this,'remove_slug_MailPress_mailing_lists'),8,4);

// for plugin
		add_action('activate_' . MP_MailPress_mailing_lists_FOLDER . '/MailPress_mailing_lists.php',	array(&$this,'install'));

// for init
		add_action('MailPress_init', array(&$this,'init'));

// for settings on plugin page
		add_filter('plugin_action_links', 				array(&$this,plugin_action_links), 10, 2 );

// for role & capabilities
		add_filter('MailPress_capabilities',  			array(&$this,'capabilities'),1,1);

// for settings page
		add_action('MailPress_settings_extraform_update',  	array(&$this,'update'));
		add_action('MailPress_settings_extraform_tab', 		array(&$this,'tab'),8,1);
		add_action('MailPress_settings_extraform_div', 		array(&$this,'div'));

// for admin mailing lists
		add_action('MailPress_mp_redirect',  			array(&$this,'mp_redirect'),1,1);
		add_filter('MailPress_title',					array(&$this,'title'),8,1);
		add_action('MailPress_register_styles', 			array(&$this,'register_styles'));
		add_filter('MailPress_enqueue_styles',			array(&$this,'enqueue_styles'),8,1);
		add_action('MailPress_register_scripts',  		array(&$this,'register_scripts'));
		add_filter('MailPress_enqueue_scripts',			array(&$this,'enqueue_scripts'),8,1);
		add_action('MailPress_screen_meta',				array(&$this,'screen_meta'),8,2);
		add_filter('MailPress_screen_meta_screen',		array(&$this,'screen_meta_screen'),8,2);
		add_filter('MailPress_manage_users_columns',		array(&$this,'manage_users_columns'),8,1);
		add_filter('MailPress_manage_columns_prefs',		array(&$this,'manage_list_columns'),8,1);
// for admin ajax
		add_action('mp_action_add-mailinglist',  			array(&$this,'mp_action_add_mailinglist'));
// for mp_users list
		add_action('MailPress_restrict_manage_users',  		array(&$this,'restrict_manage_users'),1,1); 	// filter button
		add_action('MailPress_manage_users_custom_column',  	array(&$this,'manage_users_custom_column'),1,3); 		// filter button
// for mp_user
		add_action('MailPress_user_boxes',  			array(&$this,'user_boxes'),8,2); 
		add_action('mp_action_add-user-mailinglist',  		array(&$this,'mp_action_add_user_mailinglist'));

// for mp_user in mailinglists
		add_action('MailPress_insert_user',  			array(&$this,'set_user_mailinglists'),1,1);
		add_action('MailPress_delete_user',  			array(&$this,'delete_user'),1,1);
// for sending mails
		add_filter('MailPress_mailing_lists',			array(&$this,'mailing_lists'),8,1);
		add_filter('MailPress_mailing_lists_query',		array(&$this,'mailing_lists_query'),8,1);
// for shortcode
		add_filter('MailPress_form_defaults',			array(&$this,'form_defaults'),8,1);
		add_filter('MailPress_form_options',			array(&$this,'form_options'),8,1);
		add_filter('MailPress_form_submit',  			array(&$this,'form_submit'),8,2);
		add_action('MailPress_form',		  			array(&$this,'form'),1,2); 

		$settings		= get_option('MailPress_mailinglist');
		if (isset($settings['show_mailinglists'])) 
		{
// register form
			add_action('MailPress_register_form', 		array(&$this,'register_form'),1); 
// registering user
			add_action('user_register',  				array(&$this,'register'),10,1);
		}

// for javascript plugin conflicts
		add_filter('MailPress_deregister_scripts', array(&$this,'deregister_scripts'), 10, 1 );
	}

// for taxonomy
	function _update_user_mailing_lists_count( $mailing_lists )
	{
		global $wpdb;

		foreach ( $mailing_lists as $mailing_list ) 
		{
			$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_taxonomy a, $wpdb->term_relationships b, $wpdb->mp_users c WHERE a.taxonomy = '" . MailPress_taxonomy_mailing_lists . "' AND  a.term_taxonomy_id = b.term_taxonomy_id AND a.term_taxonomy_id = %d AND c.id = b.object_id ", $mailing_list ) );
			$wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $mailing_list ) );
		}
	}
	function remove_slug_MailPress_mailing_lists( $slug , $term_id = '' , $taxonomy = MailPress_taxonomy_mailing_lists , $context = '' )
	{
		if ( MailPress_taxonomy_mailing_lists != $taxonomy ) return $slug;
		return str_ireplace('_' . MailPress_taxonomy_mailing_lists . '_','',$slug);
	}
	function add_slug_MailPress_mailing_lists( $slug , $taxonomy = MailPress_taxonomy_mailing_lists )
	{
		if ( MailPress_taxonomy_mailing_lists != $taxonomy ) return $slug;
		return '_' . MailPress_taxonomy_mailing_lists . '_' . $slug;
	}

// for plugin
	function install()
	{
		global $wpdb;
		if (!get_option('MailPress_default_mailinglist'))
		{
	// Default mailing list
			$name = $wpdb->escape(__('Uncategorized','MailPress'));
			$slug = sanitize_title(sanitize_term_field('slug', _c('Uncategorized','MailPress'), 0, MailPress_taxonomy_mailing_lists, 'db'));
			$wpdb->query("INSERT INTO $wpdb->terms (name, slug, term_group) VALUES ('$name', '$slug', '0')");
			$term_id = $wpdb->get_var("SELECT term_id FROM $wpdb->terms WHERE slug = '$slug' ");
			$wpdb->query("INSERT INTO $wpdb->term_taxonomy (term_id, taxonomy, description, parent, count) VALUES ($term_id, '" . MailPress_taxonomy_mailing_lists . "', '', '0', '0')");
			add_option('MailPress_default_mailinglist', $term_id );
		}

	// Synchronize
		$default_mailinglist	= get_option('MailPress_default_mailinglist');
		$query = "SELECT DISTINCT a.id FROM $wpdb->mp_users a WHERE NOT EXISTS (SELECT DISTINCT b.id FROM $wpdb->term_taxonomy c, $wpdb->term_relationships d, $wpdb->mp_users b WHERE c.taxonomy = '" . MailPress_taxonomy_mailing_lists . "' AND  c.term_taxonomy_id = d.term_taxonomy_id AND b.id = d.object_id AND b.id = a.id)";
		$unmatches = $wpdb->get_results($query);
		if ($unmatches) foreach ($unmatches as $unmatch) mp_set_user_mailinglists($unmatch->id,array($default_mailinglist));
	}

	function init()
	{
		global $mp_general;

		if ($mp_general['menu'])  	$file= 'admin.php';
		else					$file = ( current_user_can('edit_users') ) ? 'users.php' : 'profile.php';
		define ('MailPress_mailinglists', $file . '?page=' . MailPress_page_mailinglists);
	}

	function plugin_action_links($links, $file)
	{
		static $this_plugin;
		if( !$this_plugin ) $this_plugin = plugin_basename(__FILE__);

		if( $file == $this_plugin ){
			$settings_link = '<a href="' . MailPress_settings . '#fragment-MailPress_mailinglist">' . __('Settings') . '</a>';
			array_unshift ($links, $settings_link);
		}
		return $links;
	}

// for role & capabilities

	function mailpress_mailinglists() {include (MP_MailPress_mailing_lists_TMP . '/mp-admin/mailinglists.php');}

	function capabilities($x) 
	{
		global $mp_general;
		$m = (isset($mp_general['menu'])) ? true : false;
		$pu = ( current_user_can('edit_users') ) ? 'users.php' : 'profile.php';

		$x['MailPress_manage_mailinglists'] = array(	'name'  => __('Mailing lists','MailPress'),
										'group' => 'users',
										'menu'  => 35,

										'parent'		=> ($m) ? false : $pu,
										'page_title'	=> __('MailPress Mailing lists','MailPress'),
										'menu_title'   	=> __('Mailing lists','MailPress'),
										'page'  		=> MailPress_page_mailinglists,
										'func'  		=> array(&$this,MailPress_page_mailinglists)
									);
		return $x;
	}

// for admin settings 

	function update()
	{
		if ($_POST['formname'] != 'mailinglist_form') return;

		global $mp_general, $mp_tab;

		$mp_general['tab'] = $mp_tab = 'MailPress_mailinglist';

		$mailinglist		= $_POST['mailinglist'];
		$default_mailinglist 	= $_POST['default_mailinglist'];

		if (!add_option ('MailPress_default_mailinglist', $default_mailinglist, 'MailPress - Mailing list config' )) update_option ('MailPress_default_mailinglist', $default_mailinglist);
		if (!empty($mailinglist)) if (!add_option ('MailPress_mailinglist', $mailinglist, 'MailPress - Mailing list config' )) update_option ('MailPress_mailinglist', $mailinglist);
		if (!add_option ('MailPress_general', $mp_general, 'MailPress - general settings' )) update_option ('MailPress_general', $mp_general);

		MP_Admin::message(__("'Mailing list' settings saved",'MailPress'));
	}

	function tab($tab)
	{
?>
			<li <?php if ($tab=='MailPress_mailinglist') echo " class='ui-tabs-selected'"; ?>><a href='#fragment-MailPress_mailinglist'><span class='button-secondary'><?php _e('Mailing list','MailPress'); ?></span></a></li>
<?php
	}

	function div()
	{
		include (MP_MailPress_mailing_lists_TMP . '/mp-admin/includes/settings.php');
	}

// for admin mailing lists

	function mp_redirect($page) 
	{
		global $action;

		switch (true)
		{
			case (MailPress_page_mailinglists == $page) :

				wp_reset_vars( array('action') );

				if ( isset($_GET['deleteit']) ) $action = 'bulk-delete';
				switch($action) 
				{
					case 'add':
						$x = ( mp_insert_mailinglist($_POST ) ) ? 1 : 4 ;
						$x = MailPress_mailinglists . "&message=$x#add";
						wp_redirect($x);
						exit;
					break;

					case 'delete':
						$mailinglist_ID = (int) $_GET['id'];

						$mailinglist_name = get_mailinglistname($mailinglist_ID);

						// Don't delete the default mailing list.
					    if ( $mailinglist_ID == get_option('MailPress_default_mailinglist') )
							wp_die(sprintf(__("Can&#8217;t delete the <strong>%s</strong> mailing list: this is the default one",'MailPress'), $mailinglist_name));

						mp_delete_mailinglist($mailinglist_ID);

						$x = MailPress_mailinglists . '&message=2';
						wp_redirect($x);
						exit;
					break;

					case 'bulk-delete':

						foreach ( (array) $_GET['delete'] as $mailinglist_ID ) 
						{
							$mailinglist_name = get_mailinglistname($mailinglist_ID);

							// Don't delete the default cats.
							if ( $mailinglist_ID == get_option('MailPress_default_mailinglist') )
								wp_die(sprintf(__("Can&#8217;t delete the <strong>%s</strong> mailing list: this is the default one",'MailPress'), $mailinglist_name));

							mp_delete_mailinglist($mailinglist_ID);
						}

						$sendback = wp_get_referer();
						$sendback = preg_replace('|[^a-z0-9-~+_.?#=&;,/:]|i', '', $sendback);

						wp_redirect($sendback);
						exit();

					break;

					case 'edited':
						$x = ( mp_update_mailinglist($_POST) ) ? 3 : 5 ;
						$x = MailPress_mailinglists . "&message=$x";
						wp_redirect($x);
						exit;
					break;

					default:

					if ( !empty($_GET['_wp_http_referer']) ) 
					{
						wp_redirect(remove_query_arg(array('_wp_http_referer', '_wpnonce'), stripslashes($_SERVER['REQUEST_URI'])));
						 exit;
					}
				}
			break;
			case (MailPress_page_user == $page) :
				if (isset($_POST['mp_user_mailinglists'])) mp_set_user_mailinglists($_POST['id'],$_POST['mp_user_mailinglists']);
			break;
		}
	}

	function title($x)
	{
		$x[MailPress_page_mailinglists]     	= __('MailPress Mailing lists','MailPress');
		$x[MailPress_page_mailinglists . 'id']	= __('Edit Mailing list','MailPress');
		return $x;
	}

	function register_styles() 
	{
		$pathcss 		= MP_MailPress_mailing_lists_TMP . '/mp-admin/css/colors-' . get_user_option('admin_color') . '.css';
		$css_url 		= get_option('siteurl') . '/' . MP_MailPress_mailing_lists_PATH . 'mp-admin/css/colors-' . get_user_option('admin_color') . '.css';
		$css_url_default 	= get_option('siteurl') . '/' . MP_MailPress_mailing_lists_PATH . 'mp-admin/css/colors-fresh.css';
		$css_url = (is_file($pathcss)) ? $css_url : $css_url_default;

		wp_register_style ( MailPress_page_mailinglists, 	get_option('siteurl') . '/' . MP_MailPress_mailing_lists_PATH . 'mp-admin/css/mailinglists.css' );
		wp_register_style ( MailPress_page_mailinglists . 'color', $css_url);
		wp_register_style ( MailPress_page_mailinglists . 'user', 	get_option('siteurl') . '/' . MP_MailPress_mailing_lists_PATH . 'mp-admin/css/user.css', array( MailPress_page_mailinglists . 'color' ) );
	}

 	function enqueue_styles($x) 
	{
		$x [MailPress_page_mailinglists][] = MailPress_page_mailinglists;
		$x [MailPress_page_user][] = MailPress_page_mailinglists . 'user';
		return $x;
	}

	function register_scripts() 
	{
		global $mp_screen;
		
		wp_register_script( MailPress_page_mailinglists,      '/' . MP_MailPress_mailing_lists_PATH . 'mp-admin/js/mailinglists.js', array('mp-lists'), false, 1);
		wp_localize_script( MailPress_page_mailinglists, 	'adminmailinglistsL10n', array('pending' => __('%i% pending'),
																 'screen'  => $mp_screen ) );
		wp_register_script( 'mp-user-mailinglists', '/' . MP_MailPress_mailing_lists_PATH . 'mp-admin/js/user.js', array('mp-lists'), false, 1);
		wp_register_script( 'mp-settings-mailinglists',	'/' . MP_MailPress_mailing_lists_PATH . 'mp-admin/js/settings.js', array('jquery'), false, 1);
	}

	function enqueue_scripts($x)
	{
		$x[MailPress_page_mailinglists][]  = MailPress_page_mailinglists ;
		$x[MailPress_page_user][] = 'mp-user-mailinglists';
		$x[MailPress_page_settings][] = 'mp-settings-mailinglists';
		return $x;
	}

	function screen_meta($page,$mp_screen)
	{
		switch ($page)
		{
			case MailPress_page_mailinglists :
				add_filter('manage_' . $mp_screen . '_columns',array('MailPress_mailing_lists','manage_list_columns'));

				$help	= sprintf(__('<a href="%1$s" target="_blank">Documentation</a>','MailPress'),MailPress_help_url);
				$help	.= '<br />' . sprintf(__('<a href="%1$s" target="_blank">Support Forum</a>','MailPress'),'http://groups.google.com/group/mailpress');
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
			case MailPress_page_mailinglists :
				$mp_screen = MailPress_page_mailinglists;
			break;
		}
		return $mp_screen;
	}

	function manage_users_columns($x)
	{
		$date = array_pop($x);
		$x['mailinglists']=  __('Mailing lists','MailPress');
		$x['date']		= $date;
		return $x;
	}

	function manage_list_columns() {
		$mailinglists_columns = array('cb' 		=> '<input type="checkbox" />',
							'name' 	=> __('Name','MailPress'),
							'desc'	=> __('Description','MailPress'),
							'num' 	=> __('MailPress users','MailPress'));
		return $mailinglists_columns;
	}

// for admin ajax

	function mp_action_add_mailinglist()
	{
		if ( '' === trim($_POST['mailinglist_name']) ) 
		{
			$x = new WP_Ajax_Response( array(	'what' => 'mailinglist',
									'id' => new WP_Error( 'mailinglist_name', __('You did not enter a mailing list name.','MailPress') )
								   ) );
			$x->send();
		}

		if ( mailinglist_exists( trim( $_POST['mailinglist_name'] ) ) ) 
		{
			$x = new WP_Ajax_Response( array(	'what' => 'mailinglist',
									'id' => new WP_Error( 'mailinglist_exists', __('The mailing list you are trying to create already exists.','MailPress'), array( 'form-field' => 'mailinglist_name' ) ),
								  ) );
			$x->send();
		}
	
		$mailinglist = mp_insert_mailinglist( $_POST, true );

		if ( is_wp_error($mailinglist) ) 
		{
			$x = new WP_Ajax_Response( array(	'what' => 'mailinglist',
									'id' => $mailinglist
								  ) );
			$x->send();
		}

		if ( !$mailinglist || (!$mailinglist = get_mailinglist( $mailinglist )) ) 	die('0');

		$level 			= 0;
		$mailinglist_full_name 	= $mailinglist->name;
		$_mailinglist 		= $mailinglist;
		while ( $_mailinglist->parent ) 
		{
			$_mailinglist 		= get_mailinglist( $_mailinglist->parent );
			$mailinglist_full_name 	= $_mailinglist->name . ' &#8212; ' . $mailinglist_full_name;
			$level++;
		}
		$mailinglist_full_name = attribute_escape($mailinglist_full_name);

		$x = new WP_Ajax_Response( array(	'what' => 'mailinglist',
								'id' => $mailinglist->term_id,
								'data' => _mailinglist_row( $mailinglist, $level, $mailinglist_full_name ),
								'supplemental' => array('name' => $mailinglist_full_name, 'show-link' => sprintf(__( 'Mailing list <a href="#%s">%s</a> added' ,'MailPress'), "mailinglist-$mailinglist->term_id", $mailinglist_full_name))
							  ) );
		$x->send();
		break;
	}

// for mp_users list

	function restrict_manage_users($url_parms)
	{
		$x = (isset($url_parms['mailinglist'])) ? $url_parms['mailinglist'] : '';
		$dropdown_options = array('show_option_all' => __('View all mailing list','MailPress'), 'hide_empty' => 0, 'hierarchical' => true, 'show_count' => 0, 'orderby' => 'name', 'selected' => $x );
		mp_dropdown_mailinglists($dropdown_options);
		echo "<input type='submit' id='mailinglistsub' value=\"" . __('Filter','MailPress') . "\" class='button-secondary' />";
	}

	function manage_users_custom_column($column_name,$user,$url_parms)
	{
		if ('mailinglists' != $column_name) return;

		$mailinglists = get_the_mailinglist($user->id);
		if ( !empty( $mailinglists ) ) 
		{
			$out = array();
			foreach ( $mailinglists as $m )
				$out[] = "<a href='" . MailPress_users . "&amp;mailinglist=$m->term_id'>" . wp_specialchars(sanitize_term_field('name', $m->name, $m->term_id, MailPress_taxonomy_mailing_lists, 'display')) . "</a>";
			echo join( ', ', $out );
		}
		else
		{
			_e('Uncategorized ','MailPress');
		}
	}

// for mp_user

	function user_boxes($mp_user_id,$mp_screen)
	{
		if (current_user_can('MailPress_manage_mailinglists'))
			add_meta_box('mailinglistdiv', __('Mailing lists','MailPress'), array(&$this,'meta_box'), $mp_screen, 'side', 'core');
	}

	function meta_box($mp_user)
	{ 
?>
<ul id="user-mailinglist-tabs">
	<li class="ui-tabs-selected">
		<a href="#user-mailinglists-all" tabindex="3">
			<?php _e( 'All Mailing lists','MailPress' ); ?>
		</a>
	</li>
	<li class="hide-if-no-js">
		<a href="#user-mailinglists-pop" tabindex="3">
			<?php _e( 'Most Used' ,'MailPress'); ?>
		</a>
	</li>
</ul>
<div id="user-mailinglists-pop" class="ui-tabs-panel" style="display: none;">
	<ul id="user-mailinglistchecklist-pop" class="user-mailinglistchecklist form-no-clear" >
		<?php $popular_ids = mp_popular_terms_checklist(MailPress_taxonomy_mailing_lists,$mp_user->id); ?>
	</ul>
</div>
<div id="user-mailinglists-all" class="ui-tabs-panel">
	<ul id="user-mailinglistchecklist" class="list:user-mailinglist user-mailinglistchecklist form-no-clear">
		<?php mp_mailinglist_checklist($mp_user->id, false, false, $popular_ids) ?>
	</ul>
</div>

<div id="user-mailinglist-adder" class="wp-hidden-children">
	<h4>
		<a id="user-mailinglist-add-toggle" href="#user-mailinglist-add" class="hide-if-no-js" tabindex="3">
			<?php _e( '+ Add New mailing list' ,'MailPress'); ?>
		</a>
	</h4>
	<p id="user-mailinglist-add" class="wp-hidden-child">
		<label class="hidden" for="newuser-mailinglist">
			<?php _e( 'Add New mailing list' ,'MailPress'); ?>
		</label>
		<input type="text" name="newuser-mailinglist" id="newuser-mailinglist" class="form-required form-input-tip" value="<?php _e( 'New mailing list name','MailPress' ); ?>" tabindex="3" aria-required="true" />
		<label class="hidden" for="newuser-mailinglist_parent">
			<?php _e('Parent Mailing list','MailPress'); ?>:
		</label>
		<?php mp_dropdown_mailinglists( array( 'hide_empty' => 0, 'name' => 'newuser-mailinglist_parent', 'orderby' => 'name', 'hierarchical' => 1, 'show_option_none' => __('Parent Mailing list','MailPress'), 'tab_index' => 3 ) ); ?>
		<input type="button" id="user-mailinglist-add-sumbit" class="add:user-mailinglistchecklist:user-mailinglist-add button" value="<?php _e( 'Add','MailPress' ); ?>" tabindex="3" />
		<?php wp_nonce_field( 'add-user-mailinglist', '_ajax_nonce', false ); ?>
		<span id="user-mailinglist-ajax-response"></span>
	</p>
</div>
<?php
	}

	function mp_action_add_user_mailinglist()
	{
		$names = explode(',', $_POST['newuser-mailinglist']);
		if ( 0 > $parent = (int) $_POST['newuser-mailinglist_parent'] )	$parent = 0;
		$mp_user_mailinglist = isset($_POST['mp_user_mailinglists'])? (array) $_POST['mp_user_mailinglists'] : array();
		$checked_mailinglists = array_map( 'absint', (array) $mp_user_mailinglist );
		$popular_ids = isset( $_POST['popular_ids'] ) ? array_map( 'absint', explode( ',', $_POST['popular_ids'] ) ) : false;

		$x = new WP_Ajax_Response();
		foreach ( $names as $mailinglist_name ) 
		{
			$mailinglist_name = trim($mailinglist_name);
			$mailinglist_nicename = sanitize_title($mailinglist_name);
			if ( '' === $mailinglist_nicename ) continue;
			$mailinglist_id = mp_create_mailinglist( $mailinglist_name, $parent );
			$checked_mailinglists[] = $mailinglist_id;
			if ( $parent ) continue;										// Do these all at once in a second
			$mailinglist = get_mailinglist( $mailinglist_id );
			ob_start();
				mp_mailinglist_checklist( 0, $mailinglist_id, $checked_mailinglists, $popular_ids );
				$data = ob_get_contents();
			ob_end_clean();
			$x->add( array(	'what' => 'user-mailinglist',
						'id' => $mailinglist_id,
						'data' => $data,
						'position' => -1
					  ) );
		}
		if ( $parent ) 
		{ 									// Foncy - replace the parent and all its children
			$parent = get_mailinglist( $parent );
			ob_start();
				mp_mailinglist_checklist( 0, $mailinglist_id, $checked_mailinglists, $popular_ids );
			$data = ob_get_contents();
			ob_end_clean();
			$x->add( array(	'what' => 'user-mailinglist',
						'id' => $parent->term_id,
						'old_id' => $parent->term_id,
						'data' => $data,
						'position' => -1
					  ) );
		}
		$x->send();
	}

// for mp_user in mailinglists

	function set_user_mailinglists( $mp_user_id, $user_mailinglists = array() )
	{
		$mp_user_id = (int) $mp_user_id;
		if (!is_array($user_mailinglists) || 0 == count($user_mailinglists) || empty($user_mailinglists)) $user_mailinglists = array(get_option('MailPress_default_mailinglist'));
		else if ( 1 == count($user_mailinglists) && '' == $user_mailinglists[0] ) return true;

		$user_mailinglists = array_map('intval', $user_mailinglists);
		$user_mailinglists = array_unique($user_mailinglists);
		return wp_set_object_terms($mp_user_id, $user_mailinglists, MailPress_taxonomy_mailing_lists);
	}

	function get_user_mailinglists( $mp_user_id = 0, $args = array() ) {
		$mp_user_id = (int) $mp_user_id;

		$defaults = array('fields' => 'ids');
		$args = wp_parse_args( $args, $defaults );
	
		$mailinglists = wp_get_object_terms($mp_user_id , MailPress_taxonomy_mailing_lists, $args);
		return $mailinglists;
	}

	function delete_user($mp_user_id)
	{
		wp_delete_object_term_relationships($mp_user_id, array(MailPress_taxonomy_mailing_lists));
	}

// for sending mails

	function mailing_lists($draft_dest) 
	{
		//$args = array('hide_empty' => 0, 'hierarchical' => true, 'show_count' => 0, 'orderby' => 'name', 'selected' => $default_mailinglist, 'name' => 'default_mailinglist' );
		$args = array('hide_empty' => 0, 'hierarchical' => true, 'show_count' => 0, 'orderby' => 'name', 'name' => 'default_mailinglist' );
		foreach (mp_array_mailinglists($args) as $k => $v) $draft_dest[$k] = $v;
		return $draft_dest;
	}

	function mailing_lists_query($draft_toemail) 
	{
		$x = str_replace('MailPress_mailing_list~','',$draft_toemail,$count);
		if (0 == $count) return false;
		if (empty($x)) return false;

		$y = get_mailinglist_children($x, ',', '');
		$x = ('' == $y) ? ' = ' . $x : ' IN (' . $x . $y . ') ';

		if (empty($x)) return false;
		global $wpdb;

		$query = "SELECT DISTINCT c.id, c.email, c.status, c.confkey FROM $wpdb->term_taxonomy a, $wpdb->term_relationships b, $wpdb->mp_users c WHERE a.taxonomy = '" . MailPress_taxonomy_mailing_lists . "' AND  a.term_taxonomy_id = b.term_taxonomy_id AND a.term_id $x AND c.id = b.object_id AND c.status = 'active' ";

		return $query;
	}

// for shortcode
	function form_defaults($x) { $x['mailinglist'] = false; return $x; }
	function form_options($x)  { return $x; }
	function form_submit($shortcode_message, $email) 
	{ 
		if (!isset($_POST['mailinglist'])) return $shortcode_message;
		$mp_user_id = MP_User::get_user_id_by_email($email);
		$mailinglist_ID = $_POST['mailinglist'];

		$user_mailinglists = self::get_user_mailinglists($mp_user_id);

		if (!in_array($mailinglist_ID,$user_mailinglists))
		{
			array_push($user_mailinglists,$mailinglist_ID);
			self::set_user_mailinglists( $mp_user_id, $user_mailinglists);
		}

		return $shortcode_message . __('<br />Mailinglist added','MailPress');
	}
	function form($email,$options)  
	{
		if (!$options['mailinglist']) return;
		echo "<input type='hidden' name='mailinglist' value='" . $options['mailinglist'] . "' />\n";
	}

// register form

	public static function register_form()
	{
		if ($checklist_mailinglists = self::checklist_mp_user_mailinglists())
		{
?>
	<br />
	<p>
		<label>
			<?php _e('Mailing lists','MailPress'); ?>
			<br />
			<span style='color:#777;font-weight:normal;'>
				<?php echo $checklist_mailinglists; ?>
			</span>
		</label>
	</p>
<?php 
		}
	}

// registering user

	public static function register($wp_user_id)
	{
		$user 	= get_userdata($wp_user_id);
		$email 	= $user->user_email;
		$mp_user_id	= MP_User::get_id_by_email($email);

		self::update_mp_user_mailinglists($mp_user_id);
	}




	function checklist_mp_user_mailinglists($mp_user_id = false, $args = '') 
	{
		$settings = get_option('MailPress_mailinglist');
		if (!isset($settings['show_mailinglists'])) return false;

		$defaults = array (	'name' 	=> 'keep_mailinglists',
						'echo' 	=> 1,
						'selected' 	=> false,
						'type'	=> 'checkbox',
						'show_option_all' => false,
						'htmlmiddle'=> '&nbsp;&nbsp;',
						'htmlend'	=> "<br />\n"
					);
		$r = wp_parse_args( $args, $defaults );
		extract( $r );

		$checklist = false;

		$default_mailing_list = get_option('MailPress_default_mailinglist');
		$mls = apply_filters('MailPress_mailing_lists',array());

		foreach ($mls as $k => $v)
		{
			$x = str_replace('MailPress_mailing_list~','',$k,$count);
			if (0 == $count) 	continue;	
			if (empty($x)) 	continue;
			if ($x == $default_mailing_list) 	continue;

			switch ($type)
			{
				case 'checkbox' :
					$checked = '';
					$typ2 = $type; 
					if ($mp_user_id)
					{
						if (in_mailinglist( $x, $mp_user_id))
						{
							$typ2    = (!isset($settings['display_mailinglists'][$x])) ? 'hidden' : $typ2;
							if ('checkbox' == $typ2) $checked =  " checked='checked'";
						}
						else
						{
							if (!isset($settings['display_mailinglists'][$x])) continue;
						}
					}
					else
					{
						if (!isset($settings['display_mailinglists'][$x])) continue;
					}

					$htmlstart2  = ('checkbox' == $typ2) ? $htmlstart  : '';
					$htmlmiddle2 = ('checkbox' == $typ2) ? $htmlmiddle . str_replace('&nbsp;','',$v) : "<!-- " . str_replace('&nbsp;','',$v) . "-->";
					$htmlend2    = ('checkbox' == $typ2) ? $htmlend    : "\n";

					$checklist .= $htmlstart2 . "<input type='$typ2' name='" . $name . "[$x]'$checked />" . $htmlmiddle2 . $htmlend2;
				break;
				case 'select' :
					if (!isset($settings['display_mailinglists'][$x])) continue;

					if (empty($checklist)) $checklist = "\n" . $htmlstart . "\n<select name='" . $name . "'>\n";
					if ($show_option_all)
					{
						$checklist .= "<option value=''>" . $show_option_all . "</option>\n";
						$show_option_all = false;
					}
					$sel = ($k == $selected) ? " selected='selected'" : '';
					$checklist .= "<option value=\"$k\"$sel>" . str_replace('&nbsp;','',$v) . "</option>\n";
				break;
			}
		}
		if ('select' == $type) $checklist .= "</select>\n" . $htmlend . "\n";
		return $checklist;
	}

	function update_mp_user_mailinglists($mp_user_id, $name = 'keep_mailinglists') 
	{
		$settings = get_option('MailPress_mailinglist');
		if (!isset($settings['show_mailinglists'])) return false;

		$user_mailinglists = array();

		if (isset($_POST[$name]))
		{
			foreach ($_POST[$name] as $mailinglist_ID => $v)
			{
				array_push($user_mailinglists,$mailinglist_ID);
			}
		}
		self::set_user_mailinglists( $mp_user_id, $user_mailinglists);
	}

	function deregister_scripts($x)
	{
		$x[] = MailPress_page_mailinglists;
		return $x;
	}
}

define ('MP_MailPress_mailing_lists_FOLDER', 	basename(dirname(__FILE__)));
define ('MP_MailPress_mailing_lists_PATH', 	'wp-content/plugins/' . MP_MailPress_mailing_lists_FOLDER . '/' );
define ('MP_MailPress_mailing_lists_TMP', 	dirname(__FILE__));

require MP_MailPress_mailing_lists_TMP . "/mp-admin/includes/taxonomy.php";
require MP_MailPress_mailing_lists_TMP . "/mp-admin/includes/template.php";
require MP_MailPress_mailing_lists_TMP . "/mp-includes/mailinglist.php";
require MP_MailPress_mailing_lists_TMP . "/mp-includes/mailinglist-template.php";

$MailPress_mailing_lists = new MailPress_mailing_lists();
?>