$(function() {
	//init page elements
	$("#edit_p_info_button").button();
	$("#submit_p_info_button").button();
	$("#submit_change_password_button").button();
	
	
	
	//myinfo is clicked
	$("#my_info_a").click(function(){
		//inite the content
		$(".content").hide();
		
		//show my information
		$("#show_my_info_div").show();
	});
	
	//edit myinfo is clicked
	$("#edit_p_info_button").click(function(){
		
		//inite the content
		$(".content").hide();
		
		//show my information
		$("#edit_my_info_div").show();
	});
	
	$("#edit_my_info_li").click(function(){

		//click the edit button
		$("#edit_p_info_button").click();
	});
	
	//change password is clicked
	$("#change_password_li").click(function(){
		
		//inite the content
		$(".content").hide();
		
		//show change password
		$("#change_password_div").show();
	});
	
});

