<?php
/*
m001 last 10 actions
*/
	function MailPress_tracking_m001($mail)
	{
		global $wpdb;
		$query = "SELECT * FROM $wpdb->mp_tracks WHERE mail_id = " . $mail->id . " ORDER BY tmstp DESC LIMIT 10;";
		$tracks = $wpdb->get_results($query);
		if ($tracks) 
		{
			echo '<table>';

foreach($tracks as $track) echo '<tr><td>' . $track->tmstp . '</td><td>&nbsp;' . MP_User::get_email($track->user_id) . '</td><td>&nbsp;' . MailPress_tracking::translate_track($track->track, $track->mail_id) . '</td></tr>';
			echo '</table>';
		}
	}
?>