$(function() {
	$("#tabs").tabs();
	$("#submit-button").button();
	$("#captcha-submit").button();
	$("#resetPassword-submit").button();
	
	// prepare the options
	var options = {
		beforeSubmit : showAjaxWaiting, // pre-submit callback
		success : showResponse, // post-submit callback
		dataType : 'json', // 'xml', 'script', or 'json' (expected server
		timeout:   5000,
		error: function(){
			alert($("#alertTimeout").val());
			
			// hidden loadingimg
			$("#loadingImg").hide();
			
			// enable the button
			$("#submit-button").unbind("click");

		},
	};

	// validate confirmEmailForm
	$("#confirmEmailForm").validate({
		rules : {
			email : {
				required : true,
				email : true,
			},
			captcha : {
				required : true,
				remote : "/captcha/process.php"
			},
		},
		submitHandler : function(form) {
			// Submit form by Ajax
			jQuery(form).ajaxSubmit(options);
		}
	});

	// prepare the captchaOptions
	var captchaOptions = {
		beforeSubmit : captchaWaiting, // pre-submit callback
		success : captchaResponse, // post-submit callback
		dataType : 'json', // 'xml', 'script', or 'json' (expected server
	// response type)
	};
	
	//validate confirmCaptchaForm
	$("#confirmCaptchaForm").validate({
		rules: {
			captcha :{
				required : true,
				maxlength : 6,
			}
		},
		submitHandler : function(form) {
			// Submit form by Ajax
			jQuery(form).ajaxSubmit(captchaOptions);
		}
	});


	// prepare the resetOptions
	var resetOptions = {
		beforeSubmit : resetWaiting, // pre-submit callback
		success : resetResponse, // post-submit callback
		dataType : 'json', // 'xml', 'script', or 'json' (expected server
	// response type)
	};
	
	//validate resetPasswordForm
	$("#resetPasswordForm").validate({
		rules: {
			captcha :{
				required : true,
				maxlength : 6,
			},
			email : {
				required : true,
				email : true,
			},
			password : {
				required : true,
				minlength : 6,
				maxlength : 22,
				password : true
			},
			confirmPassword : {
				required : true,
				minlength : 6,
				maxlength : 22,
				equalTo : "#password"
			},
		},
		submitHandler : function(form) {
			// Submit form by Ajax
			jQuery(form).ajaxSubmit(resetOptions);
		}
	});

});

// reset password pre-submit callback
function resetWaiting() {
	// disable the submit button and show loadingimg
	$("#resetPassword-submit").bind("click", function(event) {
		event.preventDefault();
	});

	// show the loading img
	$("#loadingImg").show();

	// here we could return false to prevent the form from being submitted;
	// returning anything other than false will allow the form submit to
	// continue
	return true;
}

// reset passwod post-submit callback
function resetResponse(data) {
	// alert(data.message);
	// success flag =1, false flag = 0
	if (data.flag == 1) {
		// sucess

		// remove the captcha input form and email success,error message
		$("#resetPasswordDiv").remove();
		$("#captchaSuccess").remove();
		$("#passwordError").remove();

		// hidden loadingimg
		$("#loadingImg").hide();

		// show the resetpassword success message
		$("#passwordSuccess").show();

	} else if (data.flag == 0) {
		// failed
		// hidden loadingimg and captcha success and email error
		$("#loadingImg").hide();

		// enable the button
		$("#resetPassword-submit").unbind("click");

		// remove captcha sucess message
		$("#captchaSuccess").remove();

		// show the resetPassword error message
		$("#passwordError").show();

	} else {
		alert("system busy");
	}

}
// confirm captcha pre-submit callback
function captchaWaiting() {
	// disable the submit button and show loadingimg
	$("#captcha-submit").bind("click", function(event) {
		event.preventDefault();
	});

	// show the loading img
	$("#loadingImg").show();

	// here we could return false to prevent the form from being submitted;
	// returning anything other than false will allow the form submit to
	// continue
	return true;
}

// confirm captcha post-submit callback
function captchaResponse(data) {
	// success flag =1, false flag = 0
	if (data.flag == 1) {
		// sucess
		// change focus to third label
		$("#resetPassword_list2").removeClass("focusBackgroud");
		$("#resetPassword_list3").addClass("focusBackgroud");

		// put the email and captcha to hidden fields
		$("#hideEmail_reset").val($("#hideEmail").val());
		$("#hideCaptcha").val($("#captcha").val());

		// remove the captcha input form and email success,error message
		$("#captchaDiv").remove();
		$("#emailSuccess").remove();
		$("#captchaError").remove();

		// hidden loadingimg
		$("#loadingImg").hide();

		// gen a new form
		$("#resetPasswordDiv").show();

		// show the captcha success message
		$("#captchaSuccess").show();

	} else if (data.flag == 0) {
		// failed
		// hidden loadingimg and captcha success and email error
		$("#loadingImg").hide();

		// enable the button
		$("#captcha-submit").unbind("click");

		// remove email sucess message
		$("#emailSuccess").remove();

		// show the captcha error message
		$("#captchaError").show();

	} else {
		alert("system busy");
	}

}

// confirm email pre-submit callback
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

	if (data.flag == 1) {
		// sucess
		// change focus to second label
		$("#resetPassword_list1").removeClass("focusBackgroud");
		$("#resetPassword_list2").addClass("focusBackgroud");

		// put the email address to hideEmail
		$("#hideEmail").val($("#Email").val());

		// remove the input form
		$("#confirmEmailDiv").remove();

		// hidden loadingimg
		$("#loadingImg").hide();

		// gen a new form
		$("#captchaDiv").show();

		// hide the error message
		$("#emailError").hide();

		// show the success message
		$("#emailSuccess").show();

	} else {
		// failed
		// hidden loadingimg
		$("#loadingImg").hide();

		// enable the button
		$("#submit-button").unbind("click");

		// show the error message
		$("#emailError").show();

		// hide the captcha error message
		$("#captcha_error").hide();

	}
}