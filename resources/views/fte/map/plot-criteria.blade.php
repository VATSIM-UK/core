poly = new google.maps.Polyline({
    map: map,
    strokeColor: '#228B22',
    strokeOpacity: 1.0,
    strokeWeight: 3,
});

var path = poly.getPath();
path.push(new google.maps.LatLng({{ $flight->departure->latitude }}, {{ $flight->departure->longitude }}));
@foreach($criteria as $criterion)
    path.push(new google.maps.LatLng({{ $criterion->centroid()['latitude'] }}, {{ $criterion->centroid()['longitude']}}));
@endforeach
path.push(new google.maps.LatLng({{ $flight->arrival->latitude }}, {{ $flight->arrival->longitude }}));
