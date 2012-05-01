<?php
/**
 * Prism Post Type Edit Screen
 * 
 * The Prism Post Type Edit Screen creates the columns on the edit screen 
 *
 * @class 		Prism_Edit_Screen
 * @package		Prism_Portfolio
 * @category	Class
 * @author		KathyisAwesome
 *
 * Table of Contents
 *
 *
 *
 *
 */

class Prism_Edit_Screen extends Prism_Portfolio {

	public function __construct() {   

		//Add Columns to Portfolio Edit Screen
		add_filter('manage_edit-prism_portfolio_columns', array(__CLASS__,'edit_columns'));
		add_action('manage_posts_custom_column', array(__CLASS__,'display_columns'), 10, 2);
		
		//featured products from admin via ajax
		add_action('wp_ajax_prism-feature-product', array(__CLASS__,'feature_product'));

		//Add featured taxonomy radio buttons to quick edit screen
		add_action('quick_edit_custom_box', array(__CLASS__,'add_quick_edit'), 20, 2);
		add_action('admin_footer-edit.php', array(__CLASS__,'quick_edit_javascript'));
		//NB: if post type is hierarchical this must be set to page_row_actions
		add_filter('post_row_actions', array(__CLASS__,'expand_quick_edit_link'), 10, 2);

		//save taxonomy data from quick edit
		add_action('edit_post', array(__CLASS__,'save_taxonomy_data'),20,2);
		
		//Make portfolios sortable by featured taxonomy
		add_filter('manage_edit-prism_portfolio_sortable_columns', array(__CLASS__,'sortable_columns'));
		add_filter('posts_clauses', array(__CLASS__,'featured_clauses'), 10, 2);
		
		//Add Taxonomy Filter to Custom Post Type 
		add_action('restrict_manage_posts', array(__CLASS__, 'restrict_manage_posts'));

		//Parse drop down IDs to terms for query	
		add_filter('parse_query', array(__CLASS__,'convert_restrict'));
		
		//Load thickbox scripts on portfolio edit screen
		add_action('admin_print_scripts-edit.php', array(__CLASS__,'conditional_thickbox'), 10, 1);
		
		//Load custom script in media uploader popups (TODO: only load on popups from portfolio screen, if possible)
		add_action('admin_print_scripts-media-upload-popup', array(__CLASS__,'custom_set_thumbnail'), 10, 1);
		
		//add callback for custom version of set thumbnail
		add_action('wp_ajax_prism_set_thumbnail', array(__CLASS__,'set_thumbnail_callback'));	

		// TODO: Add contextual help to portfolio edit screen		
		//add_action( 'admin_head-edit.php', array(__CLASS__,'add_help_text') );

	} 


	/**
	 * Add Columns to Portfolio Edit Screen
	 * http://wptheming.com/2010/07/column-edit-pages/
	 */
	 
	function edit_columns(){
		$columns['cb'] = '<input type="checkbox" />';
		$columns['title'] = __('Title', 'prism_portfolio');
		$columns['thumbnail'] = __('Thumbnail', 'prism_portfolio');

		if(self::$category) $columns[self::$category] = __('Category', 'prism_portfolio');
		if(self::$tag) $columns[self::$tag] = __('Tags', 'prism_portfolio');
		if(self::$featured) $columns[self::$featured] = __('Featured', 'prism_portfolio');

		$columns['attachment_count'] = __('#Attachments', "prism_portfolio");
		
		$columns['comments'] = '<div class="vers"><img alt="Comments" src="' . esc_url( admin_url( 'images/comment-grey-bubble.png' ) ) . '" /></div>';
		$columns['date'] = __('Date', 'prism_portfolio');
		
		return $columns;
	}


