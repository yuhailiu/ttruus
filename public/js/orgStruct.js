$(function() {

	// prepare orgMember options
	var orgMemberOptions = {
		url : '/users/orgmanager/getJsonOrgMembers',
		data : {
			orgId : $("#orgId").val()
		},
		type : 'post',
		success : getMembersSuccess,
		dataType : 'json'
	};

	// when the member label is click, load the date for it
	$("#org_member_li").click(function() {
		// show the loading img
		$("#org_structure_loading_div").show();
		$.ajax(orgMemberOptions);
	});

	for ( var i = 1; i < 11; i++) {
		$("#orchart_dialog_div" + i).dialog({
			autoOpen : false,
			width : 500,
			modal : true,
			show : {
				effect : "blind",
				duration : 1000
			},
			hide : {
				effect : "explode",
				duration : 1000
			}
		});

	}
});

function getMembersSuccess(data) {
	// hide the waiting Img
	$("#org_structure_loading_div").hide();

	if (data.flag) {
		$(".tab-left").show();
		$(".tab-right").show();
		// show creater's photo and first_name
		$("#createrName").text(data.creater.first_name);
		$("#createrImg").attr('src',
				'/users/media/showImage/' + data.creater.email + '/thumb');
		$("#createrName").text(data.creater.first_name);
		$("#createrImg").parent().click(
				function() {
					$("#orchart_dialog_div1").dialog("open");
					$("#orchart_dialog_div1 .first_name_dialog_td").text(
							data.creater.first_name);
					$("#orchart_dialog_div1 .last_name_dialog_td").text(
							data.creater.last_name);
					$("#orchart_dialog_div1 .position_dialog_td").text(
							data.creater.title);
					$("#orchart_dialog_div1 .telephone1_dialog_td").text(
							data.creater.telephone1);
					$("#orchart_dialog_div1 .telephone2_dialog_td").text(
							data.creater.telephone2);
					$("#orchart_dialog_div1 .address_dialog_td").text(
							data.creater.address);
				});

		// show member's photo and first_name
		// clear the photo show area
		$("#members_ul li").remove();
		var index = 0;
		for (index in data.items) {
			var imgUrl = '/users/media/showImage/' + data.items[index]['email']
					+ '/thumb';
			var img = $('<img>').attr({
				'src' : imgUrl,
				'class' : 'structurePic ui-state-default ui-corner-all '
			});
			var p = "<p>" + data.items[index]['first_name'] + "</p>";
			var li = $('<li>').attr({
				'style' : 'float: left; margin-left: 18px; margin-right: 18px',
			});
			var intIndex = parseInt(index);
			var id = intIndex + 2;
			var a = $('<a>').attr({
				'href' : '#',
				'id' : id,
			});
			var close = "<p class='ui-state-default ui-corner-all' name="
					+ data.items[index]['email']
					+ " style='margin-top: -20px;width: 16px'><span class='ui-icon ui-icon-closethick'></span></p>";
			a.append(img);
			a.append(p);
			li.append(a);
			li.append(close);

			$("#members_ul").append(li);

			$("#orchart_dialog_div" + id + " .first_name_dialog_td").text(
					data.items[index]['first_name']);
			$("#orchart_dialog_div" + id + " .last_name_dialog_td").text(
					data.items[index]['last_name']);
			$("#orchart_dialog_div" + id + " .position_dialog_td").text(
					data.items[index]['title']);
			$("#orchart_dialog_div" + id + " .telephone1_dialog_td").text(
					data.items[index]['telephone1']);
			$("#orchart_dialog_div" + id + " .telephone2_dialog_td").text(
					data.items[index]['telephone2']);
			$("#orchart_dialog_div" + id + " .address_dialog_td").text(
					data.items[index]['address']);

			// img and name is click function
			$("#" + id).click(function() {
				id = $(this).attr('id');
				$("#orchart_dialog_div" + id).dialog("open");

			});

			// delete is click function
			$("#" + id).next().click(function() {
				if (confirm($("#delete_member_sure").val())) {
					alert($(this).attr('name'));

					// delete the user from org_user table by orgId and email
					var deleteUserFromOrgOptions = {
						url : '/users/orgmanager/deleteUserFromOrg',
						data : {
							orgId : data.orgId,
							userEmail : $(this).attr('name'),
						},
						success : deleteSuccess,
						type : 'post',
						dataType : 'json'
					};
					$.ajax(deleteUserFromOrgOptions);
				}
				;
			});
		}

		// Hover states on the static widgets
		$(".ui-state-default, ui-state-default").hover(function() {
			$(this).addClass("ui-state-hover");
		}, function() {
			$(this).removeClass("ui-state-hover");
		});

		// show paginator if the page count > 1
		if (data.totalPages > 1) {
			var options = {
				currentPage : data.currentPage,
				totalPages : data.totalPages,
				onPageClicked : function(e, originalEvent, type, page) {
					$("#org_structure_loading_div").show();
					$(".tab-left").hide();
					$(".tab-right").hide();
					// get relative data from server with page and orgId
					// submit an ajax request with url
					$.ajax({
						url : '/users/orgmanager/getJsonOrgMembers',
						data : {
							orgId : data.orgId,
							currentPage : page,
						},
						type : 'POST',
						success : getMembersSuccess,// show the items
						dataType : 'json',
					});
				}
			};

			$("#org_member_paginator_div").bootstrapPaginator(options);
		}

	} else {
		// show need build a team
		$("#need_org_message_div").show();
	}
}

function deleteSuccess(data){
	console.log($(this).attr('name'));
}
