<?php

//
// A Big Mess. Also some neat functions that are nicely written.
//

// Ugly recursive category stuff.
function mailinglist_rows( $parent = 0, $level = 0, $mailinglists = 0, $page = 1, $per_page = 20 ) {
	$count = 0;
	_mailinglist_rows($mailinglists, $count, $parent, $level, $page, $per_page);
}

function _mailinglist_rows( $mailinglists, &$count, $parent = 0, $level = 0, $page = 1, $per_page = 20 ) {
	if ( empty($mailinglists) ) {
		$args = array('hide_empty' => 0);
		if ( !empty($_GET['s']) )
			$args['search'] = $_GET['s'];
		$mailinglists = get_mailinglists( $args );
	}

	if ( !$mailinglists )
		return false;

	$children = _get_term_hierarchy(MailPress_taxonomy_mailing_lists);

	$start = ($page - 1) * $per_page;
	$end = $start + $per_page;
	$i = -1;
	ob_start();
	foreach ( $mailinglists as $mailinglist ) {
		if ( $count >= $end )
			break;

		$i++;

		if ( $mailinglist->parent != $parent )
			continue;

		// If the page starts in a subtree, print the parents.
		if ( $count == $start && $mailinglist->parent > 0 ) {
			$my_parents = array();
			$my_parent = $mailinglist->parent;
			while ( $my_parent) {
				$my_parent = get_mailinglist($my_parent);
				$my_parents[] = $my_parent;
				if ( !$my_parent->parent )
					break;
				$my_parent = $my_parent->parent;
			}
			$num_parents = count($my_parents);
			while( $my_parent = array_pop($my_parents) ) {
				echo "\t" . _mailinglist_row( $my_parent, $level - $num_parents );
				$num_parents--;
			}
		}

		if ( $count >= $start )
			echo "\t" . _mailinglist_row( $mailinglist, $level );

		unset($mailinglists[$i]); // Prune the working set		
		$count++;

		if ( isset($children[$mailinglist->term_id]) )
			_mailinglist_rows( $mailinglists, $count, $mailinglist->term_id, $level + 1, $page, $per_page );

	}

	$output = ob_get_contents();
	ob_end_clean();

	$output = apply_filters('mailinglist_rows', $output);

	echo $output;
}

function _mailinglist_row( $mailinglist, $level, $name_override = false ) {
	static $row_class = '';
	global $mp_screen;

	$mailinglist = get_mailinglist( $mailinglist );

	$default_mailinglist_id = get_option( 'MailPress_default_mailinglist' );
	$pad = str_repeat( '&#8212; ', $level );
	$name = ( $name_override ? $name_override : $pad . ' ' . $mailinglist->name );
	$edit_link   = MailPress_mailinglists . "&amp;action=edit&amp;id=$mailinglist->term_id";
	$delete_link = MailPress_mailinglists . "&amp;action=delete&amp;id=$mailinglist->term_id";
	if (true) {
		$edit = "<a class='row-title' href='$edit_link' title='" . attribute_escape(sprintf(__('Edit "%s"'), $mailinglist->name)) . "'>$name</a><br />";
		$actions = array();
		$actions['edit'] = '<a href="' . $edit_link . '">' . __('Edit') . '</a>';
		if ( $default_mailinglist_id != $mailinglist->term_id )
			$actions['delete'] = "<a class='submitdelete' href='" . wp_nonce_url($delete_link, 'delete-mailinglist_' . $mailinglist->term_id) . "' onclick=\"if ( confirm('" . js_escape(sprintf(__("You are about to delete this mailing list '%s'\n 'Cancel' to stop, 'OK' to delete.",'MailPress'), $name )) . "') ) { return true;}return false;\">" . __('Delete') . "</a>";
		$action_count = count($actions);
		$i = 0;
		$edit .= "<div class='row-actions'>"; 
		foreach ( $actions as $action => $link ) {
			++$i;
			( $i == $action_count ) ? $sep = '' : $sep = ' | ';
			$edit .= "<span class='$action'>$link</span>$sep";
		}
		$edit .= "</div>"; 
	} else {
		$edit = $name;
	}

	$row_class = 'alternate' == $row_class ? '' : 'alternate';

	$mailinglist->count = number_format_i18n( $mailinglist->count );

	if (current_user_can('MailPress_edit_users')) 
		$mp_users_count = ( $mailinglist->count > 0 ) ? "<a href='" . MailPress_users . "&amp;mailinglist=$mailinglist->term_id'>$mailinglist->count</a>" : $mailinglist->count;
	else
		$mp_users_count =  $mailinglist->count;

	$output = "<tr id='mailinglist-$mailinglist->term_id' class='iedit $row_class'>";

	$columns = MailPress_mailing_lists::manage_list_columns();
	$hidden = (array) get_user_option( "manage" . $mp_screen . "columnshidden" );
	foreach ( $columns as $column_name => $column_display_name ) {
		$class = "class=\"$column_name column-$column_name\"";

		$style = '';
		if ( in_array($column_name, $hidden) )
			$style = ' style="display:none;"';

		$attributes = "$class$style";

		switch ($column_name) {
			case 'cb':
				$output .= "<th scope='row' class='check-column'>";
				if ( $default_mailinglist_id != $mailinglist->term_id ) {
					$output .= "<input type='checkbox' name='delete[]' value='$mailinglist->term_id' />";
				} else {
					$output .= "&nbsp;";
				}
				$output .= '</th>';
			break;
			case 'name':
 				$output .= "<td $attributes>$edit</td>";
 			break;
 			case 'desc':
 				$output .= "<td $attributes>$mailinglist->description</td>";
 			break;
			case 'num':
 				$attributes = 'class="num column-num"' . $style;
				$output .= "<td $attributes>$mp_users_count</td>\n";
 			break;
		}
	}
	$output .= '</tr>';

	return apply_filters('mailinglist_row', $output);
}

