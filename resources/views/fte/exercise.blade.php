@extends ('layout')

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-check"></i> &thinsp; Actions</div>
                <div class="panel-body">
                    <div class="">
                        <a href="{{ route('fte.dashboard') }}" class="btn btn-primary">&lt;&lt; Dashboard</a>
                        @empty($booking)
                            <a class="btn btn-success" href="{{ route('fte.exercise.book', $flight) }}"
                               onclick="event.preventDefault(); document.getElementById('book-form').submit();">
                                Book Flight
                            </a>
                            <form id="book-form" action="{{ route('fte.exercise.book', $flight) }}" method="POST"
                                  style="display: none;">
                                {{ csrf_field() }}
                            </form>
                            @else
                                <a class="btn btn-danger" href="{{ route('fte.exercise.cancel', $flight) }}"
                                   onclick="event.preventDefault(); document.getElementById('cancel-form').submit();">
                                    Cancel Flight
                                </a>
                                <form id="cancel-form" action="{{ route('fte.exercise.cancel', $flight) }}" method="POST"
                                      style="display: none;">
                                    {{ csrf_field() }}
                                </form>
                                @endempty
                    </div>
                </div>
            </div>

            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-info"></i> &thinsp; Exercise Details
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-6">
                            <b>Name:</b>
                            {{ $flight->name }}
                        </div>
                    </div>
                    <br/>
                    <div class="row">
                        <div class="col-xs-12">
                            <b>Description:</b>
                            {{ $flight->description }}
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
                            <b>Aircraft Type:</b>
                            {{ $flight->aircraft->fullname }} ({{ $flight->aircraft->icao }})
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-book"></i> &thinsp; Resources</div>
                <div class="panel-body">
                    @forelse($flight->resources->sortBy('display_name') as $resource)
                        <p><a href="{{ $resource->asset() }}" target="_blank">{{ $resource->display_name }}</a></p>
                    @empty
                        <p>No resources available.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="panel panel-ukblue" style="min-height: 500px;">
                <div class="panel-heading"><i class="fa fa-globe"></i> &thinsp; Map
                </div>
                <div class="panel-body text-center">
                    <div id="map" style="width: 100%; height: 500px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-chart-line"></i> &thinsp; Statistics</div>
                <div class="panel-body">
                    <p>Flight Training Exercises is still really new!<br>
                        Once we have enough exercises completed, we will be able to show stats like average flight time,
                        average landing rate and average pass rate.</p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-cloud"></i> &thinsp; Weather</div>
                <div class="panel-body">
                    <strong>Departure Aerodrome:</strong> <span id="dep-metar"><a href="http://metar.vatsim.net/metar.php?id={{ $flight->departure->icao }}">Click Here</a></span>
                    <br>
                    <strong>Arrival Aerodrome:</strong> <span id="arr-metar"><a href="http://metar.vatsim.net/metar.php?id={{ $flight->arrival->icao }}">Click Here</a></span>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $.get('{{ route('api.metar', $flight->departure->icao) }}', function (data) {
            $('#dep-metar').fadeOut(400, function () {
                $('#dep-metar').html(data);
                $(this).fadeIn();
            });
        });
        $.get('{{ route('api.metar', $flight->arrival->icao) }}', function (data) {
            $('#arr-metar').fadeOut(400, function () {
                $('#arr-metar').html(data);
                $(this).fadeIn();
            });
        });
    </script>
    <script>
        var poly;
        var map;

        function initMap() {
            google.maps.Polyline.prototype.getBounds = function () {
                var bounds = new google.maps.LatLngBounds();
                this.getPath().forEach(function (element, index) {
                    bounds.extend(element);
                });
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
