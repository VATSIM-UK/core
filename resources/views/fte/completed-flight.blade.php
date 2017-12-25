@extends ('layout')

@section('content')

    <!-- Box for badge/points -->

    <div class="col-md-4">
        <div class="panel panel-ukblue">
            <div class="panel-heading"><i class="glyphicon glyphicon-ok-circle"></i> &thinsp; Actions
            </div>
            <div class="panel-body">
                <a href="{{ route('fte.history') }}" class="btn btn-primary"><< Return to List</a>
            </div>
        </div>

        <div class="panel panel-ukblue">
            <div class="panel-heading"><i class="glyphicon glyphicon-info-sign"></i> &thinsp; Flight Details
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-xs-4">
                        <b>Date:</b>
                        {{ $pirep->created_at }}
                    </div>
                    <div class="col-xs-4">
                        <b>Landing Rate:</b>
                        {{ $pirep->landing_rate }}fpm
                    </div>
                    <div class="col-xs-4">
                        <b>Duration:</b>
                        {{ $pirep->flight_time }}
                    </div>
                </div>
                <br/>
                <div class="row">
                    <div class="col-xs-4">
                        <b>ID:</b>
                        {{ $flight->id }}
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
                <br>
                <div class="row">
                    <div class="col-xs-12">
                        <b>Pass/Fail:</b>
                        @if($pirep->passed === true)
                            Passed
                        @elseif($pirep->passed === false)
                            Failed
                        @else
                            Pending
                        @endif
                    </div>
                    <div class="col-xs-12">
                        <b>Problem?</b>
                        <a href="https://helpdesk.vatsim.uk" target="_blank">Contact the Pilot Training Department.</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="panel panel-ukblue" style="min-height: 500px;">
            <div class="panel-heading"><i class="glyphicon glyphicon-globe"></i> &thinsp; Map
            </div>
            <div class="panel-body">
                <p><strong>LEGEND:</strong> <span style="color: #228B22;">Target</span>, <span style="color: #00008B;">Actual</span></p>
                <div id="map" style="width: 100%; height: 500px;"></div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        var poly;
        var map;
        var polyActual;

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

            @include('fte.map.plot-posreps')

        }
    </script>
    <script async defer
            src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps.jsapi') }}&callback=initMap">
    </script>
@endsection
