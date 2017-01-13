=== BuddyPress Member Types ===
Contributors: buddyboss
Requires at least: 3.8
Tested up to: 4.6
Stable tag: 1.0.4
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

BuddyPress Member Types plugin makes it easy to create and manage member types without having to write a single line of code.

== Description ==

BuddyPress Member Types plugin makes it easy to create and manage member types without having to write a single line of code. If your members can be categorized into more than one Member Type, then BP Member Types is the easy answer for your site.

== Installation ==

1. Make sure BuddyPress is activated.
2. Visit 'Plugins > Add New'
3. Click 'Upload Plugin'
4. Upload the file 'buddypress-member-types.zip'
5. Activate BuddyPress Member Types from your Plugins page.

= Configuration =

1. Enable 'Extended Profiles' at 'Settings > BuddyPress > Components'
2. Visit 'Member Types > Add New' and create your member types.
3. Visit 'Member Types > Options' and select your desired options.

== Changelog ==

= 1.0.4 =
* New - Option to make the Member Type field mandatory
* Fix - Member types are listed twice on the members page
* Fix - Member Shortcode being overriden by BuddyPress filter
* Fix - User roles are not getting assigned after member type selection and registration
* Fix - New users are automatically assigned to the member type, if Default Member Type has none selected
* Fix - Issue with translating the label 'Member Type'
* Fix - Error with Gravity Forms

= 1.0.3 =
* New - Allow admin to bulk assign member type (in wp-admin/users)
* New - Add shortcode to display member loop on any page [members type=typename]
* Fix - Member type slug
* Fix - WordPress roles not getting saved

= 1.0.2 =
* New - Import existing member types
* New - Assign WordPress roles to member types
* New - Hide member types completely from Members Directory
* New - Require member type selection in Registration Form
* New - Set default member type in Registration Form
* New - Remove member type selection from Registration Form

= 1.0.1 =
* Better compatibility with BP Portfolio (menu position)

= 1.0.0 =
* Initial Release
* Create and manage new Member Types
* Register for Member Type on signup
* Display member types in Members directory
* Supports profile fields per member type
