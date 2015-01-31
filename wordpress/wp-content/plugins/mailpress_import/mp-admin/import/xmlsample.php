<?php

class MP_xmlsample_import {

	function MP_xmlsample_import() {
		// Nothing.
	}

	function header() {
		echo '<div class="wrap">';
		echo "<div id='icon-mailpress-tools' class='icon32'><br /></div>";
		echo '<h2>'.__('Import XML sample','MailPress').'</h2>';
	}

	function footer() {
		echo '</div>';
	}

	function dispatch() {
		if (empty ($_GET['step']))
			$step = 0;
		else
			$step = (int) $_GET['step'];

		$this->header();
		switch ($step) {
			case 0 :
				$this->greet();
				break;
			case 1 :
				if ( $this->handle_upload() )
					if ( $this->readfile() )
						$this->import();
				break;
		}
		$this->footer();
	}

// step 0

	function greet() {
		echo '<div class="narrow">';
		echo '<p>'.__('Howdy! Upload your file and we&#8217;ll import the emails and much more ... into this blog.','MailPress');
		echo ' ';
		echo __('Choose a file to upload, then click Upload file and import.','MailPress').'</p>';
		echo "\n";
		wp_import_upload_form( MailPress_import . "&amp;import=xmlsample&amp;step=1");
		echo '</div>';
	}

// step 1

	function handle_upload() {
		$file = wp_import_handle_upload();
		if ( isset($file['error']) ) {
			echo '<div class="narrow">';
			echo '<p>'.__('Sorry, there has been an error.','MailPress').'</p>';
			echo '<p><strong>' . $file['error'] . '</strong></p>';
			echo '</div>';
			return false;
		}
		$this->file = $file['file'];
		$this->id = (int) $file['id'];
		return true;
	}

	function HandleXmlError($errno, $errstr, $errfile, $errline)
	{
		if ($errno==E_WARNING && (substr_count($errstr,"DOMDocument::loadXML()")>0))
		{
			throw new DOMException($errstr);
		}
		else
			return false;
	}

	function import() 
	{

		$this->trace = new MP_Log('MP_xmlsample_import',ABSPATH . MP_PATH,MP_MailPress_import_FOLDER,false,'MailPress_import');

		try 
		{
			set_error_handler(array(&$this,'HandleXmlError'));
			$dom = New DOMDocument();
			$dom->loadXML($this->xml);
			restore_error_handler();
		}
		catch (DOMException $e) 
		{
			$this->trace->log('**** DOM XML ERROR ****' . "NOT A XML FILE ");
			$this->trace->log('**** DOM XML ERROR ****' . "There was a problem with this file : $this->file \n" . $e->getMessage());
			$this->trace->end(true);
			echo '<div class="narrow">';
			echo '<p>'.__('Sorry, there has been an error.','MailPress').'</p>';
			echo '<p><strong><pre style="white-space:pre-wrap; ">' . __('NOT A XML FILE.','MailPress') . '</pre></strong></p>';
			echo '<p><strong><pre style="white-space:pre-wrap; ">' . $e . '</pre></strong></p>';
			echo '</div>';
			return false;
		}

		$this->root = $this->parse_node($dom,'MailPress');
		if ($this->root->nodeName == 'MailPress')
		{
if (class_exists('MailPress_mailing_lists')) : 
			$x = $this->parse_node($this->root,'mailinglist');
			if ($x)
			{
				$mailinglist = trim($x->nodeValue);

				$mailinglist_ID = MailPress_import::sync_mailinglist($mailinglist,$this->trace);

				if (!$mailinglist_ID)
				{
					$this->trace->end(true);
					echo '<div class="narrow">';
					echo '<h2>'.__('Invalid Mailinglist','MailPress').'</h2>';
					echo '<p>'. sprintf(__('Unable to read or create a mailing list :%s','MailPress'), $mailinglist)  . '</p>';	
					echo '</div>';
					return false;
				}
			}
endif;

			$userf = $this->parse_node($this->root,'users');
			$users = $userf->getElementsByTagname('user');
			foreach ($users as $user)
			{
				$email = trim($user->getAttribute('email'));

				$mp_user_id = MailPress_import::sync_mp_user($email,$this->trace);

				if ($mp_user_id)
				{
					if ($mailinglist_ID)
					{
						MailPress_import::sync_mp_user_mailinglist($mp_user_id,$mailinglist_ID,$email,$mailinglist,$this->trace);
					}

					$dataf = $this->parse_node($user,'datas');
					$datas = $dataf->getElementsByTagname('data');

					foreach ($datas as $data)
					{
						$attr = $data->getAttribute('id');
						$val  = trim($data->nodeValue);

						MailPress_import::sync_mp_usermeta($mp_user_id,$attr,$val,$this->trace);
					}
				}
			}
			$file = $this->trace->file;
			$y = substr($file,strpos($file,'wp-content'));
			$this->trace->end(true);
			echo '<div class="narrow">';
			echo '<p>' . sprintf(__("<b>File imported</b> : <i>%s</i>",'MailPress'),$this->file) . '</p>';
			if ( file_exists( $file) ) : 
				echo "<p><a href='../$y' target='_blank'>" . __('See the log','MailPress') . '</a></p>';
			endif;
			echo '</div>';
		}
		else
		{
			$this->trace->log('**** XML ERROR **** ' . "Wrong file");
			$file = $this->trace->file;
			$y = substr($file,strpos($file,'wp-content'));
			$this->trace->end(true);
			echo '<div class="narrow">';
			echo '<p>'.__('Sorry, there has been an error.','MailPress').'</p>';
			echo '<p>' . sprintf(__("<b>Wrong file</b> : <i>%s</i>",'MailPress'),$this->file) . '</p>';
			if ( file_exists( $file) ) : 
				echo "<p><a href='../$y' target='_blank'>" . __('See the log','MailPress') . '</a></p>';
			endif;
			echo '</div>';
		}
	}

////////////////////////////////////

	function parse_node($node,$tagname) 
	{
		$xs = $node->getElementsByTagname($tagname); 
		foreach ($xs as $x) {};
		return $x;
	}

	function readfile() {

		$this->xml = '';

		$fp = $this->fopen($this->file, 'r');
		if ($fp) 
		{
			while ( !$this->feof($fp) ) 
			{
				$this->xml .= $this->fgets($fp);
			}
			$this->fclose($fp);
		}
		else 
		{
			echo '<div class="narrow">';
			echo '<h2>'.__('Invalid file','MailPress').'</h2>';
			echo '<p>'.__('Please upload a valid file.','MailPress').'</p>';
			echo '</div>';
			return false;
		}
		return true;
	}

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

$MP_xmlsample_import = new MP_xmlsample_import();

mp_register_importer('xmlsample', 'xmlsample', __('Import your <strong>xmlsample</strong> file.','MailPress'), array ($MP_xmlsample_import, 'dispatch'));

?>