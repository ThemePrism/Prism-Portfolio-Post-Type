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
 */
class Prism_Portfolio_Gallery extends Prism_Portfolio {


	/***/
	public static function init() { 
		// add the metabox and save its data
    	add_action( 'add_meta_boxes', array(__CLASS__,'gallery_metabox' ));
		//add_action( 'save_post', array(__CLASS__,'save_gallery_meta' ),11,2);
		
		/*
		 * All scipts and functions for the plupload gallery 
		 */
		// load scripts and styles	
		add_action('admin_print_scripts', array(__CLASS__,'admin_scripts'), 1000);
		add_action('admin_print_styles', array(__CLASS__,'admin_styles'), 1000);

		/*
		* Ajax Actions
		*/
		Prism_Portfolio_Ajax::init();

	}
	
	/**
	 * Creates meta boxes on post editing screen
	 */
	static function gallery_metabox() {
		add_meta_box('prism_gallery', __( 'Prism Gallery', "prism_portfolio" ),  array(__CLASS__,'gallery_metabox_callback'), Prism_Portfolio::$post_type, 'normal', 'high');
	}	
	
	/**
	 * Edit post/page meta box content
	 */
	function gallery_metabox_callback() {
		global $post;

		echo 
		'<div id="pg_container">
			<noscript>
				<div class="error" style="margin: 0;">
					<p>' . __('Prism Gallery requires Javascript to function. Please enable it in your browser.', "prism_portfolio") . '</p>
				</div>
			</noscript>
		</div>
					
		<div id="prism_gallery_image_dialog">
		</div>
		
		<div id="prism_gallery_delete_dialog" title="' . __('Delete attachment dialog', "prism_portfolio") . '">
			<p><strong>' . __("Warning: one of the attachments you've chosen to delete has copies.", "prism_portfolio") . '</strong></p>
			<p>' . __('How do you wish to proceed?', "prism_portfolio") . '</p>
		</div>
		
		<div id="prism_gallery_copy_all_dialog" title="' . __('Copy all attachments from another post', "prism_portfolio") . '">
			<div id="prism_gallery_copy_all_wrap">
				<label for="prism_gallery_copy_all_from">' . __('Post ID:', "prism_portfolio") . '</label>
				<input type="text" id="prism_gallery_copy_all_from" value="" />
			</div>
		</div>';
	}	
	
	
	
	
	static function add_styles() {
		wp_enqueue_style('prism-plupload', Prism_Portfolio::$plugin_url . '/admin/css/prism-plupload.css');
	}

	static function meta_scripts() {



	}


	/**
	 * Adds js to admin area
	 */
	function admin_scripts() {
		global $pagenow, $current_screen, $wp_version, $post_ID, $prism_portfolio_gallery;

		$s = array('{"', '",', '"}', '\/', '"[', ']"');
		$r = array("\n{\n\"", "\",\n", "\"\n}", '/', '[', ']');

		if( $current_screen->base == 'post' && (isset($current_screen->post_type) && $current_screen->post_type == parent::$post_type) )
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
				'copy_all_from_original'	 => __('Copy all attachments from the original post', "prism_portfolio"),
				'copy_all_from_original_'	 => __('Copy all attachments from the original post?', "prism_portfolio"),
				'copy_all_from_translation'  => __('Copy all attachments from this translation', "prism_portfolio"),
				'copy_all_from_translation_' => __('Copy all attachments from this translation?', "prism_portfolio"),
				"set_as_featured"			 => __("Set as featured image", "prism_portfolio"),
				"unset_as_featured"			 => __("Unset as featured image", "prism_portfolio"),
				'copy_from_is_nan_or_zero'   => __('Supplied ID (%d) is zero or not a number, please correct.', "prism_portfolio"),
				'regenerating'               => __('regenerating...', "prism_portfolio")
			);
			
