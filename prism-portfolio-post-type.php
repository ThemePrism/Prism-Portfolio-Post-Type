<?php
/*
Plugin Name: Prism Portfolio Post Type
Plugin URI: http://www.kathyisawesome.com
Description: Enables a portfolio post type and taxonomies.
Version: 1.0
Author: Kathy Darling
Author URI: http://www.kathyisawesome.com
License: GPLv2
*/


/*  Copyright 2012 Kathy is Awesome

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/



// don't load directly
if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

/*
 * @class 		Prism_Portfolio
 * @package		Prism_Portfolio
 * @category	Class
 * @author		KathyisAwesome
 *
 * Table of Contents
 *
 *
 */

if (!class_exists("Prism_Portfolio")) :

class Prism_Portfolio {

	/** URLS ******************************************************************/
	
	protected static $plugin_url;
	protected static $plugin_path;
	protected static $template_url;
	
	/** Variables ******************************************************************/
	
	static $version = .1;
	static $post_type = "prism_portfolio";
	static $tag = "prism_portfolio_tag";
	static $category = "prism_portfolio_category";
	static $featured = "prism_portfolio_featured";
	static $name = "portfolio";
	static $singular = "portfolio item";
	static $plural = "portfolio items";

	/** Constructor ******************************************************************/
	
	function __construct() { 
	
		// Set up localisation
		self::load_plugin_textdomain();

		// define plugin url
		self::plugin_url();

		// Include required files
		self::includes();

		// Boot up the classes
		Prism_Portfolio_Post_Type::init(); //Register post type, taxonomies and terms
		Prism_Portfolio_Admin_Options::init(); //Add Plugin Options	
		Prism_Portfolio_Edit_Screen::init(); // Add Columns, Sorting and Quick Edit to Portfolio Edit Screen
		Prism_Portfolio_Featured_Tax::init(); //Creates, saves and validates the data for the Metaboxes
		Prism_Portfolio_Gallery::init(); //Creates, manages and saves the data for the Gallery Attachments
		
		//activation and deactivation hooks
		register_activation_hook( __FILE__, array(__CLASS__,'activate') );
		register_deactivation_hook( __FILE__, array(__CLASS__,'deactivate') );

		//add action links to plugins page
		add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(__CLASS__,'plugin_action_links' ));

		
	}
	

	/**
	 * Get the plugin url
	 */
	public static function plugin_url() { 
		if(isset(self::$plugin_url)) return self::$plugin_url;
		return self::$plugin_url = plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
	}

	
	/**
	 * Get the plugin path
	 */
	public static function plugin_path() { 	
		if(isset(self::$plugin_path)) return self::$plugin_path;
		return self::$plugin_path = plugin_dir_path( __FILE__ );
	 }



	/**
	 * Localisation
	 **/
	static function load_plugin_textdomain() {
		load_plugin_textdomain("prism_portfolio", false, basename(dirname( __FILE__ )) . '/languages');
	}


	/**
	 * Include Required Files
	 **/
	static function includes() {

		$includes = array ( 'admin/edit-screen.php',
							'admin/admin-options.php',
							'admin/post-type-init.php',
							'admin/featured-metabox.php',
							'admin/gallery-metabox.php'
							);
		
		foreach ($includes as $include) include_once $include;

	}

	/**
	 * Activation 
	 **/

	static function activate() {
		global $wpdb;

		if (function_exists('is_multisite') && is_multisite()) {
			// check if it is a network activation - if so, run the activation function for each blog id
			if (isset($_GET['networkwide']) && ($_GET['networkwide'] == 1)) {
				$old_blog = $wpdb->blogid;
				// Get all blog ids
				$blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs"));
				foreach ($blogids as $blog_id) {
					switch_to_blog($blog_id);
					self::_activate();
				}
				switch_to_blog($old_blog);
				return;
			}	
		} 
		self::_activate();		
	}
	
	static function _activate() {
		Prism_Portfolio_Post_Type::register_type();
		
		Prism_Portfolio_Admin_Options::default_options();
		
		/**
		* Flushes rewrite rules on plugin activation to ensure portfolio posts don't 404
		* http://codex.wordpress.org/Function_Reference/flush_rewrite_rules
		*/
		flush_rewrite_rules();
	}

	/**
	 * De-Activation 
	 **/

	static function deactivate() {
		global $wpdb;

		if (function_exists('is_multisite') && is_multisite()) {
			// check if it is a network activation - if so, run the activation function for each blog id
			if (isset($_GET['networkwide']) && ($_GET['networkwide'] == 1)) {
				$old_blog = $wpdb->blogid;
				// Get all blog ids
				$blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs"));
				foreach ($blogids as $blog_id) {
					switch_to_blog($blog_id);  
					self::_mu_deactivate('portfolio');
				}
				switch_to_blog($old_blog);
				return;
			}	
		} else {
		self::_deactivate();
		}
	}	
	
	//@TODO: is this correct? link to slug from plugin options
	static function _deactivate() {
		global $wp_rewrite;		
		$wp_rewrite->add_permastruct( 'portfolio', '');
		$wp_rewrite->flush_rules();
	}
  

 
	// remove all rewrite rules for a given permastruct
	static function _mu_deactivate($permastruct, $ep_mask=EP_NONE) {
		// replace all tags within permastruct  
		if (!$permastruct)return;  
		global $wp_rewrite;
		$wp_rewrite->matches = 'matches';
		$remove_rules = $wp_rewrite->generate_rewrite_rules($permastruct);
		$num_rules = count($remove_rules);
		// Get first rule
		$rule1 = reset($remove_rules); $key_rule1 = key($remove_rules);
	 
		$rules = get_option('rewrite_rules');
		$i = $num_rules;
		foreach ($rules as $pretty_link => $query_link) {
			// find the first rule
			if (($pretty_link == $key_rule1) && ($query_link == $rule1)) { $i = 0; }
			if ($i < $num_rules) {
				// Delete next $num_rules
				unset($rules[$pretty_link]); $i++;
			}	
		}
		update_option('rewrite_rules', $rules);
	}

	/**
	 * Adds a link to plugin's settings and help pages (shows up next to the 
	 * deactivation link on the plugins management page)
	 */
	static function plugin_action_links( $links )	{ 
		array_unshift( $links, '<a href="edit.php?post_type=prism_portfolio&page=settings">' . __('Settings', "prism_portfolio") . '</a>' );	
		return $links; 
	}



} // end class
endif;

/**
* Launch the whole plugin
*/
global $prism_portfolio;
if (class_exists("Prism_Portfolio") && !$prism_portfolio) $prism_portfolio = new Prism_Portfolio();	


	
?>