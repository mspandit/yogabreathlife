
jQuery(document).ready( function() {

	// Autoresponder
	jQuery('#the-arlist').wpList({
						response: 'ajar-response',
						addAfter: function( xml, s ) {jQuery('table#list-table').show();}, 
						addBefore: function( s ) {s.data += '&mail_id=' + jQuery('#mail_id').val(); return s;}
	});

});
