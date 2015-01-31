var theList; var theExtraList;
jQuery(function($) {

var dimAfter = function( r, settings ) {
}

var delAfter = function( r, settings ) {
	if ( theExtraList.size() == 0 || theExtraList.children().size() == 0 ) {
		return;
	}

	theList.get(0).wpList.add( theExtraList.children(':eq(0)').remove().clone() );
	$('#get-extra-files').submit();
}

theExtraList = $('#the-extra-file-list').wpList( { alt: '', delColor: 'none', addColor: 'none' } );
theList = $('#the-file-list').wpList( { alt: '', dimAfter: dimAfter, delAfter: delAfter, addColor: 'none' } );

} );
