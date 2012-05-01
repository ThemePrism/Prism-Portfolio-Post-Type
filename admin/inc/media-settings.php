<?php

/**
 * Registers "prism_gallery" options and adds sections/fields to the 
 * media settings page
 */
function prism_gallery_options_init()
{
	global $prism_portfolio->Gallery;

	$so = get_option('prism_portfolio_gallery');
	$prism_gallery_sizes = prism_gallery_get_intermediate_image_sizes();
	
	// add sections
	add_settings_section('intermediate_image_sizes', __('Intermediate image sizes', 'prism_portfolio'), 'prism_gallery_options_sections', 'media');
	add_settings_section('prism_gallery_options', __('Prism Gallery', 'prism_portfolio'), 'prism_gallery_options_sections', 'media');
	
	// register the prism_gallery variable
	register_setting('media', 'prism_gallery', 'prism_gallery_save_media_settings');
	
	// add additional fields and register settings for image sizes...
	foreach( $prism_gallery_sizes as $size )
	{
		if( "thumbnail" != $size && "full" != $size )
		{
			$size_translated = " " . __('size', 'prism_portfolio');
			
			if( "medium" == $size )
			{
				$translated_size = ucfirst(__("Medium size", "file-gallery"));
				$size_translated = "";
			}
			elseif( "large" == $size )
			{
				$translated_size = ucfirst(__("Large size", "file-gallery"));
				$size_translated = "";
			}
			else
			{
				$translated_size = ucfirst($size);
			}
				
			add_settings_field("size_" . $size, $translated_size . $size_translated, create_function('', 'echo prism_gallery_options_fields( array("name" => "' . $size . '", "type" => "intermediate_image_sizes", "disabled" => 0) );'), 'media', 'intermediate_image_sizes');
			
			register_setting('media', $size . "_size_w");
			register_setting('media', $size . "_size_h");
			register_setting('media', $size . "_crop");
		}
	}
	
	prism_gallery_add_settings();
}
add_action('admin_init', 'prism_gallery_options_init');



/**
 *	adds sections text
 */
function prism_gallery_options_sections( $args )
{
	switch( $args["id"] )
	{
		case "intermediate_image_sizes" :
			$output = __("Here you can specify width, height and crop attributes for intermediate image sizes added by plugins and/or themes, as well as crop options for the default medium and large sizes", "file-gallery");
			break;
		case "prism_gallery_options" :
			$output = '<p id="file-gallery-help-notice" style="margin: 0 10px; background-color: #FFFFE8; border-color: #EEEED0; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius: 3px; border-style: solid; border-width: 1px; padding: 0.6em;">' . sprintf(__('Prism Gallery help file is located in the "help" subfolder of the plugin. You can <a href="%s/help/index.html" target="_blank">click here to open it in new window</a>.', "file-gallery"), PRISM_GALLERY_URL) . '</p>';
			break;
	}
	
	if( "" != $output )
		echo "<p>" . $output . "</p>";
}


/**
 * Makes sure that plugin options do not disappear just
 * because we're lazy (using checkboxes instead of radio buttons) :D
 *
 * @since 1.6.5.4
 */
function prism_gallery_save_media_settings( $options )
{
	global $prism_portfolio->Gallery;

	$defaults = $prism_portfolio->Gallery->false_defaults;
	$defaults = prism_gallery_parse_args( $options, $defaults); // $defaults = shortcode_atts( $defaults, $options );
	$defaults['folder']  = prism_gallery_https( PRISM_GALLERY_URL );
	$defaults['abspath'] = PRISM_GALLERY_ABSPATH;
	
	return $defaults;
}


/**
 * Parses plugin options
 *
 * @since 1.6.5.2
 */
function prism_gallery_parse_args( $args, $defaults )
{
	foreach( $defaults as $key => $val )
	{
		// if key isn't set, it's a new option - add
		if( ! isset($args[$key]) )
			$args[$key] = $val;
		// if a key's value is empty, but should be a false - make it rather a zero
		elseif( '' == $args[$key] && (0 === $val || 1 === $val) )
			$args[$key] = 0;
	}
	
	return $args;
}


/**
 * Creates select option dropdowns
 *
 * @since 1.7
 */
