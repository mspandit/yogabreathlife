<?php

class MP_subscribe_to_comments_import {

	function MP_subscribe_to_comments_import() {
		// Nothing.
	}

	function header() {
		echo '<div class="wrap">';
		echo "<div id='icon-mailpress-tools' class='icon32'><br /></div>";
		echo '<h2>'.__('Import from Subscribe to comments','MailPress').'</h2>';
	}

	function footer() {
		echo '</div><!-- wrap -->';
	}

	function dispatch() {

		global $wpdb;
		$this->column_name = 'comment_subscribe';

		if (empty ($_GET['step']))
			$step = $_GET['step'] = 0;
		else
			$step = (int) $_GET['step'];

		$this->header();
		switch ($step) {
			case 0 :			/*Save your database ...*/
				$this->greet(); 
			break;
			case 1 :			/*Data analysis ...*/
				$this->trace = new MP_Log('MP_subscribe_to_comments_import',ABSPATH . MP_PATH,MP_MailPress_import_FOLDER,false,'MailPress_import');

				if ( $this->validate_data() )
				{

					$this->trace->log('>>>> step' . $_GET['step'] . ' >>>> ' . ' Subscribe to comments column >>> ' . $this->column_name . ' detected in >>> ' . $wpdb->comments);
					$sniff = $this->sniff();
					$file = $this->trace->file;
					$y = substr($file,strpos($file,'wp-content'));
					$this->trace->end(true);
					if ($sniff)
					{
						echo $this->step1;
					}
					else
					{
						echo '<div>';
						echo $this->step1;
						echo "<p class='submit'>\n";
						echo __('Sorry, there has been an error.','MailPress');
						echo "</p>\n";
						if ( file_exists( $file) ) : 
							echo "<p><a href='../$y' target='_blank'>" . __('See the log','MailPress') . '</a></p>';
						endif;
						echo '</div>';
					}
				}
			break;
			case 2:			/*Report ...*/

				$this->trace = new MP_Log('MP_subscribe_to_comments_import',ABSPATH . MP_PATH,MP_MailPress_import_FOLDER,false,'MailPress_import');

				$import = $this->import();

				$file = $this->trace->file;
				$y = substr($file,strpos($file,'wp-content'));
				$this->trace->end(true);

				echo '<div>';
				if ($import)
				{
					echo '<p>' . sprintf(__("<b>Data imported</b> : <i>%s</i>",'MailPress'),$this->file) . '</p>';
				}
				else 
				{
					echo '<div>';
					echo '<p>'.__('Sorry, there has been an error.','MailPress').'</p>';
					echo '</div>';
				}
				if ( file_exists( $file) ) : 
					echo "<p><a href='../$y' target='_blank'>" . __('See the log','MailPress') . '</a></p>';
				endif;
				echo '</div>';
			break;
		}
		$this->footer();
	}


// step 0

	function greet() {
		$x = "<div style='text-align:center;'>\n";
		$x .= "<br />\n";
		$x .= __('First Things First','MailPress');
		$x .= "<br />\n";		$x .= "<br />\n";
		$x .= __("Before importing your 'Subscribe to comments' datas : ",'MailPress');
		$x .= "<br />\n";		$x .= "<br />\n";
		$x .= "<span style='color:red;font-weight:bold;'>";
		$x .= __('SAVE YOUR DATABASE','MailPress');
		$x .= "</span>\n";
		$x .= "<br />\n";		$x .= "<br />\n";
		$x .= __('and make sure you can restore it !','MailPress');
		$x .= "<br />\n";		$x .= "<br />\n";
		$x .= "</div>\n";
?>
<?php MP_Admin::message($x,false); ?>
<br />
<div>
	<?php _e('Howdy! Ready to import your <b>Subscribe to comments</b> data into <b>MailPress</b> ...','MailPress'); ?>

	<p><?php _e('<b>Subscribe to comments</b> data are stored into the following table :','MailPress'); ?></p>
	<ol>
		<li>comments
			<p>
				<?php _e("In this table, subscribers can subscribe to posts comments.",'MailPress'); ?>
				<br />
				<?php _e('You will be able to convert these subscriptions to MailPress.','MailPress'); ?>
			</p>
		</li>
	</ol>
	<br />
	<p><?php _e('Note : If a subscriber already exists in MailPress, only the subscriptions to comments are added.','MailPress'); ?></p>

	<form method='post' action='<?php echo MailPress_import; ?>&amp;import=subscribe_to_comments&amp;step=1'>
		<p class='submit'>
			<input class='button-primary' type='submit' name='Submit' value='<?php  _e('Continue','MailPress'); ?>' />
		</p>
	</form>
</div>
<?php
	}

// step 1

	function validate_data()
	{
		global $wpdb;

		foreach ( (array) $wpdb->get_col("DESC $wpdb->comments", 0) as $column )
			if ($column == $this->column_name) return true;

		$this->trace->log('>>>> step' . $_GET['step'] . ' >>>> ' . ' Subscribe to comments column >>> ' . $this->column_name . ' not detected in >>> ' . $wpdb->comments);
		$file = $this->trace->file;
		$y = substr($file,strpos($file,'wp-content'));
		$this->trace->end(true);

	 	echo "<style type='text/css'> .general th {font-weight:bold;width:auto;} .general td, .general th {border:solid 1px #555;margin:0;padding:5px;vertical-align:top;} </style>";
	 	echo "<div>\n";
	 	echo "<h3>" . __('Data Analysis','MailPress') . "</h3>\n";
		echo "<table class='form-table'>\n";
		echo "<tr>\n";
		echo "<th scope='row'>" . $this->column_name . "</th>\n";
		echo "<td>\n";
		echo "<p>" . sprintf(__('*** ERROR *** Column not detected in %1$s','MailPress'),$wpdb->comments) . "</p>\n";
		echo "</td>\n";
		echo "</tr>\n";
	 	echo "</table>\n";
		echo "<p class='submit'>\n";
		echo __('Sorry, there has been an error.','MailPress');
		echo "</p>\n";
		if ( file_exists( $file) ) : 
			echo "<p><a href='../$y' target='_blank'>" . __('See the log','MailPress') . '</a></p>';
		endif;
		echo '</div>';

		return false;
	}

