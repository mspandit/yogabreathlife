
jQuery(document).ready( function() {

	// mailinglist tabs
	var mailinglistTabs =jQuery('#user-mailinglist-tabs').tabs();

	// Ajax Mailinglist
	var newMailinglist = jQuery('#newuser-mailinglist').one( 'focus', function() { jQuery(this).val( '' ).removeClass( 'form-input-tip' ) } );
	jQuery('#user-mailinglist-add-sumbit').click( function() { newMailinglist.focus(); } );
	var newMailinglistParent = false;
	var newMailinglistParentOption = false;
	var noSyncChecks = false; // prophylactic. necessary?
	var syncChecks = function() {
		if ( noSyncChecks )
			return;
		noSyncChecks = true;
		var th = jQuery(this);
		var c = th.is(':checked');
		var id = th.val().toString();
		jQuery('#in-mailinglist-' + id + ', #in-popular-mailinglist-' + id).attr( 'checked', c );
		noSyncChecks = false;
	};
	var popularMailinglists = jQuery('#user-mailinglistchecklist-pop :checkbox').map( function() { return parseInt(jQuery(this).val(), 10); } ).get().join(',');
	var mailinglistAddBefore = function( s ) {
		s.data += '&popular_ids=' + popularMailinglists + '&' + jQuery( '#user-mailinglistchecklist :checked' ).serialize();
		return s;
	};
	var mailinglistAddAfter = function( r, s ) {
		if ( !newMailinglistParent ) newMailinglistParent = jQuery('#newuser-mailinglist_parent');
		if ( !newMailinglistParentOption ) newMailinglistParentOption = newMailinglistParent.find( 'option[value=-1]' );
		jQuery(s.what + ' response_data', r).each( function() {
			var t = jQuery(jQuery(this).text());
			t.find( 'label' ).each( function() {
				var th = jQuery(this);
				var val = th.find('input').val();
				var id = th.find('input')[0].id
				jQuery('#' + id).change( syncChecks ).change();
				if ( newMailinglistParent.find( 'option[value=' + val + ']' ).size() )
					return;
				var name = jQuery.trim( th.text() );
				var o = jQuery( '<option value="' +  parseInt( val, 10 ) + '"></option>' ).text( name );
				newMailinglistParent.prepend( o );
			} );
			newMailinglistParentOption.attr( 'selected', true );
		} );
	};

	jQuery('#user-mailinglistchecklist').wpList( {
		alt: '',
		response: 'user-mailinglist-ajax-response',
		addBefore: mailinglistAddBefore,
		addAfter: mailinglistAddAfter
	} );

	jQuery('#user-mailinglist-add-toggle').click( function() {
		jQuery(this).parents('div:first').toggleClass( 'wp-hidden-children' );
		// mailinglistTabs.tabs( 'select', '#user-mailinglists-all' ); // this is broken (in the UI beta?)
		mailinglistTabs.find( 'a[href="#user-mailinglists-all"]' ).click();
		jQuery('#newuser-mailinglist').focus();
		return false;
	} );
	jQuery('.user-mailinglistchecklist .popular-mailinglist :checkbox').change( syncChecks ).filter( ':checked' ).change();

});
