
jQuery(document).ready( function() {

	// Custom Fields
	jQuery('#the-list').wpList({ 
						addAfter: function( xml, s ) {jQuery('table#list-table').show();}, 
						addBefore: function( s ) {s.data += '&mail_id=' + jQuery('#mail_id').val(); return s;}
	});

});
