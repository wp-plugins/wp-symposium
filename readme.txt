=== Plugin Name ===
Author: WP Symposium
Contributors: Simon Goodchild
Donate link: http://www.wpsymposium.com
Link: http://www.wpsymposium.com
Tags: wp-symposium, symposium, forum, social, chat, friends, wall, status, message, registration, directory, groups, events, foreign language, french, german, italian, dutch, spanish
Requires at least: 3.0
Tested up to: 3.2.1
Stable tag: 11.12.03

Social Networking for WordPress: forum, activity, member directory, mail, chat, profile page, groups, events, gallery, mobile, RSS and more!

== Description ==

**WP Symposium turns a WordPress website into a Social Network! It is a suite of WordPress plugins including forum, activity (similar to Facebook wall), member directory, private mail, notification bar, chat windows, chat room, profile page, social widgets, activity alerts, RSS activity feeds and support for other plugins such as Groups, Events, Gallery, Facebook Connect and Mobile support! You simply choose which you want to activate! Certain features are optional to members to protect their privacy.**

For developers, there are a growing number of WordPress hooks and filters, along with WP Symposium functions and Javascript variables. More information on the WPS Wiki at www.wpswiki.com.

Note: The WP Symposium plugin from WordPress.org is free (that includes profile, activity, forum, chat, panel, mail, member directory and widgets) - additional WPS compatible plugins may require a subscription fee.

Find out more, and try it out on our own social network at www.wpsymposium.com.

*Three steps to create a Social Network website*

1. Download WordPress
2. Install WP Symposium

erm, sorry - only two steps.

*What do you get?*

You get a number of plugins that each provide a set of functionality and features, that can be used individually or all together, to produce a social networking website built on WordPress.

You can activate a member profile page with activity wall and posts/replies; member profile photos (avatars); friends; a "notification bar" with friends status, mail alerts and live chat windows. You can also activate a forum; a directory of members and internal (private) messaging.

The features of all these will continually improve - and more plugins like Groups, Events, Facebook Shared Status, Photo Albums and Mobile Access are available at www.wpsymposium.com.

Oh, and you also get widgets, with more to come, including latest new members and latest forum posts.

*Can I change the layout and styles?*

Certainly can! Templates are provided for you to change the layout, and an easy-to-use style configuration (or way to enter CSS if you prefer) is provided to change the styles.

*How is it configured?*

WP Symposium plugins work with any WordPress theme! No complicated theme development, and no complicated styles - you can even pick the colour scheme in the WordPress admin area!

Via crowdin website at www.crowdin.net/project/symposium, a growing number of translations are being maintained by an active user base, so running WP Symposium in a different language is easily set up.

*What else is there?*

Loads! Smilies, loves AJAX, an installation "health check" page, templates for changing the page layout, options for just about everything... the list goes on!

Find out more, and try it out on our own social network at www.wpsymposium.com.
  
*Can I see what features are coming next?*

Using the same software as WordPress, we have a Trac website at https://wpsymposium-trac.sourcerepo.com/wpsymposium_trac

*Where can I get support?*

At www.wpsymposium.com or the WPS Wiki at www.wpswiki.com.

== Installation ==

An admin guide and more information is available at www.wpswiki.com.

Important - after activating for the first time, and after changing any WP Symposium shortcodes, make sure you visit the WP Symposium -> Installation page that updates internal paths.

As with all upgrades to any system, it is recommended that you take a backup of the database and website first.
Tested with PHP 5.

*Automatic installation*

* go to Plugins->Add New and search for "Symposium"
* click on Install below WP-Symposium
* activate the plugins you want to use (core plugin MUST remain activated, and be activated first)
* important - go to the Installation page to complete the setup

*Manual installation*

* download from wordpress.org/extend/plugins/wp-symposium/
* unzip the contents of the ZIP file in your /wp-content/plugins folder
* activate the plugins you want via Plugins on the WordPress admin dashboard (including the core plugin)
* important - go to the Installation page to complete the setup

*Adding WP Symposium plugins to your site*

Full instructions are available on the WPS Wiki at www.wpswiki.com

== Screenshots ==

1. Member Wall
2. Friends
3. Profile Photo
4. Custom extended information
5. Mail
6. Forum Categories
7. Forum Topic
8. Edit Forum Post
9. Smilies/Emoticons
10. Friends online status
11. Site-wide chatroom
12. Latest Forum Posts widget
13. New Members widget
14. Yes/No Vote widget
15. Easily change styles

== Frequently Asked Questions ==

Q. Where can I find more information?
A. Go to www.wpsymposium.com for a demo and an active community of users. Visit www.wpswiki.com for further information and an admin guide.

Q. Can I try it out?
A. Yep, go to www.wpsymposium.com

Q. Is WP Symposium covered by the GPL license?
A. Yes, for sure. Everything listed on the WordPress directory is covered by the GPL v3 licence. There may be other plugins that are compatible with WP Symposium that are sold commercially, but what is provided via the download at www.wordpress.org will always be covered by the GPL licence.

Q. What's the one thing I probably haven't done?
A. Visited the Installation page to update WP Symposium's internal paths. Check out www.wpswiki.com, in particular the "Try this first" page.

Q. The admin side works, but the forum (or another plugin) doesn't appear?
A. Check you have `[symposium-forum]` on your page, with a hyphen, not an underscore. Replace [symposium-forum] with the shortcode of the plugin.

Q. I've done that, but the forum (or another plugin) still doesn't appear?
A. Have you put `[symposium-forum]` on a WordPress page, not an HTML file? 

