=== MailPress_batch_send ===
Contributors: andre renaut
Tags: mail, subscribe, newsletter, Wordpress, Plugin, swiftmailer, batch
Requires at least: 2.7
Stable tag: 3.0.1

This is just an addon for MailPress to send mails in batch mode.

== Description ==

** REQUIRES WORDPRESS 2.7 **

** Requires MailPress 3.0.1 **

**FOR UPDATE FROM A FORMER RELEASE DO NOT FORGET TO DEACTIVATE/ACTIVATE THE PLUGIN AFTER UPDATE !**

Enjoy !

== Installation ==

Unzip and copy mailpress_batch_send folder in wp-content/plugins

Plugins => activate MailPress_batch_send 

MailPress>Settings or Settings>MailPress => new tab called 'Batch'.

== Frequently Asked Questions ==

* When i activate this add on under Wp, i get an error message : "Fatal error: Cannot redeclare class"

I am looking to the origin of this bug, however, the add on get activated !


* Since this plugin is activated, I cannot send a mail to more than one recipient ?

When MailPress_batch_send is activated, the mails are processed as follow :

if only one recipient, the mail is sent immediately without batch processing (subscription)
If more than one recipient, the mail is stored with a specific status 'unsent' and appears in the mails list in italic.

If you cannot see your multi recipients mail in italic in the mails list here is the diagnostic :

At install, this plugin is adding a new value ('unsent') to the status column in MailPress mail table. 
For some reasons, on some installations, this can fail.
So you have to do it manually  (see /wp-content/plugins/mailpress_batch_send/mp-admin/includes/install.php ) 

* How do i populate the settings ?

a) max mails sent per batch (for each batch submitted one MailPress mail will be sent for (Max mails) recipients)
b) max retries (if some recipients failed in the send mail process they can be “retried” (max retries) times
c) logging for batch. Log files will be available in wp-content/plugins/mailpress/tmp
d) submit batch with :
	i) wp_cron : wp_cron is a standard feature of wordpress.
		each time the blog is activated, if execution time is reached,
		- one batch will be processed,
		— some mails sent,
		— a new batch scheduled (for the next “Every”) if all mails are not processed

		(I personnally tested this option locally using the plugin WP-Crontrol)

	ii) other : if you click on the radio button, you will have more info …
		if, on your server, you can access a scheduler (such as crontab or scheduled tasks) 
		then you can schedule an event that will, at each pre-set period of time, look for any mail to be sent.

		(I personnally tested this option locally under Windows XP using scheduled tasks Windows facility and a .bat file)
		(mailpress_batch_send/mp-includes/mailpress_batch_send.bat is provided as a sample)

* Still not working !

This is the check list to prevent most (if any) issues with MailPress_batch_send

1) read the readme.txt included in the add on folder
2) check that the datatype of the column 'status' in mailpress mail table is ENUM('draft','sent','unsent','')
'unsent' requested by MailPress_batch_send, should be set up when activated but sometimes does not work !
3) check your MailPress settings, tab 'Batch', 
    * make sure the option 'Submit batch with' is properly checked.
    * make sure the option 'Max mails sent per batch' is not a huge figure => PHP execution time limit may stop the process at any time and can lead to unpredictable results.

**See** plugin MailPress plugin page at http://www.mailpress.org

Support is provided thru http://groups.google.com/group/mailpress

== Screenshots ==

1. Settings

== Log ==

** 3.0.1 ** 2009/04/20
**FOR UPDATE FROM A FORMER RELEASE DO NOT FORGET TO DEACTIVATE/UPDATE/ACTIVATE THE PLUGIN !**
* Minor changes :
 - some changes about w3c recommendation requiring a space before /> for empty elements
 - some text changes accordingly
 - addon files renamed for consistency
 - preparing wp2.8


3.0	  	2009/04/12

* When submitting a new mail to batch, batch settings are saved and updated in the mailpress mailmeta table.
		=> metakey for mailmeta changed to '_MailPress_batch_send'
* Bug introduced in the first 3.0beta on activating this add on fixed.

== Next features ==

**Any new idea** or **code improvement** can be posted at : http://groups.google.com/group/mailpress
