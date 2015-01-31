<?php
/**
 * MailPress Mailing list API
 *
 */

/**
 * Retrieves all mailing list IDs.
 *
 */
function get_all_mailinglist_ids() 
{
	if ( ! $mailinglist_ids = wp_cache_get('all_mailinglist_ids', MailPress_taxonomy_mailing_lists) ) 
	{
		$mailinglist_ids = get_terms(MailPress_taxonomy_mailing_lists, 'fields=ids&get=all');
		wp_cache_add('all_mailinglist_ids', $mailinglist_ids, MailPress_taxonomy_mailing_lists);
	}
	return $mailinglist_ids;
}

/**
 * Retrieve list of mailing list  objects.
 *
 * If you change the type to 'link' in the arguments, then the link mailing list
 * will be returned instead. Also all mailing lists will be updated to be backwards
 * compatible with pre-2.3 plugins and themes.
 *
 * @since 2.1.0
 * @see get_terms() Type of arguments that can be changed.
 * @link http://codex.wordpress.org/Function_Reference/get_mailinglists
 *
 * @param string|array $args Optional. Change the defaults retrieving mailing lists.
 * @return array List of mailing lists.
 */
function &get_mailinglists($args = '')
{
	$defaults = array('type' => MailPress_taxonomy_mailing_lists);
	$args = wp_parse_args($args, $defaults);

	$taxonomy = MailPress_taxonomy_mailing_lists;

	$mailinglists = get_terms($taxonomy, $args);

	return $mailinglists;
}

/**
 * Retrieves mailing list data given a mailing list ID or mailing list object.
 *
 * If you pass the $mailing list parameter an object, which is assumed to be the
 * mailing list row object retrieved the database. It will cache the mailing list data.
 *
 * If you pass $mailing list an integer of the mailing list ID, then that mailing list will
 * be retrieved from the database, if it isn't already cached, and pass it back.
 *
 * If you look at get_term(), then both types will be passed through several
 * filters and finally sanitized based on the $filter parameter value.
 *
 * The mailing list will converted to maintain backwards compatibility.
 *
 * @since 2.1.0
 * @uses get_term() Used to get the mailing list data from the taxonomy.
 *
 * @param int|object $mailinglist Mailing list ID or Mailing list row object
 * @param string $output Optional. Constant OBJECT, ARRAY_A, or ARRAY_N
 * @param string $filter Optional. Default is raw or no WordPress defined filter will applied.
 * @return mixed Mailing list data in type defined by $output parameter.
 */
function &get_mailinglist($mailinglist, $output = OBJECT, $filter = 'raw') 
{
	$mailinglist = get_term($mailinglist, MailPress_taxonomy_mailing_lists, $output, $filter);
	if ( is_wp_error( $mailinglist ) )	return $mailinglist;
	return $mailinglist;
}

/**
 * Retrieve mailing list based on URL containing the mailing list slug.
 *
 * Breaks the $mailinglist_path parameter up to get the mailing list slug.
 *
 * Tries to find the child path and will return it. If it doesn't find a
 * match, then it will return the first mailing list matching slug, if $full_match,
 * is set to false. If it does not, then it will return null.
 *
 * It is also possible that it will return a WP_Error object on failure. Check
 * for it when using this function.
 *
 * @since 2.1.0
 *
 * @param string $mailinglist_path URL containing mailing list slugs.
 * @param bool $full_match Optional. Whether should match full path or not.
 * @param string $output Optional. Constant OBJECT, ARRAY_A, or ARRAY_N
 * @return null|object|array Null on failure. Type is based on $output value.
 */
function get_mailinglist_by_path($mailinglist_path, $full_match = true, $output = OBJECT) 
{
	$mailinglist_path = rawurlencode(urldecode($mailinglist_path));
	$mailinglist_path = str_replace('%2F', '/', $mailinglist_path);
	$mailinglist_path = str_replace('%20', ' ', $mailinglist_path);
	$mailinglist_paths = '/' . trim($mailinglist_path, '/');
	$leaf_path  = sanitize_title(basename($mailinglist_paths));
	$mailinglist_paths = explode('/', $mailinglist_paths);
	$full_path = '';
	foreach ( (array) $mailinglist_paths as $pathdir )		$full_path .= ( $pathdir != '' ? '/' : '' ) . sanitize_title($pathdir);

	$mailinglists = get_terms(MailPress_taxonomy_mailing_lists, "get=all&slug=$leaf_path");

	if ( empty($mailinglists) )	return null;

	foreach ($mailinglists as $mailinglist) 
{
		$path = '/' . $leaf_path;
		$curmailinglist = $mailinglist;
		while ( ($curmailinglist->parent != 0) && ($curmailinglist->parent != $curmailinglist->term_id) ) 
		{
			$curmailinglist = get_term($curmailinglist->parent, MailPress_taxonomy_mailing_lists);
			if ( is_wp_error( $curmailinglist ) )	return $curmailinglist;
			$path = '/' . $curmailinglist->slug . $path;
		}

		if ( $path == $full_path )	return get_mailinglist($mailinglist->term_id, $output);
	}

// If full matching is not required, return the first mailinglist that matches the leaf.
	if ( ! $full_match )	return get_mailinglist($mailinglists[0]->term_id, $output);
	return null;
}