	function display_columns($columns, $post_id){

		global $post, $wpdb;

		switch ( $columns ) {
			// Code adapted from: http://wpengineer.com/display-post-thumbnail-post-page-overview
			
			case "thumbnail":
				$width = (int) 35; 
				$height = (int) 35;
				$url = admin_url( 'media-upload.php?post_id='.$post_id.'&amp;type=image&amp;TB_iframe=1&amp;width=640&amp;height=315');

				// Display the featured image in the column view if possible
				
					if ( has_post_thumbnail() ) {
							$text = '<div class="postimagediv"><a href="#" class="postfeaturedimage">'.get_the_post_thumbnail($post_id, array($width,$height)).'</a></div>';
						} else {
							$text = '<div class="postimagediv"><a href="#" class="postfeaturedimage hide-if-no-js">'.__('Add Image','prism_portfolio').'</a></div>';
						}  
						
						echo $text;

				break;	

				case self::$category:
			
					$taxonomies = get_the_terms( $post_id, self::$category ) ; 
					if ( !empty( $taxonomies ) ) {
						$out = array();
						foreach ( $taxonomies as $t ) {  
							$out[] = sprintf( '<a href="%s">%s</a>',
								esc_url( add_query_arg( array( 'post_type' => $post->post_type, self::$category => $t->slug ), 'edit.php' ) ),
								esc_html( sanitize_term_field( 'name', $t->name, $t->term_id, self::$category, 'display' ) )
							);
						}
						echo join( ', ', $out );
						
					} else {
						_e( 'Uncategorized' , 'prism_portfolio');
					}	
					
				break;
			
				
				// Display the portfolio tags in the column view
				case self::$tag:
				
				$taxonomies = get_the_terms( $post_id, self::$tag) ; 
					if ( !empty( $taxonomies ) ) {
						$out = array();
						foreach ( $taxonomies as $t ) {  
							$out[] = sprintf( '<a href="%s">%s</a>',
								esc_url( add_query_arg( array( 'post_type' => $post->post_type, self::$tag => $t->slug ), 'edit.php' ) ),
								esc_html( sanitize_term_field( 'name', $t->name, $t->term_id, self::$tag, 'display' ) )
							);
						}
						echo join( ', ', $out );
						
					} else {
						_e( 'No Tags' , 'prism_portfolio');
					}	
				break;		
				
				// Display featured status column view
				case self::$featured:
				
				$terms = get_the_terms( $post_id, self::$featured ) ;   
				$status = null;
				
				if( !is_wp_error($terms) && !empty($terms)) { $term = array_shift($terms); $status=$term->slug; }
				
					//should only be 1 value so pop off the first $term with 0 index
					if ( $status == "archived") {
						$archived_image = 'on';
						$archived_title = __( 'Undo Archived' , 'prism_portfolio');
						$archived_action = 'unset';
						$featured_image = 'off';
						$featured_title = __( 'Mark as Featured' , 'prism_portfolio');	
						$featured_action = 'set';						
					} elseif ( $status == "featured") {
						$archived_image = 'off';
						$archived_title = __( 'Mark as Archived' , 'prism_portfolio');
						$archived_action = 'set';
						$featured_image = 'on';
						$featured_title = __( 'Undo Featured' , 'prism_portfolio');
						$featured_action = 'unset';
					} else {			
						$archived_image = 'off';
						$archived_title = __( 'Mark as Archived' , 'prism_portfolio');
						$archived_action = 'set';
						$featured_image = 'off';
						$featured_title = __( 'Mark as Featured' , 'prism_portfolio');
						$featured_action = 'set';	
					}	
					
					printf( '<a href="%s" title="%s"><img src="%s" alt="%s"/></a>',
								wp_nonce_url( admin_url('admin-ajax.php?action=prism-feature-product&archived='.$archived_action.'&product_id=' . $post_id), 'prism-feature-product' ),
								$archived_title,
								self::plugin_url() . '/admin/images/x-mark-'.$archived_image.'.png',
								"archived"
							);
					echo "&nbsp;";
					printf( '<a href="%s" title="%s"><img src="%s" alt="%s"/></a>',
								wp_nonce_url( admin_url('admin-ajax.php?action=prism-feature-product&featured='.$featured_action.'&product_id=' . $post_id), 'prism-feature-product' ),
								$featured_title,
								self::plugin_url() . '/admin/images/star-'.$featured_image.'.png',
								"featured"
							);
					
				break;
				case "attachment_count":
					$count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(ID) FROM $wpdb->posts WHERE post_type='attachment' AND post_parent=%d", $post_id) );
			
					echo $count;
				break;
		}
	}

