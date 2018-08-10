@extends('layout')

@section('styles')
    <link media="all" type="text/css" rel="stylesheet" href="//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">

@endsection

@section('scripts')
    <script type="text/javascript" src="//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready( function () {
            @if ($airport->controllers->count() > 10)
                $('#online-controllers').DataTable();
            @endif
            @if ($airport->pilots->count() > 10)
                $('#online-pilots').DataTable();
            @endif

        } );
        $.get('{{ route('metar', $airport->icao) }}', function (data) {
            $('#metar').fadeOut(400, function () {
                $('#metar').html(data);
                $(this).fadeIn();
            });
        });
    </script>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><span class="panel-heading-lg">{{ $airport->name }}</span>
                    <span class="panel-heading-lg-secondary">{{ $airport->icao }}</span>
                    <span class="panel-heading-lg-secondary">{{ $airport->iata }}</span></div>
                @if($airport->description)
                    <div class="panel-body">
                        {!! nl2br($airport->description) !!}
                    </div>
                @endif
            </div>
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-tint"></i> Current METAR</div>
                <div class="panel-body">
                    <span id="metar"> Fetching...</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-table"></i> Key Details</div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-12 table-responsive">
                            <table class="table">
                                <tbody>
                                <tr>
                                    <th>Name</th>
                                    <td>{{$airport->name}}</td>
                                </tr>
                                <tr>
                                    <th>ICAO</th>
                                    <td>{{$airport->icao}}</td>
                                </tr>
                                <tr>
                                    <th>IATA</th>
                                    <td>{{$airport->iata}}</td>
                                </tr>
                                <tr>
                                    <th>FIR</th>
                                    <td>{{$airport->fir_type}}</td>
                                </tr>
                                <tr>
                                    <th>Latitude</th>
                                    <td>{{$airport->latitude}}</td>
                                </tr>
                                <tr>
                                    <th>Longitude</th>
                                    <td>{{$airport->longitude}}</td>
                                </tr>
                                <tr>
                                    <th>Elevation</th>
                                    <td>{{$airport->elevation}} ft</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if($airport->hasProcedures())
            <div class="col-md-8">
                <div class="panel panel-ukblue">
                    <div class="panel-heading">
                        <i class="fa fa-check"></i> Procedures
                    </div>
                    <div class="panel-body">
                        <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                            @if($airport->departure_procedures)
                                <div class="panel panel-ukblue">
                                    <div class="panel-heading" role="tab" id="headingOne"
                                         style="background-image:url({{asset('images/slice_departure.jpg')}});background-size: cover;min-height: 100px;">
                                        <h4 class="panel-title">
                                            <a role="button" data-toggle="collapse" data-parent="#accordion"
                                               href="#collapseOne"
                                               aria-expanded="false" aria-controls="collapseOne">
                                                Departure Procedures
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="collapseOne" class="panel-collapse collapse" role="tabpanel"
                                         aria-labelledby="headingOne">
                                        <div class="panel-body">
                                            {!! $airport->departure_procedures !!}
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if($airport->arrival_procedures)
                                <div class="panel panel-ukblue">
                                    <div class="panel-heading" role="tab" id="headingTwo">
                                        <h4 class="panel-title">
                                            <a class="collapsed" role="button" data-toggle="collapse"
                                               data-parent="#accordion"
                                               href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                                Arrival Procedures
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel"
                                         aria-labelledby="headingTwo">
                                        <div class="panel-body">
                                            {!! $airport->arrival_procedures !!}
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if($airport->vfr_procedures)
                                <div class="panel panel-ukblue">
                                    <div class="panel-heading" role="tab" id="headingThree">
                                        <h4 class="panel-title">
                                            <a class="collapsed" role="button" data-toggle="collapse"
                                               data-parent="#accordion"
                                               href="#collapseThree" aria-expanded="false"
                                               aria-controls="collapseThree">
                                                VFR Procedures
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="collapseThree" class="panel-collapse collapse" role="tabpanel"
                                         aria-labelledby="headingThree">
                                        <div class="panel-body">
                                            {!! $airport->vfr_procedures !!}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if($airport->other_information)
            <div class="col-md-4">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="fa fa-info"></i> Additional Information</div>
                    <div class="panel-body">
                        {!! $airport->other_information !!}
                    </div>
                </div>
            </div>
        @endif
    </div>
    <hr>
    <div class="row">
        @if($airport->navaids->merge($airport->runways)->count() > 0)
            <div class="col-md-6">
                @if($airport->navaids->count() > 0)
                    <div class="panel panel-ukblue">
                        <div class="panel-heading"><i class="fa fa-compass"></i> Navigation Aids</div>
                        <div class="panel-body table-responsive">
                            <table class="table">
                                <thead>
                                <th>Type</th>
                                <th>Identifier</th>
                                <th>Frequency</th>
                                <th></th>
                                </thead>
                                <tbody>
                                @foreach($airport->navaids as $navaid)
                                    <tr>
                                        <td class="col-md-3">{{$navaid->type}} {{$navaid->name}}</td>
                                        <td class="col-md-2">{{$navaid->ident}}</td>
                                        <td class="col-md-3">{{$navaid->frequency}} {{$navaid->frequency_band}}</td>
                                        <td class="col-md-4">{{$navaid->remarks}}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
                @if($airport->runways->count() > 0)
                    <div class="panel panel-ukblue">
                        <div class="panel-heading"><i class="fa fa-road"></i> Runways</div>
                        <div class="panel-body table-responsive">
                            <table class="table">
                                <thead>
                                <th>Runway</th>
                                <th>Mag. Bearing</th>
                                <th>Dimensions</th>
                                <th>Surface Type</th>
                                </thead>
                                <tbody>
                                @foreach($airport->runways as $runway)
                                    <tr>
                                        <td>{{$runway->ident}}</td>
                                        <td>{{$runway->heading}}&deg;</td>
                                        <td>{{$runway->length}}m x {{$runway->width}}m</td>
                                        <td>{{$runway->surface_type}}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        @endif
        @if($airport->stations->count() > 0)
            <div class="col-md-6">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="fa fa-wifi"></i> ATC Stations</div>
                    <div class="panel-body table-responsive">
                        <table class="table">
                            <thead>
                            <th>Callsign</th>
                            <th>Name</th>
                            <th>Frequency</th>
                            </thead>
                            <tbody>
                            @foreach($airport->stations->groupBy('type') as $groupedStations)
                                @foreach($groupedStations as $station)
                                    <tr>
                                        <td>{{$station->callsign}}</td>
                                        <td>{{$station->name}}</td>
                                        <td>{{$station->frequency}}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <div class="row">
        @if(($procedures = $airport->procedures->sortBy('ident')->where('type', \App\Models\Airport\Procedure::TYPE_SID))->count() > 0)
            <div class="col-md-6">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="fa fa-arrow-up"></i> Standard Instrument Departures</div>
                    <div class="panel-body table-responsive">
                        <table class="table">
                            <thead>
                            <th>Identifier</th>
                            <th>Runway</th>
                            <th>Initial Altitude</th>
                            <th></th>
                            </thead>
                            <tbody>
                            @foreach($procedures as $procedure)
                                <tr>
                                    <td>{{$procedure->ident}}</td>
                                    <td>{{$procedure->runway ? $procedure->runway->ident : ""}}</td>
                                    <td>{{$procedure->final_altitude}}ft</td>
                                    <td>{{$procedure->remarks}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
        @if(($procedures = $airport->procedures->sortBy('ident')->where('type', \App\Models\Airport\Procedure::TYPE_STAR))->count() > 0)
            <div class="col-md-6">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="fa fa-arrow-down"></i> Standard Terminal Arrival Routes</div>
                    <div class="panel-body table-responsive">
                        <table class="table">
                            <thead>
                            <th>Identifier</th>
                            <th>Initial Fix</th>
                            <th></th>
                            </thead>
                            <tbody>
                            @foreach($procedures as $procedure)
                                <tr>
                                    <td>{{$procedure->ident}}</td>
                                    <td>{{$procedure->inital_fix}}</td>
                                    <td>{{$procedure->remarks}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <hr>
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-wifi"></i> Online Controllers</div>
                <div class="panel-body table-responsive">
                    <table id="online-controllers" class="table">
                        <thead>
                        <th>Position</th>
                        <th>Controller</th>
                        <th>Frequency</th>
                        <th>Time Online</th>
                        </thead>
                        <tbody>
                        @foreach($airport->controllers as $controller)
                            <tr>
                                <td>{{$controller->callsign}}</td>
                                <td>{{$controller->account->real_name}}</td>
                                <td>{{$controller->frequency}}</td>
                                <td>{{ HTML::fuzzyDate($controller->connected_at) }}</td>
                            </tr>
                        @endforeach
                        @if($airport->controllers->count() == 0)
                            <tr>
                                <th colspan="4" class="text-center">No Controllers Online</th>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-plane"></i> Online Pilots</div>
                <div class="panel-body table-responsive">
                    <table id="online-pilots" class="table">
                        <thead>
                        <th>Callsign</th>
                        <th>Aircraft</th>
                        <th>Pilot</th>
                        <th>Origin</th>
                        <th>Destination</th>
                        <th>Connected</th>
                        </thead>
                        <tbody>
                        @foreach($airport->pilots as $pilot)
                            <tr>
                                <td>{{$pilot->callsign}}</td>
                                <td>{{$pilot->aircraft}}</td>
                                <td>{{$pilot->account->real_name}}</td>
                                <td>{{$pilot->departure_airport}}</td>
                                <td>{{$pilot->arrival_airport}}</td>
                                <td>{{ HTML::fuzzyDate($pilot->connected_at) }}</td>
                            </tr>
                        @endforeach
                        @if($airport->pilots->count() == 0)
                            <tr>
                                <th colspan="4" class="text-center">No Pilots Flying Here</th>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script>
        function initMap() {
            map = new google.maps.Map(document.getElementById('banner'), {
                mapTypeId: 'satellite',
                disableDefaultUI: true,
                gestureHandling: 'none',
                zoomControl: false,
                center: {lat: {{$airport->latitude}}, lng: {{$airport->longitude}}},
                zoom: 13
            });

            var marker = new google.maps.Marker({
                map: map,
                position: {lat: {{$airport->latitude}}, lng: {{$airport->longitude}}}
            });
        }
    </script>
    <script async defer
            src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps.jsapi') }}&callback=initMap">
    </script>
@endsection
