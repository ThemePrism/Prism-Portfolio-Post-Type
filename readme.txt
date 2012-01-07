=== Portfolio Post Type ===
Contributors: Kathy Darling
Tags: portfolio, post type
Requires at least: 3.3
Tested up to: 3.3
Stable tag: 0.1
License: GPLv2

== Description ==

This plugin converts Devin Price's Portfolio Post Type plugin to an object-oriented plugin.  Borrowing from the Portfolio Post Type plugin, it registers a custom post type for portfolio items and registers separate portfolio taxonomies for tags and categories.  It sets up columns for the post type on the edit.php screen, including displaying featured images and relevant taxonomy terms.  Additionally, this plugin, creates a taxonomy for 'featured' portfolio pieces that can be edited both via quick edit and in a custom metabox on the post edit screen.  Also, this plugin allows you to change/add a featured item from column view!  

The portfolio image used in the dashboard was designed by Ben Dunkle, who also did the other UI icons in WordPress.  

It doesn't change how portfolio items are displayed in your theme however.  You'll need to add templates for archive-portfolio.php and single.php if you want to customize the display of portfolio items.

== Installation ==

Just install and activate.

== Frequently Asked Questions ==

= How can I display portfolio items differently than regular posts? =

You will need to get your hands dirty with a little code and create a archive-portfolio.php template (for displaying multiple items) and a single-portfolio.php (for displaying the single item).

= Why did you make this? =

Because some post types should not be locked into a theme.  Hopefully, it will save some work for other people who are creating portfolio themes.   
== Changelog ==

= 0.1 =

* Initial release