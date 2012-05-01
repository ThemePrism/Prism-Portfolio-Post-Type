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

} //end class