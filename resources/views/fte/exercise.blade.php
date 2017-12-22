@extends ('layout')

@section('content')
    <div class="col-md-4">
        <div class="panel panel-ukblue">
            <div class="panel-heading"><i class="glyphicon glyphicon-ok-circle"></i> &thinsp; Actions</div>
            <div class="panel-body">
                <div class="">
                    <a href="{{ route('fte.dashboard') }}" class="btn btn-primary"><< Dashboard</a>

                    @empty($booking)
                        <a class="btn btn-success" href="{{ route('fte.exercise.book', $flight) }}"
                           onclick="event.preventDefault(); document.getElementById('book-form').submit();">
                            Book Flight
                        </a>
                        <form id="book-form" action="{{ route('fte.exercise.book', $flight) }}" method="POST" style="display: none;">
                            {{ csrf_field() }}
                        </form>
                    @else
                        <a class="btn btn-danger" href="{{ route('fte.exercise.cancel', $flight) }}"
                           onclick="event.preventDefault(); document.getElementById('cancel-form').submit();">
                            Cancel Flight
                        </a>
                        <form id="cancel-form" action="{{ route('fte.exercise.cancel', $flight) }}" method="POST" style="display: none;">
                            {{ csrf_field() }}
                        </form>
                    @endempty
                </div>
            </div>
        </div>

        <div class="panel panel-ukblue">
            <div class="panel-heading"><i class="glyphicon glyphicon-info-sign"></i> &thinsp; Flight Details
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-xs-4">
                        <b>Code:</b>
                        {{ $flight->code }}
                    </div>
                    <div class="col-xs-6">
                        <b>Name:</b>
                        {{ $flight->name }}
                    </div>
                </div>
                <br/>
                <div class="row">
                    <div class="col-xs-12">
                        <b>Departure:</b>
                        {{ $flight->departure->name }} ({{ $flight->departure->icao }})
                    </div>
                    <div class="col-xs-12">
                        <b>Arrival:</b>
                        {{ $flight->arrival->name }} ({{ $flight->arrival->icao }})
                    </div>
                </div>
                <br/>
                <div class="row">
                    <div class="col-xs-12">
                        <b>Registration:</b>
                        {{ $flight->aircraft->registration }}
                    </div>
                    <div class="col-xs-12">
                        <b>Aircraft Type:</b>
                        {{ $flight->aircraft->fullname }} ({{ $flight->aircraft->icao }})
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-ukblue">
            <div class="panel-heading"><i class="glyphicon glyphicon-book"></i> &thinsp; Resources</div>
            <div class="panel-body">
                <div class="">
                    <p><a href="https://vatsim.uk/{{ $flight->departure->icao }}/">Departure Resources</a></p>
                    <p><a href="https://vatsim.uk/{{ $flight->arrival->icao }}/">Arrival Resources</a></p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="panel panel-ukblue" style="min-height: 500px;">
            <div class="panel-heading"><i class="glyphicon glyphicon-globe"></i> &thinsp; Map
            </div>
            <div class="panel-body text-center">
                <div id="map" style="width: 100%; height: 500px;"></div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="panel panel-ukblue">
            <div class="panel-heading"><i class="glyphicon glyphicon-equalizer"></i> &thinsp; Statistics</div>
            <div class="panel-body">
                <p>Information unavailable.</p>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="panel panel-ukblue">
            <div class="panel-heading"><i class="glyphicon glyphicon-cloud"></i> &thinsp; Weather</div>
            <div class="panel-body">
                <strong>Departure Aerodrome:</strong> <span id="dep-metar"><a href="http://metar.vatsim.net/metar.php?id={{ $flight->departure->icao }}">Click Here</a></span>
                <br>
                <strong>Arrival Aerodrome:</strong> <span id="arr-metar"><a href="http://metar.vatsim.net/metar.php?id={{ $flight->arrival->icao }}">Click Here</a></span>
            </div>
        </div>
    </div>
@endsection

@section ('scripts')
@section('scripts')
    <script>
        var poly;
        var map;

        function initMap() {
            google.maps.Polyline.prototype.getBounds = function() {
                var bounds = new google.maps.LatLngBounds();
                this.getPath().forEach(function(element,index){ bounds.extend(element); });
                return bounds;
            };

            map = new google.maps.Map(document.getElementById('map'));

            @include('fte.map.plot-criteria')

            @include('fte.map.mark-airports')

            map.setCenter(poly.getBounds().getCenter());
            map.setZoom(map.fitBounds(poly.getBounds()));
        }
    </script>
    <script async defer
            src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps.jsapi') }}&callback=initMap">
    </script>
@endsection
@endsection
