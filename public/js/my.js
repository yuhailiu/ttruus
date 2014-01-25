$(function() {
//jQuery UI	
	var availableTags = [
	         			"ActionScript",
	         			"AppleScript",
	         			"Asp",
	         			"BASIC",
	         			"C",
	         			"C++",
	         			"Clojure",
	         			"COBOL",
	         			"ColdFusion",
	         			"Erlang",
	         			"Fortran",
	         			"Groovy",
	         			"Haskell",
	         			"Java",
	         			"JavaScript",
	         			"Lisp",
	         			"Perl",
	         			"PHP",
	         			"Python",
	         			"Ruby",
	         			"Scala",
	         			"Scheme"
	         		];
 	$("#button").button();
 	
 
 // show a simple loading indicator
	var loader = jQuery('<div id="loader"><img src="images/loading.gif" alt="loading..." /></div>')
		.css({position: "relative", top: "1em", left: "25em", display: "inline"})
		.appendTo("body")
		.hide();
	$(document).ajaxStart(function() {
		loader.show();
	});
//	.ajaxStop(function() {
//		loader.hide();
//	}).ajaxError(function(a, b, e) {
//		throw e;
//	});

// 	$( document ).ajaxStart(function() {
// 		loader.show();
// 	});

//send a ajax request
 	$( ".trigger" ).click(function() {
 		$( ".result" ).load( "loadingImg.html" );
 	});


});