	/*
	 * Feature a product from admin via ajax
	*/

function feature_product() {

	if( !is_admin() ) die;
	
	if( !current_user_can('edit_posts') ) wp_die( __('You do not have sufficient permissions to access this page.', 'woocommerce') );
	
	if( !check_admin_referer('prism-feature-product')) wp_die( __('You have taken too long. Please go back and retry.', 'woocommerce') );
	
	$post_id = isset($_GET['product_id']) && (int)$_GET['product_id'] ? (int)$_GET['product_id'] : '';
	
	if(!$post_id) die;
	
	$post = get_post($post_id);
	if(!$post) die;
	
	if($post->post_type !== self::$post_type) die;
	
	//once verified, update featured tax
	if (isset($_GET['archived']) && $_GET['archived']=='set' ) {
		wp_set_object_terms( $post_id, 'archived', self::$featured );
	} elseif ( isset($_GET['featured']) && $_GET['featured']=='set') {
		wp_set_object_terms( $post_id, 'featured', self::$featured );
	} else {
		wp_set_object_terms( $post_id, NULL , self::$featured  );
	}
	
	$sendback = remove_query_arg( array('trashed', 'untrashed', 'deleted', 'ids'), wp_get_referer() );
	wp_safe_redirect( $sendback );

}

	/*
	 * Add to Quick Edit 
	 * http://shibashake.com/wordpress-theme/expand-the-wordpress-quick-edit-menu
	 */
	 
	// Add a quick edit input
	function add_quick_edit($column_name, $post_type) {
		if ( $post_type != self::$post_type || $column_name != self::$featured) return;
		
		?>
		<fieldset class="inline-edit-col-left">
		<div class="inline-edit-col">
			<span class="title"><?php _e('Featured Status', 'prism_portfolio');?></span><br/>
			<input type="hidden" name="<?php echo self::$featured;?>_nonce" id="<?php echo self::$featured;?>_nonce" value="" />                      							

			<input type="radio" name="<?php echo self::$featured;?>_tax" value="featured"/> <?php _e('Featured ', 'prism_portfolio');?>
			<input type="radio" name="<?php echo self::$featured;?>_tax" value="normal"/> <?php _e('Normal ', 'prism_portfolio');?> 
			<input type="radio" name="<?php echo self::$featured;?>_tax" value="archived"/> <?php _e('Archived ', 'prism_portfolio');?>  
		</div>
		</fieldset>
		
		
		<?php
	}

	// custom javascript for quick edit box
	function quick_edit_javascript() {
		$screen = get_current_screen(); 
		
		if ( $screen->id != 'edit-'.self::$post_type || $screen->post_type != self::$post_type) return $actions; 

		?>
		<script type="text/javascript">  
		
		//@TODO - somehow load my version of the thumbnail script from here
		jQuery(document).ready(function($) {   
			$('.postfeaturedimage').on('click', function() { 
					//get post id from row checkbox value
					post_id = $(this).parents('td').prevAll('th.check-column').find('input[type=checkbox]').val(); 
					//old way involving string match todoL remove
					//if(/post-(\d+)/.exec(string)[1]) post_id = parseInt(/post-(\d+)/.exec(string)[1], 10);
					
					tbframe_interval = setInterval(function() {

						//hide Use this button 
						$('#TB_iframeContent').contents().find('.savesend input[type="submit"]').hide();
			
						//switch WPSetAsThumbnail to PrismSetAsThumbnail
						$('#TB_iframeContent').contents().find('.wp-post-thumbnail').addClass('button').css('margin-left','0').attr('onclick',function(i, val) {
							return val.replace('WPSetAsThumbnail', 'PrismSetAsThumbnail');
						});

						//remove url, alignment and size fields- auto set to null, none and full respectively
						$('#TB_iframeContent').contents().find('.url').hide().find('input').val('');
						$('#TB_iframeContent').contents().find('.align').hide().find('input:radio').filter('[value="none"]').attr('checked', true);
						$('#TB_iframeContent').contents().find('.image-size').hide().find('input:radio').filter('[value="full"]').attr('checked', true);
					}, 2000);
					
					if(post_id)	tb_show('', 'media-upload.php?post_id='+post_id+'&type=image&tab=library&TB_iframe=true'); //tab sets the opened TB window to show library by default
					return false;
				});
		 });
				 
		function set_inline_featured_status(featuredValue, nonce) {
			// revert Quick Edit menu so that it refreshes properly
			inlineEditPost.revert();

			var featuredRadioInput = document.getElementsByName('<?php echo self::$featured;?>_tax');  
			var nonceInput = document.getElementById('<?php echo self::$featured;?>_nonce'); 
			nonceInput.value = nonce;  
			// check option manually
			for (i = 0; i < featuredRadioInput.length; i++) {
				if (featuredRadioInput[i].value == featuredValue) { 
					featuredRadioInput[i].checked = true; 
				} else { featuredRadioInput[i].checked = false; }
			}
		}
		
		</script>

		<?php
	}


