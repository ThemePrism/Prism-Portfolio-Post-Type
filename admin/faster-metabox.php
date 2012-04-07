<?php
// don't load directly
if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class Faster_Metabox extends Prism_Portfolio {

	public function __construct() {  
		//hook it up
		add_action('admin_menu', array(&$this,'faster_insert_option'));
		add_action('add_meta_boxes', array(&$this,'fast_image_insert'), 20);
		add_action('admin_head', array(&$this,'faster_insert_local'));
		add_filter('media_upload_gallery', array(&$this,'faster_insert_media_upload_gallery'));
		add_filter('media_upload_library', array(&$this,'faster_insert_media_upload_library'));
		add_filter('media_upload_image', array(&$this,'faster_insert_media_upload_image'));
		add_filter('disable_captions', array(&$this,'caption_off'));
		add_filter('plugin_action_links', array(&$this,'faster_insert_action_links'), 10, 2);
	}
	
	
	
	//notice in editing panel when wp_version < 3.0
function fast_insert_form_notice() {

?>
<script type="text/javascript">
/* <![CDATA[ */
  jQuery(function($) {
    //intialize
    $('#screen-meta').ready(function() {
      var view = $('#prism_insert-hide').is(':checked');
      if(view) {
        $('#prism_insert > .inside').html('<p><?php _e('Faster Image Insert 2.0 series are for WordPress 3.0 or newer version, for older WP, download <a href="http://wordpress.org/extend/plugins/faster-image-insert/download/">1.0 series</a> instead.', 'faster-image-insert') ?></p>');
      }
    });
  });
/* ]]> */
</script>
<?php
}
  
//iframe for editing panel when wp_version > 3.0
//since FII 2.2.0, modified behaviour to workaround issue with jquery live + iframe
function fast_insert_form() {
  global $post_ID, $temp_ID;
  $id = (int) (0 == $post_ID ? $temp_ID : $post_ID);

  $upload_form = 'faster_insert_upload_form';
  $noflash = get_option( 'faster_insert_upload_form' );
  
  $noflash = true;
  

?>
<script type="text/javascript">
/* <![CDATA[ */
  jQuery(function($) {
    //intialize
    $('#screen-meta').ready(function() {
      var view = $('#prism_insert-hide').is(':checked');
      if(view) {
	    <?php if($id > 0) { 
		$flashurl = ($noflash) ? $id . '&#038;flash=0' : $id;
		?>
        $('#prism_insert > .inside').html('<iframe frameborder="0" name="fast_insert" id="fast_insert" src="<?php echo admin_url( 'media-upload.php?post_id=' . $flashurl . '&#038;type=image&#038;tab=type"'); ?> hspace="0" onload="if (document.getElementById(\'fast_insert\').contentWindow.document.title.length == 0) document.getElementById(\'fast_insert\').contentDocument.location.href = document.getElementById(\'fast_insert\').contentDocument.location.href"> </iframe>');
        <?php } else { ?>
        $('#prism_insert > .inside').html('<p><?php _e('Click here to reload after autosave. Or manually save the draft.', 'faster-image-insert') ?></p>');
        <?php } ?>
      }
    });
    //toggle metabox
    $('#screen-meta #prism_insert-hide').click(function() {
      var view = $('#prism_insert-hide').is(':checked');
      if(view) {
	    <?php if($id > 0) { ?>
        $('#prism_insert > .inside').html('<iframe frameborder="0" name="fast_insert" id="fast_insert" src="<?php echo site_url() ?>/wp-admin/media-upload.php?post_id=<?php if($noflash) echo $id.'&#038;flash=0'; else echo $id; ?>&#038;type=image&#038;tab=type" hspace="0" onload="if (document.getElementById(\'fast_insert\').contentWindow.document.title.length == 0) document.getElementById(\'fast_insert\').contentDocument.location.href = document.getElementById(\'fast_insert\').contentDocument.location.href"> </iframe>');
        <?php } else { ?>
        $('#prism_insert > .inside').html('<p><?php _e('Click here to reload after autosave. Or manually save the draft.', 'faster-image-insert') ?></p>');
        <?php } ?>
      }
    });
  });
/* ]]> */
</script>
<?php
}

//replace several scripts for new functions.
function fast_image_insert() {
	add_meta_box('prism_insert', __('Insert','prism_portfolio'), array(__CLASS__,'fast_insert_form'), self::$post_type, 'normal', 'high');
}

// various javascript / css goodies for:
// 1. selected insert
// 2. mass-editing
// 3. styling for iframe and mass-edit table
function faster_insert_local() {
  
?>
<script type="text/javascript">
/* <![CDATA[ */  
  jQuery(function($) {
  
    //bind current elements and add checkbox
    $('#media-items .new').each(function(e) {
      var id = $(this).parent().attr('id');
      id = id.split("-")[2];
      $(this).prepend('<input type="checkbox" class="item_selection" title="<?php _e('Select items you want to insert','faster-image-insert'); ?>" id="attachments[' + id.substring() + '][selected]" name="attachments[' + id + '][selected]" value="selected" /> ');
    });
    
    //bind future elements and add checkbox
    $('.ml-submit').live('mouseenter',function(e) {
      $('#media-items .new').each(function(e) {
        var id = $(this).parent().children('input[value="image"]').attr('id');
        id = id.split("-")[2];
        $(this).not(':has("input")').prepend('<input type="checkbox" class="item_selection" title="<?php _e('Select items you want to insert','faster-image-insert'); ?>" id="attachments[' + id.substring() + '][selected]" name="attachments[' + id + '][selected]" value="selected" /> ');
      });
      //$('.ml-submit').die('mouseenter');
    });
    
    //buttons for enhanced functions
    //since FII 2.2.0: changed to adapt to jquery 1.6 checkbox prop
    $('.ml-submit:first').append('<input type="submit" class="button savebutton" name="insertall" id="insertall" value="<?php echo esc_attr( __( 'Insert selected images', 'faster-image-insert') ); ?>" /> ');  
    $('.ml-submit:first').append('<input type="submit" class="button savebutton" name="invertall" id="invertall" value="<?php echo esc_attr( __( 'Invert selection', 'faster-image-insert') ); ?>" /> ');
    $('.ml-submit #invertall').click(
      function(){
        $('#media-items .item_selection').each(function(e) {
          if($(this).is(':checked')) $(this).prop("checked",false);
          else $(this).attr("checked",true);
        });
        return false;
      }
    );
    
    //mass-editing is default function for FII 2.0+
    if($('#gallery-settings').length > 0) {
      $('#gallery-settings').before('<div id="mass-edit"><div class="title"><?php _e('Mass Image Edit','faster-image-insert'); ?></div></div>');
      $('#gallery-settings .describe').clone().appendTo('#mass-edit');
      $('#mass-edit .describe tr:eq(0)').clone().prependTo("#mass-edit .describe tbody");
      $('#mass-edit').append('<p class="ml-submit"><input type="button" class="button" name="massedit" id="massedit" value="<?php _e('Apply changes','faster-image-insert'); ?>" /> <span><?php _e('Press "Save all changes" above to save. Only Title, Alt-Text and Caption are permanently saved.','faster-image-insert'); ?></span></p>');

      //setup the form
      $('#mass-edit tr:eq(0) .alignleft').html('<?php _e('Image Titles','faster-image-insert'); ?>');
      $('#mass-edit tr:eq(1) .alignleft').html('<?php _e('Image Alt-Texts','faster-image-insert'); ?>');
      $('#mass-edit tr:eq(2) .alignleft').html('<?php _e('Image Captions','faster-image-insert'); ?>');
      $('#mass-edit tr:eq(3) .alignleft').html('<?php _e('Image Alignment','faster-image-insert'); ?>');
      $('#mass-edit tr:eq(4) .alignleft').html('<?php _e('Image Sizes','faster-image-insert'); ?>');
    
      $('#mass-edit tr:eq(0) .field').html('<input type="text" name="title_edit" id="title_edit" value="" />');
      $('#mass-edit tr:eq(1) .field').html('<input type="text" name="alttext_edit" id="alttext_edit" value="" />');
      $('#mass-edit tr:eq(2) .field').html('<input type="text" name="captn_edit" id="captn_edit" value="" />');
      $('#mass-edit tr:eq(3) .field').html('<input type="radio" name="align_edit" id="align_none" value="none" />\n<label for="align_none" class="radio"><?php _e('None') ?></label>\n<input type="radio" name="align_edit" id="align_left" value="left" />\n<label for="align_left" class="radio"><?php _e('Left') ?></label>\n<input type="radio" name="align_edit" id="align_center" value="center" />\n<label for="align_center" class="radio"><?php _e('Center') ?></label>\n<input type="radio" name="align_edit" id="align_right" value="right" />\n<label for="align_right" class="radio"><?php _e('Right') ?></label>');
      $('#mass-edit tr:eq(4) .field').html('<input type="radio" name="size_edit" id="size_thumb" value="thumbnail" />\n<label for="size_thumb" class="radio"><?php _e('Thumbnail') ?></label>\n<input type="radio" name="size_edit" id="size_medium" value="medium" />\n<label for="size_medium" class="radio"><?php _e('Medium') ?></label>\n<input type="radio" name="size_edit" id="size_large" value="large" />\n<label for="size_large" class="radio"><?php _e('Large') ?></label>\n<input type="radio" name="size_edit" id="size_full" value="full" />\n<label for="size_full" class="radio"><?php _e('Full size') ?></label>');

      //read value and apply
      //since FII 2.2.0: changed to adapt to jquery 1.6 checkbox prop
      $('#massedit').click(function() {
        var massedit = new Array();
        massedit[0] = $('#mass-edit .describe #title_edit').val();
        massedit[1] = $('#mass-edit .describe #alttext_edit').val();
        massedit[2] = $('#mass-edit .describe #captn_edit').val();
        massedit[3] = $('#mass-edit tr:eq(3) .field input:checked').val();
        massedit[4] = $('#mass-edit tr:eq(4) .field input:checked').val();
        //alert(massedit);
        var num_count = 0;
        $('.media-item').each(function(e) {
          num_count++;
          if(typeof massedit[0] !== "undefined" && massedit[0].length > 0) {
            $(this).find('.post_title .field input').val(massedit[0] + " (" + num_count + ")");
          }
          if(typeof massedit[1] !== "undefined" && massedit[1].length > 0) {
            $(this).find('.image_alt .field input').val(massedit[1] + " (" + num_count + ")");
          }
          if(typeof massedit[2] !== "undefined" && massedit[2].length > 0) {
            $(this).find('.post_excerpt .field input').val(massedit[2]);
          }
          if(typeof massedit[3] !== "undefined" && massedit[3].length > 0) {
            $(this).find('.align .field input[value='+massedit[3]+']').prop("checked",true);
          }
          if(typeof massedit[4] !== "undefined" && massedit[4].length > 0) {
            $(this).find('.image-size .field input[value='+massedit[4]+']').prop("checked",true);
          }
        });
      });
    }
  });

/* ]]> */
</script>
<style type="text/css" media="screen">
#fast_insert{width:100%;height:500px;}
#mass-edit th.label{width:160px;}
#mass-edit #basic th.label{padding:5px 5px 5px 0;}
#mass-edit .title{clear:both;padding:0 0 3px;border-bottom-style:solid;border-bottom-width:1px;font-family:Georgia,"Times New Roman",Times,serif;font-size:1.6em;border-bottom-color:#DADADA;color:#5A5A5A;}
#mass-edit .describe td{vertical-align:middle;height:3.5em;}
#mass-edit .describe th.label{padding-top:.5em;text-align:left;}
#mass-edit .describe{padding:5px;width:615px;clear:both;cursor:default;}
#mass-edit .describe select,#mass-edit .describe input[type=text]{width:15em;border:1px solid #dfdfdf;}
#mass-edit label,#mass-edit legend{font-size:13px;color:#464646;margin-right:15px;}
#mass-edit .align .field label{margin:0 1.5em 0 0;}
#mass-edit p.ml-submit{border-top:1px solid #dfdfdf;}
#mass-edit select#columns{width:6em;}
</style>
<?php
}

//used for passing content to edit panel.
function fast_insert_to_editor($html) {
?>
<script type="text/javascript">
/* <![CDATA[ */
var win = window.dialogArguments || opener || parent || top;
win.send_to_editor('<?php echo str_replace('\\\n','\\n',addslashes($html)); ?>');
/* ]]> */
</script>
  <?php
  exit;
}

//catches the insert selected images post request.
function faster_insert_form_handler() {
  global $post_ID, $temp_ID;
  $post_id = (int) (0 == $post_ID ? $temp_ID : $post_ID);
  check_admin_referer('media-form');
  
  //load settings
  $customstring = 'faster_insert_plugin_custom';
  $cstring = get_option( $customstring );
  
  $line_number = 'faster_insert_line_number';
  $number = get_option( $line_number );
  
  $image_line = 'faster_insert_image_line';
  $oneline = get_option( $image_line );
  
  if(!is_numeric($number)) $number = 4;

  //modify the insertion string
  if ( !empty($_POST['attachments']) ) {
    $result = '';
    foreach ( $_POST['attachments'] as $attachment_id => $attachment ) {
      $attachment = stripslashes_deep( $attachment );
      if (!empty($attachment['selected'])) {
        $html = $attachment['post_title'];
        if ( !empty($attachment['url']) ) {
          if ( strpos($attachment['url'], 'attachment_id') || false !== strpos($attachment['url'], get_permalink($post_id)) )
            $rel = " rel='attachment wp-att-".esc_attr($attachment_id)."'";
          $html = "<a href='{$attachment['url']}'$rel>$html</a>";
        }
        $html = apply_filters('media_send_to_editor', $html, $attachment_id, $attachment);
        //since 1.5.0: &nbsp; is the same as a blank space, but can be passed onto TinyMCE
        if(!$oneline) $result .= $html.str_repeat("\\n".$cstring."\\n",$number);
        else $result .= $html.str_repeat($cstring,$number);
      }
    }
    return fast_insert_to_editor($result);
  }

  return $errors;
}

//filter for media_upload_gallery, recognize insertall request.
function faster_insert_media_upload_gallery() {
  if ( isset($_POST['insertall']) ) {
    $return = faster_insert_form_handler();
    
    if ( is_string($return) )
      return $return;
    if ( is_array($return) )
      $errors = $return;
  }
}

//filter for media_upload_image, recognize insertall request.
function faster_insert_media_upload_image() {
  if ( isset($_POST['insertall']) ) {
    $return = faster_insert_form_handler();
    
    if ( is_string($return) )
      return $return;
    if ( is_array($return) )
      $errors = $return;
  }
}

//filter for media_upload_library, recognize insertall request.
function faster_insert_media_upload_library() {
  if ( isset($_POST['insertall']) ) {
    $return = faster_insert_form_handler();
    
    if ( is_string($return) )
      return $return;
    if ( is_array($return) )
      $errors = $return;
  }
}

//for disabling captions
function caption_off() {
  $no_caption = 'faster_insert_no_caption';
  $nocaption = get_option( $no_caption );
  if($nocaption)
    return true;
}

//adds a new submenu for options
function faster_insert_option() {
    add_options_page(__('Faster Image Insert - User Options','faster-image-insert'), 'Faster Image Insert', 8, __FILE__, 'faster_insert_option_detail');
}

//display the actual content of option page.
function faster_insert_option_detail() {  

  $faster_insert_update = 'faster_insert_update';
  $faster_insert_delete = 'faster_insert_delete';
  $faster_insert_valid = 'faster_insert_valid';

  //all the options
  $upload_form = 'faster_insert_upload_form';
  $image_line = 'faster_insert_image_line';
  $line_number = 'faster_insert_line_number';
  $no_caption = 'faster_insert_no_caption';
  $customstring = 'faster_insert_plugin_custom';
  $customtype = 'faster_insert_post_type';
  
  //add options
  add_option( $upload_form, false );
  add_option( $image_line, false );
  add_option( $line_number, 1 );
  add_option( $no_caption, false );
  add_option( $customstring, "<p></p>" );
  add_option( $customtype, "" );
  
  //update options
  if( !empty($_POST[ $faster_insert_update ]) && check_admin_referer($faster_insert_valid,'check-form') ) {
  
    $_POST[ $upload_form ] == 'selected' ? $flash = true : $flash = false;
    $_POST[ $image_line ] == 'selected' ? $image = true : $image = false;
    if(is_numeric($_POST[ $line_number ])) $number = $_POST[ $line_number ]; else $number = 1;
    $_POST[ $no_caption ] == 'selected' ? $caption = true : $caption = false;
    if(is_string($_POST[ $customstring ]) && !empty($_POST[ $customstring ])) $cstring = $_POST[ $customstring ]; else $cstring = "<p></p>";
    if(is_string($_POST[ $customtype ]) && !empty($_POST[ $customtype ])) $ptype = $_POST[ $customtype ]; else $ptype = "";
    
    update_option( $upload_form, $flash );
    update_option( $image_line, $image );
    update_option( $line_number, $number );
    update_option( $no_caption, $caption );
    update_option( $customstring, $cstring );
    update_option( $customtype, $ptype );

    echo '<div class="updated"><p><strong>'.__('Settings saved.', 'faster-image-insert').'</strong></p></div>';  
  }

  //delete options
  if( isset($_POST[ $faster_insert_delete ]) && check_admin_referer($faster_insert_valid,'check-form') ) {
    
    //compatible with version older than FII 2.0
    delete_option( $load_iframe );
    delete_option( $upload_form );
    delete_option( $image_line );
    delete_option( $line_number );
    delete_option( $mass_edit );
    delete_option( $no_caption );
    delete_option( $backcompat );
    delete_option( $plugindebug );
    delete_option( $customstring );
    delete_option( $customtype );

    echo '<div class="updated"><p><strong>'.__('Settings deleted.', 'faster-image-insert').'</strong></p></div>';  
  }
  
  //current value
  $flash = get_option( $upload_form );
  $image = get_option( $image_line );
  $number = get_option( $line_number );
  $caption = get_option( $no_caption );
  $cstring = get_option( $customstring );
  $ptype = get_option( $customtype );

  echo '<div class="wrap">'."\n".
     '<div id="icon-options-general" class="icon32"><br /></div>'."\n".
     '<h2>'.__('Faster Image Insert - User Options','faster-image-insert').'</h2>'."\n".
     '<h3>'.__('Updates your settings here', 'faster-image-insert').'</h3>';
?>

<form name="faster-insert-option" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
<?php wp_nonce_field($faster_insert_valid, 'check-form'); ?>

  <table width="100%" cellspacing="2" cellpadding="5" class="form-table">

    <tr valign="top">
      <th scope="row"><?php _e("Load HTML form instead of Flash uploader", 'faster-image-insert' ); ?></th>
      <td><label for="<?php echo $upload_form; ?>"><input type="checkbox" name="<?php echo $upload_form; ?>" id="<?php echo $upload_form; ?>" value="selected" <?php if($flash) echo 'checked="checked"' ?> /> <?php _e("Enable this if you're having trouble with flash uploader.", 'faster-image-insert' ); ?></label></td>
    </tr>
    
    <tr valign="top">
      <th scope="row"><?php _e("Insert multiple images in 1 line", 'faster-image-insert' ); ?></th>
      <td><label for="<?php echo $image_line; ?>"><input type="checkbox" name="<?php echo $image_line; ?>" id="<?php echo $image_line; ?>" value="selected" <?php if($image) echo 'checked="checked"' ?> /> <?php _e("Enable this if you want to insert a serial of thumbnails without newlines.", 'faster-image-insert' ); ?></label></td>
    </tr>
	
    <tr valign="top">
      <th scope="row"><?php _e("Set custom string", 'faster-image-insert' ); ?></th>
      <td><label for="<?php echo $customstring; ?>"><input type="text" name="<?php echo $customstring; ?>" id="<?php echo $customstring; ?>" value="<?php echo $cstring; ?>" size="20" /> <?php _e("Edit this to change the custom string inserted between each image; defaults to &lt;p&gt;&lt;/p&gt;.", 'faster-image-insert' ); ?></label></td>
    </tr>

    <tr valign="top">
      <th scope="row"><?php _e("Duplicate customer string", 'faster-image-insert' ); ?></th>
      <td><label for="<?php echo $line_number; ?>"><input type="text" name="<?php echo $line_number; ?>" id="<?php echo $line_number; ?>" value="<?php echo $number; ?>" size="10" /> <?php _e("Depends on previous option; it duplicates the string inserted each time. Default is 1 time.", 'faster-image-insert' ); ?></label></td>
    </tr>
    
    <tr valign="top">
      <th scope="row"><?php _e("Disable captions", 'faster-image-insert' ); ?></th>
      <td><label for="<?php echo $no_caption; ?>"><input type="checkbox" name="<?php echo $no_caption; ?>" id="<?php echo $no_caption; ?>" value="selected" <?php if($caption) echo 'checked="checked"' ?> /> <?php _e("WordPress use caption as alternative text, but it also appends [caption] if set manually, Enable this if you want to set alternative text without appending caption.", 'faster-image-insert' ); ?></label></td>
    </tr>
    
    <tr valign="top">
      <th scope="row"><?php _e("Custom post types", 'faster-image-insert' ); ?></th>
      <td><label for="<?php echo $customtype; ?>"><input type="text" name="<?php echo $customtype; ?>" id="<?php echo $customtype; ?>" value="<?php echo $ptype; ?>" size="20" /> <?php _e("Load FII panel in custom post types other than the default post/page; default to none, comma separated.", 'faster-image-insert' ); ?></label></td>
    </tr>
    
  </table>

<p class="submit">
<input type="submit" name="<?php echo $faster_insert_update; ?>" class="button-primary" value="<?php _e('Save Changes', 'faster-image-insert' ) ?>" />
<input type="submit" name="<?php echo $faster_insert_delete; ?>" value="<?php _e('Uninstall', 'faster-image-insert' ) ?>" />
</p>

</form>

<?php     
  echo '</div>'."\n";
}




//add setting link to plugin page
function faster_insert_action_links($links, $file) {
    static $this_plugin;
    if (!$this_plugin) {
        $this_plugin = plugin_basename(__FILE__);
    }
    if ($file == $this_plugin) {
        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=faster-image-insert/faster-image-insert.php">Settings</a>';
        array_unshift($links, $settings_link);
    }
    return $links;
}

}