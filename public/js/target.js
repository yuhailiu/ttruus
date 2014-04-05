$(function() {
    // init buttons
    $("#create_single_target_submit").button();
    $("#create_sub_target_submit").button();
    $("#comment_submit").button();
    $(".js_button").button();

    // confirm request dialog
    $("#dialog-confirm-request").dialog({
	resizable : false,
	autoOpen : false,
	height : 140,
	modal : true,
	buttons : {
	    "Confirm" : function() {
		$(this).dialog("close");
	    },
	    "Cancel" : function() {
		$(this).dialog("close");
	    }
	}
    });

    // agree the target request
    $("#check_target").click(function() {
	//hide the button
	$("#operate").hide();
	//get the target_id
	var target_id = $("#target_id").val();

	//set the target status
	var target_status = '0';
	switch (sessionStorage.target_status) {
	case '8':
	    target_status = '5'; //achieve
	    break;
	case '9':
	    target_status = '6'; //fail
	    break;
	}
	//update the status by ajax
	$.post('/target/updateStatusById', {
	    target_id : target_id,
	    target_status : target_status
	}, function(data) {
	    //show the new status
	    if (data) {
		//update the sessionStorage
		alert('the status has been update, check it archive');
		//hide the right content
		$("#target_right_div").hide();
		//delete the target div
		$("#" + target_id).remove();
	    } else {
		alert('system busy');
		$("#operate").show();
	    }

	}, 'json');
    });

    // reject the target request
    $("#reject_target").click(function() {
	//hide the button
	$("#operate").hide();
	//get the target_id
	var target_id = $("#target_id").val();

	//set the target status creater reject
	var target_status = '10';

	//update the status by ajax
	$.post('/target/updateStatusById', {
	    target_id : target_id,
	    target_status : target_status
	}, function(data) {
	    //show the new status
	    if (data) {
		//update the sessionStorage
		alert('the status has been update');
	    } else {
		alert('system busy');
		$("#operate").show();
	    }

	}, 'json');
    });

    // init the accordion menue
    $("#accordion").accordion({
	heightStyle : "content"
    });
    $("#wait_accordion").accordion({
	heightStyle : "content"
    });

    // show target info
    $("#targets_a").click(function() {
	// inite the content
	$(".content").hide();

	// get targets from server
	$.getJSON('/target/getAgreeTargets', function(json) {
	    // show targets
	    $("#targets_div").show();
	    // clear the history targets
	    $("#my_create_targets_div").children().remove();
	    $("#my_shared_targets_div").children().remove();

	    // put the data to webpage
	    //var id = 1;
	    for (index in json) {
		// save the target to
		// sessionStorage
		sessionStorage.setItem(json[index]['target_id'], JSON.stringify(json[index]));
		//set id
		var id = json[index]['target_id'];
		var h6 = "<div class='target_name'>" + json[index]['target_name'] + "</div>";
		var p = "<p>" + json[index]['target_end_time'] + "</p>";
		var creater = json[index]['target_creater'];
		var info = JSON.parse(sessionStorage.getItem('ownerInfo'));
		var input = "<input type='hidden' value='" + json[index]['target_id'] + "'>";
		if (creater == info.email) {
		    // put receiver in the
		    // target
		    var img = "<img src='/users/media/showImage/" + json[index]['receiver'] + "/thumb' class='target_photo ui-state-default ui-corner-all'>";
		    var div = "<div class='target ui-corner-all' id='" + id + "'>" + h6 + img + p + input + "</div>";
		    //if it has parent_target_id!=0 then add margin to the div
		    if (json[index]['parent_target_id'] != 0) {
			div = "<div class='target ui-corner-all sub-target' id='" + id + "'>" + h6 + img + p + input + "</div>";
		    }

		    $("#my_create_targets_div").append(div);
		} else {
		    // put creater in the target
		    var img = "<img src='/users/media/showImage/" + json[index]['target_creater'] + "/thumb' class='target_photo ui-state-default ui-corner-all'>";
		    var div = "<div class='target ui-corner-all' id='" + id + "'>" + h6 + img + p + input + "</div>";
		    $("#my_shared_targets_div").append(div);
		}
		// bind the click event
		$("#" + id).click(function() {
		    //clear the box shadow
		    $(".target").attr('style', 'box-shadow: none');

		    //change the border to border shadow
		    $(this).attr('style', 'box-shadow: 10px 10px 5px #888888');

		    var target_id = $(this).children("input").val();

		    // parse the data from sessionStorage
		    var target = JSON.parse(sessionStorage.getItem(target_id));
		    var info = JSON.parse(sessionStorage.getItem('ownerInfo'));

		    // put the target data to web
		    $("#target_right_div").show();
		    $("#target_name_h").text(target.target_name);
		    $("#target_end_time_p").text(target.target_end_time);
		    $("#target_content_p").text(target.target_content);

		    //put target id to input
		    $("#target_id").val(target_id);

		    //init operate div
		    $("#operate").hide();
		    //clear the status
		    $("#target_status").children().remove();
		    
		    switch (target.target_status) {
		    case '8': //apply achieve
			//show status ongoing ontime
			var span = $("<span class='ui-icon ui-icon-play' style='background-color: #90EE90'></span>");
			$("#target_status").append(span);
			$("#operate").show();
			$("#miss_target").hide();
			$("#hit_target").show();
			//save the status to session
			sessionStorage.target_status = '8';
			break;
		    case '9': //apply fail
			$("#operate").show();
			$("#hit_target").hide();
			$("#miss_target").show();
			//save it to session
			sessionStorage.target_status = '9';
			break;
		    case '5': //achieve
			//show status achieve
			var span = $("<span class='ui-icon ui-icon-bullet' style='background-color: #90EE90'></span>");
			$("#target_status").append(span);
			break;
		    default:
			
		    }

		    //if email is as same as creater, who is 1
		    if (info.email == target.target_creater) {
			$(".who").val('1');
		    } else {
			$(".who").val('2');
		    }

		    // get the target's subtarget, if yes put it to webpage

		    // get the comments
		    $("#comments_table").children().remove();
		    $.getJSON('/target/getCommentsById?target_id=' + target_id, function(json) {
			for (index in json) {
			    var tr = createComment(index, json, target.receiver);
			    // append the tr to table
			    $("#comments_table").append(tr);
			}
		    });
		});
		//id++;
	    }
	});

    });

    //add a comment
    var options = {
	beforeSubmit : beforeComment, // pre-submit callback
	success : commentSuccess, // post-submit callback
	dataType : 'json', // 'xml', 'script', or 'json' (expected server
    };
    $("#add_comment_form").validate({
	rules : {
	    comment : {
		required : true,
		address : true,
		maxlength : 140,
	    },
	},
	submitHandler : function(form) {
	    // Submit form by Ajax
	    jQuery(form).ajaxSubmit(options);
	}

    });

    // create a target
    $("#create_target_li").click(function() {
	// inite the content
	$(".content").hide();

	// show create target
	$("#create_target_div").show();
    });

    // deal with waiting targets
    $("#waiting_target_li").click(function() {
	// inite the content
	$(".content").hide();

	// show create target
	$("#waiting_target_div").show();
    });

    // deal with close targets
    $("#close_target_li").click(function() {
	// inite the content
	$(".content").hide();

	// show create target
	$("#close_target_div").show();
    });

});

