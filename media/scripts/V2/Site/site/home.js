/**
 * Notams annimation for the homepage.
 */
function runNotams(elementID){
	$(document).ready(function(){
		$("#"+elementID).tabs();
        $("#"+elementID).tabs("rotate", 5000);
	});
}

/**
 * Events carousel / feed.
 */
function runEvents(elementID){
    $("#"+elementID).cycle({
        fx:    'fade',
        pause:  1
    });
}