@extends ('layout')

@section('content')
    <div class="col-md-4">
        <div class="panel panel-ukblue">
            <div class="panel-heading"><i class="glyphicon glyphicon-ok-circle"></i> &thinsp; Actions</div>
            <div class="panel-body">
                <div class="">
                    <a href="{{ route('fte.dashboard') }}" class="btn btn-primary"><< Dashboard</a>

                    @empty($booking)
                        <a class="btn btn-success" href="{{ route('fte.exercise.book', $exercise) }}"
                           onclick="event.preventDefault(); document.getElementById('book-form').submit();">
                            Book Flight
                        </a>
                        <form id="book-form" action="{{ route('fte.exercise.book', $exercise) }}" method="POST" style="display: none;">
                            {{ csrf_field() }}
                        </form>
                    @else
                        <a class="btn btn-danger" href="{{ route('fte.exercise.cancel', $exercise) }}"
                           onclick="event.preventDefault(); document.getElementById('cancel-form').submit();">
                            Cancel Flight
                        </a>
                        <form id="cancel-form" action="{{ route('fte.exercise.cancel', $exercise) }}" method="POST" style="display: none;">
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
                        {{ $exercise->code }}
                    </div>
                    <div class="col-xs-6">
                        <b>Name:</b>
                        {{ $exercise->name }}
                    </div>
                </div>
                <br/>
                <div class="row">
                    <div class="col-xs-12">
                        <b>Departure:</b>
                        {{ $exercise->departure->name }} ({{ $exercise->departure->icao }})
                    </div>
                    <div class="col-xs-12">
                        <b>Arrival:</b>
                        {{ $exercise->arrival->name }} ({{ $exercise->arrival->icao }})
                    </div>
                </div>
                <br/>
                <div class="row">
                    <div class="col-xs-12">
                        <b>Registration:</b>
                        {{ $exercise->aircraft->registration }}
                    </div>
                    <div class="col-xs-12">
                        <b>Aircraft Type:</b>
                        {{ $exercise->aircraft->fullname }} ({{ $exercise->aircraft->icao }})
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-ukblue">
            <div class="panel-heading"><i class="glyphicon glyphicon-book"></i> &thinsp; Resources</div>
            <div class="panel-body">
                <div class="">
                    <p><a href="https://vatsim.uk/{{ $exercise->departure->icao }}/">Departure Resources</a></p>
                    <p><a href="https://vatsim.uk/{{ $exercise->arrival->icao }}/">Arrival Resources</a></p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="panel panel-ukblue" style="min-height: 500px;">
            <div class="panel-heading"><i class="glyphicon glyphicon-globe"></i> &thinsp; Map
            </div>
            <div class="panel-body text-center">
                <div id="mapdiv" style="width: 100%; background-color:#eeeeee; height: 500px;"></div>
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
                <strong>Departure Aerodrome:</strong> <span id="dep-metar"><a href="http://metar.vatsim.net/metar.php?id={{ $exercise->departure->icao }}">Click Here</a></span>
                <br>
                <strong>Arrival Aerodrome:</strong> <span id="arr-metar"><a href="http://metar.vatsim.net/metar.php?id={{ $exercise->arrival->icao }}">Click Here</a></span>
            </div>
        </div>
    </div>
@endsection

@section ('scripts')
    <script src="//www.amcharts.com/lib/3/ammap.js" type="text/javascript"></script>
    <script src="//www.amcharts.com/lib/3/maps/js/worldHigh.js" type="text/javascript"></script>
    <script>
        $.get('{{ route('metar', $exercise->departure->icao) }}', function (data) {
            $('#dep-metar').fadeOut(400, function () {
                    $('#dep-metar').html(data);
                    $(this).fadeIn();
                });
        });
        $.get('{{ route('metar', $exercise->arrival->icao) }}', function (data) {
            $('#arr-metar').fadeOut(400, function () {
                $('#arr-metar').html(data);
                $(this).fadeIn();
            });
        });

        var map;
        var planeSVG = "m2,106h28l24,30h72l-44,-133h35l80,132h98c21,0 21,34 0,34l-98,0 -80,134h-35l43,-133h-71l-24,30h-28l15,-47";
        AmCharts.ready(function () {
            var lines, cities, dataProvider;

            map = new AmCharts.AmMap();

            dataProvider = {
                mapVar: AmCharts.maps.worldHigh
            };

            map.areasSettings = {
                unlistedAreasColor: "#8dd9ef"
            };

            map.imagesSettings = {
                color: "#585869",
                rollOverColor: "#585869",
                selectedColor: "#585869",
                pauseDuration: 0.2,
                animationDuration: 2.5,
                adjustAnimationSpeed: true
            };

            map.linesSettings = {
                color: "#585869",
                alpha: 0.4
            };

            lines = [{
                id: "line1",
                arc: 0,
                alpha: 0.3,
                latitudes: [
                    {{ $exercise->departure->latitude }},
                    @foreach($exercise->criteria->sortBy('order') as $criterion)
                    {{ $criterion->centroid()['latitude'] }},
                    @endforeach
                    {{ $exercise->arrival->latitude }},
                ],
                longitudes: [
                    {{ $exercise->departure->longitude }},
                    @foreach($exercise->criteria->sortBy('order') as $criterion)
                    {{ $criterion->centroid()['longitude'] }},
                    @endforeach
                    {{ $exercise->arrival->longitude }},
                ]
            }];

            // cities
            cities = [{
                svgPath: planeSVG,
                positionOnLine: 0,
                color: "#000000",
                alpha: 0.1,
                animateAlongLine: true,
                lineId: "line2",
                flipDirection: true,
                loop: true,
                scale: 0.03,
                positionScale: 1.3
            }, {
                svgPath: planeSVG,
                positionOnLine: 0,
                color: "#585869",
                animateAlongLine: true,
                lineId: "line1",
                flipDirection: true,
                loop: true,
                scale: 0.03,
                positionScale: 1.8
            }];

            dataProvider.images = cities;
            dataProvider.lines = lines;
            dataProvider.zoomLevel = 10;
            dataProvider.zoomLongitude = -2;
            dataProvider.zoomLatitude = 54;
            map.dataProvider = dataProvider;
            map.write("mapdiv");
        });
    </script>
@endsection