//create comment
function createComment(index, json, receiver) {
    var span = $("<span class='ui-icon ui-icon-comment comment-position'></span>");
    var tdImg = $("<td style='width: 65px;'></td>");
    var tdImgR = $("<td style='width: 65px;'></td>");
    if (json[index]['who'] == 1) {
	var img = $("<img>");
	img.attr('class', 'target_photo ui-state-default ui-corner-all');
	img.attr('alt', 'target_photo');

	// get owner photo from	sessionStorage
	var storageFiles = JSON.parse(sessionStorage.getItem("storageFiles"));
	img.attr("src", storageFiles.ownerPhoto);
	tdImg = tdImg.append(img);

	// add comment span to the td
	tdImg = tdImg.append(span);

    } else {
	// get receiver photo from server
	var imgR = $("<img>");
	imgR.attr('class', 'target_photo ui-state-default ui-corner-all');
	imgR.attr('alt', 'target_photo');
	imgR.attr('src', 'users/media/showImage/' + receiver + '/thumb');
	tdImgR = tdImgR.append(imgR);
	tdImgR = tdImgR.append(span);
    }

    // comment and time 
    var tdComment = $("<td class='comment_td'></td").text(json[index]['comment']);
    var tdTime = $("<td></td>").text(json[index]['create_time']);
    var tr = $("<tr></tr>").append(tdImg);
    tr = tr.append(tdComment);
    tr = tr.append(tdImgR);
    tr = tr.append(tdTime);
    return tr;
}

//before comment submit
function beforeComment() {
    //show the loading icon
    //hide the button
}
//
function commentSuccess(data) {
    if (data) {
	//add the comment to comments list
	//create an array
	var json = {
	    1 : {
		comment : $("#comment").val(),
		who : '1',
		create_time : 'just'
	    }
	};

	var tr = createComment(1, json);
	$("#comments_table").prepend(tr);

	//clear the comment
	$("#comment").clearFields();
    }
}
