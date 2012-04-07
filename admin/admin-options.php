<?php
/**
 * Post Type Admin Options
 * 
 * The Post Type Admin Options class creates and validates options for the plugin
 *
 * @class 		Prism_Admin_Options
 * @package		Prism_Portfolio
 * @category	Class
 * @author		Kathy Darling
 *
 * Table of Contents
 *
 *
 *
 *
 */

// don't load directly
if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class Prism_Admin_Options extends Prism_Portfolio {

	public function __construct() {  

	// add the plugin options and options page
    add_action( 'admin_init', array( __CLASS__, 'options_init' ) );
    add_action('admin_menu', array(__CLASS__, 'register_submenu_page'));

	// add notices on options save
    add_action( 'admin_notices', array(__CLASS__,'admin_notice' ),0);
	}

	static function default_options(){
		$defaults = array ('base_id' => 0,
					'prepend_portfolio_to_urls' => 'no',
					'cat_slug' => 'portfolio-category',
					'tag_slug' => 'portfolio-tag',
					'prepend_portfolio_page_to_items' => 'no',
					'prepend_category_to_items' => 'no'
		);
		update_option('prism_portfolio_defaults',$defaults);
		
		//if no options exist set them as default
		if(false === get_option('prism_portfolio_options')) update_option('prism_portfolio_options',$defaults);
	}

	// Init plugin options to white list our options
	static function options_init(){
		register_setting( 'prism_portfolio_settings','prism_portfolio_options', array(__CLASS__,'options_validate'));
	}

	static function register_submenu_page() {
		add_submenu_page( 'edit.php?post_type='.self::$post_type, __('Portfolio Settings','prism_portfolio'), __('Portfolio Settings','prism_portfolio'), 'manage_options', 'settings', array(__CLASS__,'submenu_page_callback') ); 
	}

	// Draw the menu page itself

	static function submenu_page_callback() {
		include(self::plugin_path() . '/admin/inc/options.php');
	}

	// Sanitize and validate input. Accepts an array, return a sanitized array.

	// Validate user input
	static function options_validate( $input ) {  
	
		if(isset($input['reset'])) {
			$defaults = get_option('prism_portfolio_defaults');
			return $defaults;
		}
		
		// if not a reset then proceed

		$options = get_option('prism_portfolio_options');

		$valid = array();

		$valid['base_id'] = absint($input['base_id']);

		$valid['cat_slug'] = sanitize_title ( $input['cat_slug']);
		
		$valid['tag_slug'] = sanitize_title ( $input['tag_slug']);
		
		if(isset($input['prepend_portfolio_to_urls']) && $input['prepend_portfolio_to_urls']=='yes'){
			$valid['prepend_portfolio_to_urls'] = 'yes'; 
		} else {
			$valid['prepend_portfolio_to_urls'] ='no'; 
		}
		
		if(isset($input['prepend_portfolio_page_to_items']) && $input['prepend_portfolio_page_to_items']=='yes'){
			$valid['prepend_portfolio_page_to_items'] = 'yes';
		} else {
			$valid['prepend_portfolio_page_to_items'] ='no';
		}
		
		if(isset($input['prepend_category_to_items']) && $input['prepend_category_to_items']=='yes'){
			$valid['prepend_category_to_items'] = 'yes';
		} else {
			$valid['prepend_category_to_items'] ='no';
		}
		
		//Bad slug entered, warn user
		if( $valid['cat_slug'] != $input['cat_slug'] ) {
			add_settings_error(
				'slug',           // setting title
				'prism_portfolio_texterror',            // error ID
				__('Category slug cannot contain spaces or HTML characters','prism_portfolio'),   // error message
				'error'                        // type of message
			);
		}
		
		if( $valid['tag_slug'] != $input['tag_slug'] ) {
			add_settings_error(
				'slug',           // setting title
				'prism_portfolio_texterror',            // error ID
				__('Tag slug cannot contain spaces or HTML characters','prism_portfolio'),   // error message
				'error'                        // type of message
			);
		}



		//detect a change of the permalinks remind to re-save permalinks
		if (isset($options['base_id']) && $options['base_id'] != $valid['base_id'] 
			|| isset($options['cat_slug']) && $options['cat_slug'] != $valid['cat_slug']
			|| isset($options['tag_slug']) && $options['tag_slug'] != $valid['tag_slug']
			|| isset($options['prepend_portfolio_to_urls']) && $options['prepend_portfolio_to_urls'] != $valid['prepend_portfolio_to_urls']
			|| isset($options['prepend_portfolio_page_to_items']) && $options['prepend_portfolio_page_to_items'] != $valid['prepend_portfolio_page_to_items']
			|| isset($options['prepend_category_to_items']) && $options['prepend_category_to_items'] != $valid['prepend_category_to_items']){
			add_settings_error(
				'slug',           // setting title
				'prism_portfolio_texterror',            // error ID
				__('You\'ve changed your permalink structure in some way.  Don\'t forget to <a href="'.admin_url('options-permalink.php').'">re-save your permalinks</a>.','prism_portfolio'),   // error message
				'updated'                        // type of message
			);
		}

		return $valid; 
	}


	/*  admin notice */
	static function admin_notice(){
		  //print the message
		  global $post;
		  $notice = get_option('prism_portfolio_notice');
		  if (empty($notice)) return '';
		  foreach($notice as $pid => $m){
			  if ($post->ID == $pid ){
				  echo '<div id="message" class="error"><p>'.$m.'</p></div>';
				  //make sure to remove notice after its displayed so its only displayed when needed.
				  unset($notice[$pid]);
				  update_option('prism_portfolio_notice',$notice);
				  break;
			  }
		  }
	}


} //end class