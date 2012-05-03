<?php
/**
 * Prism Gallery Metabox
 * 
 * The Prism Gallery Metaboxes class creates a metabox that will manage attachments to the prism_portfolio post type.
 *
 * @class 		Prism_Gallery
 * @package		Prism_Portfolio
 * @category	Class
 * @author		Kathy Darling
 *
 * This part of the plugin is heavily derivative of the File Gallery Plugin by Bruno "Aesque" Babic
 * http://skyphe.org/code/wordpress/file-gallery/
 *
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


/**
 * Setup default Prism Gallery options
 */

define('PRISM_GALLERY_VERSION', '1.7.4.1');
define('PRISM_GALLERY_DEFAULT_TEMPLATES', serialize( array('default', 'prism_portfolio', 'list', 'simple') ) );


/**
 * Just a variables placeholder for now
 *
 * @since 1.6.5.1
 */
class Prism_Gallery extends Prism_Portfolio {

	/**
	 * settings, 
	 * their default values, 
	 * and false default values
	 *
	 * @since 1.7
	 */
	var $settings = array();
	var $defaults = array();
	var $false_defaults = array();
	var $debug = array();
	
	/**
	 * Holds the ID number of current post's gallery
	 */
	var $gallery_id;

	/**
	 * Holds gallery options overriden 
	 * via 'prism_gallery_overrides' template function
	 */
	var $overrides;
	
	/**
	 * Whether Attachment custom fields plugin is 
	 * installed or not
	 */
	var $acf = false;
	
	/**
	 * Whether SSL is on for wp-admin
	 */
	var $ssl_admin = false;

	/***/
	function Prism_Gallery()
	{
		// Checks if Attachment custom fields plugin is installed (not released yet)
		if( false !== strpos(serialize(get_option('active_plugins')), 'attachment-custom-fields.php') )
			$this->acf = true;
		
		if( defined('FORCE_SSL_ADMIN') && true === FORCE_SSL_ADMIN )
			$this->ssl_admin = true;
	}
	
	
} //end class


/**
 * 
 * @since 1.7
 */
