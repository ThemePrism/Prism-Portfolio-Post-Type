<?php
if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

?>

<div class="wrap">   
    <?php screen_icon(); ?>

    <h2><?php _e('Prism Portfolio Settings', 'prism_portfolio');?></h2>

    <?php settings_errors();?>

    <style> 
      td span {color: #666; font-style: italic;}
    </style>

	<?php
	$options = get_option('prism_portfolio_options');
	$portfolio_page_id = isset($options['base_id']) ? $options['base_id'] : 0;
	$base_slug = ( $portfolio_page_id > 0 && get_page( $portfolio_page_id ) ) ? get_page_uri( $portfolio_page_id ) : 'portfolio';	
	$category_base = (isset ($options['prepend_portfolio_to_urls']) && $options['prepend_portfolio_to_urls'] == "yes" ) ? trailingslashit($base_slug) : '';
	$category_slug =  isset($options['cat_slug']) ? $options['cat_slug'] : _x('portfolio-category', 'slug', 'prism_portfolio');
	$tag_slug = isset($options['tag_slug']) ? $options['tag_slug'] : _x('portfolio-tag', 'slug', 'prism_portfolio');
	$portfolio_base = (isset($options['prepend_portfolio_page_to_items']) && $options['prepend_portfolio_page_to_items'] == "yes" )  ? trailingslashit($base_slug) : trailingslashit(_x('portfolio', 'slug', 'prism_portfolio'));
	if ( (isset($options['prepend_category_to_items']) && $options['prepend_category_to_items'] == "yes" ) ) $portfolio_base .= trailingslashit('%prism_portfolio_category%');
	if ( get_option('woocommerce_prepend_category_to_products') == 'yes' ) $product_base .= trailingslashit('%product_cat%');
	$portfolio_base = untrailingslashit($portfolio_base);
	?>

    <form method="post" action="options.php">
		<?php settings_fields('prism_portfolio_settings'); ?>
		<?php $options = get_option('prism_portfolio_options'); 
		
		$portfolio_page_id = isset($options['base_id']) ? $options['base_id'] : 0;
		$base_slug = ( $portfolio_page_id > 0 && get_page( $portfolio_page_id ) ) ? get_page_uri( $portfolio_page_id ) : 'portfolio';	
	
		?>
		<h3><?php _e('Permalinks','prism_portfolio');?>
		<table class="form-table">
			<tr><th scope="row"><?php _e('Portfolio Base Page', 'prism_portfolio');?></th>
				<td>
				<?php 
					$args = array( 'selected'         => $options['base_id'],
								'show_option_none' => __('Select a Page','prism_portfolio'),
								'name'             => 'prism_portfolio_options[base_id]'); 
					
					wp_dropdown_pages($args); ?>
			
					<br/><span><?php _e('Set the base page of your portfolio- this is where your portfolio archive will be.', 'prism_portfolio');?></span>
				</td>
			</tr>
			<tr><th scope="row"><?php _e('Taxonomy Base Page', 'prism_portfolio');?></th>
			<td><input type="checkbox" name="prism_portfolio_options[prepend_portfolio_to_urls]" value="yes" <?php checked( $options['prepend_portfolio_to_urls'], 'yes' ); ?> />
			<span><?php _e('Prepend portfolio categories/tags with portfolio base page ('.$base_slug.')', 'prism_portfolio');?></span>
			</td>
			</tr>
					
			<tr>
				<th scope="row"><?php _e('Portfolio Categories Base', 'prism_portfolio');?></th>
				<?php $slug = $options['cat_slug'] ? $options['cat_slug'] : 'portfolio-category'; ?>
				<td><input type="text" name="prism_portfolio_options[cat_slug]" value="<?php echo $slug; ?>" />
				<br/><span><?php _e('Permalink "slug" for portfolio categories', 'prism_portfolio');?></span>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Portfolio Tags Base', 'prism_portfolio');?></th>
				<?php $slug = $options['tag_slug'] ? $options['tag_slug'] : 'portfolio-tag'; ?>
				<td><input type="text" name="prism_portfolio_options[tag_slug]" value="<?php echo $slug; ?>" />
				<br/><span><?php _e('Permalink "slug" for portfolio tags', 'prism_portfolio');?></span>
			</td>
			</tr>
			<tr>
			<th scope="row"><?php _e('Product base page', 'prism_portfolio');?></th>
				<td><input type="checkbox" name="prism_portfolio_options[prepend_portfolio_page_to_items]" value="yes" <?php checked( $options['prepend_portfolio_page_to_items'], 'yes' ); ?> />
				<span><?php _e('Prepend portfolio permalinks with portfolio base page ('.$base_slug.')', 'prism_portfolio');?></span>
				<br/>
				<input type="checkbox" name="prism_portfolio_options[prepend_category_to_items]" value="yes" <?php checked( $options['prepend_category_to_items'], 'yes' ); ?> />
				<span><?php _e('Prepend portfolio permalinks with portfolio category', 'prism_portfolio');?></span>
				</td>
			</tr>

		</table>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			<input type="submit" name="prism_portfolio_options[reset]" value="<?php _e('Restore Defaults') ?>" onclick="return confirm( '<?php _e('Click OK to reset all plugin options. All settings will be lost!','prism_portfolio' );?>');">
		</p>
	</form>

</div>


