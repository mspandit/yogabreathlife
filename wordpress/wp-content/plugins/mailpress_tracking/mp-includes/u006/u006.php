<?php
/*
u006
*/

	function MailPress_tracking_u006($mp_user)
	{
?>
<script type='text/javascript'>
/* <![CDATA[ */
<?php
		$color 	= ('fresh' == get_user_option('admin_color')) ? '' : 'b';
		$pathimg 	= MP_MailPress_tracking_TMP . '/mp-includes/u006/images/controlmap' . $color . '.png';
		$color 	= (is_file($pathimg)) ? $color : '';
		$m = array('ip_infoL10n' => array( 	'url' 	=> get_option( 'siteurl' ) . '/' . MP_MailPress_tracking_PATH . 'mp-includes/images/006/',
								'color'	=> $color,
								'zoomwide' => js_escape(__('zoom -','MailPress')),
								'zoomtight'=> js_escape(__('zoom +','MailPress')),
								'center' 	=> js_escape(__('center','MailPress')),
								'changemap'=> js_escape(__('change map','MailPress')) 
							) 
		);
		$eol = "";
		foreach ( $m as $var => $val ) {
			echo "var $var = " . MP_Admin::print_scripts_l10n_val($val);
			$eol = ",\n\t\t";
		}
		echo ";\n";

		global $mp_general;
		$Gkey = $mp_general['gmapkey'];
		$skip = array('hostname', 'countrycode', 'countryflag', 'areacode', 'dmacode', 'queries');

		global $wpdb;
		$m = array();

		$query = "SELECT DISTINCT ip FROM $wpdb->mp_tracks WHERE user_id = " . $mp_user->id . " LIMIT 10;";
		$tracks = $wpdb->get_results($query);

		if ($tracks) foreach($tracks as $track)
		{
			$x = MailPress_tracking::get_geoip($track->ip);
			if ($x)
			{
				$xml = new SimpleXMLElement ( $x );
				foreach ($xml->result[0] as $k => $v)
				{
					if (trim($v) == 'Quota exceeded') break;
					if ($v == 'n/a') continue;
					if (in_array($k,$skip)) continue;
					if (in_array($k,array('latitude','longitude'))) {$$k = $v; continue;}
				}
				if ($latitude && $longitude) $m['u006'][] = array('lat' => $latitude, 'lng' => $longitude, 'ip' => $track->ip);
			}
		} 

		$eol = "";
		foreach ( $m as $var => $val ) {
			echo "var $var = " . MP_Admin::print_scripts_l10n_val($val);
			$eol = ",\n\t\t";
		}
		echo ";\n";
?>
/* ]]> */
</script>
<script type='text/javascript' src='http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=false&amp;key=<?php echo $Gkey;?>'></script>
<script type='text/javascript' src='<?php echo get_option('siteurl') . '/' . MP_MailPress_tracking_PATH; ?>mp-includes/u006/js/user.js'></script>
<script type="text/javascript">
		jQuery(document).ready( function() { if (GBrowserIsCompatible()) ip_info('ip_info_div'); } );
</script>
		<div id='ip_info_div' style='height:300px;width:auto;'></div>
<?php
	}
?>