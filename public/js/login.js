$(function() {
	// jQuery UI
	$("#submit-button").button();

	// prepare the options for ajax
	var options = {
		beforeSubmit : showAjaxWaiting, // pre-submit callback
		success : showResponse, // post-submit callback
		dataType : 'json',
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

	// login for pre-submit callback
	function showAjaxWaiting() {
		// show the loading img
		$("#loadingImg").show();

		// disable the submit button and show loadingimg
		$("#submit-button").bind("click", function(event) {
			event.preventDefault();
		});

		// hide the error message
		$("#error").hide();
		$("#noMatch").hide();
	}

	// login for call back
	function showResponse(data) {

		if (!data.flag) {
			// hide the loading img
			$("#loadingImg").hide();

			// enable the button
			$("#submit-button").unbind("click");

			// show the error message
			$("#error").show();
			$("#noMatch").show();

			// show the failedTimes
			$("#errorTimes").text(data.failedTimes);
		} else {
			window.location.href = "login/confirm";
		}

	}

});