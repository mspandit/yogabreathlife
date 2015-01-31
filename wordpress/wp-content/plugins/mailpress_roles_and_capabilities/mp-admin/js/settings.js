jQuery(document).ready( function() {

	jQuery('td.capacity > input').click( function() {
		var name = jQuery(this).attr('name');
		var checked = jQuery(this).attr('checked');

		var r_and_c = name.split(/\[|\]\[|]/g);

		var spanid = r_and_c[1] + '_' + r_and_c[2] ;
//ajax
		var rc_data = {	action:	"r_and_c",
					role:		r_and_c[1],
					capability: r_and_c[2],
					add:		(checked) ? '1' : '0'
				  }

		jQuery.ajax({
			data : rc_data,
//			beforeSend :
			type:"POST",
			url:settingsL10n.requestFile,
			success: crko_vs_crok(checked,spanid)
				});
		})

});

function crko_vs_crok(checked,spanid)
{
	jQuery('span#'+spanid).removeClass( (checked) ? 'crko' : 'crok' ).addClass( (checked) ? 'crok' : 'crko' );
}