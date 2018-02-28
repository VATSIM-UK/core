polyActual = new google.maps.Polyline({
    map: map,
    strokeColor: '#00008B',
    strokeOpacity: 1.0,
    strokeWeight: 3,
});
var pathActual = polyActual.getPath();
@foreach($posreps as $posrep)
    pathActual.push(new google.maps.LatLng({{ $posrep->latitude }}, {{ $posrep->longitude }}));
@endforeach
