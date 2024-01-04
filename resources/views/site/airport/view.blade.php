@extends('layout')

@section('styles')
    <link media="all" type="text/css" rel="stylesheet"
          href="//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
    <style type="text/css">
        #accordion .panel-heading-link:hover, #accordion .panel-heading-link:focus {
            color: white;
        }
        #accordion .panel-heading {
            color: white;
            background-size: cover;
            background-position: center;
            min-height: 100px;
        }
    </style>
@endsection

@section('scripts')
    <script type="text/javascript" src="//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps.jsapi') }}&callback=initMap"></script>
    <script>
        $(document).ready(function () {
            @if (($controllers = $airport->controllers)->count() > 10)
                $('#online-controllers').DataTable();
            @endif
            @if (($pilots = $airport->pilots)->count() > 10)
                $('#online-pilots').DataTable();
            @endif
            @if($stands)
                $('#stands').DataTable(
                    {
                        "aaSorting": [],
                        "columnDefs": [
                            { targets: 'no-sort', orderable: false },
                            { targets: 'no-search', searchable: false}
                        ]
                    }
                );
            @endif

            if($('#additionalInformationContainer').height() < 42){
                $('#additionalInfoController').hide();
            }
        });
        $.get('{{ route('api.metar', $airport->icao) }}', function (data) {
            $('#metar').fadeOut(400, function () {
                $('#metar').html(data);
                $(this).fadeIn();
            });
        });

        function initMap() {
            map = new google.maps.Map(document.getElementById('banner'), {
                mapTypeId: 'satellite',
                disableDefaultUI: true,
                center: {lat: {{$airport->latitude}}, lng: {{$airport->longitude}}},
                zoom: 13
            });

            @foreach($pilots->where('current_heading','!=',null) as $pilot)
                @if ($pilot->isAtAirport($airport))
                    new google.maps.Marker({
                        position: {lat: {{$pilot->current_latitude}}, lng: {{$pilot->current_longitude}}},
                        map: map,
                        draggable: false,
                        icon: {
                            path: "M24 19.999l-5.713-5.713 13.713-10.286-4-4-17.141 6.858-5.397-5.397c-1.556-1.556-3.728-1.928-4.828-0.828s-0.727 3.273 0.828 4.828l5.396 5.396-6.858 17.143 4 4 10.287-13.715 5.713 5.713v7.999h4l2-6 6-2v-4l-7.999 0z",
                            fillColor: '#fff',
                            fillOpacity: 1,
                            anchor: new google.maps.Point(0,0),
                            strokeWeight: 0,
                            scale: 0.5,
                            rotation: 45 + {{$pilot->current_heading}},
                        },
                        zIndex : -20
                    });
                @endif
            @endforeach
            @if ($stands)
                @foreach($stands as $stand)
                    @if($stand['status'] === 'available')
                        new google.maps.Circle({
                            strokeColor: '#32cd32',
                            strokeOpacity: 0.8,
                            strokeWeight: 2,
                            fillColor: '#32cd32',
                            fillOpacity: 0.35,
                            map: map,
                            center: { lat:{{$stand['latitude']}}, lng: {{$stand['longitude']}}},
                            radius: 30
                        });
                    @elseif(in_array($stand['status'], ['occupied', 'unavailable', 'reserved']))
                        new google.maps.Circle({
                            strokeColor: '#FF0000',
                            strokeOpacity: 0.8,
                            strokeWeight: 2,
                            fillColor: '#FF0000',
                            fillOpacity: 0.35,
                            map: map,
                            center: { lat:{{$stand['latitude']}}, lng: {{$stand['longitude']}}},
                            radius: 30
                        });
                    @else
                        new google.maps.Circle({
                            strokeColor: '#FFBF00',
                            strokeOpacity: 0.8,
                            strokeWeight: 2,
                            fillColor: '#FFBF00',
                            fillOpacity: 0.35,
                            map: map,
                            center: { lat:{{$stand['latitude']}}, lng: {{$stand['longitude']}}},
                            radius: 30
                        });
                    @endif
                @endforeach
            @endif
        }
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
                    <div class="panel-heading"><i class="fa fa-check"></i> Procedures</div>
                    <div class="panel-body">
                        <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                            @if($airport->departure_procedures)
                                <div class="panel panel-ukblue">
                                    <a class="collapsed panel-heading-link" role="button" data-toggle="collapse" data-parent="#accordion"
                                       href="#departureProcedures"
                                       aria-expanded="false" aria-controls="departureProcedures">
                                        <div class="panel-heading" role="tab"
                                             style="background-image:url({{asset('images/slice_departure.jpg')}});">
                                            <h4 class="panel-title"><span class="fa fa-plane-departure"></span> Departure Procedures</h4>
                                        </div>
                                    </a>
                                    <div id="departureProcedures" class="panel-collapse collapse" role="tabpanel">
                                        <div class="panel-body">
                                            {!! $airport->departure_procedures !!}
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if($airport->arrival_procedures)
                                <div class="panel panel-ukblue">
                                    <a class="collapsed panel-heading-link" role="button" data-toggle="collapse"
                                       data-parent="#accordion"
                                       href="#arrivalProcedures" aria-expanded="false"
                                       aria-controls="arrivalProcedures">
                                        <div class="panel-heading" role="tab"
                                             style="background-image:url({{asset('images/slice_arrival.jpg')}});">
                                            <h4 class="panel-title"><span class="fa fa-plane-arrival"></span> Arrival Procedures</h4>
                                        </div>
                                    </a>
                                    <div id="arrivalProcedures" class="panel-collapse collapse" role="tabpanel">
                                        <div class="panel-body">
                                            {!! $airport->arrival_procedures !!}
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if($airport->vfr_procedures)
                                <div class="panel panel-ukblue">
                                    <a class="collapsed panel-heading-link" role="button" data-toggle="collapse"
                                       data-parent="#accordion"
                                       href="#vfrProcedures" aria-expanded="false"
                                       aria-controls="vfrProcedures">
                                        <div class="panel-heading" role="tab"
                                             style="background-image:url({{asset('images/slice_vfr.jpg')}});">
                                            <h4 class="panel-title"><span class="fa fa-cloud"></span> VFR Procedures</h4>
                                        </div>
                                    </a>
                                    <div id="vfrProcedures" class="panel-collapse collapse" role="tabpanel">
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
                    <div class="text-summary panel-body">
                        <p class="summary-container collapse" id="additionalInformationContainer">
                            {!! $airport->other_information !!}
                        </p>
                        <a id="additionalInfoController" class="summary-controller collapsed" data-toggle="collapse" href="#additionalInformationContainer" aria-expanded="false" aria-controls="additionalInformationContainer"></a>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <hr>
    @if(config('services.chartfox.public_token'))
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="fa fa-map"></i> Charts</div>
                    <div class="text-summary panel-body">
                        <iframe class="w-100" style="min-height:80vh;width:100%" src="https://chartfox.org/api/interface/charts/{{ $airport->icao }}?token={{ config('services.chartfox.public_token') }}"></iframe>
                    </div>
                </div>

            </div>
        </div>
        <hr>
    @endif
    {{-- <div class="row">
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
                                        <td class="col-md-4">{!! $navaid->remarks !!}</td>
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
        @if($airport->positions->count() > 0)
            <div class="col-md-6">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="fa fa-wifi"></i> ATC Positions</div>
                    <div class="panel-body table-responsive">
                        <table class="table">
                            <thead>
                            <th>Callsign</th>
                            <th>Name</th>
                            <th>Frequency</th>
                            </thead>
                            <tbody>
                                @foreach($positions as $positions)
                                    <tr>
                                        <td>@if(!$positions->sub_station)
                                                <strong>{{$positions->callsign}}</strong>
                                            @else
                                                {{$positions->callsign}}
                                            @endif
                                        </td>
                                        <td>{{$positions->name}}</td>
                                        <td>{{$positions->frequency}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <div class="row">
        @if(($procedures = $airport->procedures->sortBy('ident')->where('type', \App\Models\Airport\Procedure::TYPE_SID)->groupBy('runway_id')->collapse())->count() > 0)
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
                                    <td><strong>{{$procedure->ident}}</strong></td>
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
                                    <td><strong>{{$procedure->ident}}</strong></td>
                                    <td>{{$procedure->initial_fix}}</td>
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
    <hr>--}}
    <div class="row">
        <div class="@if($stands) col-md-3 @else col-md-6 @endif">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-wifi"></i> Online Controllers</div>
                <div class="panel-body table-responsive">
                    <table id="online-controllers" class="table">
                        <thead>
                        <th>Position</th>
                        <th>Controller</th>
                        <th>Frequency</th>
                        <th>Connected</th>
                        </thead>
                        <tbody>
                        @foreach($controllers->sortByDesc('callsign') as $controller)
                            <tr>
                                <td>{{$controller->callsign}}</td>
                                <td>{{$controller->account->real_name}}</td>
                                <td>{{$controller->frequency}}</td>
                                <td>{{ HTML::fuzzyDate($controller->connected_at) }}</td>
                            </tr>
                        @endforeach
                        @if($controllers->count() == 0)
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
                        @foreach($pilots as $pilot)
                            <tr>
                                <td>{{$pilot->callsign}}</td>
                                <td>{{$pilot->aircraft}}</td>
                                <td>{{$pilot->account->real_name}}</td>
                                <td>{{$pilot->departure_airport}}</td>
                                <td>{{$pilot->arrival_airport}}</td>
                                <td>{{ HTML::fuzzyDate($pilot->connected_at) }}</td>
                            </tr>
                        @endforeach
                        @if($pilots->count() == 0)
                            <tr>
                                <th colspan="6" class="text-center">No Pilots Flying Here</th>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @if($stands)
        <div class="col-md-3">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-window-close-o"></i> Stands</div>
                <div class="panel-body table-responsive">
                    <table id="stands" class="table">
                        <thead>
                        <th class="no-sort">Stand</th>
                        <th class="no-sort no-search">Status</th>
                        </thead>
                        <tbody>
                            @foreach($stands as $index => $stand)
                                <tr>
                                    <td>{{$stand['identifier']}}</td>
                                    <td>
                                        @if($stand['status'] === 'available')
                                            <span style="color:green">Available</span>
                                        @elseif($stand['status'] === 'closed')
                                            <span style="color:red">Stand closed</span>
                                        @elseif($stand['status'] === 'unavailable')
                                            <span style="color:red">Unavailable</span>
                                        @elseif($stand['status'] === 'occupied')
                                            <span style="color:red">Occupied by {{$stand['callsign']}}</span>
                                        @elseif($stand['status'] === 'assigned')
                                            <span style="color:red">Assigned to {{$stand['callsign']}}</span>
                                        @elseif($stand['status'] === 'reserved')
                                            <span style="color:red">Reserved for {{$stand['callsign']}}</span>
                                        @elseif($stand['status'] === 'reserved_soon')
                                            <span style="color:#9744FD">Reserved soon</span>
                                        @elseif($stand['status'] === 'requested')
                                            <span style="color:#9744FD">Requested by {{$stand['requested_by'][0]}}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
@stop