function prism_gallery_do_settings()
{
	global $prism_portfolio_gallery;

	$prism_portfolio_gallery->settings = array(
			'disable_shortcode_handler' => array(
				'default' => 0, 
				'display' => true,
				'title' => __("Disable 'Prism Gallery' handling of [gallery] shortcode?", 'prism_portfolio'),
				'type' => 'checkbox',
				'section' => 0,
				'position' => 0
			),
			'show_on_post_type' => array(
				'default' => 1, 
				'display' => true,
				'title' => __('Display Prism Gallery on which post types?', 'prism_portfolio'),
				'type' => 'checkbox',
				'values' => prism_gallery_post_type_checkboxes(),
				'section' => 0,
				'position' => 0
			),
			'alt_color_scheme' => array(
				'default' => 1, 
				'display' => true,
				'title' => __('Use alternative color scheme (a bit more contrast)?', 'prism_portfolio'),
				'type' => 'checkbox',
				'section' => 0,
				'position' => 0
			),
			'pagination_count' => array(
				'default' => 9, 
				'display' => true,
				'title' => __('How many page links should be shown in pagination?', 'prism_portfolio'),
				'type' => 'text',
				'section' => 0,
				'position' => 0
			),
			'auto_enqueued_scripts' => array(
				'default' => 'thickbox', 
				'display' => true,
				'title' => __('Auto enqueue lightbox scripts for which link classes (separate with commas)?', 'prism_portfolio'),
				'type' => 'text',
				'section' => 0,
				'position' => 0
			),
			'default_metabox_image_size' => array(
				'default' => 'thumbnail', 
				'display' => true,
				'title' => __('Default WordPress image size for thumbnails in Prism Gallery metabox on post editing screens?', 'prism_portfolio'),
				'type' => 'select',
				'values' => prism_gallery_dropdown( 'default_metabox_image_size', 'image_size' ),
				'section' => 0,
				'position' => 0
			),
			'default_metabox_image_width' => array(
				'default' => 75, 
				'display' => true,
				'title' => __('Default width (in pixels) for thumbnails in Prism Gallery metabox on post editing screens?', 'prism_portfolio'),
				'type' => 'text',
				'section' => 0,
				'position' => 0
			),
			
			
			'default_image_size' => array(
				'default' => 'thumbnail',
				'display' => true,
				'title' => '</th></tr><tr><td colspan="2"><strong style="display: block; margin-top: -15px; font-size: 115%; color: #21759B;">' . __('Some default values for when inserting a gallery into a post', 'prism_portfolio') . '...</strong></td></tr><tr><td colspan="2"><p id="prism-gallery-media-settings-notice" style="margin: 0; background-color: #FFFFE8; border-color: #EEEED0; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius: 3px; border-style: solid; border-width: 1px; padding: 0.6em;">' . sprintf(__('The following two blocks of options <strong>do not</strong> affect the output/display of your galleries - they are here only so you could set default values for Prism Gallery metabox on post editing screen. <a href="%s/help/index.html#settings_page" target="_blank">More information is available in the help file</a>.', "prism_portfolio"), PRISM_GALLERY_URL) . '</p></td></tr><tr valign="top"><th scope="row">' . __('size', 'prism_portfolio'),
				'type' => 'select',
				'values' => prism_gallery_dropdown( 'default_image_size', 'image_size' ),
				'section' => 0,
				'position' => 0
			), 
			'default_linkto' => array(
				'default' => 'attachment', 
				'display' => true,
				'title' => __('link', 'prism_portfolio'),
				'type' => 'select',
				'values' => prism_gallery_dropdown( 'default_linkto', 'linkto' ),
				'section' => 0,
				'position' => 0
			),
			'default_linked_image_size' => array(
				'default' => 'full', 
				'display' => true,
				'title' => __('linked image size', 'prism_portfolio'),
				'type' => 'select',
				'values' => prism_gallery_dropdown( 'default_linked_image_size', 'image_size' ),
				'section' => 0,
				'position' => 0
			),
			'default_external_url' => array(
				'default' => '',  
				'display' => true,
				'title' => __('external url', 'prism_portfolio'),
				'type' => 'text',
				'section' => 0,
				'position' => 0
			),
			'default_orderby' => array(
				'default' => '',  
				'display' => true,
				'title' => __('order by', 'prism_portfolio'),
				'type' => 'select',
				'values' => prism_gallery_dropdown( 'default_orderby', 'orderby' ),
				'section' => 0,
				'position' => 0
			),
			'default_order' => array(
				'default' => 'ASC',  
				'display' => true,
				'title' => __('order', 'prism_portfolio'),
				'type' => 'select',
				'values' => prism_gallery_dropdown( 'default_order', 'order' ),
				'section' => 0,
				'position' => 0
			),
			'default_template' => array(
				'default' => 'default',  
				'display' => true,
				'title' => __('template', 'prism_portfolio'),
				'type' => 'select',
				'values' => prism_gallery_dropdown( 'default_template', 'template' ),
				'section' => 0,
				'position' => 0
			),
			'default_linkclass' => array(
				'default' => '',  
				'display' => true,
				'title' => __('link class', 'prism_portfolio'),
				'type' => 'text',
				'section' => 0,
				'position' => 0
			),
			'default_imageclass' => array(
				'default' => '',  
				'display' => true,
				'title' => __('image class', 'prism_portfolio'),
				'type' => 'text',
				'section' => 0,
				'position' => 0
			),
			'default_columns' => array(
				'default' => 3,  
				'display' => true,
				'title' => __('columns', 'prism_portfolio'),
				'type' => 'select',
				'values' => prism_gallery_dropdown( 'default_columns', 'columns' ),
				'section' => 0,
				'position' => 0
			),
			'default_mimetype' => array(
				'default' => '', 
				'display' => true,
				'title' => __('mime type', 'prism_portfolio'),
				'type' => 'text',
				'section' => 0,
				'position' => 0
			),
			'default_galleryclass' => array(
				'default' => '', 
				'display' => true,
				'title' => __('gallery class', 'prism_portfolio'),
				'type' => 'text',
				'section' => 0,
				'position' => 0
			),
			
			
			'single_default_image_size' => array(
				'default' => 'thumbnail',  
				'display' => true,
				'title' => '</th></tr><tr><td colspan="2"><strong style="display: block; margin-top: -15px; font-size: 115%; color: #21759B;">' . __('...and for when inserting (a) single image(s) into a post', 'prism_portfolio') . '</strong></td></tr><tr valign="top"><th scope="row">' . __('size', 'prism_portfolio'),
				'type' => 'select',
				'values' => prism_gallery_dropdown( 'single_default_image_size', 'image_size' ),
				'section' => 0,
				'position' => 0
			),
			'single_default_linkto' => array(
				'default' => 'attachment',  
				'display' => true,
				'title' => __('link', 'prism_portfolio'),
				'type' => 'select',
				'values' => prism_gallery_dropdown( 'single_default_linkto', 'linkto' ),
				'section' => 0,
				'position' => 0
			),
			'single_default_external_url' => array(
				'default' => '',  
				'display' => true,
				'title' => __('external url', 'prism_portfolio'),
				'type' => 'text',
				'section' => 0,
				'position' => 0
			),
			'single_default_linkclass' => array(
				'default' => '',  
				'display' => true,
				'title' => __('link class', 'prism_portfolio'),
				'type' => 'text',
				'section' => 0,
				'position' => 0
			),
			'single_default_imageclass' => array(
				'default' => '', 
				'display' => true,
				'title' => __('image class', 'prism_portfolio'),
				'type' => 'text',
				'section' => 0,
				'position' => 0
			),
			'single_default_align' => array(
				'default' => 'none', 
				'display' => true,
				'title' => __('alignment', 'prism_portfolio'),
				'type' => 'select',
				'values' => prism_gallery_dropdown( 'single_default_align', 'align' ),
				'section' => 0,
				'position' => 0
			),
			'cache' => array(
				'default' => 0, 
				'display' => true,
				'title' => '</th></tr><tr><td colspan="2"><strong style="display: block; margin-top: -15px; font-size: 115%; color: #21759B;">' . __('Cache', 'prism_portfolio') . '</strong></td></tr><tr valign="top"><th scope="row">' . __('Enable caching?', 'prism_portfolio'),
				'type' => 'checkbox',
				'section' => 0,
				'position' => 0
			),
			'cache_time' => array(
				'default' => 3600, // == 1 hour 
				'display' => true,
				'title' => __("Cache expires after how many seconds? (leave as is if you don't know what it means)", 'prism_portfolio'),
				'type' => 'text',
				'section' => 0,
				'position' => 0
			),
			'cache_non_html_output' => array(
				'default' => 0, 
				'display' => true,
				'title' => __('Cache non-HTML gallery output (<em>array, object, json</em>)', 'prism_portfolio'),
				'type' => 'checkbox',
				'section' => 0,
				'position' => 0
			),
			
			
			'e_display_attachment_count' => array(
				'default' => 1, 
				'display' => true,
				'title' => '</th></tr><tr><td colspan="2"><strong style="display: block; margin-top: -15px; font-size: 115%; color: #21759B;">' . __('Edit screens options', 'prism_portfolio') . '</strong></td></tr><tr valign="top"><th scope="row">' . __('Display attachment count?', 'prism_portfolio'),
				'type' => 'checkbox',
				'section' => 0,
				'position' => 0
			),
			'library_filter_duplicates' => array(
				'default' => 1, 
				'display' => true,
				'title' => __('Filter out duplicate attachments (copies) when browsing media library?', 'prism_portfolio'),
				'type' => 'checkbox',
				'section' => 0,
				'position' => 0
			),
			'e_display_media_tags' => array(
				'default' => 1, 
				'display' => true,
				'title' => __('Display media tags for attachments in media library?', 'prism_portfolio'),
				'type' => 'checkbox',
				'section' => 0,
				'position' => 0
			),
			'e_display_post_thumb' => array(
				'default' => 1, 
				'display' => true,
				'title' => __('Display post thumb (if set)?', 'prism_portfolio'),
				'type' => 'checkbox',
				'section' => 0,
				'position' => 0
			),

		
			'in_excerpt' => array(
				'default' => 1,
				'display' => true,
				'title' => '</th></tr><tr><td colspan="2"><strong style="display: block; margin-top: -15px; font-size: 115%; color: #21759B;">' . __('Other options', 'prism_portfolio') . '</strong></td></tr><tr valign="top"><th scope="row">' . __('Display galleries within post excerpts?', 'prism_portfolio'),
				'type' => 'checkbox',
				'section' => 0,
				'position' => 0
			), 
			'in_excerpt_replace_content' => array(
				'default' => '<p><strong>(' . __('galleries are shown on full posts only', 'prism_portfolio') . ')</strong></p>',
				'display' => true,
				'title' => __("Replacement text for galleries within post excerpts (if you haven't checked the above option)", 'prism_portfolio'),
				'type' => 'textarea',
				'section' => 0,
				'position' => 0
			), 
			'display_gallery_fieldset' => array(
				'default' => 1, 
				'display' => true,
				'title' => __('Display options for inserting galleries into a post?', 'prism_portfolio'),
				'type' => 'checkbox',
				'section' => 0,
				'position' => 0
			),
			'display_single_fieldset' => array(
				'default' => 1, 
				'display' => true,
				'title' => __('Display options for inserting single images into a post?', 'prism_portfolio'),
				'type' => 'checkbox',
				'section' => 0,
				'position' => 0
			),
			'display_acf' => array(
				'default' => 1, 
				'display' => true,
				'title' => __('Display attachment custom fields?', 'prism_portfolio'),
				'type' => 'checkbox',
				'section' => 0,
				'position' => 0
			),
			'insert_gallery_button' => array(
				'default' => 1, 
				'display' => true,
				'title' => __("Display 'insert gallery' button even if gallery options are hidden?", 'prism_portfolio'),
				'type' => 'checkbox',
				'section' => 0,
				'position' => 0
			),
			'insert_single_button' => array(
				'default' => 1, 
				'display' => true,
				'title' => __("Display 'insert single image(s)' button even if single image options are hidden?", 'prism_portfolio'),
				'type' => 'checkbox',
				'section' => 0,
				'position' => 0
			),	
			'del_options_on_deactivate' => array(
				'default' => 0, 
				'display' => true,
				'title' => __('Delete all options on deactivation?', 'prism_portfolio'),
				'type' => 'checkbox',
				'section' => 0,
				'position' => 0
			),
			
			
			/**
			 * Disabled options
			 */
			'version' => array(
				'default' => PRISM_GALLERY_VERSION,
				'display' => 'disabled',
				'title' => __('Prism Gallery version', 'prism_portfolio'),
				'type' => 'text',
				'section' => 0,
				'position' => 0
			),
			'folder' => array(
				'default' =>  Prism_Portfolio::plugin_url(),
				'display' => 'disabled',
				'title' => __('Prism Gallery folder', 'prism_portfolio'),
				'type' => 'text',
				'section' => 0,
				'position' => 0
			), 
			'abspath' => array(
				'default' => PRISM_GALLERY_ABSPATH,
				'display' => 'disabled',
				'title' => __('Prism Gallery path', 'prism_portfolio'),
				'type' => 'text',
				'section' => 0,
				'position' => 100
			),
			'media_tag_taxonomy_name' => array(
				'default' => 'media_tag',
				'display' => 'disabled',
				'title' => __('Media tags Taxonomy name', 'prism_portfolio'),
				'type' => 'text',
				'section' => 0,
				'position' => 0
			),
			'media_tag_taxonomy_slug' => array(
				'default' => 'media-tag',
				'display' => 'disabled',
				'title' => __('Media tags URL slug', 'prism_portfolio'),
				'type' => 'text',
				'section' => 0,
				'position' => 0
			),

			
			/**
			 * Hidden options
			 */
			'insert_options_state' => array(
				'default' => 1, 
				'display' => true,
				'title' => __('Gallery insert options state', 'prism_portfolio'),
				'type' => 'checkbox',
				'section' => 0,
				'position' => 0
			),
			'insert_single_options_state' => array(
				'default' => 1, 
				'display' => true,
				'title' => __('Single images insert options state', 'prism_portfolio'),
				'type' => 'checkbox',
				'section' => 0,
				'position' => 0
			),
			'acf_state' => array(
				'default' => 1, 
				'display' => true,
				'title' => __('Attachment custom fields state', 'prism_portfolio'),
				'type' => 'checkbox',
				'section' => 0,
				'position' => 0
			)
		);
	
	foreach( $prism_portfolio_gallery->settings as $key => $val )
	{
		$prism_portfolio_gallery->defaults[$key] = $val['default'];
		
		if( is_bool($val['default']) || 1 === $val['default'] || 0 === $val['default'] )
			$prism_portfolio_gallery->false_defaults[$key] = 0;
	}
}


