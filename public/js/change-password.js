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

	// prepare the options for ajax
	var options = {
		beforeSubmit : showAjaxWaiting, // pre-submit callback
		success : showResponse, // post-submit callback
		dataType : 'json',
	};

	// validate the changePassword form
	$("#changePassword").validate({
		rules : {
			old_password : {
				maxlength : 22,
				minlength : 5,
				password : true,
			},
			password : {
				maxlength : 22,
				minlength : 5,
				password : true,
			},
			confirm_password : {
				maxlength : 22,
				minlength : 5,
				password : true,
				equalTo : "#password"
			}
		},
		submitHandler : function(form) {
			// Submit form by Ajax
			jQuery(form).ajaxSubmit(options);
		}
	});
	// change passord for pre-submit callback
	function showAjaxWaiting() {
		// show the loading img
		$("#loadingImg").show();

		// disable the submit button and show loadingimg
		$("#submit-button").bind("click", function(event) {
			event.preventDefault();
		});
		
		// hide the error message
		$("#passwordError").hide();

		// here we could return false to prevent the form from being submitted;
		// returning anything other than false will allow the form submit to
		// continue
		return true;
	}

	// change password for call back
	function showResponse(data) {
		if (!data.flag) {
			// hide the loading img
			$("#loadingImg").hide();
			
			// enable the button
			$("#submit-button").unbind("click");

			// show the error message
			$("#passwordError").show();

		} else {
			//remove the change password form
			$("#changePassword").remove();
			$("#changeSuccess").show();
			
			//close the window in 5 seconds
			window.opener=null; 
			window.open('', '_self', '');//mock open by self
			setTimeout("window.close()", 5000);//close the window without prompt
		}
	}
});