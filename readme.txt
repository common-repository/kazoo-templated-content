=== Kazoo Templated Content ===
Contributors: AFriedl 
Donate link:http://www.andrewfriedl.com/wordpress-plugins/kazoo-templated-content/
Tags: widget,comment,post,page,sidebar,content
Requires at least: 2.9
Tested up to: 3.1.3
Stable tag: 1.2.0

Include custom templated post, page, comment, attachment loops, RSS feeds and conditional content anywhere without writing any PHP.

== Description ==

**Kazoo Templated Content** allows you to use HTML templates to program against the Wordpress API anywhere you can use a shortcode without PHP and without editing your theme.

*   Implement a custom templated post, page, and comments loop anywhere.
*   Implement a custom templated RSS feed anywhere.
*   Include or exclude templated content based upon user status.
*   Use only the Kazoo shortcode and either inline or databased HTML templates.
*   NO PHP CODING REQUIRED.
*   NO THEME EDITING RERQUIRED.
*   Easily extend Kazoo with custom libraries (requires PHP).

== Installation ==

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page. Install directly from the Admin Control Panel by selecting Plugins--Add New, search for "Kazoo", locate "Kazoo Templated Content" and click "Install Now, Activiate and enjoy.

== Frequently Asked Questions ==

= What type of templates does Kazoo use? =

Kazoo uses HTML templates typed in through either the Post/Page Content Editor or through its special Admin Panel.

= Where are the templates stored? =

Templates can be stored within the database table or they can be typed directly in between the start and end short code tags.

= What do the shortcodes look like? =

Either [kazoo] ... [/kazoo] for an inline template or [kazoo src="5" /] for a templates stored in the database.

= Do I have to know any PHP to use Kazoo? =

No.  Kazoo gives you access the Wordpress API without any knowledge of PHP.

= Do I need any knowledge of the Wordpress API =

It would be helpful.

= I can't find the Kazoo Admin Panel? =

It is under Settings / Kazoo Templates.

= Where can I find tutorials and get help? =

From the Kazoo Templates Content website at http://www.kazooplugin.com?

= Are you willing to build in additional features? =

Yes, all feature requests will be considered.

== Screenshots ==

1. Kazoo Content Templates stored in the Wordpress Database.
2. Editing a template from the database.

== Changelog ==

= 1.0.1 =
* Added database storage of templates and related Admin Pages.
= 1.0.3 = 
* Fixed error to remove slashes in template content.
= 1.0.4 =
* Fixed bug in debug output. Setting shortcode isdebug='true' properly generates debug template output.
* Rewrote output buffer handling in EVAL and INCLUDE library tags.
* Moved buffer output class to KudzuPHP include and eliminated CKazooWriter class.
= 1.0.5 =
* Overcome SVN issues in 1.0.4
= 1.0.6 =
* Altered Plugin Website Information
= 1.0.7 =
* Bugfix in library imports
* Bugfix in FEED library
= 1.0.8 =
* Added code to FEED library to evaluate the 'Error' node when a feed error occurs.
* Added additional parameter to FEED to allow the RSS feed to ignore date order on returned items.
= 1.0.9 =
* Added optional urlencoding in parameter substitution.
= 1.1.0 =
* Updated to latest version of KudzuPHP to include the Random tag.
= 1.2.0 =
* Updated to latest version of KudzuPHP to include parameter substitutions.