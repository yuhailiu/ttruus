$(function() {
	
	$("#tabs").tabs();
	$(".confirm_button").button();
	$("#joinOrg_submit_button").button();
	// Hover states on the static widgets
	$("#topright .ui-state-default, #topleft .ui-state-default").hover(
			function() {
				$(this).addClass("ui-state-hover");
			}, function() {
				$(this).removeClass("ui-state-hover");
			});

	// bind ajax request to #reloadOrg
	$("#reloadOrg").click(
			function() {
				$.getJSON("/users/orgmanager/getOrg", {
					id : 1
				}, function(json) {
					$("#td1").text(json.org_name);

					// reload the img from server
					var reloadSrc = $("#td2>img").attr('src');
					$("#td2>img").attr('src',
							reloadSrc + "?dummy=" + new Date().getTime());

					// update the website and href link
					$("#td3 > a").text(json.org_website);
					$("#td3 > a").attr('href', 'http://' + json.org_website);

					$("#td4").text(json.org_CT);
					$("#td5").text(json.org_LM);
					$("#td6").text(json.org_address);
					$("#td7").text(json.org_tel);
					$("#td8").text(json.org_des);
				});
			});

	// prepare the options for orgSearchForm
	var options = {
		beforeSubmit : showAjaxWaiting, // pre-submit callback
		success : showResponse, // post-submit callback
		dataType : 'json', // 'xml', 'script', or 'json' (expected server
	// resetForm: true, //reset all the form after success
	};

	// bind ajax request to #orgSearchForm
	$("#orgSearchForm").validate({
		rules : {
			org_name : {
				required : true,
				username : true,
				maxlength : 140,
			}
		},
		submitHandler : function(form) {
			// Submit form by Ajax
			jQuery(form).ajaxSubmit(options);
		}

	});

	// prepare the dialog window
	$("#joinDialog").dialog({
		autoOpen : false,
		width : 400,
	});

	// prepare options for joinOrgForm
	var joinOrgOptions = {
		beforeSubmit : beforeJoinOrgFormSubmit, // pre-submit callback
		success : successJoinOrgFormSubmit, // post-submit callback
		dataType : 'json', // 'xml', 'script', or 'json' (expected server
		error : showError,
	};

	$("#joinOrgForm").validate({
		rules : {
			additionInfo : {
				address : true,
				maxlength : 140,
			}
		},
		submitHandler : function(form) {
			// Submit form by Ajax
			jQuery(form).ajaxSubmit(joinOrgOptions);
		}

	});
	
	//select the focus tab
	var id = $("#tabs_id_input").val();
	$("#tabs_a_" + id).click();

});

// JoinOrg submit call back
function beforeJoinOrgFormSubmit() {
	
	//show loading img
	$("#dialogLoadingImg").show();
	
	//disable submit button
	$("#joinOrg_submit_button").bind("click", function(event) {
		event.preventDefault();
	});
	
}

function successJoinOrgFormSubmit(data) {
	
	//enable the submit button
	$("#joinOrg_submit_button").unbind("click");
	//hide the loading img
	$("#dialogLoadingImg").hide();
	if(data.flag){
		//hide the form
		$("#joinOrg_div").hide();
		//show the success message
		$("#successJoinOrg_div").show();
	}else{
		//show the error message
		$("#failedJoinOrg_div").show();
	}
}

// orgSearchForm pre-submit callback
function showAjaxWaiting() {

	// show the loading img
	$("#loadingImg").show();

	// disable the submit button and show loadingimg
	$("#org_submit_button").bind("click", function(event) {
		event.preventDefault();
	});

	// here we could return false to prevent the form from being submitted;
	// returning anything other than false will allow the form submit to
	// continue
	return true;
}

// confirm email post-submit callback
function showResponse(data) {
	$("#loadingImg").hide();
	// enable the button
	$("#org_submit_button").unbind("click");

	if (data.flag) {
		// show the org_search_result_div
		$("#org_search_result_div").show();

		// hide the no result div
		$("#no_result_div").hide();

		// clear the old data
		$("#org_search_result_table .deleteTr").remove();

		var index = 0;
		for (index in data.items) {
			var imgUrl = '/users/media/showImage/' + data.items[index]['id']
					+ '/logoThumb';
			var img = $('<img>')
					.attr(
							{
								'src' : imgUrl,
								'style' : 'width: 40px; height: 40px; vertical-align: -20px; margin-top: 10px;',
							});

			var tr = "<tr class='deleteTr'><td>"
					+ data.items[index]['org_name']
					+ "</td><td><img id= 'myindex'>" + "</td><td>"
					+ data.items[index]['org_website'] + "</td><td>"
					+ data.items[index]['org_des'] + "</td><td>" + "<a id="
					+ data.items[index]['id'] + " href='#'>加入</a>"
					+ "</td></tr>";
			$("#org_search_result_table").append(tr);
			$("#myindex").replaceWith(img);
			$("#org_search_result_table a").button();

			// bind the event to join button
			// Link to open the dialog
			$("#org_search_result_table a").click(function(event) {
				//init the dialog, hide success, show form
				var orgId = $(this).attr("id");
				$("#joinOrg_div").show();
				$("#successJoinOrg_div").hide();
				$("#joinDialog").dialog("open");
				$("#dialogOrgId").val(orgId);
				event.preventDefault();
			});
		}
		// show the paginator
		if (data.totalPages > 1) {
			var options = {
				currentPage : data.currentPage,
				totalPages : data.totalPages,
				onPageClicked : function(e, originalEvent, type, page) {
					$("#loadingImg").show();
					// get relative data from server with page and orgname
					// submit an ajax request with url
					$.ajax({
						url : '/users/orgmanager/searchOrgByName',
						data : {
							org_name : data.orgName,
							currentPage : page,
						},
						type : 'POST',
						success : showResponse,// show the items
						dataType : 'json',
					});
				}
			};

			$('#org_search_result_page_div').bootstrapPaginator(options);
		} else {
			$('#org_search_result_page_div').hide();
		}

	} else {
		// failed
		// hidden loadingimg
		$("#loadingImg").hide();

		// hide the result div
		$("#org_search_result_div").hide();

		// show the error message
		$("#no_result_div").show();

	}
}
function showError(){
	alert("system busy, please try later");
}
