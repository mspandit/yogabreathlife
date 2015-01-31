<?php

require_once 'parsecsv/parsecsv.lib.php';

class MP_csv_import {

	function MP_csv_import() {
		// Nothing.
	}

	function header() {
		echo '<div class="wrap">';
		echo "<div id='icon-mailpress-tools' class='icon32'><br /></div>";
		echo '<h2>'.__('Import Csv','MailPress').'</h2>';
	}

	function footer() {
		echo '</div>';
	}

	function dispatch() {
		if (empty ($_GET['step']))
			$step = $_GET['step'] = 0;
		else
			$step = (int) $_GET['step'];

		$this->header();
		switch ($step) {
			case 0 :
				$this->greet();
			break;
			case 1 :
				$this->trace = new MP_Log('MP_csv_import',ABSPATH . MP_PATH,MP_MailPress_import_FOLDER,false,'MailPress_import');
				if ( $this->handle_upload() )
				{
					$sniff = $this->sniff();
					$this->trace->end(true);
					if ($sniff)
					{
						$this->fileform();
					}
					else
					{
						echo '<div>';
						echo '<p>'.__('Sorry, there has been an error.','MailPress').'</p>';
						echo '<p><strong>' . __('Unable to determine email location','MailPress') . '</strong></p>';
						echo '</div>';
					}
				}
			break;
			case 2:
				$this->trace = new MP_Log('MP_csv_import',ABSPATH . MP_PATH,MP_MailPress_import_FOLDER,false,'MailPress_import');

				$import = $this->import( $_GET['id']);

				$file = $this->trace->file;
				$y = substr($file,strpos($file,'wp-content'));
				$this->trace->end(true);

				echo '<div>';
				if ($import)
				{
					echo '<p>' . sprintf(__("<b>File imported</b> : <i>%s</i>",'MailPress'),$this->file) . '</p>';
					echo '<p><strong>' . $import . '</strong></p>';
				}
				else 
				{
					echo '<div>';
					echo '<p>'.__('Sorry, there has been an error.','MailPress').'</p>';
					echo '<p><strong>' . $this->file . '</strong></p>';
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
		echo '<div>';
		echo '<p>'.__('Howdy! Upload your file and we&#8217;ll import the emails and much more ... into this blog.','MailPress');
		echo ' ';
		echo __('Choose a file to upload, then click Upload file and import.','MailPress').'</p>';
		echo "\n";
		wp_import_upload_form( MailPress_import . "&amp;import=csv&amp;step=1");
		echo '</div>';
	}

// step 1

	function handle_upload() {
		$file = wp_import_handle_upload();
		if ( isset($file['error']) )
		{
			$this->trace->end(true);
			echo '<div>';
			echo '<p>'.__('Sorry, there has been an error.','MailPress').'</p>';
			echo '<p><strong>' . $file['error'] . '</strong></p>';
			echo '</div>';
			return false;
		}
		
		$this->file = $file['file'];
		$this->id = (int) $file['id'];
		return true;
	}

	function sniff($first=true) 
	{
		$this->trace->log('>>>> step' . $_GET['step'] . ' file  >>> ' . $this->file);

		$this->csv = new parseCSV();
		$this->csv->auto($this->file);
		$this->hasheader = true;

		return ($first) ? $this->find_email() : true;
	}

	function find_email()
	{
		$i = 0;
		$email = array();
		foreach ($this->csv->data as $row)
		{
			foreach ($row as $k => $v)	if (MailPress::is_email($v)) if (isset($email[$k])) $email[$k]++; else $email[$k] = 1;

			$i++;
			if ($i > 9) break;
		}

		if (0 == count($email))
		{
			$this->trace->log(sprintf('Unable to determine email location'));
			return false;
		}

		asort($email);
		$this->emailcol = end(array_flip($email));
		
		$this->trace->log(sprintf('Email probably in column %s',$this->emailcol));

		return true;
	}

	function fileform() {

		if (class_exists('MailPress_mailing_lists'))
		{
			$draft_dest = $x = array (''=>'');
			$draft_dest = apply_filters('MailPress_mailing_lists',$draft_dest);
		}
?>
	<form action="<?php echo MailPress_import; ?>&amp;import=csv&amp;step=2&amp;id=<?php echo $this->id; ?>" method="post">
<?php if (class_exists('MailPress_mailing_lists')) : ?>
		<h3><?php _e('Mailing list','MailPress'); ?></h3>
		<p><?php _e('Optional, you can import the MailPress users in a specific mailing list ...','MailPress'); ?></p>
		<select name='mailinglist' id='mailinglist'>
<?php MP_Admin::select_option($draft_dest,'MailPress_mailing_list~' . get_option('MailPress_default_mailinglist')) ?>
		</select>
<?php endif; ?>
		<h3><?php _e('File scan','MailPress'); ?></h3>
		<p><?php printf(__("On the first records (see hereunder), the file scan found that the email is in column '<strong>%s</strong>'.",'MailPress'),$this->emailcol); ?>
		<?php _e('However, you can select another column.<br /> Invalid emails will not be inserted.','MailPress'); ?></p>
		<table class='widefat'>
			<thead>
				<tr>
<?php
		foreach ($this->csv->data as $row)
		{
			foreach ($row as $k => $v)
			{
?>
					<td>
						<input type="radio" name="is_email" value="<?php echo $k; ?>" <?php if ($k == $this->emailcol) echo "checked='checked'"; ?> />
						<span><?php echo $k; ?></span>
					</td>
<?php
			}
			break;
		}
?>
				</tr>
			</thead>
			<tbody>
<?php
		$i = 0;
		foreach ($this->csv->data as $row)
		{
?>
				<tr>
<?php
			foreach ($row as $k => $v)
			{
?>
					<td><span <?php if ($k == $this->emailcol) if (!MailPress::is_email($v)) echo "style='background-color:#fdd;'"; else echo "style='background-color:#dfd;'";?>><?php echo $v; ?></span></td>
<?php
			}
?>
				</tr>
<?php
			$i++;
			if ($i > 9) break;
		}
?>
			</tbody>
		</table>
		<p class='submit'>
			<input  class='button-primary' type="submit" value="<?php echo attribute_escape( __('Submit') ); ?>">
		</p>
	</form>
<?php
	}

// step 2

	function import($id) 
	{
		$this->id = (int) $id;

		$this->trace = new MP_Log('MP_csv_import',ABSPATH . MP_PATH,MP_MailPress_import_FOLDER,false,'MailPress_import');
		$this->trace->log(sprintf('Importing file'));
		
		$this->file = get_attached_file($this->id);

		$this->sniff(false);

		if ( !file_exists( $this->file) ) { $this->trace->log('File not found'); return false;}

		$this->emailcol = $_POST['is_email'];
		
		if (!empty($_POST['mailinglist']))
		{
			$mailinglist_ID = str_replace('MailPress_mailing_list~','',$_POST['mailinglist'],$mailinglist_ok);
			$mailinglist = get_mailinglistname($mailinglist_ID);
		}

		$i = 0;
		foreach ($this->csv->data as $row)
		{
			$i++;

			$curremail = trim(strtolower($row[$this->emailcol]));
			$mp_user_id = MailPress_import::sync_mp_user($curremail,$this->trace);

			if ($mp_user_id)
			{
				if ($mailinglist_ok)
				{
					MailPress_import::sync_mp_user_mailinglist($mp_user_id,$mailinglist_ID,$curremail,$mailinglist,$this->trace);
				}

				foreach ($row as $k => $v)
				{
					if ($k == $this->emailcol) continue;

					MailPress_import::sync_mp_usermeta($mp_user_id,$k,$v,$this->trace);
				}
			}
		}
		return $i;
	}

////////////////////////////////////

	function fopen($filename, $mode='r') {
		if ( $this->has_gzip() )
			return gzopen($filename, $mode);
		return fopen($filename, $mode);
	}

	function feof($fp) {
		if ( $this->has_gzip() )
			return gzeof($fp);
		return feof($fp);
	}

	function fgets($fp, $len=8192) {
		if ( $this->has_gzip() )
			return gzgets($fp, $len);
		return fgets($fp, $len);
	}

	function fclose($fp) {
		if ( $this->has_gzip() )
			return gzclose($fp);
		return fclose($fp);
	}

	function has_gzip() {
		return is_callable('gzopen');
	}
}

$MP_csv_import = new MP_csv_import();

mp_register_importer('csv', 'csv', __('Import your <strong>csv</strong> file.','MailPress'), array ($MP_csv_import, 'dispatch'));

?>