jQuery.fn.exists = function () {
    return jQuery(this).length > 0;
}
jQuery(document).ready(function($) {
 
    if($(".plupload-ui").exists()) {
        var pconfig=false;
        $(".plupload-ui").each(function() {
            var $this=$(this);
            var id1=$this.attr("id");
            var imgId= $this.prev('input.image_url').attr('id');  
 
            plu_show_thumbs(imgId);

            pconfig = base_plupload_config;
 
            pconfig["browse_button"] = imgId + pconfig["browse_button"];
            pconfig["container"] = imgId + pconfig["container"];
            pconfig["drop_element"] = imgId + pconfig["drop_element"];
            pconfig["file_data_name"] = imgId + pconfig["file_data_name"];
            pconfig["multipart_params"]["imgid"] = imgId;
 
            if($this.hasClass("multiple")) {
                pconfig["multi_selection"]=true;
            }

            if($this.find(".width").exists()) {
                var w = parseInt($this.find(".width").val());
                var h = parseInt($this.find(".height").val());
                pconfig["resize"]={
                    width : w,
                    height : h,
                    quality : 90
                };

            }
  
            var uploader = new plupload.Uploader(pconfig);
 
            // checks if browser supports drag and drop upload, makes some css adjustments if necessary
            uploader.bind('Init', function(up){
  
            if(up.features.dragdrop){
                $this.addClass('drag-drop').find('.drag-drop-area')
                    .bind('dragover.wp-uploader', function(){ $this.addClass('drag-over'); })
                    .bind('dragleave.wp-uploader, drop.wp-uploader', function(){ $this.removeClass('drag-over'); });

                } else {
                    $this.removeClass('drag-drop').find('.drag-drop-area').unbind('.wp-uploader');
                }
              });
 
            uploader.init();

            // a file was added in the queue
            uploader.bind('FilesAdded', function(up, files){
                $.each(files, function(i, file) {
                    $this.find('.filelist').append(
                        '<div class="file" id="' + file.id + '"><b>' +
 
                        file.name + '</b> (<span>' + plupload.formatSize(0) + '</span>/' + plupload.formatSize(file.size) + ') ' +
                        '<div class="fileprogress"></div></div>');
                });
 
                up.refresh();
                up.start();
            });
 
            uploader.bind('UploadProgress', function(up, file) {
 
                $('#' + file.id + " .fileprogress").width(file.percent + "%");
                $('#' + file.id + " span").html(plupload.formatSize(parseInt(file.size * file.percent / 100)));
            });
 
            // a file was uploaded
            uploader.bind('FileUploaded', function(up, file, response) {
 
                $('#' + file.id).fadeOut();  
				
				console.log(response['response']); //return false;

                //parse the json response into an object
                response = $.parseJSON(response["response"]);

                //some kind of error with the upload
                if(typeof(response.error) !== 'undefined') {
                    console.log(response.error);

                } else {
                    //presumably a successful upload so add url to the hidden field
                    src = response.url;   

                    if($this.hasClass("multiple")) {
                        // multiple
                        var v1=$.trim($("#" + imgId).val());
                        if(v1) {
                            v1 = v1 + "," + src;
                        }
                        else {
                            v1 = src;
                        }
                        $("#" + imgId).val(v1);
                    }
                    else {
                        // single
                        $("#" + imgId).val(src + "");
                    }
     
                    // show thumbs 
                    plu_show_thumbs(imgId);
                
                }
            });
 

        });
    }
});
 
function plu_show_thumbs(imgId) { 
    var $=jQuery;
    var thumbsC=$("#" + imgId + "_plupload-thumbs");
    thumbsC.html("");  
    // get urls
    var imagesS=$("#"+imgId).val(); 
    var images=imagesS.split(",");
    for(var i=0; i<images.length; i++) {
        if(images[i]) {
            var thumb=$('<div class="thumb" id="thumb' + imgId +  i + '"><img src="' + images[i] + '" alt="" /><div class="thumbi"><a id="thumbremovelink' + imgId + i + '" href="#">Remove</a></div> <div class="clear"></div></div>');
            thumbsC.append(thumb);
            thumb.find("a").click(function() {
                var ki=$(this).attr("id").replace("thumbremovelink" + imgId , "");
                ki=parseInt(ki);
                var kimages=[];
                imagesS=$("#"+imgId).val();
                images=imagesS.split(",");
                for(var j=0; j<images.length; j++) {
                    if(j != ki) {
                        kimages[kimages.length] = images[j];
                    }
                }
                $("#"+imgId).val(kimages.join());
                plu_show_thumbs(imgId);
                return false;
            });
        }
    }
    if(images.length > 1) {
        thumbsC.sortable({
            update: function(event, ui) {
                var kimages=[];
                thumbsC.find("img").each(function() {
                    kimages[kimages.length]=$(this).attr("src");
                    $("#"+imgId).val(kimages.join());
                    plu_show_thumbs(imgId);
                });
            }
        });
        thumbsC.disableSelection();
    }
}