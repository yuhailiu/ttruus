$(function(){
	$("#refreshimg").click(function(){
		$.post('/captcha/newsession.php');
		$("#captchaimage").load('/captcha/image_req.php');
		return false;
	});

});
function refreshimg(){
	$.post('/captcha/newsession.php');
	$("#captchaimage").load('/captcha/image_req.php');
	return false;
}
