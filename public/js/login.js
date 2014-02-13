$(function() {
	// jQuery UI
	$("#submit-button").button();

	// validation
	$("#loginform").validate({
		rules : {
			password : {
				required : true,
				minlength : 6,
				maxlength : 22,
				password : true,
			},
			email : {
				required : true,
				email : true,
				remote : "/users/login/checkEmail"
			},

		},
		messages : {
			email : {
				remote : "该用户邮箱没有注册"
			},
		},
	});

});