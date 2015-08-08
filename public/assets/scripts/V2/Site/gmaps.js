/**
 * Google Maps Functions
 */
var $maps = new Array();

// Add the unload action for google maps.
$(document).ready(function(){
	//$(document).bind('unload', GMapsUnload);
});

// Setup a Google map on the specified canvas.
function GMapsSetup(mapCanvas){
    $(document).ready(function(){
        mapOptions = {
            zoom: 5,
            center: new google.maps.LatLng(53.931611, -1.538086),
            disableDefaultUI: false,
            mapTypeControl: false,
            mapTypeId: google.maps.MapTypeId.SATELLITE
        }
        $maps[mapCanvas] = new google.maps.Map(document.getElementById(mapCanvas), mapOptions);
    })
}

// Add a circle at the specified location.
function GMapsAddCircle(mapCanvas, options){
    $(document).ready(function(){
        circle = new google.maps.Circle(options);
        circle.setMap($maps[mapCanvas]);
    })
}

// Add a ground position to the specified location.
function GMapsAddGnd(mapCanvas, Lat, Lon){
    var options = {
        center: new google.maps.LatLng(Lat, Lon),
        radius: 74080,
        strokeColor: "#FF0000",
        strokeOpacity: 0.4,
        strokeWeight: 1,
        fillColor: "#FF0000",
        fillOpacity: 0.35,
        zIndex: 100
    }
    GMapsAddCircle(mapCanvas, options);
}