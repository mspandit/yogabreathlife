
jQuery(document).ready( function() {

	// Custom Fields
	jQuery('#the-list').wpList({ 
						addAfter: function( xml, s ) {jQuery('table#list-table').show();}, 
						addBefore: function( s ) {s.data += '&mp_user_id=' + jQuery('#mp_user_id').val(); return s;}
	});

});
