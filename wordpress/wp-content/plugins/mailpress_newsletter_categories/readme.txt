=== MailPress_newsletter_categories ===
Contributors: andre renaut
Tags: mail, subscribe, newsletter, newsletters, Wordpress, Plugin, swiftmailer, MailPress
Requires at least: 2.7
Stable tag: 3.0.1

This is just an addon for MailPress to manage newsletter for main categories.

== Description ==

** REQUIRES WORDPRESS 2.7 **

** Requires MailPress 3.0.1 **

The 4 MailPress newsletters (post/day/week/month) are standard newsletters sent to all active subscribers.

The category newsletters created by this addon are sent only to all active subscribers that required it.
If you want to make one or all category newsletters sent only to all active subscribers, 
	read the following and modify the code (or create a new addon based on this one) accordingly.

The newsletter is known by the system using the mp_register_newsletter() php function (see mailpress/mp-includes/class/MP_Newsletter.class.php).

The parameters of this function are defined hereunder :

//		id 			: string, try to make it unique
//		mp_subject 		: mail subject
//		mp_template 	: MailPress template for this newsletter (if you create it do not forget plaintext)
//		desc 			: description of newsletter displayed under Admin
//		display 		: description of newsletter displayed under blog or false (mailpress user cannot subscribe/unsubscribe)
//
//		threshold		: array	callback  : function that should return true or false if the newsletter is to be sent.
//							name : name of the threshold (usually stored in options) where is stored the last threshold value processed
//							value: current value
//							query_posts : the value of the query_posts if any newsletter to be sent
//
//		in 			: default = false, means all active mailpress users will receive this newsletter by default.
//							true,  means (if 'display' is true) the active mailpress users have to agree to receive this newsletter 
//					  		through their individual subscription management panel
//		args			: any args you should need
//		
//	If 'display' parm is false, 'in' parm should be false or the newsletter will always have an empty recipient list
//			unless you do not modify the mp_usermeta mysql table manually ...

Tested with Firefox3, Internet Explorer 7, Safari 3.1 (Windows XP)

Enjoy !

== Installation ==

Unzip and
	a) copy mailpress_newsletter_categories folder in wp-content/plugins
	b) add the requested templates (see mailpress_newsletter_categories/mp-content/themes) in the appropriate mailpress themes folders.

Plugins => activate MailPress_newsletter_categories

MailPress>Settings or Settings>MailPress => new tab called 'Newsletter categories'.

== Frequently Asked Questions ==

**See** plugin MailPress plugin page at http://www.mailpress.org

Support is provided thru http://groups.google.com/group/mailpress

This plugin is using the last MailPress PHP class : MP_Newsletter (MailPress 1.8)

== Screenshots ==

1. Settings
2. Editing MailPress user parms

== Log ==

** 3.0.1 ** 2009/04/20
**FOR UPDATE FROM A FORMER RELEASE DO NOT FORGET TO DEACTIVATE/UPDATE/ACTIVATE THE PLUGIN !**
* Minor changes :
 - some changes about w3c recommendation requiring a space before /> for empty elements
 - some text changes accordingly
 - addon files renamed for consistency
 - preparing wp2.8

3.0	  	2009/04/12

* Default newsletters can be set now !
	- settings tab has been changed accordingly
	- In order to keep users settings, be aware that changing the status of a newsletter can lead to big updates in the mailpress users table.

* Specific optional plaintext subfolder for your mailpress theme :
	new plaintext subfolder and related templates for default and classic MailPress themes.

== Next features ==

**Any new idea** or **code improvement** can be posted at : http://groups.google.com/group/mailpress





