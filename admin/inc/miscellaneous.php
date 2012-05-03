<?php

/**
 * Checks if wp-admin is in SLL mode and replaces 
 * the protocol in links accordingly
 */
function prism_gallery_https( $input )
{
	global $prism_portfolio;

	if( $prism_portfolio->Gallery->ssl_admin && 0 === strpos($input, 'http:') && 0 !== strpos($input, 'https:') )
		$input = 'https' . substr($input, 4);
	
	return $input;
}


/**
 * Taken from WordPress 3.1-beta1
 */
if( ! function_exists('_wp_link_page') )
{
	/**
	 * Helper function for wp_link_pages().
	 *
	 * @since 3.1.0
	 * @access private
	 *
	 * @param int $i Page number.
	 * @return string Link.
	 */
	function _wp_link_page( $i ) {
		global $post, $wp_rewrite;
	
		if ( 1 == $i ) {
			$url = get_permalink();
		} else {
			if ( '' == get_option('permalink_structure') || in_array($post->post_status, array('draft', 'pending')) )
				$url = add_query_arg( 'page', $i, get_permalink() );
			elseif ( 'page' == get_option('show_on_front') && get_option('page_on_front') == $post->ID )
				$url = trailingslashit(get_permalink()) . user_trailingslashit("$wp_rewrite->pagination_base/" . $i, 'single_paged');
			else
				$url = trailingslashit(get_permalink()) . user_trailingslashit($i, 'single_paged');
		}
	
		return '<a href="' . esc_url( $url ) . '">';
	}
}


/**
 * Modified WP function to support !%mimetype% syntax // not yet, actually
 *
 * Convert MIME types into SQL.
 *
 * @since 1.6.5
 *
 * @param string|array $post_mime_types List of mime types or comma separated string of mime types.
 * @param string $table_alias Optional. Specify a table alias, if needed.
 * @return string The SQL AND clause for mime searching.
 */
function prism_gallery_wp_post_mime_type_where($post_mime_types, $table_alias = '') {
	$where = '';
	$wildcards = array('', '%', '%/%');
	if ( is_string($post_mime_types) )
		$post_mime_types = array_map('trim', explode(',', $post_mime_types));
	foreach ( (array) $post_mime_types as $mime_type ) {
		$mime_type = preg_replace('/\s/', '', $mime_type);
		$slashpos = strpos($mime_type, '/');
		if ( false !== $slashpos ) {
			$mime_group = preg_replace('/[^-*.a-zA-Z0-9]/', '', substr($mime_type, 0, $slashpos));
			$mime_subgroup = preg_replace('/[^-*.+a-zA-Z0-9]/', '', substr($mime_type, $slashpos + 1));
			if ( empty($mime_subgroup) )
				$mime_subgroup = '*';
			else
				$mime_subgroup = str_replace('/', '', $mime_subgroup);
			$mime_pattern = "$mime_group/$mime_subgroup";
		} else {
			$mime_pattern = preg_replace('/[^-*.a-zA-Z0-9]/', '', $mime_type);
			if ( false === strpos($mime_pattern, '*') )
				$mime_pattern .= '/*';
		}

		$mime_pattern = preg_replace('/\*+/', '%', $mime_pattern);

		if ( in_array( $mime_type, $wildcards ) )
			return '';

		if ( false !== strpos($mime_pattern, '%') )
			$wheres[] = empty($table_alias) ? "post_mime_type LIKE '$mime_pattern'" : "$table_alias.post_mime_type LIKE '$mime_pattern'";
		else
			$wheres[] = empty($table_alias) ? "post_mime_type = '$mime_pattern'" : "$table_alias.post_mime_type = '$mime_pattern'";
	}
	if ( !empty($wheres) )
		$where = ' AND (' . join(' OR ', $wheres) . ') ';
	return $where;
}


/**
 * Gets image dimensions, width by default
 */
function prism_gallery_get_image_size($link, $height = false)
{
	$link = trim($link);
	
	if( "" != $link )
	{
		$server_name = preg_match("#(http|https)://([^/]+)[/]?(.*)#", get_bloginfo('url'), $matches);
		$server_name = $matches[1] . "://" . $matches[2];
		
		if( false === strpos($link, $server_name) )
		{
			$size = getimagesize($link);
			
			if( $height )
				return $size[1];

			return $size[0];
		}		
	}
	
	return "";
}


/**
 * copy of the standard WordPress function found in admin
 *
 * @since 1.5.2
 */
function prism_gallery_file_is_displayable_image( $path )
{
	$path = preg_replace(array("#\\\#", "#/+#"), array("/", "/"), $path);		
	$info = @getimagesize($path);

	if ( empty($info) )
		$result = false;
	elseif ( !in_array($info[2], array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG)) )    // only gif, jpeg and png images can reliably be displayed
		$result = false;
	else
		$result = true;
	
	return apply_filters('file_is_displayable_image', $result, $path);
}



/**
 * Writes errors, notices, etc, to the log file
 * Limited to 100 kB
 */
function prism_gallery_write_log( $data = "" )
{
	$data = date("Y-m-d@H:i:s") . "\n" . str_replace("<br />", "\n", $data) . "\n";
	$filename = str_replace("\\", "/", WP_CONTENT_DIR) . "/prism_gallery_log.txt";
	
	if( @file_exists($filename) )
		$data .= @implode("", @file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)) . "\n";
	
	$file = @fopen($filename, "w+t");

	if( false !== $file )
	{		
		@fwrite($file, $data);
		
		if( 102400 < (filesize($filename) + strlen($data)) )
			@ftruncate($file, 102400);
	}
	
	@fclose($file);
}

?>