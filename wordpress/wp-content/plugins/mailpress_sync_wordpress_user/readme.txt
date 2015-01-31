=== MailPress_sync_wordpress_user ===
Contributors: andre renaut
Tags: mail, subscribe, newsletter, Wordpress, Plugin, swiftmailer, MailPress, user, users
Requires at least: 2.7
Stable tag: 3.0.1

This is just an addon for MailPress to synchronise MailPress users with WordPress users.

== Description ==

** REQUIRES WORDPRESS 2.7 **

** Requires MailPress 3.0.1 **

* Setting : Allow subscription on Registration Form (Brian ideas based on Subscribe2!).

Miscellaneous : 
1. if you are using 'mailpress_roles_and_capabilities', do not forget to grant access to 'Your Subscriptions' page.
2. if a 'waiting for ...' subscriber registers, the subscriber receives a 'confirmed' mail.
3. if an 'already subscriber' registers, the subscriptions are reset to the Registration Form ones.

Enjoy !

== Installation ==

Unzip and copy mailpress_sync_wordpress_user folder in wp-content/plugins

Plugins => activate MailPress_sync_wordpress_user

MailPress>Settings or Settings>MailPress => new tab called 'Sync WP user'.

== Screenshots ==

1. Settings
2. New box on MailPress user page
3. Managing subscription from profile
4. Register form
5. if you are using 'mailpress_roles_and_capabilities', do not forget to grant access if necessary
6. Register form with mailing lists

== Frequently Asked Questions ==

**See** plugin MailPress plugin page at http://www.mailpress.org

Support is provided thru http://groups.google.com/group/mailpress

== Log ==

** 3.0.1 ** 2009/04/20

**FOR UPDATE FROM A FORMER RELEASE DO NOT FORGET TO DEACTIVATE/UPDATE/ACTIVATE THE PLUGIN !**
* Minor changes :
 - some changes about w3c recommendation requiring a space before /> for empty elements
 - some text changes accordingly
 - addon files renamed for consistency
 - preparing wp2.8

* Bug fixed : if using the mailpress form for non registered users, the subscription process ends with a php error. thanks jhoaty!

3.0	  	2009/04/12

* Only the version number has changed

== Next features ==

**Any new idea** or **code improvement** can be posted at : http://groups.google.com/group/mailpress
