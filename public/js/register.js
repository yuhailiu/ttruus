$(function() {
	// jQuery UI
	$("#submit-button").button();

	// show a simple loading indicator
	var loader = jQuery(
			'<div id="loader"><img src="/images/loading.gif" alt="loading..." /></div>')
			.css({
				position : "relative",
				top : "1em",
				left : "145px",
				display : "inline"
			}).appendTo("#registerForm").hide();
	
	//prepare the options for ajax submit
	var options = {
			beforeSubmit : showAjaxWaiting, // pre-submit callback
			success : showResponse, // post-submit callback
	};
	
	// validation
	$("#registerForm").validate({
		rules : {
			first_name : {
				required : true,
				maxlength : 18,
				username: true,
			},
			last_name : {
				required : true,
				maxlength : 18,
				username: true,
			},
			password : {
				required : true,
				minlength : 6,
				maxlength : 22,
				password : true
			},
			confirm_password : {
				required : true,
				minlength : 6,
				maxlength : 22,
				equalTo : "#password"
			},
			email : {
				required : true,
				email : true,
				remote : "/users/register/checkEmail"
			},
			terms : "required",
			captcha : {
				required : true,
				remote : "/captcha/process.php"
			},

		},
		messages : {

			email : {
				remote : "该邮箱地址已经被占用"
			},
			terms : "您还没有接受协议",
			captcha : {
				required : "请输入验证码",
				remote : "验证码有误，请重新输入"
			},

		},
		submitHandler : function(form) {
			// Submit form by Ajax
			jQuery(form).ajaxSubmit(options);
		}

	});
	
	//regist for pre-submit callback
	function showAjaxWaiting()
	{
		// show the loading img
		loader.show();
	}
	
	//regist for call back
	function showResponse(data)
	{
		if(data){
			//redirect to confirm page
			window.location.href="register/confirm";
			
		} else {
			// hide the loading img
			loader.hide();
		}
			
		
	}

});