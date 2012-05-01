<?php
/**
 * Prism Featured Taxonomy Metabox
 * 
 * The Prism Post Type Metaboxes class creates, saves and validates the metabox for the Portfolio Featured taxonomy
 *
 * @class 		Prism_Metaboxes
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

class Prism_Featured extends Prism_Portfolio {

	public function __construct() {  
		
		//Add Custom Metabox for Featured Taxonomy
		add_action('add_meta_boxes', array(&$this,'featured_metabox'));

		//save taxonomy data from metabox
		add_action('save_post', array(__CLASS__,'save_taxonomy_data'),20,2);

		/*
		 * All scipts and functions for the plupload gallery 
		 */
		// load scripts and styles
/*		add_action( 'admin_print_styles-post.php', array(__CLASS__, 'meta_styles'), 1000 );
		add_action( 'admin_print_styles-post-new.php', array(__CLASS__, 'meta_styles'), 1000 );
		add_action( 'admin_print_scripts-post.php', array(__CLASS__, 'meta_scripts'), 1000 );
		add_action( 'admin_print_scripts-post-new.php', array(__CLASS__, 'meta_scripts'), 1000 );

		// add the metabox and save its data
    	add_action( 'add_meta_boxes', array(__CLASS__,'gallery_metabox' ));
    	add_action( 'save_post', array(__CLASS__,'save_gallery_meta' ),11,2);

    	//plupload ajax action 
    	add_action( 'wp_ajax_plupload_action' , array(__CLASS__,'plupload_action' ));
*/

	}

	/* 
	 * Add Custom Metabox for Featured Taxonomy
	 *
	 */
	 
	function featured_metabox(){
		add_meta_box('prism_portfolio_featured_tax', __('Featured or Archived Item','prism_portfolio'), array($this,'featured_tax_display'), 'prism_portfolio', 'side', 'low');
	}
			

	// This function gets called in edit-form-advanced.php
	//the guts of the custom metabox
	function featured_tax_display($post) { ?>
	 
		<input type="hidden" name="prism_portfolio_featured_nonce" id="prism_portfolio_featured_nonce" value="<?php echo wp_create_nonce( 'prism_featured_nonce_' . $post->ID ); ?>" />
	 
		<?php $terms = wp_get_post_terms( $post->ID, self::$featured  );
		$status = null;
		if(!is_wp_error($terms) && !empty($terms)) $status = $terms[0]->slug; ?> 

		<p><?php _e('Is this a featured portfolio item?', 'prism_portfolio');?></p>
	
		<input type="radio" name="prism_portfolio_featured_tax" <?php if ($status=='featured'){echo " CHECKED ";} ?> value="featured"> <?php _e('Featured', 'prism_portfolio');?> <br/>
		<input type="radio" name="prism_portfolio_featured_tax"  <?php if (!$status){echo " CHECKED ";} ?>value=""> <?php _e('Normal', 'prism_portfolio');?> <br/>			
		<input type="radio" name="prism_portfolio_featured_tax" <?php if($status == 'archived'){echo " CHECKED ";} ?> value="archived"> <?php _e('Archived', 'prism_portfolio');?> <br/>
			
	<?php  
	}

	/*
	 * Save our taxonomy data from quick edit 
	*/
	function save_taxonomy_data($post_id,$post) {  
	
		// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want to do anything
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;	
		
		// verify post type is portfolio and not a revision
		if( $post->post_type != 'prism_portfolio' || $post->post_type == 'revision' ) return $post_id;
		
		// make sure data came from our meta box, verify nonce
		$nonce = isset($_POST['prism_portfolio_featured_nonce']) ? $_POST['prism_portfolio_featured_nonce'] : NULL ;
		
		if (!wp_verify_nonce( $nonce, 'prism_featured_nonce_' . $post_id )) return $post_id;
		
		// Check permissions
		if ( 'page' == $post->post_type ) {
			if ( !current_user_can( 'edit_page', $post_id ) ) return $post_id;
		} else {
			if ( !current_user_can( 'edit_post', $post_id ) ) return $post_id;
		}	
		
		//once verified, sanitize and update featured tax 
		if (isset($_POST['prism_portfolio_featured_tax']) ) { 
			$status = esc_attr($_POST['prism_portfolio_featured_tax']);
			$allowed = array ( 'featured','archived');
			if ($status and in_array($status,$allowed)) {
				wp_set_object_terms( $post_id, $status, self::$featured );
			} else { 
				wp_set_object_terms( $post_id, NULL , self::$featured  );
			}
		}
		
	}


	static function meta_styles() {
		    wp_enqueue_style('prism-plupload', self::$plugin_url . '/admin/css/prism-plupload.css');
	}

	static function meta_scripts() {

	    wp_enqueue_script('plupload-all');
	 
	    wp_enqueue_script('prism-plupload', self::$plugin_url . '/admin/js/prism-plupload.js', array('jquery', 'plupload-all'), 0.1, true);
	 
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
	    wp_localize_script( 'prism-plupload', 'base_plupload_config', $plupload_init );	

}


// Add the Documents Metabox
	static function gallery_metabox() {
		//add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );
		add_meta_box('gallery_metabox', __('Gallery','prism_portfolio'), array(__CLASS__,'gallery_metabox_callback'), self::$post_type, 'normal', 'high');
	}


	// The Documents Metabox
	static function gallery_metabox_callback() {
		include('inc/images-metabox.php');
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