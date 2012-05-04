<?php
/**
 * html form in which all the attachments are 
 * displayed on edit post screen in admin
 */ 

	$fgf_class = '';
	
	if( isset($prism_gallery_options['alt_color_scheme']) && true == $prism_gallery_options['alt_color_scheme'] )
		$fgf_class .= ' alternative-color-scheme';
	
	if( isset($prism_gallery_options["display_gallery_fieldset"]) && false == $prism_gallery_options["display_gallery_fieldset"] && isset($prism_gallery_options["display_single_fieldset"]) && false == $prism_gallery_options["display_single_fieldset"] )
		$fgf_class .= ' no-fieldsets';
	
	if( '' != $fgf_class )
		$fgf_class = ' class="' . trim($fgf_class) . '"';

?>
<div id="prism_gallery_response_inner"><?php echo $output; ?></div>

<div id="prism_gallery_form"<?php echo $fgf_class; ?>>

	<input type="hidden" name="data_collector"           id="data_collector"           value="" style="width: 90%" />
	<input type="hidden" name="data_collector_checked"   id="data_collector_checked"   value="<?php echo $checked_attachments; ?>" style="width: 90%" />
	<input type="hidden" name="data_collector_full"      id="data_collector_full"      value="" style="width: 90%" />
	<input type="hidden" name="prism_gallery_delete_what" id="prism_gallery_delete_what" value="<?php echo $delete_what; ?>" style="width: 90%" />
	<input type="hidden" name="prism_gallery_copies"      id="prism_gallery_copies"      value="" style="width: 90%" />
	<input type="hidden" name="prism_gallery_originals"   id="prism_gallery_originals"   value="" style="width: 90%" />
	
	<div id="pg_buttons"<?php if( ( ! isset($prism_gallery_options["display_gallery_fieldset"]) && ! isset($prism_gallery_options["display_single_fieldset"]) ) || ( isset($prism_gallery_options["display_gallery_fieldset"]) && isset($prism_gallery_options["display_single_fieldset"]) && true != $prism_gallery_options["display_gallery_fieldset"] && true != $prism_gallery_options["display_single_fieldset"] ) ){ echo ' class="alt"'; }?>>
	
		<input type="button" value="<?php _e("Refresh attachments", "prism_portfolio"); ?>" title="<?php _e("Refresh attachments", "prism_portfolio"); ?>" class="button" id="prism_gallery_refresh" />
	
		<div class="basic">
			<input type="button" value="<?php _e("Check all", "prism_portfolio"); ?>" title="<?php _e("Check all", "prism_portfolio"); ?>" class="button" id="prism_gallery_check_all" />
			<input type="button" value="<?php _e("Uncheck all", "prism_portfolio"); ?>" title="<?php _e("Uncheck all", "prism_portfolio"); ?>" class="button" id="prism_gallery_uncheck_all" />
			<input type="button" value="<?php _e("Delete all checked", "prism_portfolio"); ?>" title="<?php _e("Delete all checked", "prism_portfolio"); ?>" class="button" id="prism_gallery_delete_checked" />
			<input type="button" value="<?php _e("Detach all checked", "prism_portfolio"); ?>" title="<?php _e("Detach all checked", "prism_portfolio"); ?>" class="button" id="prism_gallery_detach_checked" />
		</div>
		
		<a id="prism_gallery_upload_media" href="media-upload.php?post_id=<?php echo $post_id;?>&amp;type=image&amp;TB_iframe=1&amp;tab=library" class="thickbox button" title="<?php _e('Upload new files', "prism_portfolio");?>"><?php _e('Add an Image', "prism_portfolio");?></a>
		
		<input type="button" value="<?php _e("Copy all attachments from another post", "prism_portfolio"); ?>" title="<?php _e("Copy all attachments from another post", "prism_portfolio"); ?>" class="button" id="prism_gallery_copy_all" />
	
		<div class="additional">
			<input type="button" value="<?php _e("Save attachment order", "prism_portfolio"); ?>" title="<?php _e("Save attachment order", "prism_portfolio"); ?>" class="button" id="prism_gallery_save_menu_order" />
			<input type="button" value="<?php _e("Clear Prism Gallery cache", "prism_portfolio"); ?>" title="<?php _e("Clear Prism Gallery cache", "prism_portfolio"); ?>" class="button" id="prism_gallery_clear_cache_manual" />
			<input type="button" value="<?php _e("Open help file", "prism_portfolio"); ?>" title="<?php _e("Open help file", "prism_portfolio"); ?>" class="button thickbox" alt="<?php echo PRISM_GALLERY_URL; ?>/help/index.html?TB_iframe=1" id="prism_gallery_open_help"  />
		</div>
		
	</div><!-- / pg_butons-->
	
	<div id="prism-gallery-content">
	
		<p id="pg_info">
			<?php _e("Insert checked attachments into post as", "prism_portfolio"); ?>:
		</p>
		
		<!-- SINGLE IMAGE OPTIONS -->
	
		<fieldset id="prism_gallery_single_options" >
		
			<legend class="button-primary" id="prism_gallery_send_single_legend"><?php _e("Insert single files", "prism_portfolio"); ?>:</legend>
			<input type="button" id="prism_gallery_hide_single_options" class="<?php if( false == $single_state ){ echo 'closed'; }else{ echo 'open'; } ?>" title="<?php _e("show/hide this fieldset", "prism_portfolio"); ?>" />
	
			<div id="prism_gallery_single_toggler"<?php if( false == $single_state ){ echo ' style="display: none;"'; } ?>>
				<p>
					<label for="prism_gallery_single_size"><?php _e("size", "prism_portfolio"); ?>:</label>
					<select name="prism_gallery_single_size" id="prism_gallery_single_size">
						<?php foreach( $sizes as $size ) : ?>
						<option value="<?php echo $size; ?>"<?php if( $size == $prism_gallery_options["single_default_image_size"]){ ?> selected="selected"<?php } ?>><?php echo $size; ?></option>
						<?php endforeach; ?>
					</select>
				</p>
				
				<p>
					<label for="prism_gallery_single_linkto"><?php _e("link to", "prism_portfolio"); ?>:</label>
					<select name="prism_gallery_single_linkto" id="prism_gallery_single_linkto">
						<option value="none"<?php if( "none" == $prism_gallery_options["single_default_linkto"]){ ?> selected="selected"<?php } ?>><?php _e("nothing (do not link)", "prism_portfolio"); ?></option>
						<option value="file"<?php if( "file" == $prism_gallery_options["single_default_linkto"]){ ?> selected="selected"<?php } ?>><?php _e("file", "prism_portfolio"); ?></option>
						<option value="attachment"<?php if( "attachment" == $prism_gallery_options["single_default_linkto"]){ ?> selected="selected"<?php } ?>><?php _e("attachment page", "prism_portfolio"); ?></option>
						<option value="parent_post"<?php if( "parent_post" == $prism_gallery_options["single_default_linkto"]){ ?> selected="selected"<?php } ?>><?php _e("parent post", "prism_portfolio"); ?></option>
						<option value="external_url"<?php if( "external_url" == $prism_gallery_options["single_default_external_url"]){ ?> selected="selected"<?php } ?>><?php _e("external url", "prism_portfolio"); ?></option>
					</select>
				</p>
				
				<p id="prism_gallery_single_external_url_label">
					<label for="prism_gallery_single_external_url"><?php _e("external url", "prism_portfolio"); ?>:</label>
					<input type="text" name="prism_gallery_single_external_url" id="prism_gallery_single_external_url" value="<?php echo $prism_gallery_options["single_default_external_url"]; ?>" />
				</p>
				
				<p id="prism_gallery_single_linkclass_label">
					<label for="prism_gallery_single_linkclass"><?php _e("link class", "prism_portfolio"); ?>:</label>
					<input type="text" name="prism_gallery_single_linkclass" id="prism_gallery_single_linkclass" value="<?php echo $prism_gallery_options["single_default_linkclass"]; ?>" />
				</p>
				
				<p>
					<label for="prism_gallery_single_imageclass"><?php _e("image class", "prism_portfolio"); ?>:</label>
					<input type="text" name="prism_gallery_single_imageclass" id="prism_gallery_single_imageclass" value="<?php echo $prism_gallery_options["single_default_imageclass"]; ?>" />
				</p>
				
				<p>
					<label for="prism_gallery_single_align"><?php _e("alignment", "prism_portfolio"); ?>:</label>
					<select name="prism_gallery_single_align" id="prism_gallery_single_align">
						<option value="none"<?php if( "none" == $prism_gallery_options["single_default_align"]){ ?> selected="selected"<?php } ?>><?php _e("none", "prism_portfolio"); ?></option>
						<option value="left"<?php if( "left" == $prism_gallery_options["single_default_align"]){ ?> selected="selected"<?php } ?>><?php _e("left", "prism_portfolio"); ?></option>
						<option value="right"<?php if( "right" == $prism_gallery_options["single_default_align"]){ ?> selected="selected"<?php } ?>><?php _e("right", "prism_portfolio"); ?></option>
						<option value="center"<?php if( "center" == $prism_gallery_options["single_default_align"]){ ?> selected="selected"<?php } ?>><?php _e("center", "prism_portfolio"); ?></option>
					</select>
				</p>
				
				<p>
					<label for="prism_gallery_single_caption"><?php _e("display caption?", "prism_portfolio"); ?></label>
					<input type="checkbox" id="prism_gallery_single_caption" name="prism_gallery_single_caption" checked="checked" />
				</p>
				
				<br />
				
				<input type="button" id="prism_gallery_send_single" value="<?php _e("Insert single files", "prism_portfolio"); ?>" class="button-primary" />&nbsp;
			</div>
			
		</fieldset>
	
		<input type="button" class="button-primary" id="prism_gallery_send_single_legend" value="<?php _e("Insert single files", "prism_portfolio"); ?>" />
			
		<div id="prism_gallery_attachment_list">
		
			<p id="prism_gallery_attachments_sorting">
				<label for="prism_gallery_attachments_sortby"><?php _e('Sort attachments by', "prism_portfolio"); ?></label>
			
				<select id="prism_gallery_attachments_sortby">
					<option value="menu_order"<?php if( 'menu_order' == $attachment_orderby){ echo ' selected="selected"'; } ?>><?php _e('menu order', "prism_portfolio"); ?></option>
					<option value="post_title"<?php if( 'post_title' == $attachment_orderby){ echo ' selected="selected"'; } ?>><?php _e('title', "prism_portfolio"); ?></option>
					<option value="post_name"<?php if( 'post_name' == $attachment_orderby){ echo ' selected="selected"'; } ?>><?php _e('name', "prism_portfolio"); ?></option>
					<option value="post_date"<?php if( 'post_date' == $attachment_orderby){ echo ' selected="selected"'; } ?>><?php _e('date', "prism_portfolio"); ?></option>
				</select>
				
				<select id="prism_gallery_attachments_sort">
					<option value="ASC"<?php if( 'ASC' == $attachment_order){ echo ' selected="selected"'; } ?>><?php _e('ASC', "prism_portfolio"); ?></option>
					<option value="DESC"<?php if( 'DESC' == $attachment_order){ echo ' selected="selected"'; } ?>><?php _e('DESC', "prism_portfolio"); ?></option>
				</select>
				
				<input type="button" id="prism_gallery_attachments_sort_submit" class="button" value="<?php _e('Go', "prism_portfolio"); ?>" />
			</p>
			
			<a href="#" id="prism_gallery_save_menu_order_link" class="button button-secondary"><?php _e("Save attachment order", "prism_portfolio"); ?></a>
		
			<?php echo Prism_Portfolio_Ajax::list_attachments($count_attachments, $post_id, $attachment_order, $checked_attachments, $attachment_orderby); ?>
		</div>
		
		<div id="prism_gallery_tag_list">
			<?php Prism_Portfolio_Ajax::list_tags( array("link" => true, "separator" => " ") ); ?>
		</div>
	
	</div><!-- / prism-gallery-content -->
	
</div>

<?php

// prints number of attachments
$print_attachment_count = __("Prism Gallery &mdash; %d attachment.", "prism_portfolio");

if( 0 == $count_attachments || $count_attachments > 1 )
	$print_attachment_count = __("Prism Gallery &mdash; %d attachments.", "prism_portfolio");

echo '<script type="text/javascript">
		if( ' . $count_attachments . ' )
			jQuery("#prism_gallery").addClass("has-attachments").removeClass("no-attachments");
		else
			jQuery("#prism_gallery").removeClass("has-attachments").addClass("no-attachments");
			
		jQuery("#prism_gallery .hndle").html("<span>' . sprintf($print_attachment_count, $count_attachments) . '</span>");
	  </script>';
?>