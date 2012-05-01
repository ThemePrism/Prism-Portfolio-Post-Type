if( "undefined" == typeof(prism_gallery) )
	var prism_gallery = { L10n : prism_gallery_L10n };

jQuery(document).ready(function()
{
	var admin_url    = ajaxurl.split("/wp-admin").shift() + "/wp-admin",
		current_tab  = window.location.toString().split("wp-admin/").pop(),
		fg_inex_href = current_tab + "&amp;exclude=current",
		fg_inex      = prism_gallery.L10n.exclude_current;

	if( -1 < current_tab.search("exclude=current") )
	{
		fg_inex_href = current_tab.replace(/&exclude=current/, "");
		fg_inex = prism_gallery.L10n.include_current;
	}
	
	// displays a link to include / exclude current post's attachments from media library listing
	jQuery("#filter .subsubsub").append('<li> | <a href="' + fg_inex_href + '">' + fg_inex + '</a></li>');

	// adds a checkbox to each attachment not already attached to current post
	jQuery(".media-item").each(function()
	{
		var cbh = jQuery(this).find(".bulk-media-tag-item"); // Media Tags plugin - thanks, alx359 :)

		if( cbh.length )
		{
			cbh.addClass("prism_gallery_attach_to_post");
		}
		else
		{
			if( ! jQuery(this).hasClass("child-of-" + post_id) )
				jQuery(this).prepend('<input type="checkbox" class="prism_gallery_attach_to_post solo" value="' + jQuery(this).attr('id').split('-').pop() + '" />');
			else
				jQuery(this).prepend('<input type="checkbox" class="prism_gallery_attach_to_post solo" checked="checked" disabled="disabled" />');
		}
	});
	
	// appends a div in which we display the ajax response
	jQuery("#library-form")
		.append('<div class="updated" style="margin: 0 18px 15px 0; display: none;"><p id="prism_gallery_attach_response" >&nbsp;</p></div><input type="button" class="button" id="prism_gallery_attach_button" value="' + prism_gallery.L10n.attach_all_checked_copy + '" />');
	
	// attaches checked attachments to current post
	jQuery("#prism_gallery_attach_button").bind("click", function()
	{
		jQuery.post
		(
			ajaxurl,
			{
				action  	: "prism_gallery_copy_attachments_to_post",
				post_id	 	: post_id,
				ids     	: jQuery.map(jQuery('.prism_gallery_attach_to_post:checked'),function(i){return jQuery(i).val();}).join(","),
				_ajax_nonce : prism_gallery_attach_nonce
			},
			function(response)
			{
				var data_vars    = response.split("#"),
					attached_ids = data_vars[0];
					response     = data_vars[1];
				
				jQuery('.prism_gallery_attach_to_post:checked').each(function()
				{
					jQuery(this).parents(".media-item").addClass("child-of-" + post_id);

					if( jQuery(this).hasClass("solo") )
						jQuery(this).prop("disabled", true);
					else
						jQuery(this).prop("checked", false);
				});
				
				jQuery('#prism_gallery_attach_response')
					.html(response)
					.parent()
						.fadeTo(0, 0)
						.css({"display" : "block"})
						.fadeTo(200, 1);
			},
			'html'
		);
	});
});