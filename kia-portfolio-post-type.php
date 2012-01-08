<?php
/*
Plugin Name: KIA Portfolio Post Type
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

// Pre-2.6 compatibility
if ( ! defined( 'WP_CONTENT_URL' ) )
      define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
      define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );


define( 'PPT_DIR', WP_PLUGIN_DIR . '/shiba-example-plugin' );
define( 'PPT_URL', WP_PLUGIN_URL . '/shiba-example-plugin' );


if (!class_exists("Portfolio_Post_Type")) :

class KIA_Portfolio_Post_Type {
	
	public $plugin_domain;
	
	function KIA_Portfolio_Post_Type() {	
		$this->plugin_domain = 'kia_portfolioposttype';
		
		//Register post type, taxonomies and terms
		add_action('init', array(&$this,'register_type'));
		
		//Display the custom portfolio type icon in the dashboard
		add_action('admin_head', array(&$this,'icons'));
		
		//Add Portfolio count to "Right Now" Dashboard Widget
		add_action('right_now_content_table_end', array(&$this,'add_counts'));
		
		//Add Custom Metabox for Featured Taxonomy
		add_action('admin_menu', array(&$this,'add_theme_box'));

		//Add Columns to Portfolio Edit Screen
		add_filter('manage_edit-portfolio_columns', array(&$this,'edit_columns'));
		add_action('manage_posts_custom_column', array(&$this,'display_columns'), 10, 2);
		
		//Add featured taxonomy radio buttons to quick edit screen
		add_action('quick_edit_custom_box', array(&$this,'add_quick_edit'), 20, 2);
		add_action('admin_footer-edit.php', array(&$this,'quick_edit_javascript'));
		//NB: if post type is hierarchical this must be set to page_row_actions
		add_filter('post_row_actions', array(&$this,'expand_quick_edit_link'), 10, 2);

		//save taxonomy data from metabox and quick edit
		add_action('save_post', array(&$this,'save_taxonomy_data'),20,2);
		
		//Make portfolios sortable by featured taxonomy
		add_filter('manage_edit-portfolio_sortable_columns', array(&$this,'sortable_columns'));
		add_filter('posts_clauses', array(&$this,'portfolio_featured_clauses'), 10, 2);
		
		//Add Taxonomy Filter to Custom Post Type 
		add_action('restrict_manage_posts', array(&$this, 'restrict_manage_posts'));

		//Parse drop down IDs to terms for query	
		add_filter('parse_query', array(&$this,'convert_restrict'));
		
		//Load thickbox scripts on portfolio edit screen
		add_action('admin_print_scripts-edit.php', array(&$this,'conditional_thickbox'), 10, 1);
		
		//Load custom script in media uploader popups (TODO: only load on popups from portfolio screen, if possible)
		add_action('admin_print_scripts-media-upload-popup', array(&$this,'custom_set_thumbnail'), 10, 1);
		
		//add callback for custom version of set thumbnail
		add_action('wp_ajax_kia_set_thumbnail', array(&$this,'set_thumbnail_callback'));	

		//Add contextual help to portfolio edit screen		
		add_action('contextual_help', array(&$this,'add_help_text'), 10, 3 );
		
		register_activation_hook( __FILE__, array(&$this,'activate') );
		register_deactivation_hook( __FILE__, array(&$this,'deactivate') );
	
	}
	
	function activate() {
		
		global $wpdb;

		if (function_exists('is_multisite') && is_multisite()) {
			// check if it is a network activation - if so, run the activation function for each blog id
			if (isset($_GET['networkwide']) && ($_GET['networkwide'] == 1)) {
				$old_blog = $wpdb->blogid;
				// Get all blog ids
				$blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs"));
				foreach ($blogids as $blog_id) {
					switch_to_blog($blog_id);
					$this->_activate();
				}
				switch_to_blog($old_blog);
				return;
			}	
			$this->_activate();
		} 
	}

	function deactivate() {
		global $wpdb;

		if (function_exists('is_multisite') && is_multisite()) {
			// check if it is a network activation - if so, run the activation function for each blog id
			if (isset($_GET['networkwide']) && ($_GET['networkwide'] == 1)) {
				$old_blog = $wpdb->blogid;
				// Get all blog ids
				$blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs"));
				foreach ($blogids as $blog_id) {
					switch_to_blog($blog_id);
					$this->_deactivate();
				}
				switch_to_blog($old_blog);
				return;
			}	
			$this->_deactivate();
		} 	
	}	
	
	function _activate() {
		$this->register_type();
		
		/**
		* Flushes rewrite rules on plugin activation to ensure portfolio posts don't 404
		* http://codex.wordpress.org/Function_Reference/flush_rewrite_rules
		*/
		flush_rewrite_rules();
	}
	
	function _deactivate() {
		flush_rewrite_rules();
	}
	
	/**
	 * Register post type, taxonomies and terms
	 * http://codex.wordpress.org/Function_Reference/register_post_type
	 */
	function register_type() {
	
		load_plugin_textdomain( $this->plugin_domain, PPT_DIR . '/lang', basename( dirname( __FILE__ ) ) . '/lang' );
		/**
		 * Register the Portfolio custom post type
		 * http://codex.wordpress.org/Function_Reference/register_post_type
		 */

		if(!post_type_exists('portfolio')){
			$labels = array(
				'name' => __( 'Portfolio', $this->plugin_domain ),
				'singular_name' => __( 'Portfolio Item', $this->plugin_domain ),
				'add_new' => __( 'Add New Item', $this->plugin_domain ),
				'add_new_item' => __( 'Add New Portfolio Item', $this->plugin_domain ),
				'edit_item' => __( 'Edit Portfolio Item', $this->plugin_domain ),
				'new_item' => __( 'Add New Portfolio Item', $this->plugin_domain ),
				'view_item' => __( 'View Item', $this->plugin_domain ),
				'search_items' => __( 'Search Portfolio', $this->plugin_domain ),
				'not_found' => __( 'No portfolio items found', $this->plugin_domain ),
				'not_found_in_trash' => __( 'No portfolio items found in trash', $this->plugin_domain )
			);

			$args = array(
				'labels' => $labels,
				'public' => true,
				'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments', 'custom-fields', 'revisions' ),
				'capability_type' => 'post',
				'rewrite' => array("slug" => "portfolio"), // Permalinks format
				'menu_position' => 5,
				'supports' => array('title', 'editor', 'author', 'thumbnail','comments' /*,'post-formats'*/) //todo: support certain post formats
			);

			register_post_type( 'portfolio', $args );
		}
		/**
		 * Register a taxonomy for Portfolio Tags
		 * http://codex.wordpress.org/Function_Reference/register_taxonomy
		 */
		 
		if(!taxonomy_exists('portfolio_tag')){
			$taxonomy_portfolio_tag_labels = array(
				'name' => _x( 'Portfolio Tags', $this->plugin_domain ),
				'singular_name' => _x( 'Portfolio Tag', $this->plugin_domain ),
				'search_items' => _x( 'Search Portfolio Tags', $this->plugin_domain ),
				'popular_items' => _x( 'Popular Portfolio Tags', $this->plugin_domain ),
				'all_items' => _x( 'All Portfolio Tags', $this->plugin_domain ),
				'parent_item' => _x( 'Parent Portfolio Tag', $this->plugin_domain ),
				'parent_item_colon' => _x( 'Parent Portfolio Tag:', $this->plugin_domain ),
				'edit_item' => _x( 'Edit Portfolio Tag', $this->plugin_domain ),
				'update_item' => _x( 'Update Portfolio Tag', $this->plugin_domain ),
				'add_new_item' => _x( 'Add New Portfolio Tag', $this->plugin_domain ),
				'new_item_name' => _x( 'New Portfolio Tag Name', $this->plugin_domain ),
				'separate_items_with_commas' => _x( 'Separate portfolio tags with commas', $this->plugin_domain ),
				'add_or_remove_items' => _x( 'Add or remove portfolio tags', $this->plugin_domain ),
				'choose_from_most_used' => _x( 'Choose from the most used portfolio tags', $this->plugin_domain ),
				'menu_name' => _x( 'Portfolio Tags', $this->plugin_domain )
			);
			
			$taxonomy_portfolio_tag_args = array(
				'labels' => $taxonomy_portfolio_tag_labels,
				'public' => true,
				'show_in_nav_menus' => true,
				'show_ui' => true,
				'show_tagcloud' => true,
				'hierarchical' => false,
				'rewrite' => array( 'slug' => 'portfolio-tag' ),
				'query_var' => true
			);
			
			register_taxonomy( 'portfolio_tag', array( 'portfolio' ), $taxonomy_portfolio_tag_args );
		}
		/**
		 * Register a taxonomy for Portfolio Categories
		 * http://codex.wordpress.org/Function_Reference/register_taxonomy
		 */
		if(!taxonomy_exists('portfolio_category')){
			$taxonomy_portfolio_category_labels = array(
				'name' => _x( 'Portfolio Categories', $this->plugin_domain ),
				'singular_name' => _x( 'Portfolio Category', $this->plugin_domain ),
				'search_items' => _x( 'Search Portfolio Categories', $this->plugin_domain ),
				'popular_items' => _x( 'Popular Portfolio Categories', $this->plugin_domain ),
				'all_items' => _x( 'All Portfolio Categories', $this->plugin_domain ),
				'parent_item' => _x( 'Parent Portfolio Category', $this->plugin_domain ),
				'parent_item_colon' => _x( 'Parent Portfolio Category:', $this->plugin_domain ),
				'edit_item' => _x( 'Edit Portfolio Category', $this->plugin_domain ),
				'update_item' => _x( 'Update Portfolio Category', $this->plugin_domain ),
				'add_new_item' => _x( 'Add New Portfolio Category', $this->plugin_domain ),
				'new_item_name' => _x( 'New Portfolio Category Name', $this->plugin_domain ),
				'separate_items_with_commas' => _x( 'Separate portfolio categories with commas', $this->plugin_domain ),
				'add_or_remove_items' => _x( 'Add or remove portfolio categories', $this->plugin_domain ),
				'choose_from_most_used' => _x( 'Choose from the most used portfolio categories', $this->plugin_domain ),
				'menu_name' => _x( 'Portfolio Categories', $this->plugin_domain ),
			);
			
			$taxonomy_portfolio_category_args = array(
				'labels' => $taxonomy_portfolio_category_labels,
				'public' => true,
				'show_in_nav_menus' => true,
				'show_ui' => true,
				'show_tagcloud' => true,
				'hierarchical' => true,
				'rewrite' => array( 'slug' => 'portfolio-category' ),
				'query_var' => true
			);
			
			register_taxonomy( 'portfolio_category', array( 'portfolio' ), $taxonomy_portfolio_category_args );
		}	
		/**
		 * Register a Featured taxonomy for Portfolio Items
		 * http://codex.wordpress.org/Function_Reference/register_taxonomy
		 */
			if(!taxonomy_exists('portfolio_featured')){
			$portfolio_featured_labels = array(
				'name' => _x( 'Featured', $this->plugin_domain ),
				'singular_name' => _x( 'Featured', $this->plugin_domain )			
			);
			
			$taxonomy_portfolio_featured_args = array(
				'labels' => $portfolio_featured_labels,
				'rewrite' => array( 'slug' => 'portfolio-featured' ),
				'query_var' => true,
				'public' => true,
				'show_ui' => false,
				'show_tagcloud' => false,
				'show_in_nav_menus' => false,
			);
			register_taxonomy( 'portfolio_featured', array( 'portfolio' ), $taxonomy_portfolio_featured_args );
		}
		
		/**
		 * Create Terms for Featured Taxonomy
		 */
			if (!term_exists( 'excluded', 'portfolio_featured') ){
			wp_insert_term(
			  'excluded', // the term 
			  'portfolio_featured', // the taxonomy
			  array(
				'slug' => 'excluded',
			  )
			);
			}
			if (!term_exists( 'normal', 'portfolio_featured') ){
			wp_insert_term(
			  'normal', // the term 
			  'portfolio_featured', // the taxonomy
			  array(
				'slug' => 'normal',
			  )
			);
			}
			if (!term_exists( 'featured', 'portfolio_featured') ){
				wp_insert_term(
				  'featured', // the term 
				  'portfolio_featured', // the taxonomy
				  array(
					'slug' => 'featured',
				  )
				);
			}
	}

	/**
	 * Display the custom portfolio type icon in the dashboard
	 */

	function icons() { ?>
		<style type="text/css" media="screen">
			#menu-posts-portfolio .wp-menu-image {
				background: url(<?php echo plugins_url( 'images/portfolio-icon.png' , __FILE__ ); ?>) no-repeat 6px 6px !important;
			}
			#menu-posts-portfolio:hover .wp-menu-image, #menu-posts-portfolio.wp-has-current-submenu .wp-menu-image {
				background-position:6px -16px !important;
			}
			#icon-edit.icon32-posts-portfolio {background: url(<?php echo plugins_url( 'images/portfolio-32x32.png' , __FILE__ ); ?>) no-repeat;}
		</style>
	<?php }

	/**
	 * Add Portfolio count to "Right Now" Dashboard Widget
	 */

	function add_counts() {
			if ( ! post_type_exists( 'portfolio' ) ) {
				 return;
			}

			$num_posts = wp_count_posts( 'portfolio' );
			$num = number_format_i18n( $num_posts->publish );
			$text = _n( 'Portfolio Item', 'Portfolio Items', intval($num_posts->publish) );
			if ( current_user_can( 'edit_posts' ) ) {
				$num = "<a href='edit.php?post_type=portfolio'>$num</a>";
				$text = "<a href='edit.php?post_type=portfolio'>$text</a>";
			}
			echo '<td class="first b b-portfolio">' . $num . '</td>';
			echo '<td class="t portfolio">' . $text . '</td>';
			echo '</tr>';

			if ($num_posts->pending > 0) {
				$num = number_format_i18n( $num_posts->pending );
				$text = _n( 'Portfolio Item Pending', 'Portfolio Items Pending', intval($num_posts->pending) );
				if ( current_user_can( 'edit_posts' ) ) {
					$num = "<a href='edit.php?post_status=pending&post_type=portfolio'>$num</a>";
					$text = "<a href='edit.php?post_status=pending&post_type=portfolio'>$text</a>";
				}
				echo '<td class="first b b-portfolio">' . $num . '</td>';
				echo '<td class="t portfolio">' . $text . '</td>';

				echo '</tr>';
			}
	}

	/* 
	 * Add Custom Metabox for Featured Taxonomy
	 *
	 */
	 
	function add_theme_box(){
		add_meta_box('portfolio_featured_tax', _x('Featured or Excluded Item',$this->plugin_domain), array($this,'featured_tax_display'), 'portfolio', 'side', 'low');
	}
			

	// This function gets called in edit-form-advanced.php
	//the guts of the custom metabox
	function featured_tax_display($post) { ?>
	 
		<input type="hidden" name="portfolio_featured_nonce" id="portfolio_featured_nonce" value="<?php echo wp_create_nonce( 'kia_featured_nonce_' . $post->ID ); ?>" />
	 
		<?php $featured = wp_get_post_terms( $post->ID, 'portfolio_featured' ); ?> 

		<p><?php _e('Is this a featured portfolio item?', $this->plugin_domain);?></p>
	
		<input type="radio" name="portfolio_featured_tax" <?php if(!is_wp_error($featured) && !empty($featured) && $featured[0]->slug=='featured'){echo " CHECKED ";} ?> value="featured"> <?php _e('Featured', $this->plugin_domain);?> <br/>
		
		<input type="radio" name="portfolio_featured_tax" <?php if( (!is_wp_error($featured) && !empty($featured) && $featured[0]->slug=='normal') || is_wp_error($featured) || empty($featured)){ echo " CHECKED "; } ?> value="normal"> <?php _e('Normal', $this->plugin_domain);?> <br/>
			
		<input type="radio" name="portfolio_featured_tax" <?php if(!is_wp_error($featured) && !empty($featured) && $featured[0]->slug=='excluded'){echo " CHECKED ";} ?> value="excluded"> <?php _e('Excluded', $this->plugin_domain);?> <br/>
			
	<?php  
	}

	/**
	 * Add Columns to Portfolio Edit Screen
	 * http://wptheming.com/2010/07/column-edit-pages/
	 */
	 
	function edit_columns($portfolio_columns){
		$portfolio_columns = array(
			"cb" => "<input type=\"checkbox\" />",
			"title" => _x('Title', 'column name'),
			"author" => __('Author', $this->plugin_domain),
			"thumbnail" => __('Thumbnail', $this->plugin_domain),
			"portfolio_category" => __('Category', $this->plugin_domain),
			"portfolio_tag" => __('Tags', $this->plugin_domain),
			"featured" => __('Featured Status'),
			"comments" => __('Comments', $this->plugin_domain),
			"date" => __('Date', $this->plugin_domain),
		);
		$portfolio_columns['comments'] = '<div class="vers"><img alt="Comments" src="' . esc_url( admin_url( 'images/comment-grey-bubble.png' ) ) . '" /></div>';
		return $portfolio_columns;
	}

	function display_columns($portfolio_columns, $post_id){

		global $post;

		switch ( $portfolio_columns ) {
			// Code adapted from: http://wpengineer.com/display-post-thumbnail-post-page-overview
			
			case "thumbnail":
				$width = (int) 35;
				$height = (int) 35;
				$url = admin_url( 'media-upload.php?post_id='.$post_id.'&amp;type=image&amp;TB_iframe=1&amp;width=640&amp;height=315');

				// Display the featured image in the column view if possible
				
					if ( has_post_thumbnail() ) {
							$text = '<div class="postimagediv"><a href="#" class="postfeaturedimage">'.get_the_post_thumbnail($post_id, array($width,$height)).'</a></div>';
						} else {
							$text = '<div class="postimagediv"><a href="#" class="postfeaturedimage hide-if-no-js">'._x('Add Image',$this->plugin_domain).'</a></div>';
						}  
						
						echo $text;

				break;	
				
				case "portfolio_category":
			
					$taxonomies = get_the_terms( $post_id, 'portfolio_category' ) ; 
					if ( !empty( $taxonomies ) ) {
						$out = array();
						foreach ( $taxonomies as $t ) {  
							$out[] = sprintf( '<a href="%s">%s</a>',
								esc_url( add_query_arg( array( 'post_type' => $post->post_type, 'portfolio_category' => $t->slug ), 'edit.php' ) ),
								esc_html( sanitize_term_field( 'name', $t->name, $t->term_id, 'portfolio_category', 'display' ) )
							);
						}
						echo join( ', ', $out );
						
					} else {
						_e( 'Uncategorized' , $this->plugin_domain);
					}	
					
			break;
			
				
				// Display the portfolio tags in the column view
				case "portfolio_tag":
				
				$taxonomies = get_the_terms( $post_id, 'portfolio_tag' ) ; 
					if ( !empty( $taxonomies ) ) {
						$out = array();
						foreach ( $taxonomies as $t ) {  
							$out[] = sprintf( '<a href="%s">%s</a>',
								esc_url( add_query_arg( array( 'post_type' => $post->post_type, 'portfolio_tag' => $t->slug ), 'edit.php' ) ),
								esc_html( sanitize_term_field( 'name', $t->name, $t->term_id, 'portfolio_tag', 'display' ) )
							);
						}
						echo join( ', ', $out );
						
					} else {
						_e( 'No Tags' , $this->plugin_domain);
					}	
				break;		
				
				// Display featured status column view
				case "featured":
				
				$taxonomies = get_the_terms( $post_id, 'portfolio_featured' ) ; 
					if ( !empty( $taxonomies ) ) {
						$out = array();
						foreach ( $taxonomies as $t ) {  
							$out[] = sprintf( '<a href="%s">%s</a>',
								esc_url( add_query_arg( array( 'post_type' => $post->post_type, 'portfolio_featured' => $t->slug ), 'edit.php' ) ),
								ucwords ( esc_html( sanitize_term_field( 'name', $t->name, $t->term_id, 'category', 'display' ) ) )
							);
						}
						echo join( ', ', $out );
						
					} else {
						_e( 'Normal' );
					}	
					
			break;
		}
	}



	/*
	 * Add to Quick Edit 
	 * http://shibashake.com/wordpress-theme/expand-the-wordpress-quick-edit-menu
	 */
	 
	 
	// Add a quick edit input
	function add_quick_edit($column_name, $post_type) {
		if ( $post_type != 'portfolio' || $column_name != 'featured') return;
		
		?>
		<fieldset class="inline-edit-col-left">
		<div class="inline-edit-col">
			<span class="title"><?php _e('Featured Status');?></span><br/>
			<input type="hidden" name="portfolio_featured_nonce" id="portfolio_featured_nonce" value="" />

			<input type="radio" name="portfolio_featured_tax" value="featured"/> <?php _e('Featured ');?>
			<input type="radio" name="portfolio_featured_tax" value="normal"/> <?php _e('Normal ');?> 
			<input type="radio" name="portfolio_featured_tax" value="excluded"/> <?php _e('Excluded  ');?>  
		</div>
		</fieldset>
		
		
		<?php
	}

	// custom javascript for quick edit box
	function quick_edit_javascript() {
		global $current_screen;
		
		if (($current_screen->id != 'edit-portfolio') || ($current_screen->post_type != 'portfolio')) return; 
	 
		?>
		<script type="text/javascript">  
		
			
		jQuery(document).ready(function($) {   
			$('.postimagediv').delegate('.postfeaturedimage ','click', function() { 
					//get post id from row element id
					string = $(this).parents('tr.type-portfolio').attr('id'); 
					if(/post-(\d+)/.exec(string)[1]) post_id = parseInt(/post-(\d+)/.exec(string)[1], 10);
					
					tbframe_interval = setInterval(function() {
					
						//maybe this is the method to only load custom script from edit screen?
						created_script=document.createElement('script');
						created_script.src='<?php echo plugins_url( 'js/kia-set-post-thumbnail.js' , __FILE__ );?>';
						created_script.type='text/javascript';

						//$('#TB_iframeContent').contents().find("head").append(created_script);
						
				
						//hide Use this button 
						$('#TB_iframeContent').contents().find('.savesend input[type="submit"]').hide();
			
						//switch WPSetAsThumbnail to KIASetAsThumbnail
						$('#TB_iframeContent').contents().find('.wp-post-thumbnail').addClass('button').css('margin-left','0').attr('onclick',function(i, val) {
							return val.replace('WPSetAsThumbnail', 'KIASetAsThumbnail');
						});

						
						//remove url, alignment and size fields- auto set to null, none and full respectively
						$('#TB_iframeContent').contents().find('.url').hide().find('input').val('');
						$('#TB_iframeContent').contents().find('.align').hide().find('input:radio').filter('[value="none"]').attr('checked', true);
						$('#TB_iframeContent').contents().find('.image-size').hide().find('input:radio').filter('[value="full"]').attr('checked', true);
					}, 2000);
					
					if(post_id)	tb_show('', 'media-upload.php?post_id='+post_id+'&type=image&tab=library&TB_iframe=true'); //tab sets the opened TB window to show library by default
					//tb_show('', 'media-upload.php?type=image&TB_iframe=true');
					return false;
				});
		 });

		function set_inline_featured_status(featuredValue, nonce) {
			// revert Quick Edit menu so that it refreshes properly
			inlineEditPost.revert();
			var featuredRadioInput = document.getElementsByName('portfolio_featured_tax');
			var nonceInput = document.getElementById('portfolio_featured_nonce');
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
		global $current_screen;
		if (($current_screen->id != 'edit-portfolio') || ($current_screen->post_type != 'portfolio')) return $actions; 
	 
		$nonce = wp_create_nonce( 'kia_featured_nonce_' . $post->ID );

		$featured = wp_get_object_terms($post->ID, 'portfolio_featured'); 
		
		//if for some reason there is no term in the tax, show as normal
		if($featured){ 
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
	 * Save our taxonomy data from metabox and quick edit 
	 * (since it is the same)
	*/
	function save_taxonomy_data($post_id,$post) {  

		// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want to do anything
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;	
		
		// verify post type is portfolio and not a revision
		if( $post->post_type != 'portfolio' || $post->post_type == 'revision' ) return $post_id;
		
		// make sure data came from our meta box, verify nonce
		$nonce = isset($_POST['portfolio_featured_nonce']) ? $_POST['portfolio_featured_nonce'] : NULL ;
		if (!wp_verify_nonce( $nonce, 'kia_featured_nonce_' . $post_id )) return $post_id;
		
		// Check permissions
		if ( 'page' == $post->post_type ) {
			if ( !current_user_can( 'edit_page', $post_id ) ) return $post_id;
		} else {
			if ( !current_user_can( 'edit_post', $post_id ) ) return $post_id;
		}	
		
		//once verified, update featured tax
		if (isset($_POST['portfolio_featured_tax']) ) { 
			$status = esc_attr($_POST['portfolio_featured_tax']);
			if ($status) {
				wp_set_object_terms( $post_id, $status, 'portfolio_featured' );
			} else { 
				wp_set_object_terms( $post_id, 'normal', 'portfolio_featured' );
			}
		}
		
	}
	
	/*
	 * Make Columns Sortable
	 * http://devpress.com/blog/custom-columns-for-custom-post-types/
	*/
	function sortable_columns( $columns ) {
		$columns['featured'] = 'portfolio_featured';
		//to do: $columns['portfolio_category'] = 'portfolio_category';
		return $columns;
	}


	/*
	 * Make Columns Sortable by Taxonomy
	 * http://scribu.net/wordpress/sortable-taxonomy-columns.html
	 */
	 function portfolio_featured_clauses( $clauses, $wp_query ) {
		global $wpdb;
		//sort by featured status
		if ( isset( $wp_query->query['orderby'] ) && 'portfolio_featured' == $wp_query->query['orderby'] ) {
	
			$clauses['join'] .= <<<SQL
LEFT OUTER JOIN {$wpdb->term_relationships} ON {$wpdb->posts}.ID={$wpdb->term_relationships}.object_id
LEFT OUTER JOIN {$wpdb->term_taxonomy} USING (term_taxonomy_id)
LEFT OUTER JOIN {$wpdb->terms} USING (term_id)
SQL;
			
			$clauses['where'] .= " AND (taxonomy = 'portfolio_featured' OR taxonomy IS NULL)";
			$clauses['groupby'] = "object_id";
			$clauses['orderby']  = "GROUP_CONCAT({$wpdb->terms}.name ORDER BY name ASC) ";
			$clauses['orderby'] .= ( 'ASC' == strtoupper( $wp_query->get('order') ) ) ? 'ASC' : 'DESC';
		}
		//sort by portfolio category
		if ( isset( $wp_query->query['orderby'] ) && 'portfolio_category' == $wp_query->query['orderby'] ) {
	 
			$clauses['join'] .= <<<SQL
LEFT OUTER JOIN {$wpdb->term_relationships} ON {$wpdb->posts}.ID={$wpdb->term_relationships}.object_id
LEFT OUTER JOIN {$wpdb->term_taxonomy} USING (term_taxonomy_id)
LEFT OUTER JOIN {$wpdb->terms} USING (term_id)
SQL;
			$clauses['where'] .= " AND (taxonomy = 'portfolio_featured' OR taxonomy IS NULL)";
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
		if ( $typenow = 'portfolio')  {
		$filters = array('portfolio_category','portfolio_tag','portfolio_featured');
			foreach ($filters as $tax_slug) {
				$tax_obj = get_taxonomy($tax_slug);  
				$selected = (isset($_GET[$tax_obj->query_var])) ? $_GET[$tax_obj->query_var] : '';
				wp_dropdown_categories(array(
					'show_option_all' => _x('Show All '.$tax_obj->label, $this->plugin_domain ),
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
		if(isset($_GET['post_type']) && $_GET['post_type']=='portfolio'){
			wp_enqueue_script('media-upload');
			wp_enqueue_script('thickbox');
			wp_enqueue_style('thickbox');
		}
	}

	/**
	 * Load custom script in media uploader popups
	 * (TODO: only load on popups from portfolio screen, if possible)
	 */
	function custom_set_thumbnail(){
		wp_enqueue_script('kia-thumbnails',plugins_url( 'js/kia-set-post-thumbnail.js' , __FILE__ ),array('jquery'));
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
	 * Add contextual help menu
	 */
	 
	function add_help_text( $contextual_help, $screen_id, $screen ) { 
		if ( 'portfolio' == $screen->id ) {
			$contextual_help =
			'<p>' . __('The title field and the big Post Editing Area are fixed in place, but you can reposition all the other boxes using drag and drop, and can minimize or expand them by clicking the title bar of each box. Use the Screen Options tab to unhide more boxes (Excerpt, Send Trackbacks, Custom Fields, Discussion, Slug, Author) or to choose a 1- or 2-column layout for this screen.') . '</p>' .
			'<p>' . __('<strong>Title</strong> - Enter a title for your post. After you enter a title, you&#8217;ll see the permalink below, which you can edit.') . '</p>' .
			'<p>' . __('<strong>Post editor</strong> - Enter the text for your post. There are two modes of editing: Visual and HTML. Choose the mode by clicking on the appropriate tab. Visual mode gives you a WYSIWYG editor. Click the last icon in the row to get a second row of controls. The HTML mode allows you to enter raw HTML along with your post text. You can insert media files by clicking the icons above the post editor and following the directions. You can go the distraction-free writing screen, new in 3.2, via the Fullscreen icon in Visual mode (second to last in the top row) or the Fullscreen button in HTML mode (last in the row). Once there, you can make buttons visible by hovering over the top area. Exit Fullscreen back to the regular post editor.') . '</p>' .
			'<p>' . __('<strong>Publish</strong> - You can set the terms of publishing your post in the Publish box. For Status, Visibility, and Publish (immediately), click on the Edit link to reveal more options. Visibility includes options for password-protecting a post or making it stay at the top of your blog indefinitely (sticky). Publish (immediately) allows you to set a future or past date and time, so you can schedule a post to be published in the future or backdate a post.') . '</p>' .
			( ( current_theme_supports( 'post-formats' ) && post_type_supports( 'post', 'post-formats' ) ) ? '<p>' . __( '<strong>Post Format</strong> - This designates how your theme will display a specific post. For example, you could have a <em>standard</em> blog post with a title and paragraphs, or a short <em>aside</em> that omits the title and contains a short text blurb. Please refer to the Codex for <a href="http://codex.wordpress.org/Post_Formats#Supported_Formats">descriptions of each post format</a>. Your theme could enable all or some of 10 possible formats.' ) . '</p>' : '' ) .
			'<p>' . __('<strong>Featured Image</strong> - This allows you to associate an image with your post without inserting it. This is usually useful only if your theme makes use of the featured image as a post thumbnail on the home page, a custom header, etc.') . '</p>' .
			'<p>' . __('<strong>Send Trackbacks</strong> - Trackbacks are a way to notify legacy blog systems that you&#8217;ve linked to them. Enter the URL(s) you want to send trackbacks. If you link to other WordPress sites they&#8217;ll be notified automatically using pingbacks, and this field is unnecessary.') . '</p>' .
			'<p>' . __('<strong>Discussion</strong> - You can turn comments and pings on or off, and if there are comments on the post, you can see them here and moderate them.') . '</p>' .
			'<p><strong>' . __('For more information:') . '</strong></p>' .
			'<p>' . __('<a href="http://codex.wordpress.org/Posts_Add_New_Screen" target="_blank">Documentation on Writing and Editing Posts</a>') . '</p>' .
			'<p>' . __('<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>') . '</p>';
	  } elseif ( 'edit-portfolio' == $screen->id ) {
		$contextual_help = 
			'<p>' . __('You can customize the display of this screen in a number of ways:') . '</p>' .
			'<ul>' .
			'<li>' . __('You can hide/display columns based on your needs and decide how many posts to list per screen using the Screen Options tab.') . '</li>' .
			'<li>' . __('You can filter the list of posts by post status using the text links in the upper left to show All, Published, Draft, or Trashed posts. The default view is to show all posts.') . '</li>' .
			'<li>' . __('You can view posts in a simple title list or with an excerpt. Choose the view you prefer by clicking on the icons at the top of the list on the right.') . '</li>' .
			'<li>' . __('You can refine the list to show only posts in a specific category or from a specific month by using the dropdown menus above the posts list. Click the Filter button after making your selection. You also can refine the list by clicking on the post author, category or tag in the posts list.') . '</li>' .
			'</ul>' .
			'<p>' . __('Hovering over a row in the posts list will display action links that allow you to manage your post. You can perform the following actions:') . '</p>' .
			'<ul>' .
			'<li>' . __('Edit takes you to the editing screen for that post. You can also reach that screen by clicking on the post title.') . '</li>' .
			'<li>' . __('Quick Edit provides inline access to the metadata of your post, allowing you to update post details without leaving this screen.') . '</li>' .
			'<li>' . __('Trash removes your post from this list and places it in the trash, from which you can permanently delete it.') . '</li>' .
			'<li>' . __('Preview will show you what your draft post will look like if you publish it. View will take you to your live site to view the post. Which link is available depends on your post&#8217;s status.') . '</li>' .
			'</ul>' .
			'<p>' . __('You can also edit multiple posts at once. Select the posts you want to edit using the checkboxes, select Edit from the Bulk Actions menu and click Apply. You will be able to change the metadata (categories, author, etc.) for all selected posts at once. To remove a post from the grouping, just click the x next to its name in the Bulk Edit area that appears.') . '</p>' .
			'<p><strong>' . __('For more information:') . '</strong></p>' .
			'<p>' . __('<a href="http://codex.wordpress.org/Posts_Screen" target="_blank">Documentation on Managing Posts</a>') . '</p>' .
			'<p>' . __('<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>') . '</p>';

	  }
	  return $contextual_help;
	}



} // end class
endif;

/**
* Launch the whole plugin
*/
global $kia_ppt;
if (class_exists("KIA_Portfolio_Post_Type") && !$kia_ppt) {
    $kia_ppt = new KIA_Portfolio_Post_Type();	
}	


	
?>