function prism_gallery_dropdown( $name, $type )
{
	$output = '';
	$options = get_option('prism_portfolio_gallery');
	
	$current = $options[$name];
	
	if( 'image_size' == $type )
		$keys['image_size'] = prism_gallery_get_intermediate_image_sizes();
	
	if( 'template' == $type )
		$keys['template'] = prism_gallery_get_templates('prism_gallery_dropdown');

	$keys['align'] = array(
		'none' => __('none', 'prism_portfolio'), 
		'left' => __('left', 'prism_portfolio'), 
		'right' => __('right', 'prism_portfolio'),
		'center' => __('center', 'prism_portfolio')
	);
	$keys['linkto']	 = array(
		"none" => __("nothing (do not link)", "file-gallery"), 
		"file" => __("file", "file-gallery"), 
		"attachment" => __("attachment page", "file-gallery"),
		"parent_post" => __("parent post", "file-gallery"),
		"external_url" => __("external url", "file-gallery")
	);
	$keys['orderby'] = array(
		"default" => __("file gallery", "file-gallery"), 
		"rand" => __("random", "file-gallery"), 
		"menu_order" => __("menu order", "file-gallery"),
		"post_title" => __("title", "file-gallery"),
		"ID" => __("date / time", "file-gallery")
	);
	$keys['order'] = array(
		"ASC" => __("ascending", "file-gallery"), 
		"DESC" => __("descending", "file-gallery")
	);
	$keys['align'] = array(
		"none" => __("none", "file-gallery"), 
		"left" => __("left", "file-gallery"), 
		"right" => __("right", "file-gallery"),
		"center" => __("center", "file-gallery")
	);
	$keys['columns'] = array(
		0, 1, 2, 3, 4, 5, 6, 7, 8, 9
	);

	if( 'image_size' == $type )
	{
		$output .= '<option value="thumbnail"';
		
		if( $current == 'thumbnail' )
			$output .= ' selected="selected"';
		
		$output .= '>' . __('thumbnail', 'prism_portfolio') . '</option>';
		$output .= '<option value="medium"';
		
		if( $current == 'medium' )
			$output .= ' selected="selected"';
		
		$output .= '>' . __('medium', 'prism_portfolio') . '</option>';
		$output .= '<option value="large"';
		
		if( $current == 'large' )
			$output .= ' selected="selected"';
		
		$output .= '>' . __('large', 'prism_portfolio') . '</option>';
		$output .= '<option value="full"';
		
		if( $current == 'full' )
			$output .= ' selected="selected"';
		
		$output .= '>' . __('full', 'prism_portfolio') . '</option>';
	}
	
	foreach( $keys[$type] as $name => $description )
	{
		if( is_numeric($name) )
			$name = $description;

		if( 'image_size' == $type && in_array($name, array('thumbnail', 'medium', 'large', 'full')) )
			continue;

		$output .= '<option value="' . $name . '"';
		
		if( $current == $name )
			$output .= ' selected="selected"';
		
		$output .= '>' . $description . '</option>';
	}
	
	return $output;
}


/**
 * Returns a checkbox for each post type
 *
 * @since 1.7
 */
function prism_gallery_post_type_checkboxes()
{
	$output = '';
	$options = get_option('prism_portfolio_gallery');
	$types = get_post_types(false, 'objects');
	
	foreach( $types as $type )
	{
		if( ! isset($type->labels->name) )
			$type->labels->name = $type->label;

		if( ! in_array( $type->name, array('nav_menu_item', 'revision', 'attachment', 'deprecated_log') ) )
		{
			$output .= 
			'<input type="checkbox" name="prism_gallery[show_on_post_type_' . $type->name . ']" 
					id="prism_gallery_show_on_post_type_' . $type->name . '" 
					value="1" 
					' . str_replace("'", '"', checked('1', isset($options["show_on_post_type_" . $type->name]) && true == $options["show_on_post_type_" . $type->name] ? 1 : 0, 0)) . '
					 />
					<label for="prism_gallery_show_on_post_type_' . $type->name . '" class="prism_gallery_inline_checkbox_label">' . $type->labels->name . '</label>';
		}
	}
	
	return $output;
}


/**
 * Registers each Prism Gallery setting to the media settings page
 *
 * @since 1.7
 */