	// Adjust the quick edit link to trigger our custom inline edits
	function expand_quick_edit_link($actions, $post) {
		$screen = get_current_screen(); 

		if ( $screen->id != 'edit-'.self::$post_type || $screen->post_type != self::$post_type) return $actions; 

		$nonce = wp_create_nonce( self::$featured );

		$featured = wp_get_object_terms($post->ID, self::$featured); 
		
		//if for some reason there is no term in the tax, show as normal
		if ( !is_wp_error( $featured ) && isset($featured[0]) ){ 
			$status = $featured[0]->slug;
		} else {
			$status = 'normal';
		}
		
		$actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="';
		$actions['inline hide-if-no-js'] .= esc_attr( __( 'Edit this item inline' ) ) . '" ';
		$actions['inline hide-if-no-js'] .= " onclick=\"set_inline_featured_status('{$status}', '{$nonce}')\">";  
		$actions['inline hide-if-no-js'] .= __( 'Quick&nbsp;Edit' );
		$actions['inline hide-if-no-js'] .= '</a>';
		return $actions;	
	}

	/*
	 * Save our taxonomy data from quick edit 
	*/
	function save_taxonomy_data($post_id,$post) {  
	
		// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want to do anything
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;	
		
		// verify post type is portfolio and not a revision
		if( $post->post_type != self::$post_type || $post->post_type == 'revision' ) return $post_id;
		
		// make sure data came from our meta box, verify nonce
		$nonce = isset($_POST[self::$featured . '_nonce']) ? $_POST[self::$featured . '_nonce'] : NULL ;
		
		if (!wp_verify_nonce( $nonce, self::$featured )) return $post_id;
		
		// Check permissions
		if ( 'page' == $post->post_type ) {
			if ( !current_user_can( 'edit_page', $post_id ) ) return $post_id;
		} else {
			if ( !current_user_can( 'edit_post', $post_id ) ) return $post_id;
		}	
		
		//once verified, update featured tax
		if (isset($_POST[self::$featured . '_tax']) ) { 
			$status = esc_attr($_POST[self::$featured . '_tax']);
			if ($status) {
				wp_set_object_terms( $post_id, $status, self::$featured );
			} else { 
				wp_set_object_terms( $post_id, NULL , self::$featured );
			}
		}
		
	}
	
	/*
	 * Make Columns Sortable
	 * http://devpress.com/blog/custom-columns-for-custom-post-types/
	*/
	function sortable_columns( $columns ) {
		$columns[self::$featured] = 'portfolio_featured';
		//to do: $columns[self::$category] = 'portfolio_category';
		return $columns;
	}


	/*
	 * Make Columns Sortable by Taxonomy
	 * http://scribu.net/wordpress/sortable-taxonomy-columns.html
	 */
	 function featured_clauses( $clauses, $wp_query ) {
		global $wpdb;
		//sort by featured status
		if ( isset( $wp_query->query['orderby'] ) && self::$featured == $wp_query->query['orderby'] ) {
	
			$clauses['join'] .= <<<SQL
LEFT OUTER JOIN {$wpdb->term_relationships} ON {$wpdb->posts}.ID={$wpdb->term_relationships}.object_id
LEFT OUTER JOIN {$wpdb->term_taxonomy} USING (term_taxonomy_id)
LEFT OUTER JOIN {$wpdb->terms} USING (term_id)
SQL;
			
			$clauses['where'] .= " AND (taxonomy = self::$featured OR taxonomy IS NULL)";
			$clauses['groupby'] = "object_id";
			$clauses['orderby']  = "GROUP_CONCAT({$wpdb->terms}.name ORDER BY name ASC) ";
			$clauses['orderby'] .= ( 'ASC' == strtoupper( $wp_query->get('order') ) ) ? 'ASC' : 'DESC';
		}
		//sort by portfolio category
		if ( isset( $wp_query->query['orderby'] ) && self::$category == $wp_query->query['orderby'] ) {
	 
			$clauses['join'] .= <<<SQL
LEFT OUTER JOIN {$wpdb->term_relationships} ON {$wpdb->posts}.ID={$wpdb->term_relationships}.object_id
LEFT OUTER JOIN {$wpdb->term_taxonomy} USING (term_taxonomy_id)
LEFT OUTER JOIN {$wpdb->terms} USING (term_id)
SQL;
			$clauses['where'] .= " AND (taxonomy = self::$category OR taxonomy IS NULL)";
			$clauses['groupby'] = "object_id";
			$clauses['orderby']  = "GROUP_CONCAT({$wpdb->terms}.name ORDER BY name ASC) ";
			$clauses['orderby'] .= ( 'ASC' == strtoupper( $wp_query->get('order') ) ) ? 'ASC' : 'DESC';
		}
		return $clauses;
	}


