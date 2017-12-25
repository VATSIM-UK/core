var depMarker = new google.maps.Marker({
    position: new google.maps.LatLng({{ $flight->departure->latitude }}, {{ $flight->departure->longitude }}),
    title: '{{ $flight->departure->name }} ({{ $flight->departure->icao }})',
    map: map,
});

var depInfowindow = new google.maps.InfoWindow({
    content: '{{ $flight->departure->name }} ({{ $flight->departure->icao }})',
});

depMarker.addListener('click', function() {
    depInfowindow.open(map, depMarker);
});


var arrMarker = new google.maps.Marker({
    position: new google.maps.LatLng({{ $flight->arrival->latitude }}, {{ $flight->arrival->longitude }}),
    title: '{{ $flight->arrival->name }} ({{ $flight->arrival->icao }})',
    map: map,
});

var arrInfowindow = new google.maps.InfoWindow({
    content: '{{ $flight->arrival->name }} ({{ $flight->arrival->icao }})',
});

arrMarker.addListener('click', function() {
    arrInfowindow.open(map, arrMarker);
});
