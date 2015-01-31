=== MailPress_filter_img ===
Contributors: andre renaut
Tags: mail, subscribe, newsletter, Wordpress, Plugin, swiftmailer, post, posts, MailPress, image, images, picture, pictures, jpeg, gif, bmp
Requires at least: 2.7
Stable tag: 3.0.1

This is just an addon for MailPress to filter ALL html img tags before mailing and preview them.

== Description ==

** REQUIRES WORDPRESS 2.7 **

** Requires MailPress 3.0.1 **

Before reading hereunder, remember that some emailers such as Gmail, Hotmail ... discard css stylesheets
so in this filter, we try to put the maximum of the css styles INLINE for img tags.

For EACH image in the mail (even images from the header and footer of your MailPress theme),

The filter 
	* receives the complete html tag <img ....>
		+ tries to identify any information about positionning the image
			if class attribute (class='...') contains the string 'right' or 'left', float style is set accordingly.
		+ tries to identify inline style 
	* rebuild the  html tag <img ....> mixing in priority order :
		+ the attributes and inline style of the original html tag <img ....>
		+ the attributes and inline style identified from the original html tag <img ....>
		+ the attributes and inline style default settings (see 'Image filter' tab in MailPress settings).

You can test the filter and its results online : see 'Image filter' tab in MailPress settings

Enjoy !

== Installation ==

Unzip and copy mailpress_filter_img folder in wp-content/plugins

Plugins => activate MailPress_filter_img

MailPress>Settings or Settings>MailPress => new tab called 'Image filter'.

== Frequently Asked Questions ==

**See** plugin MailPress plugin page at http://www.mailpress.org

Support is provided thru http://groups.google.com/group/mailpress

== Screenshots ==

1. Settings
2. GMail view of mail without filter
3. GMail view of mail with filter
4. GMail view of mail (gallery) without filter
5. GMail view of mail (gallery) with filter

== Log ==

**3.0.1**  	2009/04/
**FOR UPDATE FROM A FORMER RELEASE DO NOT FORGET TO DEACTIVATE/UPDATE/ACTIVATE THE PLUGIN !**
* Minor changes :
 - some changes about w3c recommendation requiring a space before /> for empty elements
 - some text changes accordingly
 - addon files renamed for consistency
 - preparing wp2.8

3.0	  	2009/04/12

* bugs
  - better support for arabic characters, umlaut etc ...
  - minor change for tracking image

== Next features ==

**Any new idea** or **code improvement** can be posted at : http://groups.google.com/group/mailpress
