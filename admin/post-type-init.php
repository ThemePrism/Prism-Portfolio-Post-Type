<?php
/**
 * Initialize Post Type
 * 
 * The Initialize Post Type class initializes the post type and the custom taxonomies
 *
 * @class 		Prism_Post_Type_Init
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

class Prism_Post_Type_Init extends Prism_Portfolio {

	public function __construct() {  

		//Register post type, taxonomies and terms
		add_action('init', array(&$this,'register_type'));
		
		//Filter to allow prism_portfolio_category in the permalinks for portfolio items.
		add_filter( 'post_type_link', array(&$this,'filter_post_link'), 10, 4 );

		//Display the custom portfolio type icon in the dashboard
		add_action('admin_head', array(&$this,'icons'));

		//Add Portfolio count to "Right Now" Dashboard Widget
		add_action('right_now_content_table_end', array(&$this,'add_counts'));
	
	}


	/**
	 * Register post type, taxonomies and terms
	 * http://codex.wordpress.org/Function_Reference/register_post_type
	 */
	function register_type() {
		
		if ( post_type_exists('prism_portfolio') ) return;
		
		/**
		 * Slugs
		 **/
		 
		$options = get_option('prism_portfolio_options');
		
		$portfolio_page_id = isset($options['base_id']) ? $options['base_id'] : 0;
		
		$base_slug = ( $portfolio_page_id > 0 && get_page( $portfolio_page_id ) ) ? get_page_uri( $portfolio_page_id ) : 'portfolio';	
		
		$category_base = (isset ($options['prepend_portfolio_to_urls']) && $options['prepend_portfolio_to_urls'] == "yes" ) ? trailingslashit($base_slug) : '';
		
		$category_slug =  isset($options['cat_slug']) ? $options['cat_slug'] : _x('portfolio-category', 'slug', 'prism_portfolio');
		
		$tag_slug = isset($options['tag_slug']) ? $options['tag_slug'] : _x('portfolio-tag', 'slug', 'prism_portfolio');
		
		$portfolio_base = (isset($options['prepend_portfolio_page_to_items']) && $options['prepend_portfolio_page_to_items'] == "yes" )  ? trailingslashit($base_slug) : trailingslashit(_x('portfolio', 'slug', 'prism_portfolio'));
		
		if ( (isset($options['prepend_category_to_items']) && $options['prepend_category_to_items'] == "yes" ) ) $portfolio_base .= trailingslashit('%prism_portfolio_category%');
		
		$portfolio_base = untrailingslashit($portfolio_base);
		
		
		/**
		 * Register a taxonomy for Portfolio Tags
		 * http://codex.wordpress.org/Function_Reference/register_taxonomy
		 */
		 
		if(!taxonomy_exists('prism_portfolio_tag')){
			$labels = array(
				'name' => __( 'Portfolio Tags', 'prism_portfolio' ),
				'singular_name' => __( 'Portfolio Tag', 'prism_portfolio' ),
				'search_items' => __( 'Search Portfolio Tags', 'prism_portfolio' ),
				'popular_items' => __( 'Popular Portfolio Tags', 'prism_portfolio' ),
				'all_items' => __( 'All Portfolio Tags', 'prism_portfolio' ),
				'parent_item' => __( 'Parent Portfolio Tag', 'prism_portfolio' ),
				'parent_item_colon' => __( 'Parent Portfolio Tag:', 'prism_portfolio' ),
				'edit_item' => __( 'Edit Portfolio Tag', 'prism_portfolio' ),
				'update_item' => __( 'Update Portfolio Tag', 'prism_portfolio' ),
				'add_new_item' => __( 'Add New Portfolio Tag', 'prism_portfolio' ),
				'new_item_name' => __( 'New Portfolio Tag Name', 'prism_portfolio' ),
				'separate_items_with_commas' => __( 'Separate portfolio tags with commas', 'prism_portfolio' ),
				'add_or_remove_items' => __( 'Add or remove portfolio tags', 'prism_portfolio' ),
				'choose_from_most_used' => __( 'Choose from the most used portfolio tags', 'prism_portfolio' ),
				'menu_name' => __( 'Portfolio Tags', 'prism_portfolio' )
			);
			
			$args = array(
				'labels' => $labels,
				'public' => true,
				'show_in_nav_menus' => true,
				'show_ui' => true,
				'show_tagcloud' => true,
				'hierarchical' => false,
				'rewrite' => array( 'slug' => $category_base . $tag_slug, 'with_front' => false ),
				'query_var' => true
			);
			
			register_taxonomy( 'prism_portfolio_tag', array( 'prism_portfolio' ), $args );
		}
		/**
		 * Register a taxonomy for Portfolio Categories
		 * http://codex.wordpress.org/Function_Reference/register_taxonomy
		 */
		if(!taxonomy_exists('prism_portfolio_category')){
			$labels = array(
				'name' => __( 'Portfolio Categories', 'prism_portfolio' ),
				'singular_name' => __( 'Portfolio Category', 'prism_portfolio' ),
				'search_items' => __( 'Search Portfolio Categories', 'prism_portfolio' ),
				'popular_items' => __( 'Popular Portfolio Categories', 'prism_portfolio' ),
				'all_items' => __( 'All Portfolio Categories', 'prism_portfolio' ),
				'parent_item' => __( 'Parent Portfolio Category', 'prism_portfolio' ),
				'parent_item_colon' => __( 'Parent Portfolio Category:', 'prism_portfolio' ),
				'edit_item' => __( 'Edit Portfolio Category', 'prism_portfolio' ),
				'update_item' => __( 'Update Portfolio Category', 'prism_portfolio' ),
				'add_new_item' => __( 'Add New Portfolio Category', 'prism_portfolio' ),
				'new_item_name' => __( 'New Portfolio Category Name', 'prism_portfolio' ),
				'separate_items_with_commas' => __( 'Separate portfolio categories with commas', 'prism_portfolio' ),
				'add_or_remove_items' => __( 'Add or remove portfolio categories', 'prism_portfolio' ),
				'choose_from_most_used' => __( 'Choose from the most used portfolio categories', 'prism_portfolio' ),
				'menu_name' => __( 'Portfolio Categories', 'prism_portfolio' ),
			);
			
			$args = array(
				'labels' => $labels,
				'public' => true,
				'show_in_nav_menus' => true,
				'show_ui' => true,
				'show_tagcloud' => true,
				'hierarchical' => true,
				'rewrite' => array( 'slug' => $category_base . $category_slug, 'with_front' => false, 'hierarchical' => true ),
				'query_var' => true
			);
			
			register_taxonomy( 'prism_portfolio_category', array( 'prism_portfolio' ), $args );
		}	
		/**
		 * Register a Featured taxonomy for Portfolio Items
		 * http://codex.wordpress.org/Function_Reference/register_taxonomy
		 */
			if(!taxonomy_exists('prism_portfolio_featured')){
			$labels = array(
				'name' => __( 'Featured', 'prism_portfolio' ),
				'singular_name' => __( 'Featured', 'prism_portfolio' )			
			);
			
			$args = array(
				'labels' => $labels,
				'rewrite' => array( 'slug' => 'portfolio-featured' ),
				'query_var' => true,
				'public' => true,
				'show_ui' => false,
				'show_tagcloud' => false,
				'show_in_nav_menus' => false,
			);
			register_taxonomy( 'prism_portfolio_featured', array( 'prism_portfolio' ), $args );
		}
		
		/**
		 * Create Terms for Featured Taxonomy
		 */
			if (!term_exists( 'archived', 'prism_portfolio_featured') ){
			wp_insert_term(
			  'archived', // the term 
			  'prism_portfolio_featured', // the taxonomy
			  array(
				'slug' => 'archived',
			  )
			);
			}
			if (!term_exists( 'normal', 'prism_portfolio_featured') ){
			wp_insert_term(
			  'normal', // the term 
			  'prism_portfolio_featured', // the taxonomy
			  array(
				'slug' => 'normal',
			  )
			);
			}
			if (!term_exists( 'featured', 'prism_portfolio_featured') ){
				wp_insert_term(
				  'featured', // the term 
				  'prism_portfolio_featured', // the taxonomy
				  array(
					'slug' => 'featured',
				  )
				);
			}
			
		/**
		 * Register the Portfolio custom post type
		 * http://codex.wordpress.org/Function_Reference/register_post_type
		 */
		$labels = array(
				'name' => __( 'Portfolio', 'prism_portfolio' ),
				'singular_name' => __( 'Portfolio Item', 'prism_portfolio' ),
				'add_new' => __( 'Add New Item', 'prism_portfolio' ),
				'add_new_item' => __( 'Add New Portfolio Item', 'prism_portfolio' ),
				'edit_item' => __( 'Edit Portfolio Item', 'prism_portfolio' ),
				'new_item' => __( 'Add New Portfolio Item', 'prism_portfolio' ),
				'view_item' => __( 'View Item', 'prism_portfolio' ),
				'search_items' => __( 'Search Portfolio', 'prism_portfolio' ),
				'not_found' => __( 'No portfolio items found', 'prism_portfolio' ),
				'not_found_in_trash' => __( 'No portfolio items found in trash', 'prism_portfolio' )
			);

		$args = array(
				'labels' => $labels,
				'public' => true,
				'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments', 'custom-fields', 'revisions' ),
				'capability_type' => 'post',
				'has_archive' => true,
				'menu_position' => 5,
				'supports' => array('title', 'excerpt', 'custom-fields', 'editor', 'author', 'thumbnail','comments' /*,'post-formats'*/), //todo: support certain post formats
				'query_var' 			=> true,			
				'supports' 				=> array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments', 'custom-fields' ),
				'has_archive' 			=> $base_slug,
				'rewrite' 				=> array( 'slug' => $portfolio_base, 'with_front' => false, 'feeds' => $base_slug ),
		);

		register_post_type( 'prism_portfolio', $args );	
	}

	/**
	 * Filter to allow prism_portfolio_category in the permalinks for products.
	 *
	 * @param string $permalink The existing permalink URL.
	 */

	function filter_post_link( $permalink, $post, $leavename, $sample ) {
		// Abort if post is not a portfolio item
		if ($post->post_type!=='prism_portfolio') return $permalink;
		
		// Abort early if the placeholder rewrite tag isn't in the generated URL
		if ( false === strpos( $permalink, '%prism_portfolio_category%' ) ) return $permalink;

		// Get the custom taxonomy terms in use by this post
		$terms = get_the_terms( $post->ID, 'prism_portfolio_category' );

		if ( empty( $terms ) ) :
			// If no terms are assigned to this post, use a string instead (can't leave the placeholder there)
			$permalink = str_replace( '%prism_portfolio_category%', _x('uncategorized', 'slug', 'prism_portfolio'), $permalink );
		else :
			// Replace the placeholder rewrite tag with the first term's slug
			$first_term = array_shift( $terms );
			$permalink = str_replace( '%prism_portfolio_category%', $first_term->slug, $permalink );
		endif;

		return $permalink;
	}

	/**
	 * Display the custom portfolio type icon in the dashboard
	 */

	function icons() { ?>
		<style type="text/css" media="screen">
			#menu-posts-prism_portfolio .wp-menu-image {
				background: url(<?php echo self::plugin_url() . '/images/portfolio-icon.png'; ?>) no-repeat 6px 6px !important;
			}
			#menu-posts-prism_portfolio:hover .wp-menu-image, #menu-posts-prism_portfolio.wp-has-current-submenu .wp-menu-image {
				background-position:6px -16px !important;
			}
			#icon-edit.icon32-posts-prism_portfolio {background: url(<?php echo self::plugin_url() .'/images/portfolio-32x32.png'; ?>) no-repeat;}
		</style>
	<?php }

	/**
	 * Add Portfolio count to "Right Now" Dashboard Widget
	 */

	function add_counts() {
			if ( ! post_type_exists( self::$post_type ) ) {
				 return;
			}

			$num_posts = wp_count_posts( self::$post_type );
			$num = number_format_i18n( $num_posts->publish );
			$text = _n( 'Portfolio Item', 'Portfolio Items', intval($num_posts->publish) );
			if ( current_user_can( 'edit_posts' ) ) {
				$num = sprintf('<a href="edit.php?post_type=%s">%d</a>',self::$post_type,$num);
				$text = sprintf('<a href="edit.php?post_type=%1$s">%2$s</a>',self::$post_type,$text);
			}
			echo '<td class="first b b-portfolio">' . $num . '</td>';
			echo '<td class="t portfolio">' . $text . '</td>';
			echo '</tr>';

			if ($num_posts->pending > 0) {
				$num = number_format_i18n( $num_posts->pending );
				$text = _n( 'Portfolio Item Pending', 'Portfolio Items Pending', intval($num_posts->pending) );
				if ( current_user_can( 'edit_posts' ) ) {
					$num = sprintf('<a href="edit.php?post_status=pending&post_type=%s">%d</a>', self::$post_type ,$num );
					$text = sprintf('<a href="edit.php?post_status=pending&post_type=%1$s">%2$s</a>', self::$post_type, $text );
				}
				echo '<td class="first b b-portfolio">' . $num . '</td>';
				echo '<td class="t portfolio">' . $text . '</td>';

				echo '</tr>';
			}
	}

} //end class