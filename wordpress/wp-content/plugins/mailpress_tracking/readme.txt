=== MailPress_tracking ===
Contributors: andre renaut
Tags: mail, subscribe, newsletter, Wordpress, Plugin, swiftmailer, batch
Requires at least: 2.7
Stable tag: 3.0.1

This is just an addon for MailPress to track the mails/users activity.

== Description ==

** REQUIRES WORDPRESS 2.7 **

** Requires MailPress 3.0.1 **

**FOR UPDATE FROM A FORMER RELEASE DO NOT FORGET TO DEACTIVATE/ACTIVATE THE PLUGIN AFTER UPDATE !**

This add on creates a new table mailpress_tracks.
Every time a new mail is sent, the mail/link references are kept into the mailpress_mailmeta table.
Every time a mail is opened and/or a link is clicked, a row is created in the table mailpress_tracks.
if you have a large number of subscribers, the table mailpress_tracks can become hudge.

As each click in a mail will generate a little overhead, make sure your web server is properly sized.

if MailPress_tracking is deactivated, MailPress will continue to redirect properly tracking links without tracking them !
if the mail has been deleted from the mailpress tables, the user is by default routed to the home page of the blog.

Enjoy !

== Installation ==

Unzip and copy mailpress_tracking folder in wp-content/plugins

Plugins => activate MailPress_tracking 

MailPress>Settings or Settings>MailPress => new tab called 'Tracking'.

== Frequently Asked Questions ==

**See** plugin MailPress plugin page at http://www.mailpress.org

Support is provided thru http://groups.google.com/group/mailpress

== Screenshots ==

1. Settings
2. Mails list
3. Tracking mail
4. Tracking user

== Log ==

** 3.0.1 ** 2009/04/20
**FOR UPDATE FROM A FORMER RELEASE DO NOT FORGET TO DEACTIVATE/UPDATE/ACTIVATE THE PLUGIN !**
* Minor changes :
 - some changes about w3c recommendation requiring a space before /> for empty elements
 - some text changes accordingly
 - addon files renamed for consistency
 - preparing wp2.8

3.0	  	2009/04/12
* First release.

== Next features ==

**Any new idea** or **code improvement** can be posted at : http://groups.google.com/group/mailpress
