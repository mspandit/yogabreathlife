=== MailPress_mail_custom_fields ===
Contributors: andre renaut
Tags: custom fields, mail, MailPress
Requires at least: 2.7
Stable tag: 3.0.1

This is just an addon for MailPress to edit MailPress mail custom fields.

== Description ==

** REQUIRES WORDPRESS 2.7 **

** Requires MailPress 3.0.1 **

Tested with Firefox3, Internet Explorer 7, Safari 3.1 (Windows XP)

Enjoy !

== Installation ==

Unzip and copy mailpress_mail_custom_fields folder in wp-content/plugins

Plugins => activate MailPress_mail_custom_fields

See MailPress mail detailled page.

== Frequently Asked Questions ==

* How do i use the mail custom field 'mycustomfield' in a mail or a template file (header and/or footer) ?

To 'echo' the content of a mail custom field 'mycustomfield' just type something like this :

writing {{mycustomfield}} in a mail will have the value of the mail custom field

* I am already using MailPress_user_custom_fields, how can i mix them ?

Your mail has a custom field 'mycustomfield' 
one or more of the recipients have a custom field 'mycustomfield' (MailPress_user_custom_fields)

writing {{mycustomfield}} in a mail will have the value of the recipient custom field if exists, if not the value of the mail custom field

**See** plugin MailPress plugin page at http://www.mailpress.org

Support is provided thru http://groups.google.com/group/mailpress

== Screenshots ==

1. Custom fields in MailPress mail page

== Log ==

** 3.0.1 ** 2009/04/20
**FOR UPDATE FROM A FORMER RELEASE DO NOT FORGET TO DEACTIVATE/UPDATE/ACTIVATE THE PLUGIN !**
* Minor changes :
 - some changes about w3c recommendation requiring a space before /> for empty elements
 - some text changes accordingly
 - addon files renamed for consistency
 - preparing wp2.8

3.0	  	2009/04/12

* First release

== Next features ==

**Any new idea** or **code improvement** can be posted at : http://groups.google.com/group/mailpress