/**
 * Registers default Prism Gallery options when plugin is activated
 */
function prism_gallery_activate()
{
	global $prism_portfolio_gallery;

	prism_gallery_plugins_loaded();
	prism_gallery_after_setup_theme();
	prism_gallery_do_settings();
	
	$defaults = $prism_portfolio_gallery->defaults;

	// if options already exist, upgrade
	if( $options = get_option('prism_portfolio_gallery') )
	{
		// preserve display options when upgrading from below 1.6.5.3
		if( ! isset($options['display_acf']) )
		{
			if( isset($options['insert_options_states']) )
				$states = explode(',', $options['insert_options_states']);
			else
				$states = array('1', '1');
			
			if( isset($options['display_insert_fieldsets']) )
				$display = $options['display_insert_fieldsets'];
			else
				$display = 1;
	
			$defaults['insert_options_state'] = (int) $states[0];
			$defaults['insert_single_options_state'] = (int) $states[1];
			$defaults['acf_state'] = 1;
		
			$defaults['display_gallery_fieldset'] = $display;
			$defaults['display_single_fieldset'] = $display;
			$defaults['display_acf'] = 1;
		}

		$defaults = prism_gallery_parse_args( $options, $defaults);
		$defaults['folder']  =  Prism_Portfolio::plugin_url();
		$defaults['abspath'] = PRISM_GALLERY_ABSPATH;
		$defaults['version'] = PRISM_GALLERY_VERSION;
	}
	else // Fresh installation, show on posts and pages
	{
		$defaults['show_on_post_type_post'] = 1;
		$defaults['show_on_post_type_page'] = 1;
	}
	
	update_option('prism_portfolio_gallery', $defaults);
	
	// clear any existing cache
	prism_gallery_clear_cache();
}
//register_activation_hook( __FILE__, 'prism_gallery_activate' );


