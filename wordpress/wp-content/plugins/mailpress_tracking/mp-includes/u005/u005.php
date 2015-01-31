<?php
/*
u005
*/
	function MailPress_tracking_u005($mp_user)
	{
		global $wpdb;

     		$query = "SELECT context, count(*) as count FROM $wpdb->mp_tracks WHERE user_id = " . $mp_user->id . " GROUP BY context ORDER BY context;";
		$tracks = $wpdb->get_results($query);
		$total = 0;
		if ($tracks)
		{
			foreach($tracks as $track)
			{
				$context[$track->context] = $track->count;
				$total += $track->count;
			}
			foreach($context as $k => $v)
			{
				echo '<b>' . $k . '</b> : &nbsp;' . sprintf("%01.2f %%",100 * $v/$total ) . '&nbsp;&nbsp;&nbsp;&nbsp;';
			}
			echo '<br />';
		}
		echo '<br />';
		$query = "SELECT DISTINCT agent, ip FROM $wpdb->mp_tracks WHERE user_id = " . $mp_user->id . " LIMIT 10;";
		$tracks = $wpdb->get_results($query);

		if ($tracks) foreach($tracks as $track) {echo MailPress_tracking_u005_os($track->agent) . ' ' . MailPress_tracking_u005_browser($track->agent) . '&nbsp;&nbsp;&nbsp;@&nbsp;' . $track->ip . '<br />'; }
	}

	function MailPress_tracking_u005_browser($useragent)
	{
		$file = MP_MailPress_tracking_TMP . '/mp-includes/xml/nets.xml';
		$x = file_get_contents($file);

		$br = __('Unknown','MailPress');

		if ($x)
		{
			$xml = new SimpleXMLElement($x);

			foreach ($xml->browser as $browser)
			{
    				if (preg_match($browser->pattern, $useragent, $regmatch))
				{
					$version = false;
					$br = '';
					if (isset($browser->version))
					{
						foreach($browser->version as $attrs) $vp = (int) $attrs['pattern'];
						if (!empty($browser->version))
						{
							preg_match($browser->version, $useragent, $regmatch);
							$version = $regmatch[$vp];
						}
						else
						{
							$version = $regmatch[$vp];
						}
					}
					$version = ($version) ? $browser->name . " $version" : $browser->name;
					if (isset($browser->icon) && !empty($browser->icon))
						$br .= "<img src='" . get_option('siteurl') . '/' . MP_MailPress_tracking_PATH . 'mp-includes/images' . $browser->icon . "' alt='' />";
					if (isset($browser->link))
						$br .= "&nbsp<a href='" . $browser->link . "' title='" . $version . "' />" . $browser->name . '</a>';
					else
						$br .= '&nbsp;' . $version;
					break;
				}
			}
		}
		return $br;
	}

	function MailPress_tracking_u005_os($useragent)
	{
		$file = MP_MailPress_tracking_TMP . '/mp-includes/xml/oss.xml';
		$x = file_get_contents($file);

		$se = __('Unknown','MailPress');

		if ($x)
		{
			$xml = new SimpleXMLElement($x);

			foreach ($xml->os as $os)
			{
    				if (preg_match($os->pattern, $useragent, $regmatch))
				{
					$version = false;
					$se = '';
					if (isset($os->versions))
					{
						foreach($os->versions as $attrs) $vp = (int) $attrs['pattern'];

						if (isset($os->versions->version))
						{
							foreach($os->versions->version as $ver)
							{
								if (preg_match($ver->pattern, $regmatch[$vp]))
								{
									$version = $ver->name;
									break;
								}
							}
						}
						else
						{
							$version =  $regmatch[$vp];
						}
					}
					$version = ($version) ? $os->name . " $version" : $os->name;
					if (isset($os->icon) && !empty($os->icon))
						$se .= "<img src='" . get_option('siteurl') . '/' . MP_MailPress_tracking_PATH . 'mp-includes/images' . $os->icon . "' alt='' />";
					if (isset($se->link))
						$se .= "&nbsp<a href='" . $os->link . "' title='" . $version . "' />" . $os->name . '</a>';
					else
						$se .= '&nbsp;' . $version;
					break;
				}
			}
		}
		return $se;
	}
?>