	/*
	 * Add Taxonomy Filter to Custom Post Type 
	 * http://wordpress.stackexchange.com/q/578/6477#12856
	 */
	function restrict_manage_posts() {
		global $typenow;
		if ( $typenow == 'prism_portfolio')  {
		$filters = array(self::$category,self::$tag,self::$featured);
			foreach ($filters as $tax_slug) {
				$tax_obj = get_taxonomy($tax_slug);  
				$selected = (isset($_GET[$tax_obj->query_var])) ? $_GET[$tax_obj->query_var] : '';
				wp_dropdown_categories(array(
					'show_option_all' => __('Show All '.$tax_obj->label, 'prism_portfolio' ),
					'taxonomy' => $tax_slug,
					'name' => $tax_obj->name,
					'orderby' => 'term_order',
					'selected' => $selected,
					'hierarchical' => $tax_obj->hierarchical,
					'hide_if_empty' => true
				));
			}
		}
	}
	
	//Parse drop down IDs to terms for query (IDs auto-generated by wp_dropdown_categories) 
	function convert_restrict(&$query) {
		global $pagenow;
		global $typenow;
		if (is_admin() && $pagenow=='edit.php') {
			$filters = get_object_taxonomies($typenow);
			foreach ($filters as $tax_slug) {
				$var = &$query->query_vars[$tax_slug];
				if ( isset($var) && $var>0) {
					$term = get_term_by('id',$var,$tax_slug);
					$var = $term->slug;
				} 

			}
		}  
		//return $query;
	}


	/**
	 * Load thickbox scripts on portfolio edit screen
	 */

	function conditional_thickbox() {
		$screen = get_current_screen(); 
		
		if ( $screen->id != 'edit-'.self::$post_type || $screen->post_type != self::$post_type) return; 
			wp_enqueue_script('media-upload');
			wp_enqueue_script('thickbox');
			wp_enqueue_style('thickbox');

	}

	/**
	 * Load custom script in media uploader popups
	 * (TODO: only load on popups from portfolio screen, if possible)
	 */
	function custom_set_thumbnail(){ 
		wp_enqueue_script('prism-thumbnails', self::$plugin_url . '/admin/js/prism-set-post-thumbnail.js' , array('jquery'));
	}

	function set_thumbnail_callback(){

		$post_ID = intval( $_POST['post_id'] );
		if ( !current_user_can( 'edit_post', $post_ID ) )
			die( '-1' );
		$thumbnail_id = intval( $_POST['thumbnail_id'] );

		check_ajax_referer( "set_post_thumbnail-$post_ID" );

		if ( $thumbnail_id == '-1' ) {
			if ( delete_post_thumbnail( $post_ID ) )
				die( _wp_post_thumbnail_html() );
			else
				die( '0' );
		}

		if ( set_post_thumbnail( $post_ID, $thumbnail_id ) )
			$msg = get_the_post_thumbnail( $post_ID, array(35,35));
			die($msg);
		die( '0' );
	}


	/**
	 * TODO : Add contextual help menu
	 */

	function add_help_text(){  
		$screen = get_current_screen();

		if ($screen->id!='edit-prism_portfolio') return;

	    get_current_screen()->add_help_tab( array(
	        'id'        => 'prism-help-tab',
	        'title'     => __( 'My Title', 'prism_portfolio' ),
	        'content'   => __( 'This tab is below the built in tab.' )
	    ) );
	}




} // end class