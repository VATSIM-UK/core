new google.maps.Marker({
    position: new google.maps.LatLng({{ $flight->departure->latitude }}, {{ $flight->departure->longitude }}),
    title: '{{ $flight->departure->name }} ({{ $flight->departure->icao }})',
    map: map,
});

new google.maps.Marker({
    position: new google.maps.LatLng({{ $flight->arrival->latitude }}, {{ $flight->arrival->longitude }}),
    title: '{{ $flight->arrival->name }} ({{ $flight->arrival->icao }})',
    map: map,
});