/**
 * Do activation procedure on plugin upgrade
 */
function prism_gallery_upgrade()
{
	$options = get_option('prism_portfolio_gallery');
	
	if( $options && version_compare( $options['version'], PRISM_GALLERY_VERSION, '<') )
		prism_gallery_activate();
}
add_action( 'admin_init', 'prism_gallery_upgrade' );


/**
 * Some cleanup on deactivation
 */
function prism_gallery_deactivate()
{
	prism_gallery_clear_cache();
	
	$options = get_option('prism_portfolio_gallery');
	
	if( isset($options['del_options_on_deactivate']) && true == $options['del_options_on_deactivate'] )
		delete_option('prism_portfolio_gallery');
}
register_deactivation_hook( __FILE__, 'prism_gallery_deactivate' );


/**
 * Support for other plugins
 *
 * Supported so far:
 * - WordPress Mobile Edition
 * - Media Tags
 */
function prism_gallery_plugins_loaded()
{
	$prism_gallery_abspath = WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__));
	$prism_gallery_abspath = str_replace('\\', '/', $prism_gallery_abspath);
	$prism_gallery_abspath = preg_replace('#/+#', '/', $prism_gallery_abspath);
	
	// file gallery directories and template names
	define('PRISM_GALLERY_URL', WP_PLUGIN_URL . '/' . basename( dirname(__FILE__) ));
	define('PRISM_GALLERY_ABSPATH', $prism_gallery_abspath);
	
	$mobile = false;
	$options = get_option('prism_portfolio_gallery');
	
	// WordPress Mobile Edition
	if( function_exists('cfmobi_check_mobile') && cfmobi_check_mobile() )
	{
		$mobile = true;
	
		if( ! isset($options['disable_shortcode_handler']) || true != $options['disable_shortcode_handler'] )
			add_filter('stylesheet_uri', 'prism_gallery_mobile_css');
	}

	define('PRISM_GALLERY_MOBILE', $mobile);
	
	prism_gallery_media_tags_get_taxonomy_slug();
}
add_action('plugins_loaded', 'prism_gallery_plugins_loaded', 100);


/*
 * Some constants you can filter even with your theme's functions.php file
 *
 * @since 1.6.3
 */
function prism_gallery_after_setup_theme()
{
	$stylesheet_directory = get_stylesheet_directory();
	$prism_gallery_theme_abspath = str_replace('\\', '/', $stylesheet_directory);
	$prism_gallery_theme_abspath = preg_replace('#/+#', '/', $prism_gallery_theme_abspath);

	define( 'PRISM_GALLERY_THEME_ABSPATH', $prism_gallery_theme_abspath );
	define( 'PRISM_GALLERY_THEME_TEMPLATES_ABSPATH', apply_filters('prism_gallery_templates_folder_abspath', PRISM_GALLERY_THEME_ABSPATH . '/prism-gallery-templates') ) ;
	define( 'PRISM_GALLERY_THEME_TEMPLATES_URL', apply_filters('prism_gallery_templates_folder_url', get_bloginfo('stylesheet_directory') . '/prism-gallery-templates') );
	
	define( 'PRISM_GALLERY_CONTENT_TEMPLATES_ABSPATH', apply_filters('prism_gallery_content_templates_folder_abspath', WP_CONTENT_DIR . '/prism-gallery-templates') );
	define( 'PRISM_GALLERY_CONTENT_TEMPLATES_URL', apply_filters('prism_gallery_content_templates_folder_url', WP_CONTENT_URL . '/prism-gallery-templates') );
	
	define( 'PRISM_GALLERY_DEFAULT_TEMPLATE_URL', apply_filters('prism_gallery_default_template_url', PRISM_GALLERY_URL . '/templates/default') );
	define( 'PRISM_GALLERY_DEFAULT_TEMPLATE_ABSPATH', apply_filters('prism_gallery_default_template_abspath', PRISM_GALLERY_ABSPATH . '/templates/default') );
	define( 'PRISM_GALLERY_DEFAULT_TEMPLATE_NAME', apply_filters('prism_gallery_default_template_name', 'default') );
	
	// file icons directory
	$prism_gallery_crystal_url = get_bloginfo('wpurl') . '/' . WPINC . '/images/crystal';

	if( ! defined( 'PRISM_GALLERY_CRYSTAL_URL' ) )
		define( 'PRISM_GALLERY_CRYSTAL_URL', apply_filters('prism_gallery_crystal_url', $prism_gallery_crystal_url) );

	// display debug information
	if( ! defined( 'PRISM_GALLERY_DEBUG' ) )
		define( 'PRISM_GALLERY_DEBUG', false );
}
add_action('after_setup_theme', 'prism_gallery_after_setup_theme');


/**
 * Adds a link to plugin's settings and help pages (shows up next to the 
 * deactivation link on the plugins management page)
 */
function prism_gallery_plugin_action_links( $links )
{ 
	array_unshift( $links, '<a href="options-media.php">' . __('Settings', 'prism_portfolio') . '</a>' );
	array_unshift( $links, '<a href="' . PRISM_GALLERY_URL . '/help/index.html" target="_blank">' . __('Help', 'prism_portfolio') . '</a>' );
	
	return $links; 
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'prism_gallery_plugin_action_links' );


