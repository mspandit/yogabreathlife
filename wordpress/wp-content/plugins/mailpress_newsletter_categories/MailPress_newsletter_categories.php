<?php
/*
Plugin Name: MailPress_newsletter_categories
Plugin URI: http://www.mailpress.org
Description: This is just an addon for MailPress to manage newsletters per categories
Author: Andre Renaut
Version: 3.0.1
Author URI: http://www.mailpress.org
*/

class MailPress_newsletter_categories
{
	function MailPress_newsletter_categories() {

		register_activation_hook(MP_FOLDER . '/MailPress_newsletter_categories.php',	array(&$this,'install'));

// for plugin
		add_action('MailPress_register_newsletter',		array(&$this,'register'));
// for admin settings 
		add_filter('plugin_action_links', 				array(&$this,plugin_action_links), 10, 2 );

		add_action('MailPress_settings_extraform_update',  	array(&$this,'update'));
		add_action('MailPress_settings_extraform_tab', 		array(&$this,'tab'),8,1);
		add_action('MailPress_settings_extraform_div', 		array(&$this,'div'));
	}

	function install() {

	}

// for plugin
	function register() {

		add_action('publish_post',	array(&$this,'have_post'), 8, 1);

		$daily_value 	 	= date('Ymd');
		$d  				= date('Ymd',mktime(0,0,0,date('m'),date('d') - 1, date('Y')));
		$daily_query_posts 	= $d;

		$weekly_value 	 	= MP_Newsletter::get_yearweekofday(date('Y-m-d'));
		$w  				= MP_Newsletter::get_yearweekofday(date('Y-m-d',mktime(10,0,0,date('m'),date('d') - 7)));
		$weekly_query_posts_w 	=  substr($w,4,2);
		$weekly_query_posts_y 	=  substr($w,0,4);

		$monthly_value 		= date('Ym');
		$y  				= date('Y'); $m = date('m') - 1; if (0 == $m) { $m = 12; $y--;} if (10 > $m) $m = '0' . $m;
		$monthly_query_posts	= $y . $m ;

		$args = array('hierarchical' => false,'depth'=>false,'echo'=>false,'get'=>'all');
		$categories = get_categories($args);
		foreach ($categories as $category)
		{
			if ($category->category_parent) continue;

			$id   = $category->cat_ID;
			$name = $category->cat_name;

			mp_register_newsletter (	"post_category_$id",
								sprintf( __('[%1$s] New post in %2$s','MailPress'), get_bloginfo('name'), $name),
								'singlecat',
								sprintf(__('Per post "%1$s"','MailPress'), $name),
								sprintf(__('For each new post in %1$s','MailPress'), $name),
								false,
								true,
								array('category' => $id,'catname'=>$name)
						     );

			mp_register_newsletter (	"daily_category_$id",
								sprintf( __('[%1$s] Daily newsletter in %2$s','MailPress'), get_bloginfo('name'), $name),
								'dailycat',
								sprintf(__('Daily "%1$s"','MailPress'), $name),
								sprintf(__('Daily newsletter for %1$s','MailPress'), $name),
								array ( 	'callback'	 => array('MP_Newsletter', 'have')		,
										'name'	 => 'MailPress_daily_category_' . $id	,
										'value'	 => $daily_value 					,
										'query_posts'=> array(
														'm'	=> $daily_query_posts ,
														'cat'	=> $id
													)
									),
								true,
								array('category' => $id,'catname'=>$name)
						     );

			mp_register_newsletter (	"weekly_category_$id",
								sprintf( __('[%1$s] Weekly newsletter for %2$s','MailPress'), get_bloginfo('name'), $name),
								'weeklycat',
								sprintf(__('Weekly "%1$s"','MailPress'), $name),
								sprintf(__('Weekly newsletter for %1$s','MailPress'), $name),
								array ( 	'callback'	 => array('MP_Newsletter', 'have') 		,
										'name'	 => 'MailPress_weekly_category_' . $id	,
										'value'	 => $weekly_value 				,
										'query_posts'=> array(
														'w'	=> $weekly_query_posts_w ,
														'year'=> $weekly_query_posts_y ,
														'cat'	=> $id
													)
									),
								true,
								array('category' => $id,'catname'=>$name)
						     );

			mp_register_newsletter (	"monthly_category_$id",
								sprintf( __('[%1$s] Monthly newsletter for %2$s','MailPress'), get_bloginfo('name'), $name),
								'monthlycat',
								sprintf(__('Monthly "%1$s"','MailPress'), $name),
								sprintf(__('Monthly newsletter for %1$s','MailPress'), $name),
								array ( 	'callback'	 => array('MP_Newsletter', 'have')		,
										'name'	 => 'MailPress_monthly_category_' . $id	,
										'value'	 => $monthly_value				,
										'query_posts'=> array(
														'm'	=> $monthly_query_posts ,
														'cat'	=> $id
													)
									),
								true,
								array('category' => $id,'catname'=>$name)
						     );
		}
	}

// for newsletters
	function have_post($post_id) {
		if (get_post_meta($post_id, '_MailPress_prior_to_install')) return true;

		global $mp_registered_newsletters, $mp_general;

		$args = array('hierarchical' => false,'depth'=>false,'echo'=>false,'get'=>'all');
		$categories = get_categories($args);
		$cat_to_parent = self::cat_to_parent($categories);
		$post_to_cat   = wp_get_post_categories($post_id);
		$post_to_cat = (!empty($post_to_cat)) ? array_flip($post_to_cat) : array();

		foreach ($categories as $category)
		{
			if ($category->category_parent) continue;
			if (!self::post_in_category($cat_to_parent,$post_to_cat,$category->cat_ID)) continue;

			$id 			= $category->cat_ID;
			$newsletter_id 	= 'post_category_' . $id;
			$post_meta 		= '_MailPress_published_category_' . $id;

			if (isset($mp_general['newsletters'][$newsletter_id]))
			{
				if (!get_post_meta($post_id,$post_meta,true))	
				{
					add_post_meta($post_id,$post_meta,'yes',true);
					$newsletter 			= $mp_registered_newsletters[$newsletter_id];
					$newsletter['query_posts'] 	= array(	'p'	=> $post_id ,
												'cat'	=> $id	);

					$post = &get_post($post_id);
					$newsletter['the_title'] =  apply_filters('the_title', $post->post_title );

					MP_Newsletter::send($newsletter,false);
				}
			}
		}
	}

