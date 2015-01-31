<?php

function mp_set_user_mailinglists($mp_user_id = 0, $mp_user_mailinglists = array()) 
{
	$mp_user_id = (int) $mp_user_id;

	if (!is_array($mp_user_mailinglists) || 0 == count($mp_user_mailinglists) || empty($mp_user_mailinglists))	$mp_user_mailinglists = array(get_option('MailPress_default_category'));
	elseif ( 1 == count($mp_user_mailinglists) && '' == $mp_user_mailinglists[0] )					return true;

	$mp_user_mailinglists = array_map('intval', $mp_user_mailinglists);
	$mp_user_mailinglists = array_unique($mp_user_mailinglists);

	return wp_set_object_terms($mp_user_id, $mp_user_mailinglists, MailPress_taxonomy_mailing_lists);
}

function get_mailinglist_children($id, $before = '/', $after = '', $visited=array()) 
{
	if ( 0 == $id )	return '';

	$chain = '';
// TODO: consult hierarchy
	$mailinglist_ids = get_all_mailinglist_ids();
	foreach ( $mailinglist_ids as $mailinglist_id ) 
	{
		if ( $mailinglist_id == $id )	continue;

		$mailinglist = get_mailinglist($mailinglist_id);

		if ( is_wp_error( $mailinglist ) )	return $mailinglist;

		if ( $mailinglist->parent == $id && !in_array($mailinglist->term_id, $visited) ) 
		{
			$visited[] 	 = $mailinglist->term_id;
			$chain 	.= $before.$mailinglist->term_id.$after;
			$chain 	.= get_mailinglist_children($mailinglist->term_id, $before, $after);
		}
	}
	return $chain;
}

function get_mailinglist_parents($id, $link = FALSE, $separator = '/', $nicename = FALSE, $visited = array())
{
	$chain  = '';
	$parent = &get_mailinglist($id);

	if ( is_wp_error( $parent ) )	return $parent;

	if ( $nicename )	$name = $parent->slug;
	else			$name = $parent->mailinglist_name;

	if ( $parent->parent && ($parent->parent != $parent->term_id) && !in_array($parent->parent, $visited) ) 
	{
		$visited[] 	 = $parent->parent;
		$chain 	.= get_mailinglist_parents($parent->parent, $link, $separator, $nicename, $visited);
	}

	if ( $link )	$chain .= '<a href="' . get_mailinglist_link($parent->term_id) . '" title="' . sprintf(__("View all users in %s",'MailPress'), $parent->mailinglist_name) . '">'.$name.'</a>' . $separator;
	else			$chain .= $name.$separator;
	return $chain;
}

function get_the_mailinglist($id = false) 
{

	$id = (int) $id;

	$mailinglists = get_object_term_cache($id, MailPress_taxonomy_mailing_lists);

	if ( false === $mailinglists )	$mailinglists = wp_get_object_terms($id, MailPress_taxonomy_mailing_lists);

	if ( !empty($mailinglists) )		usort($mailinglists, '_usort_terms_by_name');
	else						$mailinglists = array();

	return $mailinglists;
}

function get_the_mailinglist_by_ID($mailinglist_ID) 
{
	$mailinglist_ID 	= (int) $mailinglist_ID;
	$mailinglist 	= &get_mailinglist($mailinglist_ID);

	if ( is_wp_error( $mailinglist ) )	return $mailinglist;
	return $mailinglist->name;
}