Q. It all looks okay, but I just get a spinning "please wait" symbol?
A. Read the "Try this first" page at www.wpswiki.com, if that still doesn't help visit www.wpsymposium.com. You almost certainly have a Javascript problem.

Q. Can I use other languages?
A. There are a growing number of translations available to use at www.crowdin.net (see www.wpsymposium.com for further instructions). From v0.58 support for non-Western character sets was introduced (needs a fresh v0.58+ installation).

Q. Are there images other than the usual smilies?
A. Yes, if you look in the smilies folder you can use any of the images there by putting {{xxx}} around the first part of the filename, eg: {{rofl}} would show rofl.png - to see the full list, go to the Smilies page at www.wpsymposium.com.

Q. Will WP Symposium work on WPMU/WPMS?
A. As from v0.37, yes, WordPress Multi-site is supported, although it is not as widely used as the single site installation, and hence may not be as stable (due to less user tests in a live environment).

Q. I don't see friends when they're online, can't edit forum posts or read all mail in my inbox. I see stuff, but not much works...
A. Sounds like AJAX isn't working, probably due to a Javascript error. Check out the "Try this first" page at www.wpswiki.com.

Q. There used to be a login and registration plugin, where did they go?
A. After consultation with users, the majority voted to leave authentication to WordPress or other plugins such as Theme-My-Login, however in hindsight it can be introduced in a more strategic manner. There is now a sidebar widget that allows members to login, or show links to the register/forgotten password WordPress pages. Once logged in, the user can see how any messages (including those unread) and friends (including new friend requests), etc with the same widget.

Q. How can I get rid of the Powered By message?
A. Because WP Symposium is covered by the GPL licence, you can edit and change the code - but remember you'll have to do it each time you upgrade. An easier way is to use the Templates via WP Admin -> WP Symposium -> Templates.

Q. Because of all the nice AJAX, how can I get content submitted to search engines?
A. Check out the Mobile/SEO/Accessibility plugin at www.wpsymposium.com

Q. Which plugins may require a subscription fee?
A. Currently the Groups, Facebook Connect, Gallery, Mobile/SEO and RSS Activity Feed require a Bronze membership at www.wpsymposium.com - all can be tried out at www.wpsymposium.com

Q. If I take out a Bronze membership and I decide I don't want WP Symposium, can I get a refund?
A. If the plugins don't work on your server, with TwentyTen theme and all non-WPS plugins de-activated, then you will get a full refund, less any PayPal fees.

Q. What happens if I cancel my Bronze membership?
A. After cancellation, you will no longer be able to download upgrades/patches or new plugins (that are provided to Bronze members) from www.wpsymposium.com. You can continue to use those plugins you have on your site, and upgrade the core WP Symposium plugins from www.wordpress.org. You may want to wait until the end of your year to benefit from membership for as long as possible. You will not be able to access your helpdesk account after cancelling.

Q. How much does Bronze membership cost?
A. $39 a year - if you don't want to continue your membership please cancel your PayPal subscription. You may want to wait until the end of your year to benefit from membership for as long as possible. You can continue to use Bronze plugins installed on your site even after you cancel (you just can't upgrade or get new ones).

