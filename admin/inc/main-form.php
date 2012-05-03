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
			<input type="button" value="<?php _e("Adjust media settings", "prism_portfolio"); ?>" title="<?php _e("Adjust media settings", "prism_portfolio"); ?>" class="button thickbox" alt="<?php echo admin_url("options-media.php"); ?>?TB_iframe=1" id="prism_gallery_adjust_media_settings"  />
			<input type="button" value="<?php _e("Open help file", "prism_portfolio"); ?>" title="<?php _e("Open help file", "prism_portfolio"); ?>" class="button thickbox" alt="<?php echo PRISM_GALLERY_URL; ?>/help/index.html?TB_iframe=1" id="prism_gallery_open_help"  />
		</div>
		
	</div><!-- / pg_butons-->
	
	<div id="prism-gallery-content">
	
	<?php if( (isset($prism_gallery_options["display_gallery_fieldset"]) && true == $prism_gallery_options["display_gallery_fieldset"]) || (isset($prism_gallery_options["display_single_fieldset"]) && true == $prism_gallery_options["display_single_fieldset"]) ) : ?>
		<p id="pg_info">
			<?php _e("Insert checked attachments into post as", "prism_portfolio"); ?>:
		</p>
	<?php endif; ?>
	
		<fieldset id="prism_gallery_gallery_options"<?php if( false == $prism_gallery_options["display_gallery_fieldset"] ){ echo ' class="hidden"'; } ?>>
		
			<legend class="button-primary" id="prism_gallery_send_gallery_legend"><?php _e("Insert a gallery", "prism_portfolio"); ?>:</legend>
			<input type="button" id="prism_gallery_hide_gallery_options" class="<?php if( false == $gallery_state ){ echo 'closed'; }else{ echo 'open'; } ?>" title="<?php _e("show/hide this fieldset", "prism_portfolio"); ?>" />
	
			<div id="prism_gallery_toggler"<?php if( false == $gallery_state ){ echo ' style="display: none;"'; } ?>>
	
				<p>
					<label for="prism_gallery_size"><?php _e("size", "prism_portfolio"); ?>:</label>
					<select name="prism_gallery_size" id="prism_gallery_size">
					
						<option value="thumbnail"<?php if( "thumbnail" == $prism_gallery_options["default_image_size"]){ ?> selected="selected"<?php } ?>><?php _e('thumbnail', "prism_portfolio"); ?></option>
						<option value="medium"<?php if( "medium" == $prism_gallery_options["default_image_size"]){ ?> selected="selected"<?php } ?>><?php _e('medium', "prism_portfolio"); ?></option>
						<option value="large"<?php if( "large" == $prism_gallery_options["default_image_size"]){ ?> selected="selected"<?php } ?>><?php _e('large', "prism_portfolio"); ?></option>
						<option value="full"<?php if( "full" == $prism_gallery_options["default_image_size"]){ ?> selected="selected"<?php } ?>><?php _e('full', "prism_portfolio"); ?></option>
	
						<?php foreach( $sizes as $size ) : if( in_array($size, array('thumbnail', 'medium', 'large', 'full')) ){ continue; } ?>
						<option value="<?php echo $size; ?>"<?php if( $size == $prism_gallery_options["default_image_size"]){ ?> selected="selected"<?php } ?>><?php echo $size; ?></option>
						<?php endforeach; ?>
					</select>
				</p>
			
				<p>
					<label for="prism_gallery_linkto"><?php _e("link to", "prism_portfolio"); ?>:</label>
					<select name="prism_gallery_linkto" id="prism_gallery_linkto">
						<option value="none"<?php if( "none" == $prism_gallery_options["default_linkto"]){ ?> selected="selected"<?php } ?>><?php _e("nothing (do not link)", "prism_portfolio"); ?></option>
						<option value="file"<?php if( "file" == $prism_gallery_options["default_linkto"]){ ?> selected="selected"<?php } ?>><?php _e("file", "prism_portfolio"); ?></option>
						<option value="attachment"<?php if( "attachment" == $prism_gallery_options["default_linkto"]){ ?> selected="selected"<?php } ?>><?php _e("attachment page", "prism_portfolio"); ?></option>
						<option value="parent_post"<?php if( "parent_post" == $prism_gallery_options["default_linkto"]){ ?> selected="selected"<?php } ?>><?php _e("parent post", "prism_portfolio"); ?></option>
						<option value="external_url"<?php if( "external_url" == $prism_gallery_options["default_external_url"]){ ?> selected="selected"<?php } ?>><?php _e("external url", "prism_portfolio"); ?></option>
					</select>
				</p>
				
				<p id="prism_gallery_linksize_label">
					<label for="prism_gallery_linksize"><?php _e("linked image size", "prism_portfolio"); ?>:</label>
					<select name="prism_gallery_linksize" id="prism_gallery_linksize">
					<option value="thumbnail"<?php if( "thumbnail" == $prism_gallery_options["default_linked_image_size"]){ ?> selected="selected"<?php } ?>><?php _e('thumbnail', "prism_portfolio"); ?></option>
						<option value="medium"<?php if( "medium" == $prism_gallery_options["default_linked_image_size"]){ ?> selected="selected"<?php } ?>><?php _e('medium', "prism_portfolio"); ?></option>
						<option value="large"<?php if( "large" == $prism_gallery_options["default_linked_image_size"]){ ?> selected="selected"<?php } ?>><?php _e('large', "prism_portfolio"); ?></option>
						<option value="full"<?php if( "full" == $prism_gallery_options["default_linked_image_size"]){ ?> selected="selected"<?php } ?>><?php _e('full', "prism_portfolio"); ?></option>
	
						<?php foreach( $sizes as $size ) : if( in_array($size, array('thumbnail', 'medium', 'large', 'full')) ){ continue; } ?>
						<option value="<?php echo $size; ?>"<?php if( $size == $prism_gallery_options["default_linked_image_size"]){ ?> selected="selected"<?php } ?>><?php echo $size; ?></option>
						<?php endforeach; ?>
					</select>
				</p>
				
				<p id="prism_gallery_linkrel_label">
					<label for="prism_gallery_linkrel"><?php _e("link 'rel' attribute", "prism_portfolio"); ?>:</label>
					<select type="text" name="prism_gallery_linkrel" id="prism_gallery_linkrel">
						<option value="true">true (auto generated)</option>
						<option value="false">false</option>
					</select>
	
					<span id="prism_gallery_linkrel_custom_label">
						&nbsp;<em><?php _e('or', "prism_portfolio"); ?></em>&nbsp;
						<label for="prism_gallery_linkrel_custom"><?php _e("custom value", "prism_portfolio"); ?>:</label>
						<input type="text" name="prism_gallery_linkrel_custom" id="prism_gallery_linkrel_custom" value="" />
					</span>
				</p>
				
				<p id="prism_gallery_external_url_label">
					<label for="prism_gallery_external_url"><?php _e("external url", "prism_portfolio"); ?>:</label>
					<input type="text" name="prism_gallery_external_url" id="prism_gallery_external_url" value="<?php echo $prism_gallery_options["default_external_url"]; ?>" />
				</p>
				
				<p id="prism_gallery_linkclass_label">
					<label for="prism_gallery_linkclass"><?php _e("link class", "prism_portfolio"); ?>:</label>
					<input type="text" name="prism_gallery_linkclass" id="prism_gallery_linkclass" value="<?php echo $prism_gallery_options["default_linkclass"]; ?>" />
				</p>
			
				<p>
					<label for="prism_gallery_orderby"><?php _e("order", "prism_portfolio"); ?>:</label>
					<select name="prism_gallery_orderby" id="prism_gallery_orderby">
						<option value="default"<?php if( "default" == $prism_gallery_options["default_orderby"]){ ?> selected="selected"<?php } ?>><?php _e("file gallery", "prism_portfolio"); ?></option>
						<option value="rand"<?php if( "rand" == $prism_gallery_options["default_orderby"]){ ?> selected="selected"<?php } ?>><?php _e("random", "prism_portfolio"); ?></option>
						<option value="menu_order"<?php if( "menu_order" == $prism_gallery_options["default_orderby"]){ ?> selected="selected"<?php } ?>><?php _e("menu order", "prism_portfolio"); ?></option>
						<option value="title"<?php if( "title" == $prism_gallery_options["default_orderby"]){ ?> selected="selected"<?php } ?>><?php _e("title", "prism_portfolio"); ?></option>
						<option value="ID"<?php if( "ID" == $prism_gallery_options["default_orderby"]){ ?> selected="selected"<?php } ?>><?php _e("date / time", "prism_portfolio"); ?></option>
					</select>
					<select name="prism_gallery_order" id="prism_gallery_order">
						<option value="ASC"<?php if( "ASC" == $prism_gallery_options["default_order"]){ ?> selected="selected"<?php } ?>><?php _e("ASC", "prism_portfolio"); ?></option>
						<option value="DESC"<?php if( "DESC" == $prism_gallery_options["default_order"]){ ?> selected="selected"<?php } ?>><?php _e("DESC", "prism_portfolio"); ?></option>
					</select>
				</p>
			
				<p>
					<label for="prism_gallery_template"><?php _e("template", "prism_portfolio"); ?>:</label>
					<select name="prism_gallery_template" id="prism_gallery_template">
						<?php
							$prism_gallery_templates = null;
					
							foreach( $prism_gallery_templates as $template_name )
							{
								$templates_dropdown .= "<option value=\"" . $template_name . "\"";
								
								if( $prism_gallery_options["default_template"] == $template_name )
									$templates_dropdown .= ' selected="selected"';
								
								$templates_dropdown .=">" . $template_name . "</option>\n";
							}
							
							echo $templates_dropdown;
						?>
					</select>
				</p>
				
				<p>
					<label for="prism_gallery_galleryclass"><?php _e("gallery class", "prism_portfolio"); ?>:</label>
					<input type="text" name="prism_gallery_galleryclass" id="prism_gallery_galleryclass" value="<?php echo $prism_gallery_options["default_galleryclass"]; ?>" />
				</p>
		
				<p>
					<label for="prism_gallery_imageclass"><?php _e("image class", "prism_portfolio"); ?>:</label>
					<input type="text" name="prism_gallery_imageclass" id="prism_gallery_imageclass" value="<?php echo $prism_gallery_options["default_imageclass"]; ?>" />
				</p>
				
				<p>
					<label for="prism_gallery_mimetype"><?php _e("mime type", "prism_portfolio"); ?>:</label>
					<input type="text" name="prism_gallery_mimetype" id="prism_gallery_mimetype" value="<?php echo $prism_gallery_options["default_mimetype"]; ?>" />
				</p>
				
				<p>
					<label for="prism_gallery_limit"><?php _e("limit", "prism_portfolio"); ?>:</label>
					<input type="text" name="prism_gallery_limit" id="prism_gallery_limit" value="" />
				</p>
				
				<p>
					<label for="prism_gallery_offset"><?php _e("offset", "prism_portfolio"); ?>:</label>
					<input type="text" name="prism_gallery_offset" id="prism_gallery_offset" value="" />
				</p>
				
				<p id="prism_gallery_paginate_label">
					<label for="prism_gallery_paginate"><?php _e("paginate", "prism_portfolio"); ?>:</label>
					<select type="text" name="prism_gallery_paginate" id="prism_gallery_paginate">
						<option value="true">true</option>
						<option value="false">false</option>
					</select>
				</p>
				
				<p>
					<label for="prism_gallery_columns"><?php _e("columns", "prism_portfolio"); ?>:</label>
					<select name="prism_gallery_columns" id="prism_gallery_columns">
					<?php
						$col_def = $prism_gallery_options["default_columns"];
						
						for( $i=0; $i < 10; $i++ )
						{
							$selected = "";
	
							if( $i == $col_def )
								$selected = ' selected="selected"';
							
							echo '<option value="' . $i . '"' . $selected . '>' . $i . "</option>\n";
						}
						
					?>
					</select>
				</p>
				
				<p>
					<label for="prism_gallery_postid"><?php _e("Post ID:", "prism_portfolio"); ?></label>
					<input type="text" name="prism_gallery_postid" id="prism_gallery_postid" value="" />
				</p>
				
				<br />
				
				<input type="button" id="prism_gallery_send_gallery" value="<?php _e("Insert a gallery", "prism_portfolio"); ?>" class="button-primary" />&nbsp;
				
				<br class="clear" />
				
				<p id="pg_gallery_tags_container">
					<label for="pg_gallery_tags"><?php _e("Media tags", "prism_portfolio");?>:</label>
					<input type="text" id="pg_gallery_tags" name="pg_gallery_tags" value="<?php if( isset($_POST["tag_list"]) ){ echo $_POST["tag_list"];} ?>" />
		
					<label for="pg_gallery_tags_from"><?php _e("current post's attachments only?", "prism_portfolio"); ?></label>
					<input type="checkbox" id="pg_gallery_tags_from" name="pg_gallery_tags_from" checked="checked" />
				</p>
				
				<!--<input type="button" onclick="prism_gallery_preview_template(jQuery('#prism_gallery_template').val()); return false;" value="&uArr;" title="preview template" class="button" />-->
				
			</div>
			
		</fieldset>
		
	<?php if( false == $prism_gallery_options["display_gallery_fieldset"] && true == $prism_gallery_options['insert_gallery_button'] ) : ?>
		<input type="button" class="button-primary" id="prism_gallery_send_gallery_legend" value="<?php _e("Insert a gallery", "prism_portfolio"); ?>" />
	<?php endif; ?>
		
		<!-- SINGLE IMAGE OPTIONS -->
	
		<fieldset id="prism_gallery_single_options"<?php if( false == $prism_gallery_options["display_single_fieldset"] ){ echo ' class="hidden"'; } ?>>
		
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
		
		<fieldset id="prism_gallery_tag_attachment_switcher">
		
			<input type="button" id="prism_gallery_switch_to_tags" value="<?php _e("Switch to tags", "prism_portfolio"); ?>" class="button" />
			<input type="hidden" id="files_or_tags" value="<?php echo $files_or_tags; ?>" />
		
		</fieldset>
	
	<?php if( false == $prism_gallery_options["display_single_fieldset"] && true == $prism_gallery_options['insert_single_button'] ) : ?>
		<input type="button" class="button-primary" id="prism_gallery_send_single_legend" value="<?php _e("Insert single files", "prism_portfolio"); ?>" />
	<?php endif; ?>
	
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