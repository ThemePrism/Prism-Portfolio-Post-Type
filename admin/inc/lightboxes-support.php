<?php

function prism_gallery_lightboxes_support( $value = '', $type = '', $args = array() )
{
	$options = get_option('prism_portfolio_gallery');

	$lightboxes_options = array
	(
		'colorbox' => array(
			'linkrel' => false,
			'linkclass' => false === $args['linkrel'] ? 'colorbox-link' : 'colorbox', 
			'imageclass' => false === $args['linkrel'] ? 'colorbox' : 'colorbox-' . $args['gallery_id']
		),
		'thickbox' => array(
			'linkrel' => 'thickbox-' . $args['gallery_id']
		),
		'fancybox' => array(
			'linkrel' => 'fancybox-' . $args['gallery_id']
		),
		'prettyPhoto' => array(
			'linkrel' => 'prettyPhoto[' . $args['gallery_id'] . ']'
		)
	);

	$lightboxes_options = apply_filters('prism_gallery_lightboxes_options', $lightboxes_options);
	
	if( defined('PRISM_GALLERY_LIGHTBOX_CLASSES') )
		$enqueued = unserialize(PRISM_GALLERY_LIGHTBOX_CLASSES);
	else
		$enqueued = explode(',', $options['auto_enqueued_scripts']);

	// if auto-enqueued scripts are set
	if( ! empty($enqueued) )
	{
		foreach( $enqueued as $script_name )
		{
			// supplemental value is set...
			if(	isset($lightboxes_options[$script_name][$type]) )
			{
				$search = $script_name;
			
				if( 'linkrel' == $type )
					$search .= '[' . $args['gallery_id'] . ']';

				// ...and it's not 'false'
				if(	false !== $lightboxes_options[$script_name][$type] )
				{
					/**
					 * if supplied value is not boolean ( == string || number)
					 * and script name is present in attribute value,
					 * just replace script name in attribute with supplied value.
					 *
					 * otherwise append supplied value to current attribute.
					 */
					if( ! is_bool($lightboxes_options[$script_name][$type]) && false !== strpos($args[$type], $script_name))
						return str_replace($search, $lightboxes_options[$script_name][$type], $value);
					else
						return $value . ' ' . $lightboxes_options[$script_name][$type];
				}
				// ...and it is 'false'
				elseif(	false === $lightboxes_options[$script_name][$type] )
				{
					return str_replace($search, '', $value);
				}
			}
		}
	}
	
	return $value;
}
add_filter('prism_gallery_lightbox_linkrel',    'prism_gallery_lightboxes_support', 10, 3);
add_filter('prism_gallery_lightbox_linkclass',  'prism_gallery_lightboxes_support', 10, 3);
add_filter('prism_gallery_lightbox_imageclass', 'prism_gallery_lightboxes_support', 10, 3);

?>