/**
 * Retrieve mailinglist object by mailinglist slug.
 *
 * @since 2.3.0
 *
 * @param string $slug The mailinglist slug.
 * @return object Mailinglist data object
 */
function get_mailinglist_by_slug( $slug  ) 
{
	$mailinglist = get_term_by('slug', $slug, MailPress_taxonomy_mailing_lists);
	return $mailinglist;
}


/**
 * Retrieve the ID of a mailing list from its name.
 *
 * @since 1.0.0
 *
 * @param string $mailinglist_name Optional. Default is 'General' and can be any mailing list name.
 * @return int 0, if failure and ID of mailinglist on success.
 */
function get_mailinglist_ID($mailinglist_name='General') 
{
	$mailinglist = get_term_by('name', $mailinglist_name, MailPress_taxonomy_mailing_lists);
	if ($mailinglist)	return $mailinglist->term_id;
	return 0;
}

/**
 * Retrieve the mailing list name by the mailing list ID.
 *
 * @since 0.71
 * @deprecated Use get_mailinglist_name()
 * @see get_mailinglist_name() get_mailinglistname() is deprecated in favor of get_mailinglist_name().
 *
 * @param int $mailinglist_ID Mailing list ID
 * @return string mailing list name
 */
function get_mailinglistname($mailinglist_ID) 
{
	return get_mailinglist_name($mailinglist_ID);
}

/**
 * Retrieve the name of a mailing list from its ID.
 *
 * @since 1.0.0
 *
 * @param int $mailinglist_id Mailing list ID
 * @return string Mailing list name
 */
function get_mailinglist_name($mailinglist_id) 
{
	$mailinglist_id = (int) $mailinglist_id;
	$mailinglist = &get_mailinglist($mailinglist_id);
	return $mailinglist->name;
}

/**
 * Check if a mailing list is an ancestor of another mailing list.
 *
 * You can use either an id or the mailing list object for both parameters. If you
 * use an integer the mailing list will be retrieved.
 *
 * @since 2.1.0
 *
 * @param int|object $mailinglist1 ID or object to check if this is the parent mailing list.
 * @param int|object $mailinglist2 The child mailing list.
 * @return bool Whether $mailinglist2 is child of $mailinglist1
 */
function mailinglist_is_ancestor_of($mailinglist1, $mailinglist2) 
{
	if ( is_int($mailinglist1) )	$mailinglist1 = & get_mailinglist($mailinglist1);
	if ( is_int($mailinglist2) )	$mailinglist2 = & get_mailinglist($mailinglist2);

	if ( !$mailinglist1->term_id || !$mailinglist2->parent )	return false;

	if ( $mailinglist2->parent == $mailinglist1->term_id )	return true;

	return mailinglist_is_ancestor_of($mailinglist1, get_mailinglist($mailinglist2->parent));
}

/**
 * Sanitizes mailinglist data based on context.
 *
 * @since 2.3.0
 * @uses sanitize_term() See this function for what context are supported.
 *
 * @param object|array $mailinglist Mailing list data
 * @param string $context Optional. Default is 'display'.
 * @return object|array Same type as $mailinglist with sanitized data for safe use.
 */
function sanitize_mailinglist($mailinglist, $context = 'display') 
{
	return sanitize_term($mailinglist, MailPress_taxonomy_mailing_lists, $context);
}

/**
 * Sanitizes data in single mailinglist key field.
 *
 * @since 2.3.0
 * @uses sanitize_term_field() See function for more details.
 *
 * @param string $field Mailing list key to sanitize
 * @param mixed $value Mailing list value to sanitize
 * @param int $mailinglist_id Mailing list ID
 * @param string $context What filter to use, 'raw', 'display', etc.
 * @return mixed Same type as $value after $value has been sanitized.
 */
function sanitize_mailinglist_field($field, $value, $mailinglist_id, $context) 
{
	return sanitize_term_field($field, $value, $mailinglist_id, MailPress_taxonomy_mailing_lists, $context);
}

/* Cache */

/**
 * Update the categories cache.
 *
 * This function does not appear to be used anymore or does not appear to be
 * needed. It might be a legacy function left over from when there was a need
 * for updating the mailinglist cache.
 *
 * @since 1.5.0
 *
 * @return bool Always return True
 */
function update_mailinglist_cache() 
{
	return true;
}

/**
 * Remove the mailinglist cache data based on ID.
 *
 * @since 2.1.0
 * @uses clean_term_cache() Clears the cache for the mailinglist based on ID
 *
 * @param int $id Mailing list ID
 */
function clean_mailinglist_cache($id) 
{
	clean_term_cache($id, MailPress_taxonomy_mailing_lists);
}
?>