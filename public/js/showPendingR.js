$(function() {
	$("#tabs").tabs();
	// Hover states on the static widgets
	$(".ui-state-default, .ui-state-default").hover(function() {
		$(this).addClass("ui-state-hover");
	}, function() {
		$(this).removeClass("ui-state-hover");
	});

	// prepare the options for showRequestJoinPending
	var options = {
		url : '/users/orgmanager/getJsonPendingRequest',
		success : showResponse, // post-submit callback
		dataType : 'json', // 'xml', 'script', or 'json' (expected server
	};

	$.ajax(options);
});

// confirm email post-submit callback
function showResponse(data) {
	$("#loadingImg").hide();

	if (data.flag) {

		// clear the old data
		$("#show_pending_request_table .deleteTr").remove();

		var index = 0;
		for (index in data.items) {
			var imgUrl = '/users/media/showImage/'
					+ data.items[index]['requester'] + '/thumb';
			var img = $('<img>')
					.attr(
							{
								'src' : imgUrl,
								'style' : 'width: 40px; height: 40px; vertical-align: -20px; margin-top: 10px;',
							});
			var controlDiv = "<div class='ui-state-default ui-corner-all tdIcon' title='agree'><span id="
					+ data.items[index]['requester']
					+ " class='ui-icon ui-icon-check'></span></div>"
					+ "<div class='ui-state-default ui-corner-all tdIcon' title='reject'><span id="
					+ data.items[index]['requester']
					+ " class='ui-icon ui-icon-close'></span></div>";
			var tr = "<tr class='deleteTr'><td>" + "<img id= 'myindex'><p>"
					+ data.items[index]['first_name'] + "</p></td><td>"
					+ data.items[index]['create_time'] + "</td><td>"
					+ data.items[index]['addition_info'] + "</td><td>"
					+ controlDiv + "</td></tr>";
			$("#show_pending_request_table").append(tr);
			$("#myindex").replaceWith(img);

		}
		// bind the event to check button
		$(".ui-icon-check").click(function(event) {
			// get requester from id
			var requesterEmail = $(this).attr("id");

			//hide the buttons
			$(this).parent().parent().hide();
			
			// ajax request to server
			$.ajax({
				url : '/users/orgmanager/updateUserOrg',
				success : showDecision, // post-submit callback
				dataType : 'json', // 'xml
				type : 'POST',
				data : {
					requester : requesterEmail,
					orgId : $("#orgId").val(),
					decision : 1
				}
			});

		});

		// bind the event to close button
		$(".ui-icon-close").click(function(event) {
			// init the dialog, hide success, show form
			var requesterEmail = $(this).attr("id");
			
			//hide the buttons
			$(this).parent().parent().hide();
			
			// ajax request to server
			$.ajax({
				url : '/users/orgmanager/updateUserOrg',
				success : showDecision, // post-submit callback
				dataType : 'json', // 'xml
				type : 'POST',
				data : {
					requester : requesterEmail,
					orgId : $("#orgId").val(),
					decision : 2
				}
			});

			alert(requesterEmail + "--close");
		});

		// reexcute Hover states on the static widgets
		$(".ui-state-default, .ui-state-default").hover(function() {
			$(this).addClass("ui-state-hover");
		}, function() {
			$(this).removeClass("ui-state-hover");
		});

		// add class for all the td
		$("#show_pending_request_table td").addClass("showInfoTd");

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
						url : '/users/orgmanager/getJsonPendingRequest',
						data : {
							currentPage : page,
							org_name : data.orgName
						},
						type : 'POST',
						success : showResponse,// show the items
						dataType : 'json',
					});
				}
			};

			$('#requestJ_pending_page_div').bootstrapPaginator(options);
		} else {
			$('#requestJ_pending_page_div').hide();
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

function showDecision(data) {
	if (!data.flag) {
		alert(data.flag);
		// if true delete the button and show result
	}
}
