$(function() {
//jQuery UI		
 	$("#submit-button").button();

// show a simple loading indicator
	var loader = jQuery('<div id="loader"><img src="/images/loading.gif" alt="loading..." /></div>')
		.css({position: "relative", 
			top: "1em", 
			left: "145px", 
			display: "inline"})
		.appendTo("#registerForm")
		.hide();
	$(document).ajaxStart(function() {		
		loader.show();
	})
	.ajaxStop(function() {
		loader.hide();
	}).ajaxError(function(a, b, e) {
		throw e;
	});
//validation
 	
 	$("#registerForm").validate({
		rules: {
			first_name: {
				required: true,
				maxlength: 18,
			},
			last_name: {
				required: true,
				maxlength: 18,
			},
			password: {
				required: true,
				minlength: 5,
				maxlength: 14,
			},
			confirm_password: {
				required: true,
				minlength: 5,
				maxlength: 14,
				equalTo: "#password"
			},			
			email: {
				required: true,
				email: true,
				//remote: "emails.action"
				remote: "/users/register/checkEmail"
			},
			terms: "required",
			captcha: {
				required: true,
				remote: "/captcha/process.php"
			},
			
		},
		messages: {
			first_name: {
				required: "请输入您的名字",
				maxlength: "名字的长度最多为18个字符"	
			},
			last_name: {
				required: "请输入您的姓氏",
				maxlength: "姓氏的长度最多为18个字符"	
			},
			password: {
				required: "请输入密码",
				minlength: "密码的长度至少为5个字符",
				maxlength: "密码的长度最多为14个字符"	
			},
			confirm_password: {
				required: "请重复输入您的密码",
				minlength: "密码的长度至少为5个字符",
				maxlength: "密码的长度最多为14个字符",
				equalTo: "确认密码与建立密码不一致"
			},
			
			email: {
				email: "请输入正确的电子邮箱地址",
				required: "请输入电子邮箱地址",
				remote: "该邮箱地址已经被占用"			
			},
			terms: " ",
			captcha: {
				required: "请输入验证码",
				remote: "验证码有误，请重新输入"	
			},
			
		},
//		disable the submit
//		submitHandler: function(form) {
//			jQuery(form).ajaxSubmit({
//				target: "#result"
//			});
//			
//		},
		onkeyup: false,
	});

});