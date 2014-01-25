$(function(){
	$( "#tabs" ).tabs();
	// Hover states on the static widgets
	$( "#topright .ui-state-default, #topleft .ui-state-default" ).hover(
		function() {
			$( this ).addClass( "ui-state-hover" );
		},
		function() {
			$( this ).removeClass( "ui-state-hover" );
		}
	);
	
});