/**
 * Adds media_tags taxonomy so we can tag attachments
 */
function prism_gallery_add_textdomain_and_taxonomies()
{
	global $mediatags;

	if( ! (isset($mediatags) && is_a($mediatags, 'MediaTags') && defined('MEDIA_TAGS_TAXONOMY')) )
	{
		$args = array(
			'public'                => true,
			'update_count_callback' => 'prism_gallery_update_media_tag_term_count',
			'rewrite'               => array(
										'slug' => PRISM_GALLERY_MEDIA_TAG_SLUG
			),
			'labels'                => array(
										'name'           => __('Media tags', 'prism_portfolio'),
										'singular_label' => __('Media tag', 'prism_portfolio')
			)
		);
		
		register_taxonomy( PRISM_GALLERY_MEDIA_TAG_NAME, 'attachment', $args );
	}
	
	if( true == get_option('prism_gallery_flush_rewrite_rules') )
	{
		global $wp_rewrite;
		$wp_rewrite->flush_rules( false );

		delete_option('prism_gallery_flush_rewrite_rules');
	}
}
add_action('init', 'prism_gallery_add_textdomain_and_taxonomies', 100);


/**
 * A slightly modified copy of WordPress' _update_post_term_count function
 * 
 * Updates number of posts that use a certain media_tag
 */
function prism_gallery_update_media_tag_term_count( $terms )
{
	global $wpdb;

	foreach ( (array) $terms as $term )
	{
		$count = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM $wpdb->term_relationships, $wpdb->posts 
						 WHERE $wpdb->posts.ID = $wpdb->term_relationships.object_id 
						 AND post_type = 'attachment' 
						 AND term_taxonomy_id = %d",
					$term )
		);
		
		do_action( 'edit_term_taxonomy', $term );
		
		$wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );
		
		do_action( 'edited_term_taxonomy', $term );
	}
	
	// clear cache
	prism_gallery_clear_cache('mediatags_all');
}


/**
 * Adds media tags submenu
 */
function prism_gallery_media_submenu()
{
	global $mediatags;
	
	if( ! (isset($mediatags) && is_a($mediatags, 'MediaTags') && defined('MEDIA_TAGS_TAXONOMY')) )
    	add_submenu_page('upload.php', __('Media tags', 'prism_portfolio'), __('Media tags', 'prism_portfolio'), 'upload_files', 'edit-tags.php?taxonomy=' . PRISM_GALLERY_MEDIA_TAG_NAME);
}
add_action('admin_menu', 'prism_gallery_media_submenu');


/**
 * Gets intermediate image sizes
 */
function prism_gallery_get_intermediate_image_sizes()
{
	$sizes = array();

	if( function_exists('get_intermediate_image_sizes') )
		$sizes = get_intermediate_image_sizes();

	$additional_intermediate_sizes = apply_filters('intermediate_image_sizes', $sizes);
	
	array_unshift($additional_intermediate_sizes, 'thumbnail', 'medium', 'large', 'full');
	
	return array_unique($additional_intermediate_sizes);
}


/**
 * Media library extensions
 */
function prism_gallery_add_library_query_vars( $input )
{
	global $wpdb, $pagenow;
	
	if( is_admin() )
	{
		$options = get_option('prism_portfolio_gallery');
	
		// affect the query only if we're on a certain page
		if( "media-upload.php" == $pagenow && "library" == $_GET["tab"] && is_numeric($_GET['post_id']) )
		{
			if( isset($_GET['exclude']) && "current" == $_GET['exclude'] )
				$input .= " AND `post_parent` != " . (int) $_GET["post_id"] . " ";
	
			if( isset($options["library_filter_duplicates"]) && true == $options["library_filter_duplicates"] )
				$input .= " AND $wpdb->posts.ID NOT IN ( SELECT ID FROM $wpdb->posts AS ps INNER JOIN $wpdb->postmeta AS pm ON pm.post_id = ps.ID WHERE pm.meta_key = '_is_copy_of' ) ";
		}
		elseif( "upload.php" == $pagenow && isset($options["library_filter_duplicates"]) && true == $options["library_filter_duplicates"] )
		{
			$input .= " AND $wpdb->posts.ID NOT IN ( SELECT ID FROM $wpdb->posts AS ps INNER JOIN $wpdb->postmeta AS pm ON pm.post_id = ps.ID WHERE pm.meta_key = '_is_copy_of' ) ";
		}
	}

	return $input;
}
add_filter('posts_where', 'prism_gallery_add_library_query_vars');


/**
 * Adds js to admin area
 */
