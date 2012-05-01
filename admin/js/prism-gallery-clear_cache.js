if( "undefined" == typeof(prism_gallery) )
	var prism_gallery = { options : prism_gallery_options };

jQuery(document).ready(function()
{
	function prism_gallery_clear_cache_manual()
	{
		jQuery('#prism_gallery_response').stop().fadeTo(0, 1).html('<img src="' + ajaxurl.split("/admin-ajax.php").shift() + '/images/loading.gif" width="16" height="16" alt="loading" id="fg_loading_on_bar" />').show();
		
		jQuery.post
		(
			ajaxurl, 
			{
				action		: "prism_gallery_clear_cache_manual",
				_ajax_nonce	: prism_gallery.options.clear_cache_nonce
			},
			function(response)
			{
				jQuery('#prism_gallery_response').html(response).fadeOut(7500);
			},
			"html"
		);
	}
	
	jQuery("#prism_gallery_clear_cache_manual").live("click", function()
	{
		prism_gallery_clear_cache_manual();
		
		return false;
	});
});