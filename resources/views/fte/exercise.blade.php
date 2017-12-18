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

        // svg path for plane icon
        var planeSVG = "m2,106h28l24,30h72l-44,-133h35l80,132h98c21,0 21,34 0,34l-98,0 -80,134h-35l43,-133h-71l-24,30h-28l15,-47";

        AmCharts.ready(function () {
            map = new AmCharts.AmMap();

            var dataProvider = {
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

            // LONDON
            var lines = [{
                id: "line1",
                arc: 0,
                alpha: 0.3,
                latitudes: [50.95621, 50.95627, 50.95622, 50.95587, 50.95547, 50.95508, 50.95641, 50.95835, 50.96162, 50.96525, 50.9687, 50.97193, 50.97506, 50.97798, 50.9798, 50.97835, 50.97661, 50.97464, 50.9721, 50.96856, 50.96332, 50.95575, 50.94873, 50.94189, 50.93519, 50.932, 50.93515, 50.93932, 50.94484, 50.95029, 50.95585, 50.96137, 50.96659, 50.97176, 50.977, 50.98235, 50.9881, 50.99391, 51.00169, 51.0094, 51.01711, 51.02455, 51.03175, 51.03924, 51.04662, 51.0541, 51.0616, 51.0692, 51.07682, 51.08456, 51.0923, 51.09987, 51.10725, 51.11439, 51.12152, 51.12882, 51.13595, 51.14302, 51.15026, 51.15765, 51.16334, 51.1686, 51.17384, 51.17905, 51.18376, 51.18811, 51.192, 51.19566, 51.1993, 51.20293, 51.20665, 51.2108, 51.21522, 51.2196, 51.22386, 51.22818, 51.23317, 51.23831, 51.24412, 51.2495, 51.25439, 51.25924, 51.26391, 51.26857, 51.27333, 51.27802, 51.28179, 51.28565, 51.28895, 51.29069, 51.29158, 51.29256, 51.29355, 51.29494, 51.29632, 51.29887, 51.30105, 51.30282, 51.30416, 51.30532, 51.30612, 51.306, 51.30464, 51.30315, 51.30159, 51.30038, 51.2998, 51.29949, 51.29902, 51.2982, 51.29745, 51.29665, 51.29368, 51.29138, 51.28931, 51.28743, 51.28539, 51.28302, 51.28157, 51.27991, 51.27746, 51.27572, 51.27444, 51.27347, 51.27512, 51.27861, 51.28217, 51.28555, 51.28851, 51.29337, 51.30027, 51.30554, 51.31044, 51.31507, 51.31964, 51.32441, 51.32903, 51.33298, 51.33637, 51.33792, 51.33803, 51.33803, 51.33803, 51.33835, 51.33841, 51.33841, 51.33846, 51.33696, 51.33507, 51.33429],
                longitudes: [0.93479, 0.93492, 0.93553, 0.93621, 0.93689, 0.93785, 0.93939, 0.94144, 0.94477, 0.94855, 0.95232, 0.95633, 0.96136, 0.96726, 0.97361, 0.98042, 0.98721, 0.99382, 1.00076, 1.00944, 1.01736, 1.01652, 1.0108, 1.00465, 0.9977, 0.98685, 0.97553, 0.9647, 0.95599, 0.94782, 0.93992, 0.93206, 0.92458, 0.91751, 0.91007, 0.90243, 0.89349, 0.88353, 0.88152, 0.88029, 0.87911, 0.87974, 0.8811, 0.88269, 0.8849, 0.88689, 0.88897, 0.89082, 0.89208, 0.89192, 0.8901, 0.88719, 0.88462, 0.8825, 0.88002, 0.87731, 0.87411, 0.87029, 0.86732, 0.86621, 0.85694, 0.84648, 0.83611, 0.82567, 0.81472, 0.80341, 0.79183, 0.7799, 0.76749, 0.75542, 0.74335, 0.73147, 0.71998, 0.70854, 0.69731, 0.68582, 0.67496, 0.6643, 0.65478, 0.64479, 0.6337, 0.62238, 0.61122, 0.60032, 0.58881, 0.57778, 0.5663, 0.55505, 0.54263, 0.52932, 0.51612, 0.50329, 0.49072, 0.47799, 0.46449, 0.45098, 0.43685, 0.42263, 0.40865, 0.39507, 0.3817, 0.36805, 0.35431, 0.34054, 0.32697, 0.31346, 0.30024, 0.28713, 0.27421, 0.26134, 0.24836, 0.23529, 0.22253, 0.20918, 0.19559, 0.18219, 0.16924, 0.15629, 0.14315, 0.1304, 0.11746, 0.1036, 0.08984, 0.07666, 0.06353, 0.05171, 0.03925, 0.02602, 0.01272, 0.00233, 0.00131, 0.00632, 0.01159, 0.01654, 0.02189, 0.02668, 0.03074, 0.03397, 0.0366, 0.03778, 0.03792, 0.03792, 0.03792, 0.03749, 0.03726, 0.03726, 0.03682, 0.03537, 0.0339, 0.03327]
            }];


            // cities
            var cities = [{
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
            dataProvider.zoomLongitude = 0;
            dataProvider.zoomLatitude = 50;
            map.dataProvider = dataProvider;
            map.write("mapdiv");
        });
    </script>
@endsection
