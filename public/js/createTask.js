$(function() {
	$("#tabs").tabs();
	$("#task_submit").button();
	$("#sub_task_submit").button();

	// Hover states on the static widgets
	$("#topright .ui-state-default, #topleft .ui-state-default").hover(
			function() {
				$(this).addClass("ui-state-hover");
			}, function() {
				$(this).removeClass("ui-state-hover");
			});

	// init datepicker
	$("#task_begin_time").datepicker({
		minDate : 0,
		changeMonth : true,
		changeYear : true,
		onClose : function(selectedDate) {
			$("#task_end_time").datepicker("option", "minDate", selectedDate);
		}
	});

	$("#task_end_time").datepicker(
			{
				minDate : 0,
				changeMonth : true,
				changeYear : true,
				onClose : function(selectedDate) {
					$("#task_begin_time").datepicker("option", "maxDate",
							selectedDate);
				}

			});

	// when the org is changed, init the member select
	$("#org_id_select").change(function() {
		$("#single_member").attr('checked', false);
		$("#mutiple_member").attr('checked', false);
		$("#single_member_td").hide();

		// show the task assign div
		$("#task_assign_div").show();
	});

	// when single member radio is check, show single memue
	// get members by orgId options
	var memberOptions = {
		url : '/task/getOrgMemberByOrgId',
		type : 'post',
		success : getMemberSuccess,
		dataType : 'json',
		error : showError,
	};

	// create a single member task
	$("#single_member").click(function() {
		// hide the select menue
		$("#task_assign_div").hide();

		// get members from server
		$.ajaxSetup({
			data : {
				orgId : $("#org_id_select").val()
			}
		});
		$.ajax(memberOptions);

		$("#single_member_td").show();

		// submit button show
		$("#task_submit").parent().show();
	});

	// validation and prepare createMainTask options
	var createMainTaskOptions = {
		beforeSubmit : beforeCreateMainTask, // pre-submit callback
		success : successCreateMainTask, // post-submit callback
		dataType : 'json',
		error : showError,
	};

	$("#createMainTask").validate({
		rules : {
			org_id : {
				required : true,
			},
			task_name : {
				required : true,
				username : true,
				maxlength : 140,
			},
			task_begin_time : {
				required : true,
			},
			task_end_time : {
				required : true,
			},
			task_content : {
				address : true,
				maxlength : 140,
			},
			receiver : {
				required : true,
			},
		},
		submitHandler : function(form) {
			// Submit form by Ajax
			jQuery(form).ajaxSubmit(createMainTaskOptions);
		}

	});

	// create a mutiple member task
	$("#mutiple_member").click(function() {
		$("#task_submit").click();
		$("#mutiple_member").attr('checked', false);
		
		// get members from server
		$.ajaxSetup({
			data : {
				orgId : $("#org_id_select").val()
			}
		});
		$.ajax(memberOptions);
	});

	// validation and prepare createSubTask options
	var createSubTaskOptions = {
		beforeSubmit : beforeCreateSubTask, // pre-submit callback
		success : successCreateSubTask, // post-submit callback
		dataType : 'json',
		error : showError,
	};

	$("#createSubTask").validate({
		rules : {
			task_id : {
				required : true,
			},
			sub_task_name : {
				required : true,
				username : true,
				maxlength : 140,
			},
			sub_task_begin_time : {
				required : true,
			},
			sub_task_end_time : {
				required : true,
			},
			sub_task_content : {
				address : true,
				maxlength : 140,
			},
			receiver : {
				required : true,
			},
		},
		submitHandler : function(form) {
			// Submit form by Ajax
			jQuery(form).ajaxSubmit(createSubTaskOptions);
		}

	});

	// create a new task
	$("#create_task_again").click(function() {
		// clear the form
		$(".tdFormElement").children().val(null);
		$("#single_member").attr('checked', false);
		$("#mutiple_member").attr('checked', false);
		$("#single_member_td").hide();

		// hide the success message
		$("#success_insert_div").hide();

		// show the task assign div
		$("#task_assign_div").show();

	});

	// create a new sub task
	$("#create_sub_task_again").click(function() {
		// clear the form
		$("#createSubTask .tdFormElement").children().val(null);

		// hide the success message
		$("#success_insert_sub_div").hide();

		// show the sub task submit button
		$("#sub_task_submit").show();

	});

});

function beforeCreateMainTask() {
	// hide the submit button
	$("#task_submit").parent().hide();
}

function beforeCreateSubTask() {
	// hide the submit button
	$("#sub_task_submit").hide();
}

function successCreateMainTask(data) {
	if (data.flag) {
		// check the type
		if (data.type == 'mutiple') {
			// hide the mainTask form
			$("#createMainTask").hide();
			// show the subTask form
			$("#createSubTask").show();
			
			
			// init the datepicker
			$("#sub_task_begin_time").datepicker(
					{
						minDate : $("#task_begin_time").val(),
						maxDate : $("#task_end_time").val(),
						changeMonth : true,
						changeYear : true,
						onClose : function(selectedDate) {
							$("#sub_task_end_time").datepicker("option",
									"minDate", selectedDate);
						}
					});

			$("#sub_task_end_time").datepicker(
					{
						minDate : $("#task_begin_time").val(),
						maxDate : $("#task_end_time").val(),
						changeMonth : true,
						changeYear : true,
						onClose : function(selectedDate) {
							$("#sub_task_begin_time").datepicker("option",
									"maxDate", selectedDate);
						}

					});

			// write the mainTask id and name to sub task form
			$("#task_id_input").val(data.mainTaskId);
			$("#main_task_name").text(data.task_name);
		} else {
			// show the success message
			$("#success_insert_div").show();
		}
	} else {
		$("#success_insert_div").hide();
		alert("falied to create task");
	}
}

function successCreateSubTask(data) {
	if (data.flag) {
		// show the success message
		$("#success_insert_sub_div").show();
	} else {
		$("#success_insert_div").hide();
		alert("falied to create task");
	}
}

function getMemberSuccess(data) {
	// hide the loading img
	// add the members to select tag
	if (data.flag) {
		// clear the options
		$('.added').remove();
		// add option to select
		for (x in data.members) {
			var option = "<option class='added' value="
					+ data.members[x]['email'] + ">"
					+ data.members[x]['first_name'] + "</option>";
			$("#receiver_select").append(option);
			$("#sub_receiver_select").append(option);
		}
	}
}

function showError() {
	alert($("#alertTimeout").val());
}