function prism_gallery_js_admin()
{
	global $pagenow, $current_screen, $wp_version, $post_ID, $prism_portfolio_gallery;

	$s = array('{"', '",', '"}', '\/', '"[', ']"');
	$r = array("\n{\n\"", "\",\n", "\"\n}", '/', '[', ']');

	if(
	      "post.php" == $pagenow 
	   || "post-new.php" == $pagenow
	   || "page.php" == $pagenow 
	   || "page-new.php" == $pagenow 
	   || ("post" == $current_screen->base && isset($current_screen->post_type))
	  )
	{
		// prism_gallery.L10n
		$prism_gallery_localize = array(
			"switch_to_tags" 			 => __("Switch to tags", "prism_portfolio"),
			"switch_to_files" 			 => __("Switch to list of attachments", "prism_portfolio"),
			"pg_info" 					 => __("Insert checked attachments into post as", "prism_portfolio"),
			"no_attachments_upload" 	 => __("No files are currently attached to this post.", "prism_portfolio"),
			"sure_to_delete" 			 => __("Are you sure that you want to delete these attachments? Press [OK] to delete or [Cancel] to abort.", "prism_portfolio"),
			"saving_attachment_data" 	 => __("saving attachment data...", "prism_portfolio"),
			"loading_attachment_data"	 => __("loading attachment data...", "prism_portfolio"),
			"deleting_attachment" 		 => __("deleting attachment...", "prism_portfolio"),
			"deleting_attachments" 		 => __("deleting attachments...", "prism_portfolio"),
			"loading" 					 => __("loading...", "prism_portfolio"),
			"detaching_attachment"		 => __("detaching attachment", "prism_portfolio"),
			"detaching_attachments"		 => __("detaching attachments", "prism_portfolio"),
			"sure_to_detach"			 => __("Are you sure that you want to detach these attachments? Press [OK] to detach or [Cancel] to abort.", "prism_portfolio"),
			"close"						 => __("close", "prism_portfolio"),
			"loading_attachments"		 => __("loading attachments", "prism_portfolio"),
			"post_thumb_set"			 => __("Featured image set successfully", "prism_portfolio"),
			"post_thumb_unset"			 => __("Featured image removed", "prism_portfolio"),
			'copy_all_from_original'	 => __('Copy all attachments from the original post', 'prism_portfolio'),
			'copy_all_from_original_'	 => __('Copy all attachments from the original post?', 'prism_portfolio'),
			'copy_all_from_translation'  => __('Copy all attachments from this translation', 'prism_portfolio'),
			'copy_all_from_translation_' => __('Copy all attachments from this translation?', 'prism_portfolio'),
			"set_as_featured"			 => __("Set as featured image", "prism_portfolio"),
			"unset_as_featured"			 => __("Unset as featured image", "prism_portfolio"),
			'copy_from_is_nan_or_zero'   => __('Supplied ID (%d) is zero or not a number, please correct.', 'prism_portfolio'),
			'regenerating'               => __('regenerating...', 'prism_portfolio')
		);
		
		// prism_gallery.options
		$prism_gallery_options = array( 
			"prism_gallery_url"   =>  Prism_Portfolio::plugin_url(),
			"prism_gallery_nonce" => wp_create_nonce('prism-gallery'),
			"prism_gallery_mode"  => "list",

			"num_attachments"    => 1,
			"tags_from"          => true,
			"clear_cache_nonce"  => wp_create_nonce('prism-gallery-clear_cache'),
			"post_thumb_nonce"   => wp_create_nonce( "set_post_thumbnail-" . $post_ID )
		);
		
		// acf.L10n
		$acf_localize = array(
			'new_custom_field' => __("Add New Custom Field", "prism_portfolio"),
			'add_new_custom_field' => __("Add Custom Field", "prism_portfolio"),
			'error_deleting_attachment_custom_field' => __("Error deleting attachment custom field!", "prism_portfolio"),
			'error_adding_attachment_custom_field' => __("Error adding attachment custom field!", "prism_portfolio"),
			'name' => __("Name:", "prism_portfolio"),
			'value' => __("Value:", "prism_portfolio")
		);
		
		// acf.options
		$acf_options = array( 
			'add_new_attachment_custom_field_nonce' => wp_create_nonce( 'add_new_attachment_custom_field_nonce' ),
			'delete_attachment_custom_field_nonce' => wp_create_nonce( 'delete_attachment_custom_field_nonce' ),
			'custom_fields' => '[]'
		);
		
		$dependencies = array('jquery', 'jquery-ui-core', 'jquery-ui-draggable', 'jquery-ui-sortable', 'jquery-ui-dialog');
		
		wp_enqueue_script('prism-gallery-main',   Prism_Portfolio::plugin_url() . '/admin/js/prism-gallery.js', $dependencies, PRISM_GALLERY_VERSION);
		wp_enqueue_script('prism-gallery-clear_cache',   Prism_Portfolio::plugin_url() . '/admin/js/prism-gallery-clear_cache.js', false, PRISM_GALLERY_VERSION);
		wp_enqueue_script('acf-attachment-custom-fields',  Prism_Portfolio::plugin_url() . '/admin/js/prism-gallery-attachment_custom_fields.js', false, PRISM_GALLERY_VERSION);

		wp_localize_script( 'prism-gallery-main', 'Prism_Portfolio_Settings', array( 'prism_gallery_L10n' => $prism_gallery_localize , 'prism_gallery_options' => $prism_gallery_options, 'acf_L10n' => $acf_localize, 'init_prism_gallery' => 1, 'acf_options' => $acf_options )); 
	}
	elseif( "edit.php" == $pagenow  )
	{
		$prism_gallery_options = array( 
			"prism_gallery_url"   =>  Prism_Portfolio::plugin_url(),
			"prism_gallery_nonce" => wp_create_nonce('prism-gallery')
		);
		
		wp_enqueue_script('prism-gallery-main',   Prism_Portfolio::plugin_url() . '/admin/js/prism-gallery.js', array('jquery'), PRISM_GALLERY_VERSION);
		
		echo '
		<script type="text/javascript">
			var prism_gallery_L10n = {},
				prism_gallery_options = ' . str_replace($s, $r, json_encode($prism_gallery_options)) . ',
				init_prism_gallery = false;
		</script>
		';
	}
	elseif( "media.php" == $pagenow && is_numeric($_GET['attachment_id']) && "edit" == $_GET["action"] )
	{
		$custom_fields = array();
		$custom = get_post_custom($_GET['attachment_id']);

		foreach( (array) $custom as $key => $val )
		{
			if( 1 < count($val) || "_" == substr($key, 0, 1) || is_array($val[0]) )
				continue;
	
			$custom_fields[] = $key;
		}

		$custom_fields = (! empty($custom_fields)) ? "'" . implode("','", $custom_fields) . "'" : "";

		$acf_localize = array(
			'new_custom_field' => __("Add New Custom Field", "prism_portfolio"),
			'add_new_custom_field' => __("Add Custom Field", "prism_portfolio"),
			'error_deleting_attachment_custom_field' => __("Error deleting attachment custom field!", "prism_portfolio"),
			'error_adding_attachment_custom_field' => __("Error adding attachment custom field!", "prism_portfolio"),
			'name' => __("Name:", "prism_portfolio"),
			'value' => __("Value:", "prism_portfolio")
		);
		
		$acf_options = array( 
			'add_new_attachment_custom_field_nonce' => wp_create_nonce( 'add_new_attachment_custom_field_nonce' ),
			'delete_attachment_custom_field_nonce' => wp_create_nonce( 'delete_attachment_custom_field_nonce' ),
			'custom_fields' => '[' . $custom_fields . ']'
		);

		wp_enqueue_script('acf-attachment-custom-fields',  Prism_Portfolio::plugin_url() . '/admin/js/prism-gallery-attachment_custom_fields.js', false, PRISM_GALLERY_VERSION);
		
		echo '
		<script type="text/javascript">
			var acf_L10n = ' . str_replace($s, $r, json_encode($acf_localize)) . ',
				acf_options = ' . str_replace($s, $r, json_encode($acf_options)) . ';
		</script>
		';
	}
	elseif( "media-upload.php" == $pagenow && isset($_GET["tab"]) && "library" == $_GET["tab"] )
	{
		$prism_gallery_localize = array(
			'attach_all_checked_copy' => __("Attach all checked items to current post", "prism_portfolio"),
			'exclude_current' => __("Exclude current post's attachments", "prism_portfolio"),
			'include_current' => __("Include current post's attachments", "prism_portfolio")
		);

		wp_enqueue_script('prism-gallery-attach',  Prism_Portfolio::plugin_url() . '/admin/js/prism-gallery-attach.js', false, PRISM_GALLERY_VERSION);
		
		echo '
		<style type="text/css">
			#library-form .media-item.child-of-' . $_GET["post_id"] . '
			{
				background-color: #FFE;
			}
		</style>
		<script type="text/javascript">
			var prism_gallery_attach_nonce = "' . wp_create_nonce( 'prism-gallery-attach' ) . '",
				prism_gallery_L10n = ' . str_replace($s, $r, json_encode($prism_gallery_localize)) . ';
		</script>
		';
	}
	elseif( "options-media.php" == $pagenow )
	{
		echo '
		<script type="text/javascript">
			var prism_gallery_options =
			{
				clear_cache_nonce : "' . wp_create_nonce('prism-gallery-clear_cache') . '"
			};
		</script>
		';

		wp_enqueue_script('prism-gallery-clear_cache',  Prism_Portfolio::plugin_url() . '/admin/js/prism-gallery-clear_cache.js', false, PRISM_GALLERY_VERSION);
	}
	elseif( 'edit-tags.php' == $pagenow && PRISM_GALLERY_MEDIA_TAG_NAME == $_GET['taxonomy'] && 3 > floatval($wp_version) )
	{
		echo '
		<script type="text/javascript">
			jQuery(document).ready(function()
			{
				jQuery("h2").html("' . __("Media tags", "prism_portfolio") . '");
			});
		</script>
		';
	}
}
add_action('admin_print_scripts', 'prism_gallery_js_admin');