function prism_gallery_add_settings()
{
	global $prism_portfolio->Gallery;

	prism_gallery_do_settings();
	
	$settings = $prism_portfolio->Gallery->settings;
	$options = get_option('prism_portfolio_gallery');
	
	foreach( $settings as $key => $val )
	{
		if( false !== $val['display'] )
		{
			$name = $key;
			$type = $val['type'];
			$current = isset($options[$key]) ? "'" . $options[$key] . "'" : 0;
			$values = isset($val['values'])  ? "'" . $val['values'] . "'" : 0;
			$disabled = ('disabled' === $val['display']) ? '1' : '0';

			$anon = "echo prism_gallery_options_fields(
						array(
							'name' => '" . $name . "',
							'type' => '" . $type . "',
							'current' => " . $current . ",
							'values' => " . $values . ",
							'disabled' => " . $disabled . "
						));";

			add_settings_field(
				$key, 
				$val['title'], 
				create_function(
					'', 
					$anon), 
				'media', 
				$val['section'] ? $val['section'] : 'prism_gallery_options'
			);
		}
	}
}


/**
 * Returns form elements for the media settings page
 *
 * @since 1.7
 */
function prism_gallery_options_fields( $args )
{
	global $_wp_additional_image_sizes;
	
	$name_id = 'name="prism_gallery[' . $args['name'] . ']" id="prism_gallery_' . $args['name'] . '"';
	$ro = true == $args['disabled'] ? ' readonly="readonly"' : '';
	
	if( in_array($args['type'], array('checkbox', 'button')) )
		$ro = true == $args['disabled'] ? ' disabled="disabled"' : '';
	
	if( 'intermediate_image_sizes' == $args['type'] )
	{
		$checked = '';
		$size = $args["name"];
		
		if( "1" == get_option($size . "_crop") || (isset($_wp_additional_image_sizes[$size]['crop']) && 1 == $_wp_additional_image_sizes[$size]['crop']) )
			$checked = ' checked="checked" ';
		
		if( "medium" == $size )
		{	
			$output = 
			'<input name="medium_crop" id="medium_crop" value="1" ' . $checked . ' type="checkbox" />
			 <label for="medium_crop">' . __('Crop medium size to exact dimensions', 'prism_portfolio') . '</label>';
		}
		elseif( "large" == $size )
		{	
			$output = 
			'<input name="large_crop" id="large_crop" value="1" ' . $checked . ' type="checkbox" />
			 <label for="large_crop">' . __('Crop large size to exact dimensions', 'prism_portfolio') . '</label>';
		}
		else
		{
			$size_w = get_option($size . "_size_w");
			$size_h = get_option($size . "_size_h");
			
			if( ! is_numeric($size_w) )
				$size_w = $_wp_additional_image_sizes[$size]['width'];
			
			if( ! is_numeric($size_h) )
				$size_h = $_wp_additional_image_sizes[$size]['height'];
			
			$output = 
			'<label for="'  . $size . '_size_w">' . __("Width", 'prism_portfolio') . '</label>
			 <input name="' . $size . '_size_w" id="' . $size . '_size_w" value="' . $size_w . '" class="small-text" type="text" />
			 <label for="'  . $size . '_size_h">' . __("Height", 'prism_portfolio') . '</label>
			 <input name="' . $size . '_size_h" id="' . $size . '_size_h" value="' . $size_h . '" class="small-text" type="text" /><br />
			 <input name="' . $size . '_crop" id="' . $size . '_crop" value="1" ' . $checked . ' type="checkbox" />
			 <label for="'  . $size . '_crop">' . sprintf(__('Crop %s size to exact dimensions', 'prism_portfolio'), $size) . '</label>';
		}
		
		return $output;
	}

	switch( $args['type'] )
	{
		case 'checkbox' :
			if( false != $args['values'] )
				return $args['values'];
			return '<input class="prism_gallery_checkbox ' . $args['name'] . '" type="checkbox" ' . $name_id . ' value="1"' . checked('1', true == $args['current'] ? 1 : 0, false) . $ro . ' />';
			break;
		case 'select' :
			return '<select class="prism_gallery_select ' . $args['name'] . '" ' . $name_id . $ro . '>' . $args['values'] . '</select>';
			break;
		case 'textarea' :
			return '<textarea cols="51" rows="5" class="prism_gallery_textarea ' . $args['name'] . '" ' . $name_id . $ro . '>' . $args['current'] . '</textarea>';
			break;
		case 'text' :
			return '<input size="63" class="prism_gallery_text ' . $args['name'] . '" type="text" ' . $name_id . ' value="' . $args['current'] . '"' . $ro . ' />';
			break;
	}
}

?>