	function sniff() 
	{
		global $wpdb;
		$import = false;

		$this->step1  = '';

		$subs = $wpdb->get_results( "SELECT distinct LCASE(comment_author_email) as email FROM $wpdb->comments WHERE comment_subscribe='Y' AND comment_approved = '1' " );

		if ( $subs )
		{
		 	$head1  = "<style type='text/css'> .general th {font-weight:bold;width:auto;} .general td, .general th {border:solid 1px #555;margin:0;padding:5px;vertical-align:top;} </style>";
		 	$head1 .= "<h3>" . __('Data Analysis','MailPress') . "</h3>\n";
		 	$head1 .= "<form action='" . MailPress_import . "&amp;import=subscribe_to_comments&amp;step=2' method='post'><table class='form-table'>\n";

		 	$foot1 = "</table>\n";
			$foot1 .= "<p class='submit'>\n";
			$foot1 .= "<input type='submit' value='" . attribute_escape( __('Submit')) . "' />\n";
			$foot1 .= "</p>\n";
			$foot1 .= "</form>\n";

			$import = true; 
		}
		else
		{ 	
		 	$head1  = "<style type='text/css'> .general th {font-weight:bold;width:auto;} .general td, .general th {border:solid 1px #555;margin:0;padding:5px;vertical-align:top;} </style>";
		 	$head1 .= "<h3>" . __('Data Analysis','MailPress') . "</h3>\n";
			$head1 = "<table class='form-table'>\n";

		 	$foot1 = "</table>\n";
		}

		if ($subs)
		{
			$this->trace->log('>>>> step' . $_GET['step'] . ' >>>> ' . sprintf('%1$s subscriber(s) found', count($subs) ));
			
			$this->step1 .= "<tr>\n";
			$this->step1 .= "<th scope='row'>" . $wpdb->comments . "</th>\n";
			$this->step1 .= "<td>\n";
			$this->step1 .= "<p>" .  sprintf('%1$s subscriber(s) found', count($subs) ) . "</p>\n";
			$this->step1 .= "</td>\n";
			$this->step1 .= "</tr>\n";
		}
		else
		{
			$this->trace->log('>>>> step' . $_GET['step'] . ' >>>> ' . 'Comments table: no data');

			$this->step1 .= "<tr>\n";
			$this->step1 .= "<th scope='row'>" . $wpdb->comments . "</th>\n";
			$this->step1 .= "<td>\n";
			$this->step1 .= "<p>" .  __('no data','MailPress') . "</p>\n";
			$this->step1 .= "</td>\n";
			$this->step1 .= "</tr>\n";
		}

		$this->step1 = $head1 . $this->step1 . $foot1;
		return $import;
	}

// step 2

	function import() 
	{
		global $wpdb;
		$this->import_subscribe_to_comments();
		return true;
	}

	function import_subscribe_to_comments() 
	{
		global $wpdb;

		$subs = $wpdb->get_results( "SELECT comment_author_email as email, comment_post_ID as post_ID FROM $wpdb->comments WHERE comment_subscribe='Y' AND comment_approved = '1' order by email " );

		if ($subs) 
		{
			$this->trace->log('>>>> step' . $_GET['step'] . ' >>>> ' . 'Importing from subscribe to comments');

			$email = '';
			foreach ($subs as $sub)
			{
				if ($email != $sub->email)
				{
					$email = $sub->email;
					if (MailPress::is_email($email))
					{
						$mp_user_id = MailPress_import::sync_mp_user($email,$this->trace,'waiting');
					}
					else
					{
						$mp_user_id = false;
						$this->trace->log('>>>> step' . $_GET['step'] . ' >>>> **' . $email . ' not an email **');
					}

				}

				if ($mp_user_id)
				{
					$postid = $sub->post_ID; 
					update_post_meta($postid,'_MailPress_subscribe_to_comments_',$mp_user_id);
					MailPress::update_stats('c',$postid,1);
					$this->trace->log('>>>> step' . $_GET['step'] . ' >>>> ' . $email . ' subscribed to post #' . $postid);
				}
			}
		}
		elseif($wpdb->last_error)
		{
			$this->trace->log('>>>> step' . $_GET['step'] . ' >>>> ' . sprintf('*** ERROR *** Database error : %1$s', $wpdb->last_error));
		}
		else
		{
			$this->trace->log('>>>> step' . $_GET['step'] . ' >>>> ' . 'no data');
		}

		return true;
	}
}

$MP_subscribe_to_comments_import = new MP_subscribe_to_comments_import();

mp_register_importer('subscribe_to_comments', 'subscribe to comments', __('Import data from <strong>subscribe to comments</strong> plugin.','MailPress'), array ($MP_subscribe_to_comments_import, 'dispatch'));

?>