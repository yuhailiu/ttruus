$(function() {
//jQuery UI	
 	$("#submit-button").button();
 	
 	
//validation  
 	$("#loginform").validate({
		rules: {
			
			password: {
				required: true,
				minlength: 5
			},
			email: {
				required: true,
				email: true,
				remote: "/users/login/checkEmail"
			},
			
		},
		messages: {
			
			password: {
				required: "请输入密码",
				minlength: "密码的长度至少为5个字符"					
			},
			
			email: {
				email: "请输入正确的电子邮箱地址",
				required: "请输入电子邮箱地址",
				remote: "该邮箱地址用户不存在"			
			},
			
		}
	});
	

});