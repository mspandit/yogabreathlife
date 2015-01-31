jQuery(document).ready( function() {

	jQuery('input#show_mailinglists').click( function() {
		var checked = jQuery(this).attr('checked');

		if (!checked) 	jQuery('table#mailinglists').addClass('hidden');
		else			jQuery('table#mailinglists').removeClass('hidden');

	})

});