	function cat_to_parent($categories) {
		$cat_to_parent = array();
		foreach ($categories as $category)
		{
			if (!$category->category_parent) 	continue;
			$cat_to_parent[$category->cat_ID] = $category->category_parent;
		}
		
		do
		{
			$herit = false;
			foreach ($cat_to_parent as $k => $v)
			{
				if (isset($cat_to_parent[$v]))
				{
					$cat_to_parent[$k] = $cat_to_parent[$v];
					$herit = true;
				}
			}
		} while ($herit);
		return $cat_to_parent;
	}

	function post_in_category($cat_to_parent,$post_to_cat,$cat_ID) {
		if (isset($post_to_cat[$cat_ID])) return true;
		foreach ($cat_to_parent as $k => $v)
			if ($v == $cat_ID) if (isset($post_to_cat[$k])) return true;
		return false;
	}

// for admin settings 

	function plugin_action_links($links, $file)
	{
		static $this_plugin;
		if( !$this_plugin ) $this_plugin = plugin_basename(__FILE__);

		if( $file == $this_plugin ){
			$settings_link = '<a href="' . MailPress_settings . '#fragment-MailPress_newsletter_categories">' . __('Settings') . '</a>';
			array_unshift ($links, $settings_link);
		}
		return $links;
	}

	function update() {

		if ($_POST['formname'] != 'categories_newsletters_form') return;

		global $mp_general, $mp_tab;

		$old_default_newsletters = (isset($mp_general['default_newsletters'])) ? $mp_general['default_newsletters'] : MP_Newsletter::get_defaults();
		if (!isset($_POST['general']['default_newsletters'])) $_POST['general']['default_newsletters'] = array();

		$mp_general['newsletters'] = $_POST['general']['newsletters'];
		$mp_general['default_newsletters'] = $_POST['general']['default_newsletters'];
		$mp_general['tab'] = $mp_tab = 'MailPress_newsletter_categories';

		$diff_default_newsletters = array();
		foreach($mp_general['default_newsletters'] as $k => $v) if (!isset($old_default_newsletters[$k])) $diff_default_newsletters[$k] = true;
		foreach($old_default_newsletters as $k => $v) if (!isset($mp_general['default_newsletters'][$k])) $diff_default_newsletters[$k] = true;
		foreach ($diff_default_newsletters as $k => $v) MP_Newsletter::reverse_subscriptions($k);
		MP_Newsletter::register();

		update_option ('MailPress_general', $mp_general);

		MP_Admin::message(__("'Category Newsletters' settings saved",'MailPress'));
	}

	function tab($tab) {
?>
			<li <?php if ($tab=='MailPress_newsletter_categories') echo " class='ui-tabs-selected'"; ?>><a href='#fragment-MailPress_newsletter_categories'><span class='button-secondary'><?php _e('Category Newsletters','MailPress'); ?></span></a></li>
<?php
	}

	function div() {
		include (MP_MailPress_newsletter_categories_TMP . '/mp-admin/includes/settings.php');
	}
}

define ('MP_MailPress_newsletter_categories_FOLDER', 	basename(dirname(__FILE__)));
define ('MP_MailPress_newsletter_categories_PATH', 	'wp-content/plugins/' . MP_MailPress_newsletter_categories_FOLDER . '/' );
define ('MP_MailPress_newsletter_categories_TMP', 	dirname(__FILE__));

$MailPress_newsletter_categories = new MailPress_newsletter_categories();
?>