<div class="prism_meta">

<?php $attachment_meta = wp_get_attachment_metadata(3450); 
print_r($attachment_meta);

// adjust values here
$id = "_prism_gallery"; // this will be the name of form field. Image url(s) will be submitted in $_POST using this key. So if $id == “img1” then $_POST[“img1”] will have all the image urls
 
$svalue = get_post_meta(get_the_ID(),$id, true);  // this will be initial value of the above form field. Image urls.

$multiple = true; // allow multiple files upload
 
$width = null; // If you want to automatically resize all uploaded images then provide width here (in pixels)
 
$height = null; // If you want to automatically resize all uploaded images then provide height here (in pixels)
?>

<input type="hidden" name="<?php echo $id; ?>_nonce" value="<?php echo wp_create_nonce($id) ?>" />

<input type="hidden" name="<?php echo $id; ?>" id="<?php echo $id; ?>" class="image_url" value="<?php echo $svalue; ?>" />

<div class="plupload-ui hide-if-no-js <?php if ($multiple): ?>multiple<?php endif; ?>" id="<?php echo $id; ?>_plupload-ui">
	<div id="<?php echo $id; ?>_drag-drop-area" class="drag-drop-area">
		<div class="drag-drop-inside">
        	<p class="drag-drop-info"><?php _e('Drop files here','textdomain'); ?></p>
        	<p><?php _ex('or', 'Uploader: Drop files here - or - Select Files','textdomain'); ?></p>
        <p class="drag-drop-buttons"><input id="<?php echo $id; ?>_browse-button" type="button" value="<?php esc_attr_e('Select Files', 'textdomain'); ?>" class="button" /></p>
	    </div>
	</div>
	<?php if ($width && $height): ?>
            <input type="hidden" class="width" value="<?php echo $width;?>" />
            <input type="hidden" class="height" value="<?php echo $height;?>" />
    <?php endif; ?>
    <div class="filelist"></div>
</div>
<div class="plupload-thumbs <?php if ($multiple): ?>plupload-thumbs-multiple<?php endif; ?>" id="<?php echo $id; ?>_plupload-thumbs">
</div>
<div class="clear"></div>


</div>