			// prism_gallery.options
			$prism_gallery_options = array( 
				"prism_gallery_img"   =>  Prism_Portfolio::plugin_url() . '/admin/images',
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
			
			//plupload settings
			$plupload_init = array(
					'runtimes' => 'html5,silverlight,flash,html4',
					'browse_button' => '_browse-button', // will be adjusted per uploader
					'container' => '_plupload-ui', // will be adjusted per uploader
					'drop_element' => '_drag-drop-area', // will be adjusted per uploader
					'file_data_name' => '_async-upload', // will be adjusted per uploader
					'multiple_queues' => true,
					'max_file_size' => wp_max_upload_size() . 'b',
					'url' => admin_url('admin-ajax.php'),
					'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
					'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
					'filters' => array(array('title' => __('Allowed Files'), 'extensions' => '*')),
					'multipart' => true,
					'urlstream_upload' => true,
					'multi_selection' => false, // will be added per uploader
					 // additional post data to send to our ajax hook
					'multipart_params' => array(
						'_ajax_nonce' => wp_create_nonce('prism-plupload'), 
						'action' => 'plupload_action', // the ajax action name
						'imgid' => 0, // will be added per uploader
						'post_id' => get_the_ID()
					)
			);

			$dependencies = array('jquery', 'jquery-ui-core', 'jquery-ui-draggable', 'jquery-ui-sortable', 'jquery-ui-dialog');
			
			wp_enqueue_script('prism-gallery-main', parent::plugin_url() . '/admin/js/prism-gallery.js', $dependencies, parent::$version);
			wp_enqueue_script('prism-gallery-clear_cache', parent::plugin_url() . '/admin/js/prism-gallery-clear_cache.js', false, parent::$version);
			wp_enqueue_script('acf-attachment-custom-fields',  parent::plugin_url() . '/admin/js/prism-gallery-attachment_custom_fields.js', false, parent::$version);

			wp_localize_script( 'prism-gallery-main', 'Prism_Portfolio_Settings', array( 'prism_gallery_L10n' => $prism_gallery_localize , 'prism_gallery_options' => $prism_gallery_options, 'acf_L10n' => $acf_localize, 'init_prism_gallery' => 'true', 'acf_options' => $acf_options )); 
				
			wp_enqueue_script('plupload-all');
			wp_enqueue_script('prism-plupload', Prism_Portfolio::$plugin_url . '/admin/js/prism-plupload.js', array('jquery', 'plupload-all'), 0.1, true);
			
			wp_localize_script( 'prism-plupload', 'base_plupload_config', $plupload_init );	
			
		}
		
	}


	/**
	 * Adds css to admin area
	 */
	function admin_styles() {
		global $pagenow, $current_screen, $prism_gallery;
		
		if( ($current_screen->base == 'post' && isset($current_screen->post_type) && $current_screen->post_type == parent::$post_type))
		{
			wp_enqueue_style('prism_gallery_admin', apply_filters('prism_gallery_admin_css_location',  parent::plugin_url() . '/admin/css/prism-gallery.css'), false, parent::$version );
			
			if( 'rtl' == get_bloginfo('text_direction') )
				wp_enqueue_style('prism_gallery_admin_rtl', apply_filters('prism_gallery_admin_rtl_css_location',  parent::plugin_url() . '/admin/css/prism-gallery-rtl.css'), false, parent::$version );
		}
	}



	static function save_gallery_meta($post_id, $post) { 
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        	return $post_id;
	
	    if ( !wp_verify_nonce( $_POST['_prism_gallery_nonce'], '_prism_gallery' )) 
	        return $post_id;

	         // Check permissions
	    if ( 'page' == $_POST['post_type'] ) {
	      if ( !current_user_can( 'edit_page', $post_id ) )
	        return $post_id;
	    } else {
	      if ( !current_user_can( 'edit_post', $post_id ) )
	        return $post_id;
	    }
	    
	    // sanitize the meta data 

	    $valid = array();

	    if(isset($_POST['_prism_gallery'])){
	    	$img_array = explode ( ',' , $_POST['_prism_gallery'] );
	    	//escape the url of each image
	    	foreach ($img_array as $img){
	    		$valid[] = esc_url($img);
	    	}
	    	//put it back into a comma-separated string
	    	$valid = implode(',',$valid);

	    	update_post_meta($post_id, '_prism_gallery', $valid);

	    } 

	}


	static function plupload_action() {

	    // check ajax nonce
	    check_ajax_referer('prism-plupload');
		
		// the post id
		$post_id = $_POST['post_id']; 

	   	// the name of this particular metabox - is sent by custom plupload script
		$imgid = $_POST["imgid"];
	 
		// you can use WP's wp_handle_upload() function:
	  	$file = wp_handle_upload($_FILES[$imgid . '_async-upload'], array('test_form' => true, 'action' => 'plupload_action'));

	  	//if there was an error quit early
	 	if ( isset( $file['error'] )) { 
	 		echo json_encode($file); 
	 		exit;
	 	}

		//if it is an image generate the intermediate sizes
	 	$file_type = wp_check_filetype($_FILES[$imgid . '_async-upload']['name'], array(
		      'jpg|jpeg' => 'image/jpeg',
		      'gif' => 'image/gif',
		      'png' => 'image/png',
		    ));  
		    
		if ($file_type['type']) {
			$name_parts = pathinfo( $file['file'] );
			$name = $name_parts['filename'];
			$type = $file['type'];	
					
			//Adds file as attachment to WordPress
			$attachment = array(
							'post_title' => preg_replace('/\.[^.]+$/', '',$name),			
							'post_title' => $name,
							'post_type' => 'attachment',
							'post_mime_type' => $type,
							'guid' => $file['url']);
			$attachment_id = wp_insert_attachment( $attachment ,$file['file'], $post_id);
			
			//	print_r($attach_data); exit;
			// send the uploaded file url in response
			$file['attachment_id'] = $attachment_id;
		
			@set_time_limit( 900 ); // 5 minutes per image should be PLENTY
			
			$attach_data = wp_generate_attachment_metadata( $attachment_id, $file['file'] );

			wp_update_attachment_metadata( $file['attachment_id'],  $attach_data );
			
			echo json_encode($file); 
			
		}
		
	    exit;
	}
	
	
} //end class





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
 * Includes
 */

require_once('inc/attachments.php');
require_once('inc/miscellaneous.php');
require_once('inc/mime-types.php');


require_once('inc/main.php');
require_once('inc/functions.php');
require_once('inc/cache.php');



?>