//
// Category Checklists
//
class Walker_Mailinglist_Checklist extends Walker {
	var $tree_type = MailPress_taxonomy_mailing_lists;
	var $db_fields = array ('parent' => 'parent', 'id' => 'term_id'); //TODO: decouple this

	function start_lvl(&$output, $depth, $args) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent<ul class='children'>\n";
	}

	function end_lvl(&$output, $depth, $args) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}

	function start_el(&$output, $mailinglist, $depth, $args) {
		extract($args);

		$class = in_array( $mailinglist->term_id, $popular_mailinglists ) ? ' class="popular-mailinglist"' : '';
		$output .= "\n<li id='mailinglist-$mailinglist->term_id'$class>" . '<label for="in-mailinglist-' . $mailinglist->term_id . '" class="selectit"><input value="' . $mailinglist->term_id . '" type="checkbox" name="mp_user_mailinglists[]" id="in-mailinglist-' . $mailinglist->term_id . '"' . (in_array( $mailinglist->term_id, $selected_mailinglists ) ? ' checked="checked"' : "" ) . ' /> ' . wp_specialchars( apply_filters('the_mailinglist', $mailinglist->name )) . '</label>';
	}

	function end_el(&$output, $mailinglist, $depth, $args) {
		$output .= "</li>\n";
	}
}
function mp_get_mp_user_mailinglist()
{
}
function mp_mailinglist_checklist( $mp_user_id = 0, $descendants_and_self = 0, $selected_mailinglists = false, $popular_mailinglists = false ) {
	$walker = new Walker_Mailinglist_Checklist;
	$descendants_and_self = (int) $descendants_and_self;

	$args = array();
	
	if ( is_array( $selected_mailinglists ) )
		$args['selected_mailinglists'] = $selected_mailinglists;
	elseif ( $mp_user_id )
		$args['selected_mailinglists'] = MailPress_mailing_lists::get_user_mailinglists($mp_user_id);
	else
		$args['selected_mailinglists'] = array();

	if ( is_array( $popular_mailinglists ) )
		$args['popular_mailinglists'] = $popular_mailinglists;
	else
		$args['popular_mailinglists'] = get_terms( MailPress_taxonomy_mailing_lists, array( 'fields' => 'ids', 'orderby' => 'count', 'order' => 'DESC', 'number' => 10, 'hierarchical' => false ) );

	if ( $descendants_and_self ) {
		$mailinglists = get_mailinglists( "child_of=$descendants_and_self&hierarchical=0&hide_empty=0" );
		$self = get_mailinglist( $descendants_and_self );
		array_unshift( $mailinglists, $self );
	} else {
		$mailinglists = get_mailinglists('get=all');
	}

	// Post process $mailinglists rather than adding an exclude to the get_terms() query to keep the query the same across all mp_users (for any query cache)
	$checked_mailinglists = array();
	for ( $i = 0; isset($mailinglists[$i]); $i++ ) {
		if ( in_array($mailinglists[$i]->term_id, $args['selected_mailinglists']) ) {
			$checked_mailinglists[] = $mailinglists[$i];
			unset($mailinglists[$i]);
		}
	}

	// Put checked mailinglists on top
	echo call_user_func_array(array(&$walker, 'walk'), array($checked_mailinglists, 0, $args));
	// Then the rest of them
	echo call_user_func_array(array(&$walker, 'walk'), array($mailinglists, 0, $args));
}

function mp_popular_terms_checklist( $taxonomy, $mp_user_id, $default = 0, $number = 10, $echo = true ) {

	if ( $mp_user_id )
		$checked_mailinglists = MailPress_mailing_lists::get_user_mailinglists($mp_user_id);
	else
		$checked_mailinglists = array();
	$mailinglists = get_terms( $taxonomy, array( 'orderby' => 'count', 'order' => 'DESC', 'number' => $number, 'hierarchical' => false ) );

	$popular_ids = array();
	foreach ( (array) $mailinglists as $mailinglist ) {
		$popular_ids[] = $mailinglist->term_id;
		if ( !$echo ) // hack for AJAX use
			continue;
		$id = "popular-mailinglist-$mailinglist->term_id";
		?>

		<li id="<?php echo $id; ?>" class="popular-mailinglist">
			<label class="selectit" for="in-<?php echo $id; ?>">
			<input id="in-<?php echo $id; ?>" type="checkbox" value="<?php echo (int) $mailinglist->term_id; ?>" />
				<?php echo wp_specialchars( apply_filters('the_mailinglist', $mailinglist->name ) ); ?>
			</label>
		</li>

		<?php
	}
	return $popular_ids;
}

?>