function get_the_mailinglist_list($separator = '', $parents='', $post_id = false) 
{
	global $wp_rewrite;

	$mailinglists = get_the_mailinglist($post_id);

	if (empty($mailinglists)) return apply_filters('the_mailinglist', __('Uncategorized','MailPress'), $separator, $parents);

	$rel = ( is_object($wp_rewrite) && $wp_rewrite->using_permalinks() ) ? 'rel="mailinglist tag"' : 'rel="mailinglist"';

	$thelist = '';
	if ( '' == $separator ) 
	{
		$thelist .= '<ul class="post-mailinglists">';
		foreach ( $mailinglists as $mailinglist ) 
		{
			$thelist .= "\n\t<li>";
			switch ( strtolower($parents) ) 
			{
				case 'multiple':
					if ($mailinglist->parent) 	$thelist .= get_mailinglist_parents($mailinglist->parent, TRUE);
					$thelist .= '<a href="' . get_mailinglist_link($mailinglist->term_id) . '" title="' . sprintf(__("View all users in %s",'MailPress'), $mailinglist->name) . '" ' . $rel . '>' . $mailinglist->name.'</a></li>';
					break;
				case 'single':
					$thelist .= '<a href="' . get_mailinglist_link($mailinglist->term_id) . '" title="' . sprintf(__("View all users in %s",'MailPress'), $mailinglist->name) . '" ' . $rel . '>';
					if ($mailinglist->parent)	$thelist .= get_mailinglist_parents($mailinglist->parent, FALSE);
					$thelist .= $mailinglist->name.'</a></li>';
					break;
				case '':
				default:
					$thelist .= '<a href="' . get_mailinglist_link($mailinglist->term_id) . '" title="' . sprintf(__("View all users in %s",'MailPress'), $mailinglist->name) . '" ' . $rel . '>' . $mailinglist->mailinglist_name.'</a></li>';
			}
		}
		$thelist .= '</ul>';
	}
	else
	{
		$i = 0;
		foreach ( $mailinglists as $mailinglist ) 
		{
			if ( 0 < $i )	$thelist .= $separator . ' ';
			switch ( strtolower($parents) ) 
			{
				case 'multiple':
					if ( $mailinglist->parent )	$thelist .= get_mailinglist_parents($mailinglist->parent, TRUE);
					$thelist .= '<a href="' . get_mailinglist_link($mailinglist->term_id) . '" title="' . sprintf(__("View all users in %s",'MailPress'), $mailinglist->name) . '" ' . $rel . '>' . $mailinglist->mailinglist_name.'</a>';
					break;
				case 'single':
					$thelist .= '<a href="' . get_mailinglist_link($mailinglist->term_id) . '" title="' . sprintf(__("View all users in %s",'MailPress'), $mailinglist->name) . '" ' . $rel . '>';
					if ( $mailinglist->parent )	$thelist .= get_mailinglist_parents($mailinglist->parent, FALSE);
					$thelist .= "$mailinglist->mailinglist_name</a>";
					break;
				case '':
				default:
					$thelist .= '<a href="' . get_mailinglist_link($mailinglist->term_id) . '" title="' . sprintf(__("View all users in %s",'MailPress'), $mailinglist->name) . '" ' . $rel . '>' . $mailinglist->name.'</a>';
			}
			++$i;
		}
	}
	return apply_filters('the_mailinglist', $thelist, $separator, $parents);
}

function is_mailinglist ($mailinglist = '') 
{
	global $wp_query;
	if ( !$wp_query->is_category )	return false;
  
	if ( empty($mailinglist) )		return true;

	$mailinglist_obj = $wp_query->get_queried_object();

	$mailinglist = (array) $mailinglist;

	if ( in_array( $mailinglist_obj->term_id, $mailinglist ) )		return true;
	elseif ( in_array( $mailinglist_obj->name, $mailinglist ) )		return true;
	elseif ( in_array( $mailinglist_obj->slug, $mailinglist ) )		return true;

	return false;
}

/*
 * in_mailinglist() - Checks whether the current MP User is within a particular mailing list
 *
 * This function checks to see if the post is within the supplied mailinglist.  The mailing list
 * can be specified by number or name and will be checked as a name first to allow for mailing lists with numeric names.
 * Note: Prior to v2.5 of WordPress mailinglist names where not supported.
 *
*/

function in_mailinglist( $mailinglist, $mp_user_id ) 
{

	if ( empty($mailinglist) )	return false;

	// If mailinglist is not an int, check to see if it's a name

	if ( ! is_int($mailinglist) ) 
	{	
		$mailinglist_ID = get_mailinglist_ID($mailinglist);
		if ( $mailinglist_ID )	$mailinglist = $mailinglist_ID;
	}

	$mailinglists = get_object_term_cache($mp_user_id, MailPress_taxonomy_mailing_lists);

	if ( false === $mailinglists )	$mailinglists = wp_get_object_terms($mp_user_id, MailPress_taxonomy_mailing_lists);

	foreach ($mailinglists as $k => $v) if ($v->term_id == $mailinglist) return true;

	return false;
}

/*
function the_mailinglist($separator = '', $parents='', $post_id = false) 
{
	echo get_the_mailinglist_list($separator, $parents, $post_id);
}

function mailinglist_description($x = 0) 
{
	global $mailinglist;
	if ( !$$mailinglist )	$x = $mailinglist;

	return get_term_field('description', $x, MailPress_taxonomy_mailing_lists);
}

*/