/**
 * Adds css to admin area
 */
function prism_gallery_css_admin()
{
	global $pagenow, $current_screen, $prism_gallery;

	
	
	if(
		   'post.php' 			== $pagenow
		|| 'post-new.php' 		== $pagenow 
		|| 'page.php' 			== $pagenow 
		|| 'page-new.php' 		== $pagenow 
		|| 'media.php' 			== $pagenow 
		|| 'options-media.php'	== $pagenow 
		|| 'media-upload.php'	== $pagenow 
		|| 'upload.php'			== $pagenow 
		|| 'edit.php'			== $pagenow 
		|| 'options-permalink.php' == $pagenow
		|| (isset($current_screen->post_type) && 'post' == $current_screen->base)
	  )
	{
		wp_enqueue_style('prism_gallery_admin', apply_filters('prism_gallery_admin_css_location',  Prism_Portfolio::plugin_url() . '/admin/css/prism-gallery.css'), false, PRISM_GALLERY_VERSION );
		
		if( 'rtl' == get_bloginfo('text_direction') )
			wp_enqueue_style('prism_gallery_admin_rtl', apply_filters('prism_gallery_admin_rtl_css_location',  Prism_Portfolio::plugin_url() . '/admin/css/prism-gallery-rtl.css'), false, PRISM_GALLERY_VERSION );
	}
}
add_action('admin_print_styles', 'prism_gallery_css_admin');


/**
 * Edit post/page meta box content
 */
function prism_gallery_content()
{
	global $post;

	echo 
	'<div id="pg_container">
		<noscript>
			<div class="error" style="margin: 0;">
				<p>' . __('Prism Gallery requires Javascript to function. Please enable it in your browser.', 'prism_portfolio') . '</p>
			</div>
		</noscript>
	</div>
				
	<div id="prism_gallery_image_dialog">
	</div>
	
	<div id="prism_gallery_delete_dialog" title="' . __('Delete attachment dialog', 'prism_portfolio') . '">
		<p><strong>' . __("Warning: one of the attachments you've chosen to delete has copies.", 'prism_portfolio') . '</strong></p>
		<p>' . __('How do you wish to proceed?', 'prism_portfolio') . '</p>
		<p><a href="' . PRISM_GALLERY_URL . '/help/index.html#deleting_originals" target="_blank">' . __('Click here if you have no idea what this dialog means', 'prism_portfolio') . '</a> ' . __('(opens Prism Gallery help in new browser window)', 'prism_portfolio') . '</p>
	</div>
	
	<div id="prism_gallery_copy_all_dialog" title="' . __('Copy all attachments from another post', 'prism_portfolio') . '">
		<div id="prism_gallery_copy_all_wrap">
			<label for="prism_gallery_copy_all_from">' . __('Post ID:', 'prism_portfolio') . '</label>
			<input type="text" id="prism_gallery_copy_all_from" value="" />
		</div>
	</div>';
}


/**
 * Creates meta boxes on post editing screen
 */
