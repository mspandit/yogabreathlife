=== MailPress_user_custom_fields ===
Contributors: andre renaut
Tags: custom fields, user, MailPress
Requires at least: 2.7
Stable tag: 3.0.1

This is just an addon for MailPress to edit MailPress user custom fields.

== Description ==

** REQUIRES WORDPRESS 2.7 **

** Requires MailPress 3.0.1 **

Tested with Firefox3, Internet Explorer 7, Safari 3.1 (Windows XP)

Enjoy !

== Installation ==

Unzip and copy mailpress_user_custom_fields folder in wp-content/plugins

Plugins => activate MailPress_user_custom_fields

See MailPress user detailled page.

== Frequently Asked Questions ==

* How do i use the user custom field 'mycustomfield' in a mail ?

To 'echo' the content of a user custom field 'mycustomfield' just type something like this :

{{mycustomfield}}

if the 'mycustomfield' custom field is known for the recipient, it will be replaced by the value of the 'mycustomfield' custom field for that recipient
if not, the text {{mycustomfield}} remain unchanged and will appear in the mail unless a default value has been set using MailPress_mail_custom_fields.

**See** plugin MailPress plugin page at http://www.mailpress.org

Support is provided thru http://groups.google.com/group/mailpress

== Screenshots ==

1. Custom fields in MailPress user page

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