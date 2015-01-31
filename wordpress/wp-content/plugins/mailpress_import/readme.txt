=== MailPress_import ===
Contributors: andre renaut
Tags: user, import
Requires at least: 2.7
Stable tag: 3.0.1

This is just an addon for MailPress providing an import API for files.

== Description ==

** REQUIRES WORDPRESS 2.7 **

** Requires MailPress 3.0.1 **

Supported languages : English, French	(.pot provided)

Enjoy !

== Installation ==

Unzip and copy mailpress_import folder in wp-content/plugins

Plugins => activate MailPress_import

MailPress>Settings or Settings>MailPress => new tab called 'Import'.

New menu item Tools>MailPress Import or Mails>Import.

AS SAMPLES, 4 importers and some sample datas are provided AS IS : xmlsample, csv, subscribe2 and subscribe_to_comments. 
Sorry no support on these importers !

For PHP Coders :

You want to create your own importer. 

A)
	The plugin provides 4 static functions in class MailPress_import
		1) sync_mailinglist : to create a new mailing list or get an existing one, returns the mailing list id.
		2) sync_mp_user : to create a new MailPress user or get an existing one, returns the MailPress user id.
		3) sync_mp_usermeta : to create or update extra data from your MailPress user (name, phone ...), these datas can be retrieved in a mail.
		4) sync_mp_user_mailinglist : (you must be using MailPress_mailing_lists addon) to affect a user to a mailing list.
B)
	Once coded, place it into the following wp-content/plugins/mailpress_import/mp_admin/import
C)
	Once tested, if you think your importer can be of any help, send it to contact@nogent94.com with some sample data.
	It will be included in a future release.

== Frequently Asked Questions ==

**See** plugin MailPress plugin page at http://www.mailpress.org

Support is provided thru http://groups.google.com/group/mailpress

== Screenshots ==

1. Settings
2. MailPress Import
3. if you are using 'mailpress_roles_and_capabilities', do not forget to grant access if necessary

== Log ==

** 3.0.1 ** 2009/04/20
**FOR UPDATE FROM A FORMER RELEASE DO NOT FORGET TO DEACTIVATE/UPDATE/ACTIVATE THE PLUGIN !**
* Minor changes :
 - some changes about w3c recommendation requiring a space before /> for empty elements
 - some text changes accordingly
 - addon files renamed for consistency
 - preparing wp2.8

3.0	  	2009/04/12

* bugs
  - main menu is tools.php now
  - import api modified : metadata are updated now on re-load

== Next features ==

**Any new idea** or **code improvement** can be posted at : http://groups.google.com/group/mailpress