function prism_gallery()
{
	$options = get_option('prism_portfolio_gallery');
	
	if( function_exists('get_post_types') )
	{
		$types = get_post_types();
		
		foreach( $types as $type )
		{
			if( ! in_array( $type, array('nav_menu_item', 'revision', 'attachment') ) && 
				isset($options['show_on_post_type_' . $type]) && true == $options['show_on_post_type_' . $type]
			)
				add_meta_box('prism_gallery', __( 'Prism Gallery', 'prism_portfolio' ), 'prism_gallery_content', $type, 'normal');
		}
	}
	else // pre 2.9
	{
		add_meta_box('prism_gallery', __( 'Prism Gallery', 'prism_portfolio' ), 'prism_gallery_content', 'post', 'normal');
		add_meta_box('prism_gallery', __( 'Prism Gallery', 'prism_portfolio' ), 'prism_gallery_content', 'page', 'normal');
	}
}
add_action('admin_menu', 'prism_gallery');


/**
 * Outputs attachment count in the proper column
 */
function prism_gallery_posts_custom_column($column_name, $post_id)
{
	global $wpdb;
	
	$options = get_option('prism_portfolio_gallery');

	if( 'attachment_count' == $column_name && isset($options['e_display_attachment_count']) && true == $options['e_display_attachment_count'] )
	{
		$count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(ID) FROM $wpdb->posts WHERE post_type='attachment' AND post_parent=%d", $post_id) );
		
		echo apply_filters('prism_gallery_post_attachment_count', $count, $post_id);
	}
	elseif( 'post_thumb' == $column_name && isset($options['e_display_post_thumb']) && true == $options['e_display_post_thumb'] )
	{
		if( $thumb_id = get_post_meta( $post_id, '_thumbnail_id', true ) )
		{
			$thumb_src = wp_get_attachment_image_src( $thumb_id, 'thumbnail', false );
			$content   = '<img src="' . $thumb_src[0] .'" alt="Post thumb" />';
			
			echo apply_filters('prism_gallery_post_thumb_content', $content, $post_id, $thumb_id);
		}
		else
		{
			echo apply_filters('prism_gallery_no_post_thumb_content', '<span class="no-post-thumbnail">-</span>', $post_id);
		}
	}
}
add_action('manage_posts_custom_column', 'prism_gallery_posts_custom_column', 100, 2);
add_action('manage_pages_custom_column', 'prism_gallery_posts_custom_column', 100, 2);


/**
 * Adds attachment count column to the post and page edit screens
 */
function prism_gallery_posts_columns( $columns )
{
	$options = get_option('prism_portfolio_gallery');
	
	if( isset($options['e_display_attachment_count']) && true == $options['e_display_attachment_count'] )
		$columns['attachment_count'] = __('No. of attachments', 'prism_portfolio');
		
	if( isset($options['e_display_post_thumb']) && true == $options['e_display_post_thumb'] )
		$columns = array('post_thumb' => __('Post thumb', 'prism_portfolio')) + $columns; // $columns['post_thumb'] = 'Post thumb';
	
	return $columns;
}
add_filter('manage_posts_columns', 'prism_gallery_posts_columns');
add_filter('manage_pages_columns', 'prism_gallery_posts_columns');


/**
 * Outputs attachment media tags in the proper column
 */
function prism_gallery_media_custom_column($column_name, $post_id)
{
	global $prism_portfolio_gallery, $wpdb;
	
	$options = get_option('prism_portfolio_gallery');
	
	if( 'media_tags' == $column_name && isset($options['e_display_media_tags']) && true == $options['e_display_media_tags'])
	{
		if( isset($options['cache']) && true == $options['cache'] )
		{
			$transient = 'fileglry_mt_' . md5($post_id);
			$cache     = get_transient($transient);
			
			if( $cache )
			{
				echo $cache;
				
				return;
			}
		}
		
		$l = '?taxonomy=' . PRISM_GALLERY_MEDIA_TAG_NAME . '&amp;term=';
		$out = __('No Media Tags', 'prism_portfolio');
		
		$q = "SELECT `name`, `slug` 
			  FROM $wpdb->terms
			  LEFT JOIN $wpdb->term_taxonomy ON ( $wpdb->terms.term_id = $wpdb->term_taxonomy.term_id ) 
			  LEFT JOIN $wpdb->term_relationships ON ( $wpdb->term_taxonomy.term_taxonomy_id = $wpdb->term_relationships.term_taxonomy_id ) 
			  WHERE `taxonomy` = '" . PRISM_GALLERY_MEDIA_TAG_NAME . "'
			  AND `object_id` = %d
			  ORDER BY `name` ASC";
		
		if( $r = $wpdb->get_results($wpdb->prepare($q, $post_id)) )
		{
			$out = array();
			
			foreach( $r as $tag )
			{
				$out[] = '<a href="' . $l . $tag->slug . '">' . $tag->name . '</a>';
			}
			
			$out = implode(', ', $out);
		}
		
		if( isset($options['cache']) && true == $options['cache'] )
			set_transient($transient, $out, $options['cache_time']);
		
		echo $out;
	}
}
add_action('manage_media_custom_column', 'prism_gallery_media_custom_column', 100, 2);


/**
 * Adds media tags column to attachments
 */
function prism_gallery_media_columns( $columns )
{
	global $mediatags;

	if( ! (is_a($mediatags, 'MediaTags') && defined('MEDIA_TAGS_TAXONOMY')) )	
		$columns['media_tags'] = __('Media tags', 'prism_portfolio');
	
	return $columns;
}
add_filter('manage_media_columns', 'prism_gallery_media_columns');


/**
 * Includes
 */
require_once('inc/media-tags.php');
require_once('inc/media-settings.php');
// require_once('inc/media-upload.php');
require_once('inc/attachments.php');
require_once('inc/miscellaneous.php');
require_once('inc/mime-types.php');
require_once('inc/lightboxes-support.php');
require_once('inc/templating.php');
require_once('inc/main.php');
require_once('inc/functions.php');
require_once('inc/cache.php');
require_once('inc/regenerate-images.php');
require_once('inc/attachments-custom-fields.php');

if( 3.1 <= floatval(get_bloginfo('version')) )
	require_once('inc/media-tags-list-table.class.php');

?>