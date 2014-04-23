$(function() {

    //init buttons
    $("#search_submit").button();
    $("#invite_helper_submit").button();
    $("#accordion_waiting").accordion();
    $('#helpers_page_div').bootstrapPaginator();

    //show target info
    $("#helper_a").click(function() {
	//inite the content
	$(".content").hide();

	//get helpers from server
	$.getJSON('helper/getHelpersByOwner', function(json) {
	    if(json.flag){
		//clear the web
		$(".helpers_group").children().remove();
		
		//show the helers by first name
		for(x in json.helpers){
		    var img = "<img alt='photo' src='/users/media/showImage/"+ json.helpers[x]['email'] +"/thumb' class='personalPhoto ui-corner-all'>";
		    var p = "<p>" + json.helpers[x]['first_name']+ "</p>";
		    var p1 = "<p>"+ json.helpers[x]['self_descript'] +"</p>";
		    var p2 = "<p style='float: right'>x</p>";
		    var div = "<div class='personalInfo ui-corner-all'>" 
			+ img + p + p1 + p2
			+ "</div>";
		    $(".helpers_group").append(div);
		}
		
		//bind the click event to the div
		
	    }else {
		alert(json.message);
	    }

	});

	//show targets
	$("#helper_div").show();

    });

    //look for helper
    $("#lookfor_helper_li").click(function() {
	//inite the content
	$(".content").hide();

	//show create target
	$("#lookfor_helper_div").show();
    });

    //help others
    $("#help_others_li").click(function() {
	//inite the content
	$(".content").hide();

	//show create target
	$("#help_others_div").show();
    });

    //waiting helper
    $("#waiting_helper_li").click(function() {
	//inite the content
	$(".content").hide();

	//show create target
	$("#waiting_helper_div").show();
    });

});
