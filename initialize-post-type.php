<?php
/**
 * Initialize Post Type
 * 
 * The Initialize Post Type class initializes the post type and the custom taxonomies
 *
 * @class 		Initialize_Post_Type
 * @package		Prism_Portfolio_Post_Type
 * @category	Class
 * @author		KathyisAwesome
 *
 * Table of Contents
 *
 *
 *
 *
 */

class Initialize_Post_Type extends Prism_Portfolio_Post_Type {

	protected $post_type;

	public function __construct($post_type,$name) {  

		$this->post_type = $post_type;

		//Register post type, taxonomies and terms
		add_action('init', array(&$this,'register_type'));
		
		//Display the custom portfolio type icon in the dashboard
		add_action('admin_head', array(&$this,'icons'));
		
		//Add Portfolio count to "Right Now" Dashboard Widget
		add_action('right_now_content_table_end', array(&$this,'add_counts'));

		add_action('thematic_abovecontainer',array(&$this,'testing'));

	}

	/**
	 * Register post type, taxonomies and terms
	 * http://codex.wordpress.org/Function_Reference/register_post_type
	 */
	function register_type() {
	
		/**
		 * Register the Portfolio custom post type
		 * http://codex.wordpress.org/Function_Reference/register_post_type
		 */

		if(!post_type_exists('prism_portfolio')){
			$labels = array(
				'name' => __( 'Portfolio', self::TEXT_DOMAIN ),
				'singular_name' => __( 'Portfolio Item', self::TEXT_DOMAIN ),
				'add_new' => __( 'Add New Item', self::TEXT_DOMAIN ),
				'add_new_item' => __( 'Add New Portfolio Item', self::TEXT_DOMAIN ),
				'edit_item' => __( 'Edit Portfolio Item', self::TEXT_DOMAIN ),
				'new_item' => __( 'Add New Portfolio Item', self::TEXT_DOMAIN ),
				'view_item' => __( 'View Item', self::TEXT_DOMAIN ),
				'search_items' => __( 'Search Portfolio', self::TEXT_DOMAIN ),
				'not_found' => __( 'No portfolio items found', self::TEXT_DOMAIN ),
				'not_found_in_trash' => __( 'No portfolio items found in trash', self::TEXT_DOMAIN )
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

			register_post_type( 'prism_portfolio', $args );
		}
		/**
		 * Register a taxonomy for Portfolio Tags
		 * http://codex.wordpress.org/Function_Reference/register_taxonomy
		 */
		 
		if(!taxonomy_exists('prism_portfolio_tag')){
			$taxonomy_portfolio_tag_labels = array(
				'name' => __( 'Portfolio Tags', self::TEXT_DOMAIN ),
				'singular_name' => __( 'Portfolio Tag', self::TEXT_DOMAIN ),
				'search_items' => __( 'Search Portfolio Tags', self::TEXT_DOMAIN ),
				'popular_items' => __( 'Popular Portfolio Tags', self::TEXT_DOMAIN ),
				'all_items' => __( 'All Portfolio Tags', self::TEXT_DOMAIN ),
				'parent_item' => __( 'Parent Portfolio Tag', self::TEXT_DOMAIN ),
				'parent_item_colon' => __( 'Parent Portfolio Tag:', self::TEXT_DOMAIN ),
				'edit_item' => __( 'Edit Portfolio Tag', self::TEXT_DOMAIN ),
				'update_item' => __( 'Update Portfolio Tag', self::TEXT_DOMAIN ),
				'add_new_item' => __( 'Add New Portfolio Tag', self::TEXT_DOMAIN ),
				'new_item_name' => __( 'New Portfolio Tag Name', self::TEXT_DOMAIN ),
				'separate_items_with_commas' => __( 'Separate portfolio tags with commas', self::TEXT_DOMAIN ),
				'add_or_remove_items' => __( 'Add or remove portfolio tags', self::TEXT_DOMAIN ),
				'choose_from_most_used' => __( 'Choose from the most used portfolio tags', self::TEXT_DOMAIN ),
				'menu_name' => __( 'Portfolio Tags', self::TEXT_DOMAIN )
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
			
			register_taxonomy( 'prism_portfolio_tag', array( 'prism_portfolio' ), $taxonomy_portfolio_tag_args );
		}
		/**
		 * Register a taxonomy for Portfolio Categories
		 * http://codex.wordpress.org/Function_Reference/register_taxonomy
		 */
		if(!taxonomy_exists('prism_portfolio_category')){
			$taxonomy_portfolio_category_labels = array(
				'name' => __( 'Portfolio Categories', self::TEXT_DOMAIN ),
				'singular_name' => __( 'Portfolio Category', self::TEXT_DOMAIN ),
				'search_items' => __( 'Search Portfolio Categories', self::TEXT_DOMAIN ),
				'popular_items' => __( 'Popular Portfolio Categories', self::TEXT_DOMAIN ),
				'all_items' => __( 'All Portfolio Categories', self::TEXT_DOMAIN ),
				'parent_item' => __( 'Parent Portfolio Category', self::TEXT_DOMAIN ),
				'parent_item_colon' => __( 'Parent Portfolio Category:', self::TEXT_DOMAIN ),
				'edit_item' => __( 'Edit Portfolio Category', self::TEXT_DOMAIN ),
				'update_item' => __( 'Update Portfolio Category', self::TEXT_DOMAIN ),
				'add_new_item' => __( 'Add New Portfolio Category', self::TEXT_DOMAIN ),
				'new_item_name' => __( 'New Portfolio Category Name', self::TEXT_DOMAIN ),
				'separate_items_with_commas' => __( 'Separate portfolio categories with commas', self::TEXT_DOMAIN ),
				'add_or_remove_items' => __( 'Add or remove portfolio categories', self::TEXT_DOMAIN ),
				'choose_from_most_used' => __( 'Choose from the most used portfolio categories', self::TEXT_DOMAIN ),
				'menu_name' => __( 'Portfolio Categories', self::TEXT_DOMAIN ),
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
			
			register_taxonomy( 'prism_portfolio_category', array( 'prism_portfolio' ), $taxonomy_portfolio_category_args );
		}	
		/**
		 * Register a Featured taxonomy for Portfolio Items
		 * http://codex.wordpress.org/Function_Reference/register_taxonomy
		 */
			if(!taxonomy_exists('prism_portfolio_featured')){
			$portfolio_featured_labels = array(
				'name' => __( 'Featured', self::TEXT_DOMAIN ),
				'singular_name' => __( 'Featured', self::TEXT_DOMAIN )			
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
			register_taxonomy( 'prism_portfolio_featured', array( 'prism_portfolio' ), $taxonomy_portfolio_featured_args );
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
	}

	/**
	 * Display the custom portfolio type icon in the dashboard
	 */

	function icons() { ?>
		<style type="text/css" media="screen">
			#menu-posts-prism_portfolio .wp-menu-image {
				background: url(<?php echo plugins_url( 'images/portfolio-icon.png' , __FILE__ ); ?>) no-repeat 6px 6px !important;
			}
			#menu-posts-prism_portfolio:hover .wp-menu-image, #menu-posts-prism_portfolio.wp-has-current-submenu .wp-menu-image {
				background-position:6px -16px !important;
			}
			#icon-edit.icon32-posts-prism_portfolio {background: url(<?php echo plugins_url( 'images/portfolio-32x32.png' , __FILE__ ); ?>) no-repeat;}
		</style>
	<?php }

	/**
	 * Add Portfolio count to "Right Now" Dashboard Widget
	 */

	function add_counts() {
			if ( ! post_type_exists( 'prism_portfolio' ) ) {
				 return;
			}

			$num_posts = wp_count_posts( 'prism_portfolio' );
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


} //end class