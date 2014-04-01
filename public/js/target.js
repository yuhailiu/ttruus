$(function() {
	// init buttons
	$("#create_single_target_submit").button();
	$("#create_sub_target_submit").button();
	$("#radioset").buttonset();

	// init the accordion menue
	$("#accordion").accordion({
		heightStyle : "content"
	});
	$("#wait_accordion").accordion({
		heightStyle : "content"
	});

	// show target info
	$("#targets_a").click(
			function() {
				// inite the content
				$(".content").hide();

				// get targets from server
				$.getJSON('/target/getTargets', function(json) {
					// show targets
					$("#targets_div").show();
					//clear the history targets
					$("#my_create_targets_div").children().remove();
					$("#my_shared_targets_div").children().remove();

					// put the data to webpage
					var id = 1;
					for (index in json) {
							//save the target to sessionStorage
							sessionStorage.setItem(json[index]['target_id'], JSON.stringify(json[index]));
						
							var h6 = "<div class='target_name'>"
									+ json[index]['target_name'] + "</div>";
							var p = "<p>" + json[index]['target_end_time'] + "</p>";
							var creater = json[index]['target_creater'];
							var info = JSON.parse(sessionStorage.getItem('ownerInfo'));
							var input = "<input type='hidden' value='"+json[index]['target_id']+"'>";
							if(creater == info.email){
								//put receiver in the target
								var img = "<img src='/users/media/showImage/"
									+ json[index]['receiver']
									+ "/thumb' class='target_photo ui-state-default ui-corner-all'>";
								var div = "<div class='target ui-corner-all' id='target_"+ id +"'>" + h6
									+ img + p + input + "</div>";
								$("#my_create_targets_div").append(div);
							} else {
								//put creater in the target
								var img = "<img src='/users/media/showImage/"
									+ json[index]['target_creater']
									+ "/thumb' class='target_photo ui-state-default ui-corner-all'>";
								var div = "<div class='target ui-corner-all' id='target_"+ id +"'>" + h6
								+ img + p + input + "</div>";
								$("#my_shared_targets_div").append(div);
							}
							//bind the click event
							$("#target_" + id).click(function(){
								var target_id = $(this).children("input").val();
								
								//parse the data from sessionStorage
								var target = JSON.parse(sessionStorage.getItem(target_id));
								
								//put the target data to web
								$("#target_right_div").show();
								$("#target_name_h").text(target.target_name);
								$("#target_end_time_p").text(target.target_end_time);
								$("#target_content_p").text(target.target_content);
								
								//get the target's subtarget, if yes put it to webpage
								
								//get the comments
								$("#comments_table").children().remove();
								$.getJSON('/target/getCommentsById?target_id=' + target_id, function(json){
									for(index in json) {
										var span = $("<span class='ui-icon ui-icon-comment comment-position'></span>");
										var tdImg = $("<td style='width: 65px;'></td>");
										var tdImgR = $("<td style='width: 65px;'></td>");
										if(json[index]['who'] == 1){
											var img = $("<img>");
											img.attr('class', 'target_photo ui-state-default ui-corner-all');
											img.attr('alt', 'target_photo');
											
											//get owner photo from sessionStorage
											var storageFiles = JSON.parse(sessionStorage.getItem("storageFiles"));
											img.attr("src", storageFiles.ownerPhoto);
											tdImg = tdImg.append(img);
											
											//add comment span to the td
											tdImg = tdImg.append(span);
											
										} else {
											//get receiver photo from server
											var imgR = $("<img>");
											imgR.attr('class', 'target_photo ui-state-default ui-corner-all');
											imgR.attr('alt', 'target_photo');
											imgR.attr('src', 'users/media/showImage/'+ target.receiver +'/thumb');
											tdImgR = tdImgR.append(imgR);
											tdImgR = tdImgR.append(span);
										}
										
										//comment and time
										var tdComment = $("<td class='comment_td'></td").text(json[index]['comment']);
										var tdTime = $("<td></td>").text(json[index]['create_time']);
										var tr = $("<tr></tr>").append(tdImg);
										tr = tr.append(tdComment);
										tr = tr.append(tdImgR);
										tr = tr.append(tdTime);
										
										//append the tr to table
										$("#comments_table").append(tr);
									}
								});
							});
							id ++;
					}
				});

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


