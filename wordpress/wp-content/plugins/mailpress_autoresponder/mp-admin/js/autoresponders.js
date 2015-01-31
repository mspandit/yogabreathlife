jQuery(function($) {
	// columns
	columns.init(adminautorespondersL10n.screen);

	// thickbox
	var thickDims = function() {
		var tbWindow = $('#TB_window');
		var H = $(window).height();
		var W = $(window).width();

		var nW = ((W - 90) > 815) ? 815 : (W - 90);
		var nH = ((H - 60) > 820) ? 820 : (H - 60);
		

		if ( tbWindow.size() ) {
			tbWindow.width( nW ).height( nH );
			$('#TB_iframeContent').width( nW ).height( nH );
			tbWindow.css({'margin-left': '-' + parseInt(( nW / 2),10) + 'px'});
			if ( typeof document.body.style.maxWidth != 'undefined' )
				tbWindow.css({'top':'30px','margin-top':'0'});
		};

		return $('a.thickbox').each( function() {
			var href = $(this).attr('href');
			if ( ! href ) return;
			href = href.replace(/&width=[0-9]+/g, '');
			href = href.replace(/&height=[0-9]+/g, '');
			$(this).attr( 'href', href + '&width=' + ( nW - 20 ) + '&height=' + ( nH - 40 ) );
		});
	};

	thickDims()
	.click( function() {
		$('#TB_title').css({'background-color':'#222','color':'#cfcfcf'});
		$('#TB_closeAjaxWindow').css({'float':'right'});
		$('#TB_ajaxWindowTitle').css({'float':'left'});

		$('#TB_iframeContent').width('100%');
		return false;
	} );

	$(window).resize( function() { thickDims() } );
});