//
// DROPDOWN Mailing lists
//
function mp_dropdown_mailinglists($args = '') 
{
	$defaults = array('child_of' 		=> 0,
				'class'		=> 'postform',
				'depth' 		=> 0,
				'echo' 		=> 1,
				'exclude' 		=> '',
				'hide_empty' 	=> 1,
				'hierarchical'	=> 0,
				'htmlid'		=> 1,
				'name' 		=> 'mailinglist',
				'order' 		=> 'ASC',
				'orderby' 		=> 'ID',
				'selected' 		=> 0,
				'show_count' 	=> 0,
				'show_last_update'=> 0, 
				'show_option_all' => '', 
				'show_option_none'=> '',
				'tab_index' 	=> 0
				);

	$defaults['selected'] = ( is_mailinglist() ) ? get_query_var('mailinglist') : 0;

	$r = wp_parse_args( $args, $defaults );
	$r['include_last_update_time'] = $r['show_last_update'];
	extract( $r );

	$tab_index_attribute = '';
	if ( (int) $tab_index > 0 )	$tab_index_attribute = " tabindex=\"$tab_index\"";

	$mailinglists = get_mailinglists($r);

	$output = '';
	if ( ! empty($mailinglists) ) 
	{
		$htmlid = ($htmlid) ? "id='$name'" : '';
		$output = "<select name='$name' $htmlid class='$class' $tab_index_attribute>\n";

		if ( $show_option_all ) 
		{
			$show_option_all = apply_filters('list_mailinglists', $show_option_all);
			$output .= "\t<option value='0'>$show_option_all</option>\n";
		}

		if ( $show_option_none) 
		{
			$show_option_none = apply_filters('list_mailinglists', $show_option_none);
			$output .= "\t<option value='-1'>$show_option_none</option>\n";
		}

		if ( $hierarchical )	$depth = $r['depth'];  		// Walk the full depth.
		else				$depth = -1; 			// Flat.

		$output .= walk_mailinglist_dropdown_tree($mailinglists, $depth, $r);
		$output .= "</select>\n";
	}

	$output = apply_filters('mp_dropdown_mailinglists', $output);

	if ( $echo )	echo $output;

	return $output;
}

function walk_mailinglist_dropdown_tree() 
{
	$walker = new Walker_MailinglistDropdown;
	$args = func_get_args();
	return call_user_func_array(array(&$walker, 'walk'), $args);
}

class Walker_MailinglistDropdown extends Walker 
{
	var $tree_type = MailPress_taxonomy_mailing_lists;
	var $db_fields = array ('parent' => 'parent', 'id' => 'term_id'); //TODO: decouple this
	
	function start_el(&$output, $mailinglist, $depth, $args) 
	{
		$pad = str_repeat('&nbsp;', $depth * 3);

		$mailinglist_name = apply_filters('list_mailinglists', $mailinglist->name, $mailinglist);
		$output .= "\t<option value=\"".$mailinglist->term_id."\"";
		if ( $mailinglist->term_id == $args['selected'] ) $output .= ' selected="selected"';
		$output .= '>';
		$output .= $pad.$mailinglist_name;
		if ( $args['show_count'] ) $output .= '&nbsp;&nbsp;('. $mailinglist->count .')';
		if ( $args['show_last_update'] ) 
		{
			$format = 'Y-m-d';
			$output .= '&nbsp;&nbsp;' . gmdate($format, $category->last_update_timestamp);
		}
		$output .= "</option>\n";
	}
}
//
// 	ARRAY Mailing lists
//
function mp_array_mailinglists($args = '') 
{
	$defaults = array('child_of' 		=> 0,
				'class'		=> 'postform',
				'depth' 		=> 0,
				'exclude' 		=> '',
				'hide_empty' 	=> 1,
				'hierarchical'	=> 0,
				'name' 		=> 'mailinglist',
				'order' 		=> 'ASC',
				'orderby' 		=> 'ID',
				'selected' 		=> 0,
				'show_count' 	=> 0,
				'show_last_update'=> 0, 
				'show_option_all' => '', 
				'show_option_none'=> '',
				'tab_index' 	=> 0
				);

	$defaults['selected'] = ( is_mailinglist() ) ? get_query_var('mailinglist') : 0;

	$r = wp_parse_args( $args, $defaults );
	$r['include_last_update_time'] = $r['show_last_update'];
	extract( $r );

	$tab_index_attribute = '';
	if ( (int) $tab_index > 0 )	$tab_index_attribute = " tabindex=\"$tab_index\"";

	$mailinglists = get_mailinglists($r);

	$output = array();
	if ( ! empty($mailinglists) ) 
	{
		if ( $show_option_all ) 
		{
			$show_option_all = apply_filters('list_mailinglists', $show_option_all);
			$output [0] = $show_option_all;
		}

		if ( $show_option_none) 
		{
			$show_option_none = apply_filters('list_mailinglists', $show_option_none);
			$output [-1] = $show_option_none;
		}

		if ( $hierarchical )		$depth = $r['depth'];  			// Walk the full depth.
		else					$depth = -1; 				// Flat.

		$output = array_merge(walk_mailinglist_array($mailinglists, $depth, $r),$output );
	}

	$output = apply_filters('mp_array_mailinglists', $output);

	return $output;
}

