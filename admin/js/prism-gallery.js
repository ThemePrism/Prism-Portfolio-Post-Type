var prism_gallery =
{
	L10n : Prism_Portfolio_Settings.prism_gallery_L10n,
	options : Prism_Portfolio_Settings.prism_gallery_options
};

jQuery(document).ready(function($){	

	$.extend(prism_gallery,
	{
		gallery_image_clicked : false,
		refreshed : false,
		tmp : 1,
		
		
		/**
		 * takes care of communication with tinyMCE
		 */
		tinymce : function( retry )
		{
			if( "undefined" === typeof(tinymce) )
				return;
			
			// get editor instance
			var ed = tinymce.EditorManager.get("content");
			
			if( ! ed )
			{
				if( retry )
					return false;
				
				setTimeout(function(){ prism_gallery.tinymce( true ); }, 500);
				return false;
			}
			
			// trigger prism_gallery.tinymce_gallery() if clicked-on image has a wpGallery class
			ed.onClick.add( function(tinymce_object, mouseEvent)
			{
				if( mouseEvent.target.className.match(/wpGallery/) )
				{
					// call tinymce_gallery with image title as argument (title holds gallery options)
					prism_gallery.tinymce_gallery( mouseEvent.target.title );
					prism_gallery.gallery_image_clicked = true;
				}
				/*
				else if( "IMG" == mouseEvent.target.nodeName )
				{
					prism_gallery.tinymce_single_image( mouseEvent.target );
				}
				*/
				else
				{
					// uncheck all items and serialize()
					$("#prism_gallery_uncheck_all").trigger("click");
					prism_gallery.gallery_image_clicked = false;
				}
			});

			// clear options on delete
			ed.onEvent.add(function(ed, e)
			{
				if( 46 === e.keyCode && "keyup" == e.type && true === prism_gallery.gallery_image_clicked )
				{					
					$("#prism_gallery_uncheck_all").trigger("click");
					prism_gallery.gallery_image_clicked = false;
				}
			});
		},


		/**
		 * updates the contents of [gallery] shortcode
		 */
		tinymce_change_gallery_content : function( serial )
		{
			if( "undefined" === typeof(tinymce) )
				return;
			
			// skips setContent for webkit browsers if tinyMCE version is below 3.3.6
			if( (! $.browser.webkit && ! $.browser.safari) || (3 <= parseFloat(tinymce.majorVersion) && 3.6 <= parseFloat(tinymce.minorVersion)) )
			{
				var ed = tinymce.EditorManager.get("content"),
					new_content = serial.replace(/\[gallery([^\]]*)\]/g, function(a,b)
					{
						return "<img src='" + tinymce.baseURL + "/plugins/wpgallery/img/t.gif' class='wpGallery mceItem' title='gallery" + tinymce.DOM.encode(b).replace(/\[/, '\[').replace(/\]/, '\]') + "' id='prism_gallery_tmp_" + prism_gallery.tmp + "' />";
					});
				
				ed.focus();
				ed.selection.setContent(new_content);
				
				ed.selection.select(ed.getDoc().getElementById("prism_gallery_tmp_" + prism_gallery.tmp));
				tinyMCE.execCommand("mceFocus", false, "content");
				
				prism_gallery.tmp++;
			}
		},



		/**
		 * sets up the file gallery options when clicked on a gallery already
		 * inserted into visual editor
		 */
		tinymce_gallery : function( title )
		{
			var opt = title.replace("gallery", ""), // gets gallery options from image title
				attachment_ids = opt.match(/attachment_ids=['"]([0-9,]+)['"]/),
				attachment_includes = opt.match(/include=['"]([0-9,]+)['"]/),
				post_id = opt.match(/id=['"](\d+)['"]/),
				size = opt.match(/\ssize=['"]([^'"]+)['"]/i),
				linkto = opt.match(/link=['"]([^'"]+)['"]/i),
				thelink = linkto ? linkto[1] : "attachment",
				linkrel = opt.match(/rel=['"]([^'"]+)['"]/i),
				linksize = opt.match(/link_size=['"]([^'"]+)['"]/i),
				external_url = '',
				template = opt.match(/template=['"]([^'"]+)['"]/i),
				order = opt.match(/order=['"]([^'"]+)['"]/i),
				orderby = opt.match(/orderby=['"]([^'"]+)['"]/i),
				linkclass = opt.match(/linkclass=['"]([^'"]+)['"]/i),
				imageclass = opt.match(/imageclass=['"]([^'"]+)['"]/i),
				galleryclass = opt.match(/galleryclass=['"]([^'"]+)['"]/i),
				mimetype = opt.match(/mimetype=['"]([^'"]+)['"]/i),
				limit = opt.match(/limit=['"](\d+)['"]/),
				offset = opt.match(/offset=['"](\d+)['"]/),
				paginate = opt.match(/paginate=['"]([^'"]+)['"]/i),
				columns = opt.match(/columns=['"](\d+)['"]/),
				tags = opt.match(/tags=['"]([^'"]+)['"]/i),
				tags_from = opt.match(/tags_from=['"]([^'"]+)['"]/i);

			if( linkto && "none" != thelink && "file" != thelink && "parent_post" != thelink )
			{
				external_url = decodeURIComponent(thelink);
				thelink = "external_url";
			}
			
			$("#prism_gallery_postid").val( post_id ? post_id[1] : ""  );
			$("#prism_gallery_size").val(size ? size[1] : "thumbnail" );
			$("#prism_gallery_linkto").val( thelink );
			$("#prism_gallery_linkrel").val(linkrel ? linkrel[1] : "true" );
			$("#prism_gallery_linksize").val(linksize ? linksize[1] : "full" );
			$("#prism_gallery_external_url").val( external_url );
			$("#prism_gallery_template").val(template ? template[1] : "default" );
			$("#prism_gallery_order").val(order ? order[1] : "ASC" );
			$("#prism_gallery_orderby").val(orderby ? orderby[1] : "file gallery" );
			$("#prism_gallery_linkclass").val(linkclass ? linkclass[1] : "" );
			$("#prism_gallery_imageclass").val(imageclass ? imageclass[1] : "" );
			$("#prism_gallery_galleryclass").val(galleryclass ? galleryclass[1] : "" );
			$("#prism_gallery_mimetype").val(mimetype ? mimetype[1] : "" );
			$("#prism_gallery_limit").val(limit ? limit[1] : "" );
			$("#prism_gallery_offset").val(offset ? offset[1] : "" );
			$("#prism_gallery_paginate").val(paginate ? paginate[1] : "false" );
			$("#prism_gallery_columns").val(columns ? columns[1] : "3" );
			
			if( linkrel && "true" != linkrel[1] && "false" != linkrel[1])
			{
				$("#prism_gallery_linkrel").val("true");
				$("#prism_gallery_linkrel_custom").val( linkrel[1].replace(/\\\[/, '[').replace(/\\\]/, ']') );
			}
			
			if( tags )
			{
				$("#fg_gallery_tags").val(tags[1]);
				$("#files_or_tags").val("tags");
				prism_gallery.files_or_tags( false );
				
				if( tags_from )
					$("#fg_gallery_tags_from").prop("checked", false);
				else
					$("#fg_gallery_tags_from").prop("checked", true);
				
				$("#prism_gallery_toggler").show();
			}
			else
			{
				$("#files_or_tags").val("files");
				prism_gallery.files_or_tags( false );
			}

			if( null !== attachment_ids )
				attachment_ids = attachment_ids[1].split(",");
			else if( null !== attachment_includes )
				attachment_ids = attachment_includes[1].split(",");
			else
				attachment_ids = "all";
			
			if( 0 < prism_gallery.options.num_attachments )
			{
				$("#prism_gallery_uncheck_all").trigger("click_tinymce_gallery");
				
				$("#pg_container .sortableitem .checker").map(function()
				{
					if( "all" === attachment_ids || -1 < attachment_ids.indexOf($(this).attr("id").replace("att-chk-", "")) )
					{
						$(this).parents(".sortableitem").addClass("selected");
						return this.checked = true;
					}
				});
				
				prism_gallery.serialize("tinymce_gallery");
			}
		},


		/**
		 * collapses selection if gallery placeholder is clicked
		 */
		tinymce_deselect : function( force )
		{
			if( "undefined" === typeof(tinymce) )
				return;
			
			if( "undefined" === typeof(force) )
				force = false;
			
			if( false === prism_gallery.gallery_image_clicked && false === force )
				return;

			var ed = tinymce.EditorManager.get("content");

			if( force && 0 < $("#TB_overlay").length )
				return setTimeout( function(){ prism_gallery.tinymce_deselect( force ); }, 100 );
			
			if( "undefined" !== typeof(ed) )
			{
				if( ed.selection )
					ed.selection.collapse(false);
			
				tinyMCE.execCommand("mceRepaint", false, "content");
				tinyMCE.execCommand("mceFocus", false, "content");
			}
		},


		/**
		 * checks if all the attachments are, eh, checked...
		 */
		is_all_checked : function()
		{
			var all_checked = true;
			
			$("#pg_container .sortableitem .checker").map(function()
			{
				if( ! this.checked )
				{
					all_checked = false;
					// return as soon as an unchecked item is found
					return;
				}
			});
			
			return all_checked;
		},


		/**
		 * loads main file gallery data via ajax
		 */
		init : function( response_message )
		{  
			var tags_from = $("#fg_gallery_tags_from").prop("checked"), 
				container = $("#pg_container"), 
				fieldsets = $("#prism_gallery_fieldsets").val(),
				data = null,
				attachment_order = $("#data_collector_full").val();
			
			if( 0 === $("#prism_gallery_response").length )
				$("#prism_gallery.postbox").prepend('<div id="prism_gallery_response"></div>'); 
			
			if( "return_from_single_attachment" == response_message )
			{
				prism_gallery.tinymce_deselect();
			}
			else if( "refreshed" == response_message )
			{
				prism_gallery.refreshed = true;
			}
			else if( "sorted" == response_message )
			{
				prism_gallery.refreshed = true;
				attachment_order = $("#prism_gallery_attachments_sort").val();
			}
			
			if( "undefined" == typeof(fieldsets) )
				fieldsets = "";
			
			if( true === tags_from || "undefined" == typeof( tags_from ) || "undefined" == tags_from )
				tags_from = true;
			else
				tags_from = false;

			data = {
				action				: "prism_gallery_load",
				post_id 			: $("#post_ID").val(),
				attachment_order 	: attachment_order,
				attachment_orderby 	: $("#prism_gallery_attachments_sortby").val(),
				checked_attachments : $("#data_collector_checked").val(),
				files_or_tags 		: $("#files_or_tags").val(),
				tag_list 			: $("#fg_gallery_tags").val(),
				tags_from 			: tags_from,
				fieldsets			: fieldsets,
				_ajax_nonce			: prism_gallery.options.prism_gallery_nonce
			};
			
			response_message = null;

			container
				.empty()
				.append('<p class="loading_image"><img src="' + prism_gallery.options.prism_gallery_img + '/ajax-loader.gif" alt="' + prism_gallery.L10n.loading_attachments + '" /><br />' + prism_gallery.L10n.loading_attachments + '<br /></p>')
				.css({height : "auto"})
				.show();
			
			var request = $.post
			(
				ajaxurl, 
				data,
				function(response)
				{
					container.html(response);
					prism_gallery.setup();
				},
				"html"
			);

			return;
		},


		/**
		 * some basic show / hide setup
		 */
		setup : function()
		{
			var container = $("#pg_container"),
				files_or_tags = $("#files_or_tags");
			
			if( 0 === container.length || (0 === files_or_tags.length && 0 < $("prism_gallery_gallery_options").length) )
				return;

			prism_gallery.options.num_attachments = $("#pg_container #prism_gallery_list li").length;
			
			if( 0 < prism_gallery.options.num_attachments )
				$("#prism_gallery_copy_all").appendTo("#fg_buttons .advanced");
			else
				$("#prism_gallery_copy_all").appendTo("#fg_buttons");

			container.css({height : "auto"});
			$("#prism_gallery_switch_to_tags").show();
			
			// hide elements if post has no attachments
			if( 0 === prism_gallery.options.num_attachments )
			{				
				if( 0 === $("#fg_info").length )
					$("#prism_gallery_form").append('<div id="fg_info"></div>');
				
				$("#fg_info").html(prism_gallery.L10n.no_attachments_upload).show();
				$("#prism_gallery_upload_files").show();
				container.css({overflow:"hidden", paddingBottom: 0});
			}
			else
			{
				$("#prism_gallery fieldset").not(".hidden").show();
				container.css({overflow:"auto"});
				$("#prism_gallery_upload_files").hide();
			}
			
			// tags from current post only checkbox
			$("#fg_gallery_tags_from").prop("checked", ("false" == prism_gallery.options.tags_from) ? false : true);
			
			// clickable tags
			$(".fg_insert_tag").each( function()
			{
				var ct = "," + $("#fg_gallery_tags").val() + ",",
					ns = "," + $(this).attr("name") + ",",
					nn = "," + $(this).html() + ",";
				
				if ( -1 < ct.search(ns) || -1 < ct.search(nn) )
					$(this).addClass("selected");
				else
					$(this).removeClass("selected");
			});
			
			// display tags or attachments
			if( "undefined" == typeof( files_or_tags.val() ) || "undefined" == files_or_tags.val() )
				files_or_tags.val("tags");

			// load files / tags respectively
			prism_gallery.files_or_tags( true );
			prism_gallery.do_plugins();
			prism_gallery.serialize();
			prism_gallery.tinymce();
			prism_gallery.fieldset_toggle();
		},


		/**
		 * processes attachments data, builds the [gallery] shortcode
		 */
		serialize : function( internal_event )
		{
			var serial = "",
				id = ""
				size = "",
				linkto = "",
				linkrel = "",
				linksize = "",
				linkto_val = $("#prism_gallery_linkto").val(),
				external_url = $("#prism_gallery_external_url").val(),
				template = "",
				order = "",
				orderby = "",
				linkclass = "",
				imageclass = "",
				galleryclass = "",
				mimetype = "",
				limit = "",
				offset = "",
				paginate = "",
				columns = "",
				tags = "",
				tags_from = "",
				ctlen = ""
				ct = "",
				ns = "",
				nn = "",
				copies = "",
				originals = "",
				prism_gallery_order = "",
				prism_gallery_orderby = "";
			
			if( "undefined" == typeof(internal_event) )
				internal_event = "normal";
			
			if( "" != $("#prism_gallery_linkrel_custom").val() && "undefined" != typeof($("#prism_gallery_linkrel_custom").val()) )
			{
				$("#prism_gallery_linkrel_custom").val( $("#prism_gallery_linkrel_custom").val().replace(/\[/, '').replace(/\]/, '') );
				linkrel = ' rel="' + $("#prism_gallery_linkrel_custom").val() + '"';
			}
			else if( "false" == $("#prism_gallery_linkrel").val() )
			{
				linkrel = ' rel="false"';
			}

			if( "external_url" == linkto_val )
				linkto_val = encodeURIComponent(external_url);


			// tags
			if( 0 < $("#fg_gallery_tags").length )
			{
				if( "undefined" == typeof( $("#fg_gallery_tags").val() ) || "undefined" == $("#fg_gallery_tags").val() )
					$("#fg_gallery_tags").val("");
				
				tags      = $("#fg_gallery_tags").val();
				tags_from = $("#fg_gallery_tags_from").prop("checked");
				
				tags = tags.replace(/\s+/g, " ").replace(/\s+,/g, ",").replace(/,+\s*/g, ",");
			
				ctlen = tags.length;
				
				if( "," == tags[0] )
					tags = tags.substring(1);
				
				if( "," == tags[ctlen-2] )
					tags = tags.substring(0, ctlen-1);
			
				$("#fg_gallery_tags").val(tags);
				
				$(".fg_insert_tag").each( function()
				{
					ct = "," + $("#fg_gallery_tags").val() + ",";
					ns = "," + $(this).attr("name") + ",";
					nn = "," + $(this).html() + ",";
					
					if ( -1 < ct.search(ns) || -1 < ct.search(nn) )
						$(this).addClass("selected");
					else
						$(this).removeClass("selected");
				});
			}


			if( 0 < prism_gallery.options.num_attachments )
				serial = $("#prism_gallery_list").sortable("serialize");
			
			serial = serial.toString().replace(/image\[\]=/g, '').replace(/&/g, ',').replace(/,+/g, ',');
			$("#data_collector_full").val(serial);
			
			// get checked items
			serial = prism_gallery.map("checked", serial);
			$("#data_collector_checked").val(serial);
			
			// get checked copies
			copies = prism_gallery.map("copy", serial);
			$("#prism_gallery_copies").val(copies);
		
			// get checked originals
			originals = prism_gallery.map("has_copies", serial);
			$("#prism_gallery_originals").val(originals);
			
			if( "" == $("#prism_gallery_originals").val() && "" == $("#prism_gallery_copies").val() )
				$("#prism_gallery_delete_what").val("all");
			
			prism_gallery_order   = $("#prism_gallery_order");
			prism_gallery_orderby = $("#prism_gallery_orderby");
			
			order = ' order="' + prism_gallery_order.val() + '"';
				
			if( "default" != prism_gallery_orderby.val() )
			{
				if( "rand" == prism_gallery_orderby.val() )
				{
					prism_gallery_order.hide();
					order = "";
				}
				else
				{
					prism_gallery_order.css({display : "inline"});
				}
				
				orderby = ' orderby="' + prism_gallery_orderby.val() + '"';
			}
			else
			{
				prism_gallery_order.hide();
				order = "";
				orderby = "";
			}
			
			if( "external_url" == $("#prism_gallery_linkto").val() )
				$("#prism_gallery_external_url_label").show();
			else
				$("#prism_gallery_external_url_label").hide();
			
			if( "external_url" == $("#prism_gallery_single_linkto").val() )
				$("#prism_gallery_single_external_url_label").show();
			else
				$("#prism_gallery_single_external_url_label").hide();

			if( "none" == $("#prism_gallery_linkto").val() )
				$("#prism_gallery_linkclass_label").hide();
			else
				$("#prism_gallery_linkclass_label").show();

			if( "none" == $("#prism_gallery_single_linkto").val() )
				$("#prism_gallery_single_linkclass_label").hide();
			else
				$("#prism_gallery_single_linkclass_label").show();
			
			if( 0 < Number($("#prism_gallery_limit").val()) )
				$("#prism_gallery_paginate_label").show();
			else
				$("#prism_gallery_paginate_label").hide();
			
			if( "file" == $("#prism_gallery_linkto").val() || "external_url" == $("#prism_gallery_linkto").val())
			{
				$("#prism_gallery_linksize_label").show();
				$("#prism_gallery_linkrel_custom_label").show();

				if( "full" != $("#prism_gallery_linksize").val() )
					linksize = ' link_size="' + $("#prism_gallery_linksize").val() + '"';
			}
			else
			{
				$("#prism_gallery_linksize_label").hide();
				linksize = "";
			}
			
			
			if( tags_from )
				tags_from = "";
			else
				tags_from = ' tags_from="all"';
		
			if( "" != tags )
				serial = '[gallery tags="' + tags + '"' + tags_from;
			else if( "" != serial && false === prism_gallery.is_all_checked() )
				serial = '[gallery include="' + serial + '"';
			else
				serial = '[gallery';
		
			if( "thumbnail" != $("#prism_gallery_size").val() )
				size = ' size="' + $("#prism_gallery_size").val() + '"';

			if( "attachment" != $("#prism_gallery_linkto").val() )
				linkto = ' link="' + linkto_val + '"';
		
			if( "default" != $("#prism_gallery_template").val() )
				template = ' template="' + $("#prism_gallery_template").val() + '"';
			
			if( "" != $("#prism_gallery_linkclass").val() && "none" != $("#prism_gallery_linkto").val() )
				linkclass = ' linkclass="' + $("#prism_gallery_linkclass").val() + '"';
			
			if( "" != $("#prism_gallery_imageclass").val() )
				imageclass = ' imageclass="' + $("#prism_gallery_imageclass").val() + '"';
			
			if( "" != $("#prism_gallery_galleryclass").val() )
				galleryclass = ' galleryclass="' + $("#prism_gallery_galleryclass").val() + '"';
			
			if( "" != $("#prism_gallery_mimetype").val() )
				mimetype = ' mimetype="' + $("#prism_gallery_mimetype").val() + '"';
				
			if( 0 < Number($("#prism_gallery_limit").val()) )
			{
				limit = ' limit="' + $("#prism_gallery_limit").val() + '"';
				
				if( "true" == $("#prism_gallery_paginate").val() )
					limit += ' paginate="true"';
			}
			
			if( 0 < Number($("#prism_gallery_offset").val()) )
				limit += ' offset="' + $("#prism_gallery_offset").val() + '"';
			
			if( "" != $("#prism_gallery_postid").val() )
				id = ' id="' + $("#prism_gallery_postid").val() + '"';
			
			if( "" != $("#prism_gallery_columns").val() && "3" != $("#prism_gallery_columns").val() )
				columns = ' columns="' + $("#prism_gallery_columns").val() + '"';
			
			serial += id + size + linkto + linksize + linkclass + imageclass + galleryclass + mimetype + limit + order + orderby + template + columns + linkrel + "]\n";
			
			$("#data_collector").val(serial);
			
			if( "undefined" !== typeof(tinymce) && prism_gallery.gallery_image_clicked && '' != tinymce.EditorManager.get('content').selection.getContent() && 'normal' == internal_event )
			{
				prism_gallery.tinymce_change_gallery_content( serial );
				$('#prism_gallery_response').html("Gallery contents updated").show().fadeOut(1000);
			}											 
		},


		/**
		 * binds jquery plugins to objects
		 */
		do_plugins : function()
		{
			try
			{
				$("#prism_gallery_list")
					.sortable(
					{
						placeholder : "ui-selected",
						tolerance   : "pointer",
						items       : "li",
						opacity     : 0.6,
						start		: function()
						{
							var sitem = $("#prism_gallery_list .sortableitem.image:first-child");
							$("#pg_container .fgtt").unbind("click.prism_gallery");
							$("#prism_gallery_list .ui-selected").css({width : sitem.width()  + "px", height : sitem.height() + "px"});
						},
						update      : function(){ prism_gallery.serialize(); }
					});
			}
			catch(error)
			{
				alert("Error initializing $.ui.sortables: " + error.description);
			};
			
			if( true !== prism_gallery.refreshed )
			{
				// set up draggable / sortable list of attachments
				$("#prism_gallery_list")
					.sortable(
					{
						placeholder : "ui-selected",
						tolerance   : "pointer",
						items       : "li",
						opacity     : 0.6,
						start		: function()
						{
							var sitem = $("#prism_gallery_list .sortableitem.image:first-child");
							$("#pg_container .fgtt").unbind("click.prism_gallery");
							$("#prism_gallery_list .ui-selected").css({"width"  : sitem.width()  + "px", "height" : sitem.height() + "px"});
						},
						update      : function(){ prism_gallery.serialize(); }
					});
				
				// set up delete originals choice dialog
				$("#prism_gallery_delete_dialog")
					.dialog(
					{
						autoOpen    : false,
						closeText   : prism_gallery.L10n.close,
						bgiframe    : true,
						resizable   : false,
						width       : 600,
						modal       : true,
						draggable   : false,
						dialogClass : 'wp-dialog',
						close     : function(event, ui)
									{
										var id = $("#prism_gallery_delete_dialog").data("single_delete_id");
										$("#detach_or_delete_" + id + ", #detach_attachment_" + id + ",#del_attachment_" + id).fadeOut(100);
									},
						buttons   :
						{
							"Cancel" : function()
							{
								var id = $("#prism_gallery_delete_dialog").data("single_delete_id");
								
								$("#prism_gallery_delete_what").val("data_only");
								$("#detach_or_delete_" + id + ", #detach_attachment_" + id + ",#del_attachment_" + id).fadeOut(100);
								$("#prism_gallery_delete_dialog").removeData("single_delete_id");
								
								$(this).dialog("close");
							},
							"Delete attachment data only" : function()
							{
								var message = false, id;
								
								if( $(this).hasClass("single") )
								{
									id = $("#prism_gallery_delete_dialog").data("single_delete_id");
								}
								else
								{
									message = prism_gallery.L10n.sure_to_delete;
									id = $('#data_collector_checked').val();
								}
								
								$("#prism_gallery_delete_what").val("data_only");
								prism_gallery.delete_attachments( id, message );
								
								$(this).dialog("close");
							},
							"Delete attachment data, its copies and the files" : function()
							{
								var message = false, id;
								
								if( $(this).hasClass("single") )
								{
									id = $("#prism_gallery_delete_dialog").data("single_delete_id");
								}
								else
								{
									message = prism_gallery.L10n.sure_to_delete;
									id = $('#data_collector_checked').val();
								}
								
								$("#prism_gallery_delete_what").val("all");
								prism_gallery.delete_attachments( id, message );
								
								$(this).dialog("close");
							}
						}
					});
					
				$("#prism_gallery_image_dialog")
					.dialog(
					{
						autoOpen    : false,
						closeText   : prism_gallery.L10n.close,
						bgiframe    : true,
						resizable   : false,
						position    : "center",
						modal       : true,
						draggable   : false,
						dialogClass : 'wp-dialog'
					});
				
				$("#prism_gallery_copy_all_dialog")
					.dialog(
					{
						autoOpen    : false,
						closeText   : prism_gallery.L10n.close,
						bgiframe    : true,
						resizable   : false,
						position    : "center",
						width       : 500,
						modal       : true,
						draggable   : false,
						dialogClass : 'wp-dialog',
						buttons     :
						{
							"Cancel" : function()
							{
								$(this).dialog("close");
							},
							"Continue" : function()
							{
								var from_id = $("#prism_gallery_copy_all_dialog input#prism_gallery_copy_all_from").val();
									from_id = parseInt(from_id); 
								
								if( isNaN(from_id) || 0 === from_id )
								{
									if( isNaN(from_id) )
										from_id = "-none-";

									alert(prism_gallery.L10n.copy_from_is_nan_or_zero.replace(/%d/, from_id));
									
									return false;
								}
								
								prism_gallery.copy_all_attachments(from_id);
								
								$(this).dialog("close");
							}
						}
					});
			}
		},


		/**
		 * Displays the jQuery UI modal delete dialog
		 */
		delete_dialog : function( id, single )
		{
			var m = false,
				delete_dialog = $("#prism_gallery_delete_dialog"),
				o = $("#prism_gallery_originals").val();
			
			if( single )
				delete_dialog.addClass("single");
			else
				m = prism_gallery.L10n.sure_to_delete
			
			if( ("" != o && "undefined" != o && "undefined" != typeof( o )) || $("#image-" + id).hasClass("has_copies") )
				delete_dialog.data("single_delete_id", id).dialog('open'); //originals present in checked list
			else
				prism_gallery.delete_attachments( id, m );
			
			return false;
		},


		/**
		 * handles adding and removing of tags that will be used
		 * in gallery shortcode instead of attachment_ids,
		 * both when edited by hand and when a tag link is clicked
		 */
		add_remove_tags : function( tag )
		{
			var current_tags 	= $("#fg_gallery_tags").val(),
				newtag_slug  	= $(tag).attr("name"),
				newtag_name		= $(tag).html(),
				ct 			 	= "," + current_tags + ",",
				ns			 	= "," + newtag_slug  + ",",
				nn			 	= "," + newtag_name  + ",",
				ctlen			= 0;
			
			if( "-1" == ct.search(ns) && "-1" == ct.search(nn) )
			{
				$(tag).addClass("selected");
				
				if( "" != current_tags )
					newtag_slug = "," + newtag_slug;
				
				current_tags += newtag_slug;
			}
			else
			{
				$(tag).removeClass("selected");
		
				if( "-1" != ct.search(ns) )
					current_tags = ct.replace(ns, ",");
				else if( "-1" != ct.search(nn) )
					current_tags = ct.replace(nn, ",");
			}
			
			// clean up whitespace
			current_tags = current_tags.replace(/\s+/g, " ").replace(/\s+,/g, ",").replace(/,+\s*/g, ",");
		
			ctlen = current_tags.length;
			
			if( "," == current_tags[0] )
				current_tags = current_tags.substr(1);
			
			if( "," == current_tags[ctlen-2] )
				current_tags = current_tags.substr(0, ctlen-2);
			
			$("#fg_gallery_tags").val(current_tags);
			
			prism_gallery.serialize();
			
			return false;
		},


		/**
		 * maps attachment data (checked, has copies, is a copy)
		 */
		map : function(what, data)
		{
			data = data.split(',');
			var dl = data.length;
			
			if( "checked" == what )
			{
				while( 0 < dl )
				{
					if( false === $("#att-chk-" + data[dl-1]).prop('checked') )
						delete data[dl-1];
					
					dl--;
				}
			}
			else if( "copy" == what || "has_copies" == what )
			{
				while( 0 < dl )
				{
					if( false === $("#image-" + data[dl-1]).hasClass(what) )
						delete data[dl-1];
					
					dl--;
				}
			}
			else
			{
				return false;
			}
			
			data = '"' + data.toString() + '"';
			
			return data.replace(/,+/g, ',').replace(/",/g, '').replace(/,"/g, '').replace(/"/g, '');
		},


		/**
		 * displays attachments thumbnails or the tag list
		 */
		files_or_tags : function( do_switch )
		{
			var files_or_tags = $("#files_or_tags");
			
			if( do_switch )
			{
				if( "files" == files_or_tags.val() )
					files_or_tags.val("tags")
				else
					files_or_tags.val("files")
			}
			
			if( "files" == files_or_tags.val() || "undefined" == typeof( files_or_tags.val() ) || "undefined" == files_or_tags.val() )
			{
				$("#prism_gallery_switch_to_tags").attr("value", prism_gallery.L10n.switch_to_tags);
				$("#fg_gallery_tags_container, #prism_gallery_tag_list").fadeOut(250, function(){ $("#prism_gallery_attachment_list").fadeIn(); });
				$("#fg_gallery_tags").val('');
				
				files_or_tags.val("tags");
			}
			else if( "tags" == $("#files_or_tags").val() )
			{
				$("#prism_gallery_switch_to_tags").attr("value", prism_gallery.L10n.switch_to_files);
				$("#prism_gallery_attachment_list").fadeOut(250, function(){ $("#fg_gallery_tags_container, #prism_gallery_tag_list").fadeIn(); });
				
				files_or_tags.val("files");
			}
			
			if( "undefined" == typeof(do_switch) || false === do_switch )
				prism_gallery.serialize("files_or_tags");
		},


		/**
		 * saves attachment metadata
		 */
		save_attachment : function( attachment_data )
		{
			prism_gallery.options.prism_gallery_mode = "list";
			
			$("#pg_container")
				.html('<p class="loading_image"><img src="' + prism_gallery.options.prism_gallery_img + '/ajax-loader.gif" alt="' + prism_gallery.L10n.saving_attachment_data + '" /><br />' + prism_gallery.L10n.saving_attachment_data + '</p>');
			
			$.post
			(
				ajaxurl, 
				{
					post_id 			: $("#post_ID").val(),
					attachment_id 		: attachment_data.id, 
					action 				: "prism_gallery_main_update",
					post_alt	   		: attachment_data.alt,
					post_title   		: attachment_data.title,
					post_content 		: attachment_data.content,
					post_excerpt 		: attachment_data.excerpt,
					tax_input	 		: attachment_data.tax_input,
					menu_order   		: attachment_data.menu_order,
					custom_fields   	: attachment_data.custom_fields,
					attachment_order 	: $("#attachment_order").val(),
					checked_attachments : $("#checked_attachments").val(),
					_ajax_nonce			: prism_gallery.options.prism_gallery_nonce
				},
				function(response)
				{
					$("#pg_container").html(response).css({height : "auto"});
					$("#prism_gallery_response").html($("#prism_gallery_response_inner").html()).stop().fadeTo(0, 1).show().fadeOut(7500);
					
					prism_gallery.setup();
				},
				"html"
			);
			
			return false;
		},


		/**
		 * deletes checked attachments
		 */
		delete_attachments : function( attachment_ids, message )
		{
			var delete_what 	= $("#prism_gallery_delete_what"),
				delete_what_val = delete_what.val(),
				a,
				copies,
				originals,
				data,
				attachment_count = 1;
			
			if( "" == attachment_ids || "undefined" == typeof( attachment_ids ) || "" == delete_what_val || "undefined" == typeof( delete_what_val ) || "undefined" == delete_what_val )
				return false;
			
			if( "undefined" == typeof( message ) )
				message = false;
		
			if( (false !== message && confirm(message)) || false === message )
			{
				if( "-1" != attachment_ids.search(/,/) )
					attachment_count = attachment_ids.split(",").length;
				
				if( 1 < attachment_count )
					a = prism_gallery.L10n.deleting_attachments;
				else
					a = prism_gallery.L10n.deleting_attachment;
				
				if( 2 > attachment_count )
				{
					if( $("#image-" + attachment_ids).hasClass("copy") )
						$("#prism_gallery_copies").val(attachment_ids);
					else if( $("#image-" + attachment_ids).hasClass("has_copies") )
						$("#prism_gallery_originals").val(attachment_ids);
				}
				
				copies 	  = $("#prism_gallery_copies").val();
				originals = $("#prism_gallery_originals").val();
				
				if( "" == copies || "undefined" == copies || "undefined" == typeof( copies ))
					copies = "";
				
				if( "" == originals || "undefined" == originals || "undefined" == typeof( originals ))
					originals = "";
					
				$("#pg_container")
					.css({height : $("#pg_container").height()})
					.html('<p class="loading_image"><img src="' + prism_gallery.options.prism_gallery_img + '/ajax-loader.gif" alt="' + prism_gallery.L10n.loading + '" /><br />' + a + '</p>');
				
				data = {
						post_id 			: $("#post_ID").val(),
						action 				: "prism_gallery_main_delete",
						attachment_ids 		: attachment_ids, 
						attachment_order 	: $("#data_collector_full").val(),
						checked_attachments : $("#data_collector_checked").val(),
						copies				: copies,
						originals			: originals,
						delete_what			: delete_what_val,
						_ajax_nonce			: prism_gallery.options.prism_gallery_nonce
				};
				
				$.post
				(
					ajaxurl, 
					data,
					function(response)
					{
						$('#pg_container').html(response).css({height : "auto"});
						$('#prism_gallery_response').html($("#prism_gallery_response_inner").html()).stop().fadeTo(0, 1).css({display : "block"}).fadeOut(7500);
						
						prism_gallery.setup();
					},
					"html"
				);
			}
			
			delete_what.val("data_only")
		},


		/**
		 * detaches checked attachments
		 */
		detach_attachments : function( attachment_ids, message )
		{
			if( "" == attachment_ids || "undefined" == typeof( attachment_ids ) )
				return false;
			
			if( "undefined" == typeof( message ) )
				message = false;
		
			if( (false !== message && confirm(message)) || false === message )
			{
				var attachment_count = 1,
					a = prism_gallery.L10n.detaching_attachment;
				
				if( "-1" != attachment_ids.search(/,/) )
					attachment_count = attachment_ids.split(",").length;
		
				if( 1 < attachment_count )
					a = prism_gallery.L10n.detaching_attachments;
		
				$("#pg_container")
					.css({"height" : $("#pg_container").height()})
					.html('<p class="loading_image"><img src="' + prism_gallery.options.prism_gallery_img + '/ajax-loader.gif" alt="' + prism_gallery.L10n.loading + '" /><br />' + a + '</p>');
		
				data = {
						post_id 			: $("#post_ID").val(),
						action 				: "prism_gallery_main_detach",
						attachment_ids 		: attachment_ids, 
						attachment_order 	: $("#data_collector_full").val(),
						checked_attachments : $("#data_collector_checked").val(),
						_ajax_nonce			: prism_gallery.options.prism_gallery_nonce
				};
				
				$.post
				(
					ajaxurl, 
					data,
					function(response)
					{
						$("#pg_container")
							.html(response)
							.css({height : "auto"});
						
						$("#prism_gallery_response")
							.html($("#prism_gallery_response_inner").html())
							.stop()
							.fadeTo(0, 1)
							.show()
							.fadeOut(7500);
						
						prism_gallery.setup();
					},
					"html"
				);
			}
			
			return false;
		},


		/**
		 * saves attachment order as menu_order
		 */ 
		save_menu_order : function()
		{
			var attachment_order = $("#data_collector_full").val(),
				admin_url = ajaxurl.split("/admin-ajax.php").shift(),
				data;
		
			if( "undefined" == attachment_order || "" == attachment_order )
				return false;
			
			$('#prism_gallery_response').stop().fadeTo(0, 1).html('<img src="' + admin_url + '/images/loading.gif" width="16" height="16" alt="' + prism_gallery.L10n.loading + '" id="fg_loading_on_bar" />').show();
			
			data = {
				action			 : "prism_gallery_save_menu_order",
				post_id 		 : $("#post_ID").val(),
				attachment_order : attachment_order,
				_ajax_nonce		 : prism_gallery.options.prism_gallery_nonce
			};
			
			$.post
			(
				ajaxurl, 
				data,
				function(response)
				{
					$("#prism_gallery_response").html(response).fadeOut(7500);
				},
				"html"
			);
		},

		
		send_to_editor : function( id )
		{
			if( "prism_gallery_send_gallery_legend" == id )
			{
				var gallery_data = $('#data_collector').val();
				
				if( "" == gallery_data || "undefined" == typeof(gallery_data) )
					return false;
				
				send_to_editor(gallery_data);
				$("#prism_gallery_uncheck_all").trigger("click");
			}
			else
			{
				attachment_id = $("#data_collector_checked").val();
		
				if( "" == attachment_id || "undefined" == typeof(attachment_id) )
					return false;
				
				var data = {
					action		  : "prism_gallery_send_single",
					attachment_id : attachment_id,
					size 		  : $("#prism_gallery_single_size").val(),
					linkto 		  : $("#prism_gallery_single_linkto").val(),
					external_url  : $("#prism_gallery_single_external_url").val(),
					linkclass 	  : $("#prism_gallery_single_linkclass").val(),
					imageclass 	  : $("#prism_gallery_single_imageclass").val(),
					align 	      : $("#prism_gallery_single_align").val(),
					post_id 	  : $("#post_ID").val(),
					caption       : $("#prism_gallery_single_caption:checked").length ? true : false,
					_ajax_nonce	  : prism_gallery.options.prism_gallery_nonce
				};
				
				$.post
				(
					ajaxurl, 
					data,
					function( single_data )
					{
						send_to_editor(single_data);
						$("#prism_gallery_uncheck_all").trigger("click");
					},
					"html"
				);
			}
		},
		
		
		tinymce_set_ie_bookmark : function()
		{
			if( typeof tinyMCE != 'undefined' && tinymce.isIE && tinyMCE.activeEditor && ! tinyMCE.activeEditor.isHidden() )
			{
				tinyMCE.activeEditor.focus();
				tinyMCE.activeEditor.windowManager.insertimagebookmark = tinyMCE.activeEditor.selection.getBookmark();
			}
		},


		/**
		 * loads the attachment metadata edit page into pg_container
		 */
		edit : function( attachment_id )
		{
			if( "" == attachment_id || "undefined" == typeof( attachment_id ) )
				return false;
			
			prism_gallery.options.prism_gallery_mode = "edit";
			
			var data = {
				action				: "prism_gallery_edit_attachment",
				post_id 			: $("#post_ID").val(),
				attachment_id 		: attachment_id, 
				attachment_order 	: $("#data_collector_full").val(),
				checked_attachments : $("#data_collector_checked").val(),
				_ajax_nonce			: prism_gallery.options.prism_gallery_nonce
			};
			
			$("#pg_container")
				.html('<p class="loading_image"><img src="' + prism_gallery.options.prism_gallery_img + '"/ajax-loader.gif" alt="' + prism_gallery.L10n.loading_attachment_data + '" /><br />' + prism_gallery.L10n.loading_attachment_data + '</p>');
			
			$.post
			(
				ajaxurl, 
				data,
				function(response)
				{
					$('#pg_container').html(response);
					
					prism_gallery.tinymce_deselect();
				},
				"html"
			);
			
			return false;
		},


		/**
		 * zooms the thumbnail (needs to be replaced with lightbox)
		 */
		zoom : function( element )
		{
			var image = new Image();
			image.src = $(element).attr("href");
		
			$("#prism_gallery_image_dialog")
				.html('<p class="loading_image"><img src="' + prism_gallery.options.prism_gallery_img + '/ajax-loader.gif" alt="' + prism_gallery.L10n.loading + '" />	</p>')
				.dialog( 'option', 'width',  'auto' )
				.dialog( 'option', 'height', 'auto' )
				.dialog("open");
			
			$(image).bind("load", function()
			{
				var ih    = this.height,
					iw    = this.width,
					src   = this.src,
					ratio = iw/ih,
					ww    = $(window).width(),
					wh    = $(window).height();
				
				if( ih > (wh - 50) )
				{
					ih = wh - 50;
					iw = ratio * ih;
				}
				else if( iw > (ww - 50) )
				{
					iw = ww - 50;
					ih = ratio * iw;
				}
				
				$("#prism_gallery_image_dialog")
					.html('<img src="' + src + '" width="' + iw + '" height="' + ih + '" alt="image" />')
					.dialog( 'option', 'position', 'center');
			});
			
			return false;
		},
		

		fieldset_toggle : function( toggler )
		{
			var	state = 0,
				togglee = "prism_gallery_toggler",
				action = "prism_gallery_save_toggle_state";
			
			if( "undefined" == typeof( toggler ) )
				return;
			
			switch( toggler )
			{
				case "prism_gallery_hide_single_options" : 
					togglee = "prism_gallery_single_toggler";
					action = "prism_gallery_save_single_toggle_state";
					break;
				case "prism_gallery_hide_acf" : 
					togglee = "fieldset_attachment_custom_fields #media-single-form";
					action = "prism_gallery_save_acf_toggle_state";
					break;
				default : 
					break;
			}

			if( $("#" + toggler).hasClass("open") )
			{
				$("#" + toggler).removeClass("open").addClass("closed");
			}
			else
			{
				$("#" + toggler).removeClass("closed").addClass("open");
				state = 1;
			}

			$("#" + togglee).toggle();
			
			var data = {
				'action'		: action,
				'state'			: state,
				'_ajax_nonce'	: prism_gallery.options.prism_gallery_nonce
			};
			
			$.post
			(
				ajaxurl, 
				data
			);
		},


		copy_all_attachments : function(from_id)
		{
			if( "" == from_id || "undefined" == typeof( from_id ) )
				return false;
			
			var admin_url = ajaxurl.split("/admin-ajax.php").shift();
			
			prism_gallery.options.prism_gallery_mode = "list";
			
			var data = {
				action				: "prism_gallery_copy_all_attachments",
				to_id 				: $("#post_ID").val(),
				from_id 		    : from_id, 
				_ajax_nonce			: prism_gallery.options.prism_gallery_nonce
			};
			
			$('#prism_gallery_response').stop().fadeTo(0, 1).html('<img src="' + admin_url + '/images/loading.gif" width="16" height="16" alt="' + prism_gallery.L10n.loading + '" id="fg_loading_on_bar" />').show();
			
			$.post
			(
				ajaxurl, 
				data,
				function(response)
				{
					$("#prism_gallery_response").stop().html(response).show().css({opacity : 1}).fadeOut(7500); 
					prism_gallery.init("refreshed");
				},
				"html"
			);
		},
		
		
		/**
		 * set / unset image as post thumb
		 */
		set_post_thumb : function( attachment_ids, unset )
		{
			if( "" == attachment_ids || "undefined" == typeof( attachment_ids ) )
				return false;
			
			var action = "prism_gallery_unset_post_thumb";
			
			if( false === unset )
				action = "prism_gallery_set_post_thumb";
			
			var admin_url = ajaxurl.split("/admin-ajax.php").shift();

			$('#prism_gallery_response').stop().fadeTo(0, 1).html('<img src="' + admin_url + '/images/loading.gif" width="16" height="16" alt="' + prism_gallery.L10n.loading + '" id="fg_loading_on_bar" />').show();

			$("#image-" + attachment_ids).append('<img src="' + prism_gallery.options.prism_gallery_img + '/loading-big.gif" width="32" height="32" alt="' + prism_gallery.L10n.loading + '" id="fg_loading_on_thumb" class="thumb_switch_load" />').children("#fg_loading_on_thumb").fadeIn(250);
			
			data = {
				action			: action,
				post_id			: $("#post_ID").val(),
				attachment_ids	: attachment_ids,
				_ajax_nonce		: prism_gallery.options.prism_gallery_nonce
			};
			
			$.post
			(
				ajaxurl, 
				data,
				function( new_thumb )
				{
					var src = $("#image-" + attachment_ids + " .post_thumb_status img").attr("src"),
						response = prism_gallery.L10n.post_thumb_set;
					
					$("#fg_loading_on_thumb").fadeOut(250).remove();
					
					if( "prism_gallery_set_post_thumb" == action )
					{
						$(".sortableitem.post_thumb .post_thumb_status img")
							.attr("alt", prism_gallery.L10n.set_as_featured)
							.attr("src", src.replace(/star_unset.png/, "star_set.png"))
							.parent()
								.attr("title", prism_gallery.L10n.set_as_featured)
								.parent()
									.removeClass("post_thumb");
						
						$("#image-" + attachment_ids + " .post_thumb_status img")
							.attr("src", src.replace(/star_set.png/, "star_unset.png"))
							.attr("alt", prism_gallery.L10n.unset_as_featured)
							.parent()
								.attr("title", prism_gallery.L10n.unset_as_featured);
						
						$("#image-" + attachment_ids).addClass("post_thumb");
						
						$("#postimagediv .inside")
							.html(new_thumb);
					}
					else
					{						
						WPRemoveThumbnail(prism_gallery.options.post_thumb_nonce);
						
						response = prism_gallery.L10n.post_thumb_unset;
						
						$("#image-" + attachment_ids + " .post_thumb_status img")
							.attr("alt", prism_gallery.L10n.set_as_featured)
							.attr("src", src.replace(/star_unset.png/, "star_set.png"))
							.parent()
								.attr("title", prism_gallery.L10n.set_as_featured)
								.parent()
									.removeClass("post_thumb");
					}
					
					$('#prism_gallery_response').html(response).fadeOut(7500);
				}
			);
			
			return false;
		},


		post_edit_screen_adjust : function()
		{
			if( 1024 > $(window).width() )
			{
				$(".column-post_thumb, .column-attachment_count")
					.css({width : "60px", height : "auto", padding : "3px"})
					.children("img")
						.css({width : "60px", height : "auto", padding : 0});
			}
			else
			{
				$(".column-post_thumb, .column-attachment_count")
					.css({width : "auto", height : "auto", padding : "7px"})
					.children("img")
						.css({width : "auto", height : "auto", padding : 0});
			}
			
			if( 90 < $("th.column-post_thumb").width() )
				$("th.column-post_thumb").width(90);
		
			if( 85 < $("th.column-attachment_count").width() )
				$("th.column-attachment_count").width(85);
			
			// IE6 fixes
			if( $.browser.msie && 7 > $.browser.version )
			{
				var w = $("td.column-post_thumb img").width(),
					h = $("td.column-post_thumb img").height(),
					r = w / h,
					c = false;
				
				if( 80 < w )
				{
					c = true;
					w = 80;
					h = w / r;
					
					if( 60 < h )
					{
						h = 60;
						w = h * r;
					}
				}
				else if( 60 < h )
				{
					c = true;
					h = 60;
					w = h * r;
				}
				
				if( c )
					$("td.column-post_thumb img").width(w).height(h);
			}
		},
		
		get_attachment_custom_fields : function()
		{
			var output = {};
			
			$("#attachment_data_edit_form #media-single-form .custom_field textarea").each(function()
			{
				var key = $(this).attr("name").match(/attachments\[\d+\]\[([^\]]+)\]/)[1], // attachments[ID][FIELDNAME]
					val = $(this).val();
				
				output[key] = val;
			});
			
			return output;
		},
		
		regenerate_thumbnails : function( attachment_ids )
		{
			var el = "#prism_gallery_attachment_edit_image a.prism_gallery_regenerate",
				text = $(el).html();
			
			$(el).html('<img src="' + prism_gallery.options.prism_gallery_img + '/ajax-loader.gif" alt="' + prism_gallery.L10n.regenerating + '" />' + prism_gallery.L10n.regenerating);

			$.post
			(
				ajaxurl, 
				{
					action : "prism_gallery_regenerate_thumbnails",
					attachment_ids : attachment_ids
				},
				function(response)
				{
					$("#prism_gallery_response").stop().html(response.message).show().css({opacity : 1}).fadeOut(7500);
					$("#fg_loading_on_thumb").fadeOut(250).remove();
					$(el).html(text);
				},
				"json"
			);
		}
	});
	

/* end prism_gallery object */

	if( "undefined" !== typeof(Prism_Portfolio_Settings.init_prism_gallery) && 'true' === Prism_Portfolio_Settings.init_prism_gallery )
	{
		// regenerate thumbnails
		$("#prism_gallery_attachment_edit_image a.prism_gallery_regenerate").live("click", function(e)
		{
			var id = $(this).attr("id").replace(/\]/, '').replace(/regenerate\[/, '');
			
			prism_gallery.regenerate_thumbnails( [id] );
			
			e.preventDefault();
		});
	
	
		// WPML
		if( $("#icl_div").length > 0 )
		{
			if( $("#icl_translations_table").length > 0 )
			{
				$("#icl_translations_table a[title=edit]").each(function()
				{
					var fg_icl_trans_id = Number($(this).attr('href').match(/post=([\d]+)&/).pop());
		
					if( "number" == typeof(fg_icl_trans_id) )
					{
						$(this).after('<a title="' + prism_gallery.L10n.copy_all_from_translation + '" href="#" id="copy-from-translation-' + fg_icl_trans_id + '"><img src="' + prism_gallery.options.prism_gallery_img + '/famfamfam_silk/image_add.png" alt="' + prism_gallery.L10n.copy_all_from_translation + '" /></a>');
		
						$("#copy-from-translation-" + fg_icl_trans_id).bind("click", function()
						{
							if( confirm(prism_gallery.L10n.copy_all_from_translation_) )
								prism_gallery.copy_all_attachments(fg_icl_trans_id);
		
							return false;
						});
					}
				});
			}
			else
			{
				var fg_icl_ori_id = $("#icl_translation_of option:selected").val();
		
				if( "undefined" != typeof(fg_icl_ori_id) && "undefined" != fg_icl_ori_id )
				{
					$("#icl_div .inside").append('<a href="#" id="prism_gallery_copy_from_wmpl_original">' + prism_gallery.L10n.copy_all_from_original + '</a>');
		
					$("#prism_gallery_copy_from_wmpl_original").bind("click", function()
					{
						if( confirm(prism_gallery.L10n.copy_all_from_original_) )
							prism_gallery.copy_all_attachments(fg_icl_ori_id);
		
						return false;
					});
				}
			}
		} 
	
	
		// show / hide additional gallery options depending on preselected values
		if( "default" != $("#prism_gallery_orderby").val() )
		{
			if( "rand" == $("#prism_gallery_orderby").val() )
			{
				$("#prism_gallery_order").css({display : "none"});
				order = "";
			}
			else
			{
				$("#prism_gallery_order").css({display : "inline"});
			}
			
			orderby = ' orderby="' + $("#prism_gallery_orderby").val() + '"';
		}
		else
		{
			$("#prism_gallery_order").css({display : "none"});
			order 	= "";
			orderby = "";
		}



		// start file gallery
		prism_gallery.init();



		/* === BINDINGS === */
	
	
		$("#prism_gallery_linkclass, #prism_gallery_imageclass, #prism_gallery_galleryclass, #prism_gallery_mimetype, #prism_gallery_limit, #prism_gallery_offset, #prism_gallery_external_url, #prism_gallery_single_linkclass, #prism_gallery_single_imageclass, #prism_gallery_single_external_url, #fg_gallery_tags, #prism_gallery_postid, #prism_gallery_mimetype, #prism_gallery_linkrel_custom").live('keypress keyup', function(e)
		{
			// on enter
			if ( 13 === e.which || 13 === e.keyCode )
			{
				prism_gallery.serialize();
				
				if( "prism_gallery_limit" == $(this).attr("id") )
				{
					if( 0 < Number($(this).val()) )
						$("#prism_gallery_paginate_label").show();
					else
						$("#prism_gallery_paginate_label").hide();
				}
				
				
				return false;
			}
		});
	
		
		$("#fgae_post_alt, #fgae_post_title, #fgae_post_excerpt, #fgae_tax_input, #fgae_menu_order").live('keypress keyup', function(e)
		{
			if ( 13 === e.which || 13 === e.keyCode ) // on enter
			{
				$("#prism_gallery_edit_attachment_save").trigger("click");
				e.preventDefault();
				return false;
			}
			else if( 27 === e.which || 27 === e.keyCode ) // on esc
			{
				$("#prism_gallery_edit_attachment_cancel").trigger("click");
			}
		});
	
		$("a.post_thumb_status").live("click", function()
		{
			var what = false;
			
			if( $(this).parent().hasClass("post_thumb") )
				what = true;
			
			return prism_gallery.set_post_thumb($(this).attr("rel"), what);
		});
			
		$("#remove-post-thumbnail").attr("onclick", "").live("click.prism_gallery", function()
		{		
			if( 0 < $(".sortableitem.post_thumb").length )
				return prism_gallery.set_post_thumb($(".sortableitem.post_thumb").attr("id").split("-").pop(), true);
	
			WPRemoveThumbnail(prism_gallery.options.post_thumb_nonce);
			
			return false;
		});
		
		$("#prism_gallery_copy_all_form").bind("submit", function(){ return false; });
	
	
		// copy all attachments from another post
		$("#prism_gallery_copy_all").live("click", function()
		{
			$("#prism_gallery_copy_all_dialog").dialog("open");
		});
		
		
		// toggle fieldsets
		$("#prism_gallery_hide_gallery_options, #prism_gallery_hide_single_options, #prism_gallery_hide_acf").live("click", function()
		{
			prism_gallery.fieldset_toggle( $(this).attr("id") );
		});


		/* attachment edit screen */
		
		// save attachment
		$("#prism_gallery_edit_attachment_save").live("click", function()
		{
			var attachment_data =
			{
				id : $('#fgae_attachment_id').val(),
				alt : $('#fgae_post_alt').val(),
				title : $('#fgae_post_title').val(),
				excerpt : $('#fgae_post_excerpt').val(),
				content : $('#fgae_post_content').val(),
				tax_input : $('#fgae_tax_input').val(),
				menu_order : $('#fgae_menu_order').val(),
				custom_fields : prism_gallery.get_attachment_custom_fields()
			};
			
			return prism_gallery.save_attachment( attachment_data );
		});
		
		// cancel changes
		$("#prism_gallery_edit_attachment_cancel").live("click", function()
		{
			return prism_gallery.init('return_from_single_attachment');
		});
	
		// acf enter on new field name
		$("#new_custom_field_key").live("keypress keyup", function(e)
		{
			if ( 13 === e.which || 13 === e.keyCode ) // on enter
			{
				$("#new_custom_field_submit").trigger("click");
				e.preventDefault();
			}
		});


		/* thumbnails */
		
		// attachment thumbnail click
		$("#pg_container .fgtt, #pg_container .checker_action").live("click.prism_gallery", function()
		{
			var p = $(this).parent(), c = "#att-chk-" + p.attr("id").replace("image-", "");
			
			p.toggleClass("selected");
			$(c).prop("checked", $(c).prop("checked") ? false : true).change();
		});
		
		// attachment thumbnail double click
		$("#pg_container .fgtt, #pg_container .checker_action").live("dblclick", function()
		{
			prism_gallery.edit( $(this).parent("li:first").attr("id").replace("image-", "") );
		});
		
		// edit attachment button click
		$("#pg_container .img_edit").live("click", function()
		{
			return prism_gallery.edit( $(this).attr("id").replace('in-', '').replace('-edit', '') );
		});
	
		// zoom attachment button click
		$("#pg_container .img_zoom, .attachment_edit_thumb").live("click", function()
		{
			return prism_gallery.zoom( this );
		});
	
		// delete or detach single attachment link click
		$("#pg_container .delete_or_detach_link").live("click", function()
		{
			var id = $(this).attr("rel"),
				 a = '#detach_or_delete_' + id,
				 b = '#detach_attachment_' + id,
				 c = '#del_attachment_' + id;
	
			if( $(a).is(":hidden") && $(b).is(":hidden") && $(c).is(":hidden") )
				$(a).fadeIn(100);
			else
				$(a + ", " + b + ", " + c).fadeOut(100);
			
			return false;
		});
			
		// detach single attachment link click
		$("#pg_container .do_single_detach").live("click", function()
		{
			var id = $(this).attr("rel");
			
			$('#detach_or_delete_' + id).fadeOut(250);
			$('#detach_attachment_' + id).fadeIn(100);
			
			return false;
		});
			
		// delete single attachment link click
		$("#pg_container .do_single_delete").live("click", function()
		{
			var id = $(this).attr("rel");
			
			if( $("#image-" + id).hasClass("has_copies") )
				return prism_gallery.delete_dialog( id, true );
	
			$('#detach_or_delete_' + id).fadeOut(100);
			$('#del_attachment_' + id).fadeIn(100);
	
			return false;
		});	
			
		// delete single attachment link confirm
		$("#pg_container .delete").live("click", function()
		{
			var id = $(this).parent("div").attr("id").replace(/del_attachment_/, "");
			
			if( $("#image-" + id).hasClass("copy") )
				$("#prism_gallery_delete_what").val("data_only");
			else
				$("#prism_gallery_delete_what").val("all");
	
			return prism_gallery.delete_dialog( id, true );
		});
			
		// delete single attachment link confirm
		$("#pg_container .detach").live("click", function()
		{
			return prism_gallery.detach_attachments( $(this).parent("div").attr("id").replace(/detach_attachment_/, ""), false );
		});
		
		// delete / detach single attachment link cancel
		$("#pg_container .delete_cancel, #pg_container .detach_cancel").live("click", function()
		{
			 $(this)
				.parent("div")
					.fadeOut(250);
					
			 return false;
		});
	
	
		/* send gallery or single image(s) to editor */
		
		$("#prism_gallery_send_gallery_legend, #prism_gallery_send_single_legend").live("click mouseover", function(e)
		{
			if( "click" == e.type )
				prism_gallery.send_to_editor( $(this).attr("id") );
			else
				prism_gallery.tinymce_set_ie_bookmark();
		});
	
	
		/* main menu buttons */
	
		// refresh attachments button click
		$("#prism_gallery_refresh").live("click", function()
		{
			 prism_gallery.init( 'refreshed' );
		});
		
		// resort attachments button click
		$("#prism_gallery_attachments_sort_submit").live("click", function()
		{
			 prism_gallery.init( 'sorted' );
		});
		
		// delete checked attachments button click
		$("#prism_gallery_delete_checked").live("click", function()
		{
			prism_gallery.delete_dialog( $('#data_collector_checked').val() );
		});
			
		// detach checked attachments button click
		$("#prism_gallery_detach_checked").live("click", function()
		{
			prism_gallery.detach_attachments($('#data_collector_checked').val(), prism_gallery.L10n.sure_to_detach);
		});
		
		// save attachments menu order button click
		$("#prism_gallery_save_menu_order, #prism_gallery_save_menu_order_link").live("click", function(e)
		{
			prism_gallery.save_menu_order();
			
			e.preventDefault();
			return false;
		});
			
		// check all attachments button click
		$("#prism_gallery_check_all").live("click", function()
		{
			if( $("#data_collector_checked").val() != $("#data_collector_full").val() )
			{
				$('#pg_container .sortableitem .checker').map(function()
				{
					$(this).parents(".sortableitem").addClass("selected");
					return this.checked = true;
				});
				
				prism_gallery.serialize();
			}
		});
			
		// uncheck all attachments button click
		$("#prism_gallery_uncheck_all").live("click click_tinymce_gallery", function(e)
		{
			if( "" != $("#data_collector_checked").val() )
			{
				$('#pg_container .sortableitem .checker').map(function()
				{
					$(this).parents(".sortableitem").removeClass("selected");
					return this.checked = false;
				});
			}
			
			// with serialization if tinymce gallery placeholder isn't clicked
			if( "click" === e.type )
				prism_gallery.serialize();
		});
	

		/* other bindings */
		
		// bind dropdown select boxes change to serialize attachments list
		$("#prism_gallery_size, #prism_gallery_linkto, #prism_gallery_orderby, #prism_gallery_order, #prism_gallery_template, #prism_gallery_single_linkto, #pg_container .sortableitem .checker, #prism_gallery_columns, #prism_gallery_linkrel,  #prism_gallery_paginate, #prism_gallery_linksize").live("change", function()
		{
			prism_gallery.serialize();
		});
		
		// tags from current post only checkbox, switch to tags button
		$("#fg_gallery_tags_from, #prism_gallery_switch_to_tags").live("click", function()
		{
			prism_gallery.serialize();
		});
		
		// blur binding for text inputs and dropdown selects
		$("#fg_gallery_tags, #prism_gallery_linkclass, #prism_gallery_imageclass, #prism_gallery_galleryclass, #prism_gallery_single_linkclass, #prism_gallery_single_imageclass, #prism_gallery_single_external_url, #prism_gallery_external_url, #prism_gallery_postid, #prism_gallery_limit").live("blur", function()
		{
			prism_gallery.serialize();
		});
	
		// whether to show tags or list of attachments
		$("#prism_gallery_switch_to_tags").live("click", function()
		{
			prism_gallery.files_or_tags( false );
		});
			
		// clickable tag links
		$(".fg_insert_tag").live("click", function()
		{
			return prism_gallery.add_remove_tags( this );
		});
		
		// thickbox window closed
		if( "function" === typeof(jQuery.fn.on) )
		{
			jQuery(document.body).on("tb_unload", "#TB_window", function(e)
			{
				prism_gallery.tinymce_deselect( true );
				prism_gallery.init();
			});
		}
		else
		{
			jQuery('#TB_window').live("unload", function(e)
			{
				prism_gallery.tinymce_deselect( true );
				prism_gallery.init();
			});
		}
	} 


	/* === edit.php screens === */


	// min/max-width/height adjustments for post thumbnails
	if( 0 < $(".column-post_thumb").length )
	{		
		$(window).bind("load resize", function()
		{
			prism_gallery.post_edit_screen_adjust();
		});
	}
});


// --------------------------------------------------------- //


/**
 * thanks to http://soledadpenades.com/2007/05/17/arrayindexof-in-internet-explorer/
 */
if( ! Array.indexOf )
{
	Array.prototype.indexOf = function(obj)
	{
		var l = this.length, i;
		
		for( i=0; i<l; i++ ){
			if( this[i] == obj )
				return i;
		}
		
		return -1;
	}
}


/**
 * thanks to http://phpjs.org/functions/strip_tags:535
 */
function strip_tags (input, allowed)
{
	allowed = (((allowed || "") + "")
		.toLowerCase()
		.match(/<[a-z][a-z0-9]*>/g) || [])
		.join(''); // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
	
	var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
		commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;

	return input.replace(commentsAndPhpTags, '').replace(tags, function($0, $1)
	{
		return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
	});
}