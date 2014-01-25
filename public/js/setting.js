$(function(){
	$( "#tabs" ).tabs();
	$( "#submit-button" ).button();
	// Hover states on the static widgets
	$( "#topright .ui-state-default, #topleft .ui-state-default" ).hover(
		function() {
			$( this ).addClass( "ui-state-hover" );
		},
		function() {
			$( this ).removeClass( "ui-state-hover" );
		}
	);
	
	 	
 	//upload and preview the pic
	'use strict';
    // Change this to the location of your server-side upload handler:
    /* var url = window.location.hostname === 'blueimp.github.io' ?
                '//jquery-file-upload.appspot.com/' : 'server/php/'; */
    var url = '/users/media/processUpload';
	//var url = '/server/php/';
    $('#fileupload').fileupload({
        url: url,
        dataType: 'json',
        //autoUpload: false,
        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
        maxFileSize: 2000000, // 2 MB
        // Enable image resizing, except for Android and Opera,
        // which actually support image resizing, but fail to
        // send Blob objects via XHR requests:
        disableImageResize: /Android(?!.*Chrome)|Opera/
            .test(window.navigator.userAgent),
        previewMaxWidth: 130,
        previewMaxHeight: 130,
        previewCrop: true
    }).on('fileuploadprocessalways', function (e, data) {
    	//remove the old photo img
        $('#dialog-link').children(".ui-corner-all, .text-danger").remove();
        
        //show waiting images
        $('#waittingImg1').show();
        
        var index = data.index,
        file = data.files[index],
        //node = $(data.context.children()[index]);
        node = $('#photoImg');
    if (file.error) {
    	$('#waittingImg1').hide();
        node
            .append($('<p class="text-danger"/>').text("必须是小于2M的图像文件"));
    	}
	}).on('fileuploaddone', function (e, data) {
		

		var id = imgUrl=$('#imgUrl').val();
		var imgUrl = '/users/media/showImage/' + id +'/thumb?dummy=' + new Date().getTime();
        //alert("upload done" + url);
        
      //get the image and update the new one
		$.ajax({
			url: imgUrl,
			cache: false,
			success: function(){
				
				var img=$('<img>').attr({
					'src': imgUrl,
					'class': 'ui-state-default ui-corner-all',
					'style': 'height: 130px; width: 130px;'				
						});
				
				//window.open(img);
				$("#dialog-link").append(img);
				//$('#userImg').replaceWith(img);
				
				//show the dialog window
				//$( "#dialog" ).dialog( "open" );
				//event.preventDefault();
			},
			complete: function(){
				$('#waittingImg1').hide();
		}
		});
    }).on('fileuploadfail', function (e, data) {
        $.each(data.files, function (index, file) {
            var error = $('<span class="text-danger"/>').text('文件上传失败.');
            $('#dialog-link')
                .append('<br>')
                .append(error);
        });
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');
});