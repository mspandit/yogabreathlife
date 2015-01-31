<?php

//
// Mailing list
//

function mailinglist_exists($mailinglist_name) 
{
	$id = is_term($mailinglist_name, MailPress_taxonomy_mailing_lists);

	if ( is_array($id) )	$id = $id['term_id'];

	return $id;
}

function get_mailinglist_to_edit( $id ) 
{
	return get_mailinglist( $id, OBJECT, 'edit' );
}

function mp_create_mailinglist( $mailinglist_name, $parent = 0 ) 
{
	if ( $id = mailinglist_exists($mailinglist_name) )		return $id;

	return mp_insert_mailinglist( array('mailinglist_name' => $mailinglist_name, 'mailinglist_parent' => $parent) );
}

function mp_create_mailinglists($mailinglists, $mp_user_id = '') 
{
	$mailinglist_ids = array ();
	foreach ($mailinglists as $mailinglist) 
	{
		if ($id = mailinglist_exists($mailinglist)) 		$mailinglist_ids[] = $id;
		elseif ($id = mp_create_mailinglist($mailinglist)) 	$mailinglist_ids[] = $id;
	}

	if ($mp_user_id) mp_set_mp_user_mailinglist($mp_user_id, $mailinglist_ids);

	return $mailinglist_ids;
}

function mp_insert_mailinglist($mailinglistarr, $mp_error = false) 
{
	$mailinglist_defaults = array('mailinglist_ID' => 0, 'mailinglist_name' => '', 'mailinglist_description' => '', 'mailinglist_nicename' => '', 'mailinglist_parent' => '');
	$mailinglist_arr = wp_parse_args($mailinglist_arr, $mailinglist_defaults);
	extract($mailinglistarr, EXTR_SKIP);

	if ( trim( $mailinglist_name ) == '' ) 
	{
		if ( ! $mp_error )	return 0;
		else				return new WP_Error( 'mailinglist_name', __('You did not enter a mailing list name.','MailPress') );
	}

	if ( trim( $mailinglist_nicename ) == '' ) $mailinglist_nicename = $mailinglist_name;

	$mailinglist_ID = (int) $mailinglist_ID;

	// Are we updating or creating?
	$update = (!empty ($mailinglist_ID) ) ? true : false;

	$name 		= $mailinglist_name;
	$description 	= $mailinglist_description;
	$slug 		= $mailinglist_nicename;
	$parent 		= $mailinglist_parent;

	$parent 		= (int) $parent;
	if ( $parent < 0 ) $parent = 0;

	if ( empty($parent) || !mailinglist_exists( $parent ) || ($mailinglist_ID && mailinglist_is_ancestor_of($mailinglist_ID, $parent) ) )
		$parent = 0;

	$args = compact('name', 'slug', 'parent', 'description');

	if ( $update )	$mailinglist_ID = wp_update_term($mailinglist_ID,   MailPress_taxonomy_mailing_lists, $args);
	else			$mailinglist_ID = wp_insert_term($mailinglist_name, MailPress_taxonomy_mailing_lists, $args);

	if ( is_wp_error($mailinglist_ID) ) 
	{
		if ( $wp_error )	return $mailinglist_ID;
		else			return 0;
	}

	return $mailinglist_ID['term_id'];
}

function mp_update_mailinglist($mailinglistarr)
 {
	$mailinglist_ID = (int) $mailinglistarr['mailinglist_ID'];

	if ( $mailinglist_ID == $mailinglistarr['mailinglist_parent'] )	return false;

	// First, get all of the original fields
	$mailinglist = get_mailinglist($mailinglist_ID, ARRAY_A);

	// Escape data pulled from DB.
	$mailinglist = add_magic_quotes($mailinglist);

	// Merge old and new fields with new fields overwriting old ones.
	$mailinglistarr = array_merge($mailinglist, $mailinglistarr);

	return mp_insert_mailinglist($mailinglistarr);
}

function mp_delete_mailinglist($mailinglist_ID) 
{
	$mailinglist_ID 	= (int) $mailinglist_ID;
	$default 		= get_option('MailPress_default_mailinglist');

	// Don't delete the default cat
	if ( $mailinglist_ID == $default ) return 0;

	$settings		= get_option('MailPress_mailinglist');
	unset($settings['display_mailinglists'][$mailinglist_ID]);
	update_option ('MailPress_mailinglist', $settings);

	return wp_delete_term($mailinglist_ID, MailPress_taxonomy_mailing_lists, array('default' => $default));
}
?>