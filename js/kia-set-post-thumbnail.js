function KIASetAsThumbnail(id, nonce){  
	var $link = jQuery('a#wp-post-thumbnail-' + id);

	$link.text( setPostThumbnailL10n.saving );
	jQuery.post(ajaxurl, {
		action:"prism_set_thumbnail", post_id: post_id, thumbnail_id: id, _ajax_nonce: nonce, cookie: encodeURIComponent(document.cookie)
	}, function(str){
		var win = window.dialogArguments || opener || parent || top;  
		$link.text( setPostThumbnailL10n.setThumbnail );
		if ( str == '0' ) {
			alert( setPostThumbnailL10n.error );
		} else {
			jQuery('a.wp-post-thumbnail').show();
			$link.text( setPostThumbnailL10n.done );
			$link.fadeOut( 2000 );
					
			//display new thumbnail in the columns w/o refresh
			jQuery('#post-'+win.post_id + ' .postimagediv a', win.parent.document).html(str).fadeIn();
			
			//if successful close thickbox
			win.parent.tb_remove();
		} 
	}
	);
}