Q. What is Silver membership?
A. For $99 a year, we will do a full install on your server (if it won't work for any reason you get a full refund, less any PayPal fees). Any support tickets raised by Silver members will take priority over Bronze members.

== Changelog ==

**WP Symposium Trac**

From 11.9.10 (10th September 2011) the change log can be seen on the WPS Trac:

* https://wpsymposium-trac.sourcerepo.com/wpsymposium_trac/report/2 Change log
* https://wpsymposium-trac.sourcerepo.com/wpsymposium_trac/report/6 Roadmap of releases
* https://wpsymposium-trac.sourcerepo.com/wpsymposium_trac/report/11 Future enhancements, features and fixes
* https://wpsymposium-trac.sourcerepo.com/wpsymposium_trac/report/2 Current and previous release information

Previous change log:

= 11.9.4 =

* Admin: Can set permitted upload file types (extensions, eg: *.jpg.*.gif,*.png)
* Forum: Video attachments now work on forum (optionally). MP4 are the best way to upload due to compatibility!
* Forum: File attachments now work on forum (optionally).

= 11.9.1 =

* Styles: Save style templates
* Styles: Changes to make WPS Styles more consistent
* Chat: Windows now scroll to bottom on new messages
* UI: Changed busy icon to work with dark and light backgrounds
* Forum: Group forum was showing if set to default whether switched on or not, fixed this

= 11.8.27 =

* Added record of last and previous logged in date/time for use on the forum
* Forum: implemented 'new' flags (a star)
* Forum: changed favourite image to a heart (so star can be used for new posts)
* Forum Moderation: log posts/replies can now be viewed in full via "View" link

= 11.8.21 =

* General Tidyup!
* Admin: Smarter and quicker installation page
* Forum: Fix to option to disallow new topics in Forum Categories
* Profile: Added public option for privacy - be warned, visitors to your site not logged in can view public content and may be picked up by search engines
* Widget: Fixed Summary widget layout when logged out

= 11.8.19.1 =

* Installation: Fixed rogue bug involving img_url

= 11.8.19 =

* Forum: New widget to display top "experts". A member gets an expert point when one of their answers is accepted.
* Forum: New widget to display topics without an accepted answer. Members who start a topic, and site admins, can accept answers.
* Forum: Topics can be set as "for information only", so no answer expected (when owner or admin viewing a topic)
* Forum: Accepted answer icon (green tick by default) shown on pop-up boxes and [symposium-forumlatestposts] shortcode
* Admin: Further cleaning up of PHP Warnings/Notices, in particular management of forum ranks

= 11.8.18 =

* Forum: Added "Accept answer" feature to forum. Green 'tick' shown against topics with an accepted answer.
* Admin: Cleaned up PHP Warnings
* Changed format of version to a build instead, Year.Month.Day.Release (release only if required)
* Core: Fixed security vulnerability when viewing profile from database

= 0.64 =

* Core: Changes to make Forum and Groups per site in WPMU network installations
* Profile: Added code to avoid having to click twice on initial profile page load
* Forum: Optionally show number of posts under avatar, and if using votes the members reputation
* Forum: Much better vote recording/governance (one vote per post per person, can re-vote, minimum posts level before vote)
* Forum: Shortcode [symposium-forumlatestposts] now showing Started date correctly
* Forum: Topic replies now line up same as intial topic post (ie. indented from avatar)

= 0.63.3 =

* Forum attachments can now be shown inline or as links, both will use colorbox (if gallery plugin installed).
* Inline forum attachments can have a maximum width set via Forum Settings

= 0.63.2.1 =

* Small but vital change to avoid reliance on gallery plugin for forum attachments

= 0.63.2 =

* Forum: Further code changes for attachments
* Profile: Empty display name no longer permitted

= 0.63.1 =

* Forum: Added check to avoid PHP warnings appearing when no attachments

= 0.63 =

* Core: Fixed security risk in image/file upload routines
* Forum: Added option to attach files to forum posts (images shown with colorbox if gallery plugin installed)
* Settings: Added option to strip tags or replace < and > with &lt; and &gt; (depends if you want to allow code to be posted)
* Forum admin: Fixed bug that was resetting order of replies
* Various: changed /wp-content to WP_CONTENT_URL where hardcoded by mistake

= 0.62.2 =

* Widgets: strips Vote widget into separate plugin. Deactivate/delete if not using because it loads a large .js file. Yes/No Vote widget can be download for free at www.wpsymposium.com/downloadinstall.

= 0.62.1 =

* Templates: Added [poke] to 'reset to default'
* Profile: Profile redirect won't happen if profile plugin not activated
* Mail: added 'Send Mail' button to messages in Sent box (to send another mail to same person)
* Profile: added addslashes() to fix rogue apostrophe error
* Profile: fixed rogue error regarding show_profile_menu field

= 0.62 =

* Mail: carriage returns in emails sent for new Mail messages now shown
* Mail: header now uses PHP_EOL constant to support Windows and Unix (and Mac) web servers
* Core: Fixed Installation page to update page URL correctly on multi-site
* Forum: No longer allows a topic with blank subject or post
* Templates: Blank footer allowed (places an HTML comment so that default version doesn't get re-inserted when upgrading)

= 0.61.1 =

* Mail: When searching, top item in results is shown in message area
* Mail: When searching, if no results are found, message area is cleared
* Forum: Will hide new topic button and reply field if not member of group (Groups)
* Forum: Added catch for <script> tags in forum posts, profile settings/preferences
* Profile: Added email validation on Preferences

= 0.61 =

* Profile: Added option to redirect WordPress profile page to WPS profile page (under WP Symposium -> Profile)
* Forum: Added parameter to [symposium-forumlatestposts] shortcode to override per page, eg: [symposium-forumlatestposts count=10]

= 0.60 =

* Templates: Can now Import and Export all your templates - ready to share ;)
* Forum: Set number of forum topics to show with [symposium-forumlatestposts] shortcode via WP Symposium->Forum

= 0.59.6 =

* Mail: search reacts to Return key, not just submit button
* Mail: fixed search results displaying "undefined" mail item
* Core: added core code version to installation table
* Forum: fix for [symposium-forumlatestposts] shortcode

= 0.59.5 =

* Core: Fix to menu not showing up (sorry!) - and skipped versions due to WordPress SVN problems :(

= 0.59.2 =

* Core: Added additional check that "Alerts" plugin is activated to avoid function not found error
* Forum: Added shortcode for showing latest forum activity on a WordPress page [symposium-forumlatestposts]

= 0.59.1 =

* Forum Categories: Added "Delete All" option to delete category and contents
* Forum Categories: Fixed bug in "Allow new topics" option
* Forum Categories: Add Topic count column
* Forum: Simplified headings on "My Activity"
* Profile: "Poke" feature supports Alerts plugin and mail sent includes link to the member who sent it
* Forum: Banned word list in panel settings is now shared with Forum (and can be set from either)

= 0.59 =

* Tested up to WordPress 3.2.1
* Admin: changed structure of WPS admin menus to operate like standard WordPress admin menus (so consistent with WP and WPS plugins)
* Admin: consistent UI for WPS admin screens
* Member Directory: Added option to hide admin from member directory
* Member Directory: Search now includes extended profile fields
* Profile: Can now add multiple default friend requests
* Profile (Extended Fields): Can use checkboxes as a format option

= 0.58.1 =

* Fix: Sorted out internal version numbers to reflect current version (sorry!)
* Update: Changed Javascript to use mouseenter/mouseleave instead of mouseover/mouseout
* Admin: Improved UI for Forum Categories

= 0.58 =

* Tested up to, and compatible with, WordPress 3.2
* Support for non-Western characters (UTF8). Note this will only apply to new installations.
* New message notifications now link straight to new mail item, not just in box

= 0.57.2 =

* Tested up to WordPress 3.1.4
* Forum: Small fix to voting system (so 0 is ignored)
* Members: Minor bug fix on searching
* Widgets (Vote): upgraded to v3 for IE9 compatibility, sorry - this might mean you need to purchase a licence from www.jscharts.com, see widget settings :(

= 0.57.1 =

* Profile: Fixed links in WPS on sites that are not installed on root of site
* Forum: New setting that will hide forums posts with a given score (- and + value accepted)
* Widgets (Vote): Added option to show/hide vote counts
* Widgets (Vote): Can now change options without resetting (this is an option too)

= 0.57 =

* Mail: Fix bug where More... not showing after first use
* Profile: Added admin option to show/hide buttons on activity page
* Profile: Check for double email notification (as author and subject of profile post)
* Templates: Fixed problem if Profile Avatar less than 100 pixels in Profile Page Header
* RSS Feed: Member's display name shown as icon title instead of ID
* Core: Installation page, modified to support foreign language installations

= 0.56.3 =

* Gallery: Added upload max file size message

= 0.56.2 =

* Mail: Bug fix showing random number (should have been hidden DIV)

= 0.56.1 =

* Forum: Added option votes on posts (set via admin Forum Settings)
* Core: Opened up Templates to all members
* Core: Removed symposium_get_siteURL() as not necessary
* Activity Widget: changed to use activity share level, not personal info share level

= 0.56 =

* Forum: Added forum category descriptions
* Forum: Added minimum level to post/reply on the forum
* Forum: Added support for Group Forum alerts

= 0.55.1 =

* Fixed typo in symposium_time_ago function

= 0.55 =

* Bonus Plugin: RSS Feed of members activity feed (enabled via Personal settings)
* Profile: Added symposium_profile_wall_header_filter filter (used with RSS Feed Plugin)
* Core: Added support for :-) smiley
* Core: If user not logged in since WPS installed, last active shows 'a while ago' instead of '4 decades'

= 0.54.1 =

* Core (Security): Added protection against SQL injection attacks
* Core (Security): AJAX no longer passed current user ID when not necessary
* Core (Security): Added more checks in AJAX functions that user is logged in
* Added more accurate message when viewing member profiles when not logged in
* Added more accurate message when viewing group page when not logged in
* Profile: Fixed: if not using date of birth, location shows

= 0.54 =

* Removed unnecessary buttons on profile page
* Message shown if extended info is empty, but choosing to show it as a site option
* Removed all references to symposium_notifications table as no longer used (alerts will replace)
* Profile: Option to show/hide date of birth
* Profile: Added hide option to each part of date of birth

= 0.53.10 =

* Profile: Status, posts on others profile and replies/comments now also trigger by pressing Return
* Forum: Added option to 'bump' topics to the top with new replies
* Core: WPS avatars now appear throughout the WordPress site
* Panel: Links now work with WordPress installation not in the root directory
* Core: Minor modifications to installation page
* Core: Minor modifications to further support Alerts plugin

= 0.53.9 =

* Core: Big fix on installation page so only checking for shortcode on a post of type page

= 0.53.8 =

* Core: Bug fix on installation page effecting identification of groups shortcode

= 0.53.7 =

* Admin: Fixed bug saving CSS with % within the text
* Plugins: Added Lounge plugin as a demonstrator for plugin developers

= 0.53.6 =

* Widgets: Addition of Login/Summary Widget

= 0.53.5 = 

* Profile: Bug fixed where some databases not honouring default values (adding friends)
* Profile: Bug fixed where Send Mail not working from friends profile pages

= 0.53.4 =

* Core: Fix to URLs in Widgets paths
* Core: Fix to menu filter

= 0.53.3 =

* Core: Changed Health Check to new Installation page (removes awkward manual setting)
* Core: Update to Daily Digest
* Core: Fixed POT parameter for months
* Facebook Plugin: Added additional check to ensure function not previously loaded
* Friends: Added Show more... link
* Gallery: Added core support demonstrating hooks/filters (gallery plugin an example)
* Profile: Admin can now view activity, irrespective of sharing setting

= 0.52.5 =

* Profile: Fix if no extended fields, PHP warning is not shown

= 0.52.4 =

* Profile: Line breaks are shown for extended textarea fields
* Profile: Order works on extended fields
* Profile: If Googlemap size is 0, it is not shown
* Widgets: Added "Show replies" option to Latest Forum Posts
* Panel: Added locks to AJAX calls to help reduce load on servers

= 0.52.3 =

* Widget: Added category select and hide preview (set length to 0) for Latest Forum Posts
* WPS "Pop-up" DIVs replaced with jQueryUI dialog()
* Core: Changed mail functions to use symposium_get_siteURL() for correct path
* Health Check: Added to Health Check

= 0.52.1 =

* Profile: Post to other members walls, fixed rogue variable name (error in v0.52)
* Profile: Fixed email notification for friend requests
* Profile: Fixed extended information showing when clicking on Show more...

= 0.52 =

* New plugin: Support for Mobile/SEO/Accessibility plugin (BETA)
* Profile: Logged out version of Profile page now displays a message or content if set to 'Everyone'
* Profile: Fixed Show more on All Activity only showing current members activity
* Group: Fixed Join Group button shows even if not logged in
* Group: Login link on Group page if not logged in now shown 
* Core: Links "in" the Daily Digest now have complete URLs

= 0.51.2 =

* Panel: Fix to URLs from Panel in some circumstances
* Forum: Font size of Topic Subject
* Forum: Last Activity/etc is in context with Forum (ie. in a group or not)
* Forum: Search works on main forum and only groups you are a member of
* Forum: Verified links work to group forums and non-group forums from search, etc

= 0.51.1 =

* Styles: Fixed color of fonts in input elements to black
* Forum: Fixed avatars in forum wrong at top level
* Avatars: Fixed uploading avatars on multi-site installations

= 0.51 =

* Group: Added support for forums (activate via Group Settings)
* Group: Added link to menu to go to Groups page
* Mail: Hide reply button in message header for 'sent' tray
* Profile/Group: Don't show 'Show more' if there is nothing more to show
* Core: Change to new_post DIV ID to avoid clash in p2 theme
* Forum: Added Permalink 'share' icon
* Forum: Minimum level now works
* Widget: Latest Forum Posts (follows group security)
* Profile: Fix for large profile photo appearing after wall post for some sites
* Core: Added Daily Digest to Health Check
* Core: Sends summary to admin when sending Daily Digests

= 0.50 =

* New Widget: Latest member posts, observes privacy settings. Excludes group posts, and replies/posts to other members
* Moved use of my-symposium.css into Admin->Symposium->Styles (copy contents here if using my-symposium.css)
* Upgraded jQueryUI to v1.8.11 (full set, smoothness theme)
* Forum: Added option to show/hide login prompt if not logged in
* Profile: Added Textarea option to extended profile fields
* Profile: Improved the layout of personal/preferences
* Profile: Reduced default size of Google map to 150px for new installs

= 0.49.9 =

* Plugins: Added support for Facebook Shared Status plugin (Bronze members)

= 0.49.8 =

* Forum: Added 'show more...' link at bottom of topics list
* Profile: Added 'show more...' link at bottom of activity page
* Groups: Added 'show more...' link at bottom of activity page

= 0.49.6 =

* Core: Removed unnecessary fields from usermeta get/update function
* Core: Added reference to current user on install
* Core: Removed WPS version from WP store (for repeat installations on same site)

= 0.49.5 = 

* Groups: support for Group page template
* Groups: improved layout for Group page
* Admin: Quick setup (adds pages and shortcodes in one click)

= 0.49.1 =

* Small bug fix on alert in WP admin ("getBox" alert)
* Small change to CSS to avoid horizontal scrollbar in some browsers on wall when hover to show Delete link

= 0.49 =

* Mail: New mail layout, also fully AJAX
* Profile: Wall posts/reply notifications are more controlled! Less Spam about wall posts and replies...
* Templates: Now include Profile Page Header, Profile Page Body, Page Footer, Forum Header, Mail Page, Email Notifications
* Note: only profile page header template is available to all, others are for Bronze+ members

= 0.48.2 =

* Forum: Added All Activity and Latest Posts views
* Core: Removed redundant admin message re: re-activating core

= 0.48.1 =

* Core: Added Templates Admin page to allow customisation of various page areas (initially Profile Header)
* Forum: Formatting of new topics corrected
* Forum: Increased field length for member titles
* Profile: Admin's can set "trusted" checkbox to highlight those members forum replies
* Profile: Optional initial friend request to new users
* Profile: Admin can change member personal settings and preferences
* Core: Numerous PHP code changes to address PHP warnings

= 0.47.2 = 

* Core: Activation changed in line with WP v3.1 (activation trigger not fired on automatic upgrade, based on WP option value)
* Forum: Changed member rankings so admin can set levels manually
* Forum: Choice of AJAX or hyperlinks/page reloading

= 0.46.1 =

* CSS: Minor change to avoid global font size change
* Forum: Change to view count

= 0.46 =

* Forum: Member rankings
* Forum: Edit functions re-implemented
* Admin: Updated Forum Categories to create sub-categories
* Core: Powered by WPS message not shown if Groups plugin is activated (doesn't need to be used, just activated)

= 0.45 =

* Forum now works on AJAX (to support use in Groups)

= 0.44 =

* Core: Site-wide support for uploaded avatars (uses Gravatar if no local avatar available)
* Image Upload: Option to allow or disallow cropping 

= 0.43 =

* Profile: Avatar plugin removed, functionality added to Profile plugin. Use [symposium-avatar] to default profile page to profile photo
* Profile: Uploaded photos can be now stored in either the database, or the file system
* Profile: Wall: Text is persistent in post/reply fields
* Health Check: Upload image test added

= 0.42 =

* Core: Fixed rogue error report on Health check

= 0.41 =

* Core: BBCode now supports [b], [i], [u], [s], [code], [url], [url=], [img] and [youtube]
* Groups: Added supporting core code for Groups plugin

= 0.40.1 =

* Panel: Added user-defined online status
* Core: Change button class to symposium-button to avoid clashes with theme CSS

= 0.40 =

* Avatar: Fix for sites not installed on the root of URL

= 0.39.1 = 

* Mail: Added Search to InBox
* Forum: Add as favourite star only shows if logged in
* Panel: Chat window: Status light shows correctly

= 0.39 =

* Profile: Added option to set temporary upload folder (useful on shared servers with PHP restrictions)
* Forum: Changed link when search result is a reply to point to parent post
* Panel: Chat windows titles changed colour if minimized and there is a new message
* Panel: Chatroom icon changes to green if there is a new message and chatroom not open
* Panel: Changed icons so they are more consistent (gloss effect)
* Panel: Double default chat window width to make reading messages easier

= 0.38.2 =

* Panel: Changed chat windows/room to post messages instantly
* Panel: Changed order of messages so most recent at top
* Panel: Reduced amount of information in messages to reduce server load

= 0.38.1 = 

* Panel: Chatroom: Maximise/Minimise
* Panel: Fixed bug in online status of friends
* Panel: Changed frequency of friends online status to match chat update

= 0.38 =

* Forum: Added Search
* Notification Bar: Renamed to Panel
* Panel: Added logout icon

= 0.37 =

* Core: Added support for WordPress MS (Multi-site)
* Core: Fixed CSS to work with IE6 and IE7 (not 100% but passable)

= 0.36.1 =

* Re-uploaded to WordPress repository as some files not included in their download ZIP file

= 0.36 =

* Core: Added release notes to Options page
* Mail: Fixed horizontal scrollbar

= 0.35 =

* Avatar: fix to get_url function
* Avatar: uploaded filenames have all characters not A-Z, a-z, 0-9 and . replaced with _
* Introduced new version numbering system

= 0.1.34.2 = 

* Core: Various minor bug fixes to support recent changes
* Profile: Added style for profile name, replacing h1

= 0.1.34.1 =

* Forum: Added Activity log of forum and member activity
* Forum: Made forum replies use AJAX
* Core: removed some references to register_url and login_url

= 0.1.34 =

* Removed: Login and Registration Plugins
* Forum/Mail: Added support for BBcodes [b][/b], [i][/i], [u][/u], [s][/s] and [code][/code]
* Core: Added release notes (that can be hidden after each activation)
* Health Check: Improved Javascript file check
* Notification Bar: Fixed: Dates on chat/chatroom

= 0.1.33.5 =

* Avatar: Fixed JS so uploadify works if WordPress not installed in site root
* Mail: Fixed horizontal scroll bar on Compose
* Health Check: Added Uploadify check

= 0.1.33.4 = 

* Core: Completed CSS compilation
* Members Directory: Added profile photo to autocomplete results
* Profile: CSS fix to post replies

= 0.1.33.3 = 

* Core: Profile CSS moved into Stylesheet
* Re-upload as plugin disappeared from wordpress.org!

= 0.1.33.2 =

* Forum: Added: Auto-expanding textboxes for new topic and replies
* Core: WPS jjvascript filename changes with version (to avoid caching)

= 0.1.33.1 =

* Profile: Wall: Corrected avatar when posting/replying

= 0.1.33 =

* Profile: Wall: Posts and replies now instant
* Profile: Added shortcodes: `[symposium-menu]` and `[symposium-member-header]` - please refer to admin guide
* Profile: Moved Google location map to My Profile information
* Admin Option: How many days old chat messages should be, to be purged
* Admin Option: Width of Google location map
* Core: Added function symposium_members('bar') for use in PHP that displays members search - please refer to admin guide
* Core: Fixed CSS problems and tidied up loads
* Core: Tons of minor changes

= 0.1.32 =

* Notification Bar: Added Chatroom
* Notification Bar: Chat windows and chatroom can be dragged around screen
* Forum: Added login link after prompt to do so if not logged in
* Other minor bug fixes and changes

= 0.1.31 =

* Forum: Favourites can now be saved
* Fix to wall/profile to reflect personal settings

= 0.1.30.2 =

* New version as problem uploading 0.1.30.1 to SVN

= 0.1.30.1 =

* Profile: Added 'Profile' menu link for extended information
* Profile: Added shortcode `[symposium-extended]` - please refer to admin guide
* Profile: Added 'My...' to make it clearer which page your are on
* Admin: Added delete to Extended Fields
* Mail: Fixed so that inbox shows after sending a mail with correct tab
* Mail: Fixed so that mail unread count works when less than 1
* Styes: Fixed rounded corners in Firefox/Mozilla

= 0.1.30 =

* Profile: Friend Request/Cancel and Accept/Reject/Remove now AJAX enabled
* Profile: Preferences and Personal are now AJAX enabled
* Profile: Sub-menu on left is now optional (allowing admin's to arrange their pages)
* Profile: Added shortcodes `[symposium-activity]` and `[symposium-all]` - please refer to admin guide
* Admin: Profile: Field names are just labels, can be renamed without losing data
* Core: minor bug fixes

= 0.1.29.4 =

* Core: several improvements to use of gettext
* Core: Further removal of CSS from code to stylesheet
* Forum: Changed stylesheet to handle narrow template design
* Profile: Changed stylesheet to handle narrow template design

= 0.1.29.3 = 

* Removal of "green" rogue style on widget area
* Profile: Hides menu if you can't see wall/activity based on preferences
* Profile: Shows message if you can't see wall/activity
* Health Check: Added stylesheets report error or not
* Profile: Wall: Fixed link to be subject ID not author ID
* Core: Link to profile page now handles default permalinks
* Core: Fixed clash with emoticons and RSS feed
* Minor Code patches
* Languages: Added correct French translation

= 0.1.29.2 = 

* Patch to fix CSS loading

= 0.1.29.1 = 

* Patch to fix Health Check report and mail plugin version

= 0.1.29 =

* Core: Add full CSS support (copy symposium.css to your theme root directory name the file my-symposium.css)
* Profile: Added Friends Activity and All Activity, and Wall is now "your" wall (like Facebook for example)

= 0.1.28 =

* New plugin: Member avatars (upload profile photos)
* Forum: Added: Option to share with Facebook, Twitter, Email, etc
* Profile: Wall: Limits initial list of replies to 4 with "View all x comments" prompt (AJAX)
* Profile: Wall: Now delivered via AJAX (preparing for photos, etc all via AJAX)
* Profile: Wall: Can now view a single post and replies (such as link sent via notification emails)
* Profile: Preferences, Personal and Friends all delivered via AJAX "within" profile page
* Profile: Added `[symposium-settings]`, `[symposium-personal]` and `[symposium-friends]` shortcodes
* Registration: Optional message to be sent to new members
* Languages: Added Swedish

= 0.1.27.1 = 

* Profile: Wall: Fixed avoidance of receiving post/reply email notifications
* Languages: Added some overlooked text

= 0.1.27 =

* New Widget: Yes/No vote
* Core: Minor code amendments/improvement

= 0.1.26.1 =

* Profile/Forum/Mail: Added automatic conversion of URLs
* Profile: Wall: Added delete options to post and reply (AJAX)
* Created first version of Admin Guide

= 0.1.26 = 

* Core: Added support for .POT and .po/.mo files for languages
* Core: Improved Dashboard widget
* Core: A number of minor code improvements
* Admin: Moved email from/footer to Settings
* Profile: Wall: Added new status post and reply (AJAX)
* Login: Added redirect to previous page option

= 0.1.25 =

* New Plugin: Login/Forgotten Password
* Member Directory: Added search on submit prior to name selection (non-JS)
* Registration: Added email address format validation
* Registration: Added dual password fields that have to match
* Registration: Added addition of wp_usermeta, and now complete wp_user field insert
* Admin: Added option to not load jQuery UI (in case of plugin clashes)
* Notification Bar: Combined login and logout custom redirect (to support Login plugin)

= 0.1.24 =

* Registration: Added password strength and maths anti-spam question
* Members Directory: Highlights friends, shows country and latest status/wall post if permitted
* Forum: Don't get sent an email on your own posts/replies

= 0.1.23 =

* New Plugin: Site Registration
* Notification Bar: Custom Registration link
* Forum: Fixed 'Back to Forum' incorrectly showing on top level
* Core: Improved the pagination function

= 0.1.22 =

* New Plugin: Members Directory/Search (basic for now, but will improve)
* Core: Re-wrote AJAX functions
* Core: Organised files with folder structure
* Core: Reduced number of SQL calls throughout
* Core: If profile plugin deactivated, language is taken from core options
* Admin: Added option to clear event audit table
* Admin: Added options to disable certain features (in case of plugin clashes/server load issues)
* Admin: Added option to disable password change
* Notification Bar: Added Register link if enabled in WordPress
* Notification Bar: Added Site Admin link if site administrator
* Profile: Added option to recieve email on new wall post/reply
* Profile: If birthday still system default (1/1/2010+) then not shown
* Forum: Fixed post author can delete as well as administrators

= 0.1.21 =

* Core: Added Custom redirect URL after logging in
* Core: Added Custom redirect URL after logging out
* Profile: Fixed birthday text

= 0.1.20.1 = 

* Profile: Added friends posts/comments to wall

= 0.1.20 =

* Profile: Added reply to wall posts
* Profile: Can update Email address
* Profile: Can update Display name
* Profile: Password change (currently logs you out to re-authenticate)
* Notification bar: Hides email/friends icons if plugins deactivated
* Notification bar: Added option to show only to logged in members
* Core: option to redirect user to a particular page
* Core: Fixed return value from "ago" for less than 1 second
* Core: Member's default language set to the default language
* Core: Added "ago" for Russian
* Core: Added alignment setting for all WPS plugins

= 0.1.19 =

* Profile: Added Wall with personal status and post comment (this is to be improved with replies, etc)
* Profile: Added custom profile extension fields (to be improved with delete, etc)
* Profile: Added friends and profile extension fields
* Profile: Added privacy for wall

= 0.1.18.1 =

* Forum: Fixed inclusion of functions file prior to function call

= 0.1.18 =

* Chat: Chatbox now shows friends status beside name
* Chat: Friends online status now updates, at notification polling refresh frequency
* Notification bar: Improved polling to reduce server load
* Notification bar: Busy image now hidden if not logged in
* Notification bar: Choose between login/logout via WP or your own URLs
* Notification bar: Choose between WP profile page or WPS profile page
* Core: Email subjects sent in recipients language
* Health Check: Added test for symposium_get_current_userlevel() function
* Languages: Updated Swedish (thanks to Mattias Dahl)
* Languages: Updated 'ago' in Portuguese (thanks to marclatino)
* Lanaguges: Added 'Requires Moderation', 'Friend Request', 'New Message'

= 0.1.17 =

* Friends: Added plugin
* Friends: Added %f as page replacement for pending friends (use in page titles)
* Notification Bar: Added friends online count
* Notification Bar: Added chat windows (click on friends name)
* Notification Bar: Added unread mail icon
* Notification Bar: Added new friends requests icon
* Mail: Added %m as page replacement for unread mail (use in page titles)
* Mail: Added avatar to received mail
* Mail: Added hyperlinks in forum email notifications instead of raw text
* Mail: Enchanced autocomplete to include city and country (and use jQuery UI)
* Member Profile (Personal tab): Added level of privacy (everyone/friends/no-one)
* Member Profile (Personal tab): Added Google Map displaying location
* Member Profile (Personal tab): Added birth date
* Member Profile (Personal tab): Added city and country member is currently in
* Member Profile (Settings tab): Added select language
* Member Profile (Settings tab): Added local time zone
* Member Profile (Settings tab): Added option to receive email notifications of new mail
* Member Profile (Settings tab): Added option to place notification bar top or bottom of screen
* Member Profile (Settings tab): Added option to change alert sound
* Admin Options: Added allow/disallow personal settings in Member Profile
* Code: Started on more Symposium functions to replace repeated code
* Code: Moved Symposium functions to external file
* and changed format of change log...

= 0.1.16.3 =

* Changed: Enhanced field check during activation
* Added: Paging on Audit Log

= 0.1.16.2 =

* Added: New Members widget
* Changed: Enhanced language field health check

= 0.1.16.1 =

* Added: Health check for languages option field
* Changed: Field modification on activation

= 0.1.16 =

* Added: Mail plugin
* Added: Member Profile plugin
* Added: Notification bar plugin
* Changed: creation/updates of tables simplified in core activation
* Added: Hungarian and Swedish languages

= 0.1.15 =

* Changed: Language fields set to TEXT to handle longer language versions and maximum row size in mySQL)

= 0.1.14.2 = 

* Changed: Enhanced moderation page to include all posts for quick administration

= 0.1.14.1 =

* Changed: Corrected 'replies' label on topic level of forum
* Changed: French language translation
* Added: Languages: Czech, Hungarian, Portuguese, Braxilian Portuguese, Norwegian, Dutch, Russian, Polish

= 0.1.14 =

* Added: Additional language fields

= 0.1.13 =

* Added: Moderation
* Changed: Languages no longer loaded from external XML file, built into plugin instead
* Added: Option to load jQuery
* Added: Option to enable smilies/emoticons
* Added: Option to use SEO friendly links
* Changed: Font settings now with each style template, not global options

= 0.1.12.1 =

* Added: Default internal language (English) in case external language XML file can't be opened (usually fopen and curl are both disabled on the server)

= 0.1.12 =

* Fix: Fatal Error on activating in symposium_menu.php when activated before core
* Added: Now try fopen first when loading language XML, if that fails/disabled try curl instead
* Added: Detail audit when loading XML file

= 0.1.11.1 =

* Added: Widget for recent forum posts
* Added: Check that audit function exists before doing audit when forum activates

= 0.1.11 = 

* Added: Event log (admin menu option)
* Added: AJAX test to Health Check

= 0.1.10.1 =

* Change: Improved the Health Check

= 0.1.10 = 

* Added: Change font family and size for headings and body text
* Added: Link in email notifications to stop receiving them
* Fixed: Some overlooked language sentences
* Fixed: Microsoft opacity for `[closed]` tag
* Changed: z-index of please wait/saving messages so they appear on top of other div's

= 0.1.9 =

* Added: Style option for main background color
* Added: Style option for opacity of topics with `[closed]` in the subject
* Added: Option to change the "opacity trigger word" from closed
* Added: Links in email notifications
* Added: Italian language (thanks to Luca Trovato)
* Added: Turkish language (thanks to Cahit Cengizhan)
* Added: Czeck language (thanks to Va&#353;e jm&eacute;no)

= 0.1.8.2 =

* Added: Subscribe to individual categories for new topics
* Removed: Forum-wide new topic subscription
* Fixed: Error caused with links from rest of site (eg. a blog) redirecting to the forum
* Fixed: Problem with apostrophe when editing topic subjects
* Fixed: CSS issue with internal widths hiding far right pixels

= 0.1.8.1 =

* Fixed: Clash with EasingSlider plugin

= 0.1.8 =

* Added: Option to change length of preview text on topics page
* Added: Set level of user that can view the forum
* Added: Members can choose if to receive digests via email
* Added: Set width of forum (helps to fit properly in with some themes)
* Added: Option to count administrator in "topic views"
* Added: Option to change order of replies (old to new, and vice versa)
* Added: Option to not permit replies (for when a topic is for info only)
* Added: Extra security on top of existing security to combat attacks

= 0.1.7.1 =

* Patch: Change to styles to handle underline option

= 0.1.7 =

* Added: Avatars in first topic post
* Added: Small avatars on started by/last reply column
* Added: Underline style option to links
* Added: Support for '.html on PAGES' plugin that appends .html to Wordpress URLs
* Added: Better reporting if loading of language XML file fails
* Change: Enhanced the layout to be simpler, and removed unnecessary headings
* Fix: loading language XML file won't rely upon allow_url_fopen being enabled in php.ini

= 0.1.6 =

* Added: Loads of smilies, and add your own! Check the Forum Smilies page on www.wpsymposium.com
* Added: Search engine friendly links within the forum
* Added: Last Topic in category takes notice of sticky posts
* Added: Views when looking at list of categories
* Added: Spanish language (thanks to Patricia Blanco)
* Added: German language (thanks to Pascal)
* Added: Use of external XML file for languages
* Added: Warning to set Options for WP Symposium after initial installation
* Fixed: Handling apostrophe's when editing a post and other places
* Fixed: Don't show backslashes where shouldn't do
* Fixed: To work if not using Permalinks, ie. using ?page_id=x
* Fixed: Avoid double posting on page refresh
* Fixed: Other minor bug fixes

= 0.1.5 =

* Added: Client side check if post/reply form not filled in
* Added: Automatic smiley replace and image tag replacement, eg: {{rofl}}
* Fixed: Delete category
* Fixed: Set 'allow new topics' for new category

= 0.1.4 =

* Fixed: Bug where all topics were appearing as new in notifications
* Fixed: Two nobreak spaces prior to Back to `[topic]`... link for narrow forums

= 0.1.3 =

* Added: support for multiple languages

= 0.1.2 =

* First external release candidate, no changes yet

== Upgrade Notice ==

Latest news and information on www.wpsymposium.com, also posted on WP Symposium Facebook Group.
