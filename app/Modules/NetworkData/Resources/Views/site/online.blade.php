@extends('visittransfer::site._layout')

@section('content')
    <div class="row">
        <div class="col-md-6">
            {!! HTML::panelOpen("Online ATC", ["type" => "vuk", "key" => "letter-a"]) !!}
            <div class="row">
                <div class="col-md-12" style="margin-bottom: 15px;">
                    <table class="table table-striped tabled-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Callsign</th>
                                <th>Type</th>
                                <th>Airport(s)</th>
                                <th>Name</th>
                                <th>Online Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($atcSessions as $atc)
                                <tr>
                                    <td>{{ $atc->callsign }}</td>
                                    <td>{{ $atc->facility_type }}</td>
                                    <td>{{ $atc->airport }}</td>
                                    <td>{{ $atc->account ? $atc->account->name : "Unknown User" }}</td>
                                    <td>{{ $atc->online_at }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-center">
                                    There are currently {{ $atcSessions->count() }} ATC Sessions online.
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
            {!! HTML::panelClose() !!}
        </div>

        <div class="col-md-6">
            {!! HTML::panelOpen("Online Pilots", ["type" => "vuk", "key" => "letter-p"]) !!}
            <div class="row">
                <div class="col-md-12" style="margin-bottom: 15px;">
                    <p class="text-center">
                        Coming early 2017!
                    </p>
                </div>

            </div>
            {!! HTML::panelClose() !!}
        </div>
    </div>
@stop

@section("scripts")
    @parent
@stop