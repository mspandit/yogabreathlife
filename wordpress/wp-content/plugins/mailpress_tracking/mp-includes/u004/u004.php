<?php
/*
u004
*/
	function MailPress_tracking_u004($mp_user)
	{
		global $wpdb;
		$query = "SELECT DATE(tmstp) as tmstp, count(*) as count FROM $wpdb->mp_tracks WHERE user_id = " . $mp_user->id . " AND track <> '" . MailPress_tracking_openedmail . "' GROUP BY 1 ORDER BY 1 DESC ;";
		$tracks = $wpdb->get_results($query);
		if ($tracks) foreach($tracks as $track) echo $track->tmstp . ' <b>' . $track->count . '</b><br />';
	}
?>