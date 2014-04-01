$(function() {
	
	//init buttons
	$("#search_submit").button();
	$("#invite_helper_submit").button();
	$( "#accordion_waiting" ).accordion();
	
	//show target info
	$("#helper_a").click(function(){
		//inite the content
		$(".content").hide();
		
		//show targets
		$("#helper_div").show();
	});
	
	//look for helper
	$("#lookfor_helper_li").click(function(){
		//inite the content
		$(".content").hide();
		
		//show create target
		$("#lookfor_helper_div").show();
	});
	
	//help others
	$("#help_others_li").click(function(){
		//inite the content
		$(".content").hide();
		
		//show create target
		$("#help_others_div").show();
	});
	
	//waiting helper
	$("#waiting_helper_li").click(function(){
		//inite the content
		$(".content").hide();
		
		//show create target
		$("#waiting_helper_div").show();
	});
	
	
});
