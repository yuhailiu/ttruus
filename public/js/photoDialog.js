$(function() {
		//hide images
		$('#waittingImg1').hide();
		$('#waittingImg').hide();

		$( "#dialog" ).dialog({
			autoOpen: false,
			width: 800,
			modal : true,
			show : {
				effect : "blind",
				duration : 1000
			},
			hide : {
				effect : "explode",
				duration : 1000
			},
			buttons: [
				{
					text: "Ok",
					click: function() {
						$( this ).dialog( "close" );
					}
				}				
			]
		});

		// Link to open the dialog
		$( "#dialog-link" ).click(function( event ) {
			
			//get the image URL
			var key=$('#imgUrl').val();
			imgUrl = "/users/media/showImage/"+ key + "?dummy=" + new Date().getTime();
			
			//remove the last image
			$("#userImg").remove();
			
			//show the dialog window
			$( "#dialog" ).dialog( "open" );
			$("#waittingImg").show();
			
			//get the image and update the new one
			$.ajax({
				url: imgUrl,
				cache: false,
				success: function(){
					
					var img=$('<img>').attr({
						'src': imgUrl,
						'id': 'userImg',
						'class': 'ui-state-default ui-corner-all',
					});
					
					//window.open(img);
					$("#dialog").append(img);
					
					
					event.preventDefault();
				}
			}).done(function(){
				$("#waittingImg").hide();
			});
		});
});