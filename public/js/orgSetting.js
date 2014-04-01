
$(function() {
	

	$("#tabs").tabs();
	$("#submit-button").button();
	// Hover states on the static widgets
	$("#topright .ui-state-default, #topleft .ui-state-default").hover(
			function() {
				$(this).addClass("ui-state-hover");
			}, function() {
				$(this).removeClass("ui-state-hover");
			});

	// upload file
	// prepare the options for upload
	var uploadOptions = {
		beforeSubmit : beforeUpload, // pre-submit callback
		success : afterUpload, // post-submit callback
		dataType : 'json', // 'xml', 'script', or 'json' (expected server
	};

	// bind change to #uploadFile
	$("#uploadFile").change(function() {
		// check file size and type
		if (checkImgType(this)) {
			// submit the upload form
			$("#fileupload").ajaxSubmit(uploadOptions);
		} else {
			// show the error
			$("#uploadError").show();

		}

	});

	// validation
	// prepare the options
	var options = {
		beforeSubmit : showAjaxWaiting, // pre-submit callback
		success : showResponse, // post-submit callback
		dataType : 'json', // 'xml', 'script', or 'json' (expected server
	// resetForm: true, //reset all the form after success
	};

	// user setting form
	$("#orgSet").validate({
		rules : {
			org_name : {
				username : true,
				maxlength : 140,
			},
			org_website : {
				website : true,
				maxlength : 140,
			},
			org_tel : {
				telephone : true,
				maxlength : 15,
			},
			org_des : {
				address : true,
				maxlength : 140,
			},
			org_address : {
				address : true,
				maxlength : 140,
			},
		},
		submitHandler : function(form) {
			// Submit form by Ajax
			jQuery(form).ajaxSubmit(options);
		}
	});

});

// upload form beforeUpload
function beforeUpload() {

	// hide the old photo img
	$('#dialog-link').children(".ui-corner-all, .text-danger").hide();

	// show waiting images
	$('#waittingImg1').show();
}

// uplaod form afterUpload
function afterUpload(data) {
	if (data.flag) {

		// hide the error message
		$("#uploadError").hide();

		// remove the old img
		$('#dialog-link').children(".ui-corner-all, .text-danger").remove();

		var key = $('#keyValue').val();
		var imgUrl = '/users/media/showImage/' + key + '/logoThumb?dummy='
				+ new Date().getTime();

		// get the image and update the new one
		$.ajax({
			url : imgUrl,
			cache : false,
			success : function() {

				var img = $('<img>').attr({
					'src' : imgUrl,
					'class' : 'ui-state-default ui-corner-all',
					'style' : 'height: 130px; width: 120px;'
				});

				// window.open(img);
				$("#dialog-link").append(img);
			},
			complete : function() {
				$('#waittingImg1').hide();
			}
		});
	} else {

		$('#waittingImg1').hide();

		// show old img
		$('#dialog-link').children(".ui-corner-all, .text-danger").show();

		// show the error
		$("#uploadError").show();

	}
}

// user set form pre-submit callback
function showAjaxWaiting() {

	// show the loading img
	$("#loadingImg").show();

	// disable the submit button and show loadingimg
	$("#submit-button").bind("click", function(event) {
		event.preventDefault();
	});

	// here we could return false to prevent the form from being submitted;
	// returning anything other than false will allow the form submit to
	// continue
	return true;
}

// confirm email post-submit callback
function showResponse(data) {
	if (data.flag) {
		// hide form and others
		$("#showInfo").hide();
		$("#loadingImg").hide();

		// show success message
		$("#successUpdate").show();
		
		//close the window in 5 seconds
		window.opener=null; 
		window.open('', '_self', '');//mock open by self
		setTimeout("window.close()", 5000);//close the window without prompt
	} else {
		// failed
		// hidden loadingimg
		$("#loadingImg").hide();

		// enable the button
		$("#submit-button").unbind("click");

		// show the error message
		$("#settingError").show();

	}
}

function close_window() {
	window.close();// close the window without prompt
}

function checkImgType(this_) {

	var filepath = $(this_).val();
	var extStart = filepath.lastIndexOf(".");
	var ext = filepath.substring(extStart, filepath.length).toUpperCase();

	if (ext != ".PNG" && ext != ".GIF" && ext != ".JPG" && ext != ".JPEG") {
		return false;
	}
	var f = this_.files[0];
	if (f.size > 2 * 1024 * 1000 || f.fileSize > 2 * 1024 * 1000) {
		return false;
	}
	return true;
}
