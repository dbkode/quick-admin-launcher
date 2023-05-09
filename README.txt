=== Quick Admin Launcher ===
Contributors: dbeja
Tags: admin, search, dashboard, menu
Donate link: https://www.paypal.com/paypalme/dbkode
Requires at least: 5.7 or higher
Tested up to: 6.2
Requires PHP: 7.2 or higher
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Quickly launch any admin tool & search posts/pages with a shortcut key for enhanced productivity.

== Description ==
Quick Admin Launcher is a plugin for WordPress that allows you to quickly launch any admin tool that is part of WP Admin menu and search any post/page/custom post type with a shortcut key. It saves time and effort by providing a simple and effective way to navigate your WordPress dashboard.

The plugin is lightweight and easy to use, with a sleek interface that makes it simple to find what you\'re looking for. Whether you\'re a beginner or an experienced WordPress user, Quick Admin Launcher is the perfect tool to help you work more efficiently.

== Features ==
* Quickly launches any admin tool that is present on the Admin menu
* Quickly searches any post/page/custom post type to edit it
* Customizable shortcut key
* Customizable post types
* Search for users
* Filter hooks to customize what\'s searchable
* Easy to use and lightweight.
* Sleek interface that is simple to navigate.
* Helps you work more efficiently in WordPress.

== Installation ==
To install this plugin:
1. Download the plugin zip file from the WordPress plugin repository.
2. Go to the WordPress Admin Dashboard -> Plugins -> Add New -> Upload Plugin.
3. Click on the \"Choose file\" button and select the zip file you downloaded in step 1.
4. Click on \"Install Now\" button.
5. Once installed, click on the \"Activate Plugin\" button.
6. You can start using the plugin by pressing `CTRL+K` and start searching for any admin tool or post type
7. Go to `Settings > Quick Admin Launcher` to customize the plugin

== Frequently Asked Questions ==
= Will the launcher only list items the user has access to? =
Yes, the plugin just searches admin items he can see on the menu when he\'s logged in.

= How to add custom items to the search results that are not present on the WP admin menu? =
Use the filter hook `quickal_extra_items` to add new items:
```
add_filter( \'quickal_extra_items\', \'add_custom_items_to_quickal\', 10, 1 );
function add_custom_items_to_quickal( $items ) {
	$items[] = array(
		\'label\' => \'Custom Item\',  // Item label
		\'link\'  => \'https://custom-link.com\',  // Link when clicked
		\'icon\'  => \'dashicons-admin-site\',  // Dashicon or Base64 icon
		\'term\'  => \'custom item\'. // Terms that this item will react to, generally the label lowercase
	);

	return $items;
}
```


== Screenshots ==
1. Launch any admin tool
2. Search for posts
3. Settings Screen

== Changelog ==
= 1.0 =
* Plugin release