function walk_mailinglist_array() 
{
	$walker = new Walker_MailinglistArray;
	$args = func_get_args();
	return call_user_func_array(array(&$walker, 'walk'), $args);
}

class Walker_MailinglistArray extends Walker 
{
	var $tree_type = MailPress_taxonomy_mailing_lists;
	var $db_fields = array ('parent' => 'parent', 'id' => 'term_id'); //TODO: decouple this
	
	function start_el(&$output, $mailinglist, $depth, $args) 
	{
		$pad = str_repeat('&nbsp;', $depth * 3);

		$mailinglist_name = apply_filters('list_mailinglists', $mailinglist->name, $mailinglist);
		$x = MailPress_taxonomy_mailing_lists . '~' . $mailinglist->term_id;
		$output [$x] = $pad.$mailinglist_name;
	}
}
//
// HTML LIST Mailing lists
//

function mp_list_mailinglists($args = '') 
{
	$defaults = array('child_of' 			=> 0,
				'current_mailinglist' 	=> 0,
				'depth' 			=> 0,
				'echo' 			=> 1,
				'exclude' 			=> '',
				'feed' 			=> '',
				'feed_image' 		=> '',
				'feed_type' 		=> '',
				'hide_empty' 		=> 1,
				'hierarchical' 		=> true,
				'order' 			=> 'ASC',
				'orderby' 			=> 'name',
				'show_count' 		=> 0,
				'show_last_update' 	=> 0,
				'show_option_all' 	=> '',
				'style' 			=> 'list',
				'title_li' 			=> __('Mailing lists','MailPress'),
				'use_desc_for_title' 	=> 1
				);

	$r = wp_parse_args( $args, $defaults );

	if ( !isset( $r['pad_counts'] ) && $r['show_count'] && $r['hierarchical'] ) 	$r['pad_counts'] = true;
	if (  isset( $r['show_date'] ) ) 								$r['include_last_update_time'] = $r['show_date'];

	extract( $r );

	$mailinglists = get_mailinglists($r);

	$output = '';
	if ( $title_li && 'list' == $style )	$output = '<li class="mailinglists">' . $r['title_li'] . '<ul>';

	if ( empty($mailinglists) ) 
	{
		if ( 'list' == $style )	$output .= '<li>' . __("No mailing lists",'MailPress') . '</li>';
		else				$output .= __("No mailing lists",'MailPress');
	}
	else
	{
		global $wp_query;

		if( !empty($show_option_all) )
			if ('list' == $style )	$output .= '<li><a href="' .  get_bloginfo('url')  . '">' . $show_option_all . '</a></li>';
			else				$output .= '<a href="' .  get_bloginfo('url')  . '">' . $show_option_all . '</a>';

		if ( empty( $r['current_mailinglist'] ) && is_mailinglist() )	$r['current_mailinglist'] = $wp_query->get_queried_object_id();

		if ( $hierarchical )	$depth = $r['depth'];
		else				$depth = -1; 			// Flat.

		$output .= walk_mailinglist_tree($mailinglists, $depth, $r);
	}

	if ( $title_li && 'list' == $style )	$output .= '</ul></li>';

	$output = apply_filters('wp_list_mailinglists', $output);

	if ( $echo )	echo $output;
	else			return $output;
}

function walk_mailinglist_tree() 
{
	$walker = new Walker_Mailinglist;
	$args = func_get_args();
	return call_user_func_array(array(&$walker, 'walk'), $args);
}
?>