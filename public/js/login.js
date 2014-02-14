$(function() {
	// jQuery UI
	$("#submit-button").button();

	//prepare the options for ajax
	var options = {
			beforeSubmit : showAjaxWaiting, // pre-submit callback
			success : showResponse, // post-submit callback
	};
	
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
				remote : $("#jsNoSuchEmail").val()
			},
		},
		submitHandler : function(form) {
			// Submit form by Ajax
			jQuery(form).ajaxSubmit(options);
		}
	});
	
	//login for pre-submit callback
	function showAjaxWaiting()
	{
		// show the loading img
		$("#loadingImg").show();
		
		// hide the error message
		$("#error").hide();
		$("#noMatch").hide();
	}
	
	//login for call back
	function showResponse(data)
	{
		if(!data){
			// hide the loading img
			$("#loadingImg").hide();
			
			//show the error message
			$("#error").show();
			$("#noMatch").show();
		} else {
			alert("sucess login");
			window.location.href="login/confirm";
		}
			
		
	}

});