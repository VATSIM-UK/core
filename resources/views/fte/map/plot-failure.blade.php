var failMarker = new google.maps.Marker({
position: new google.maps.LatLng({{ $pirep->failedAt->latitude }}, {{ $pirep->failedAt->longitude }}),
title: 'Failure Point',
map: map,
label: 'F',
});

var failMarkerwindow = new google.maps.InfoWindow({
content: '{{ $pirep->pass_reason }}',
});

failMarker.addListener('click', function() {
failMarkerwindow.open(map, failMarker);
});
