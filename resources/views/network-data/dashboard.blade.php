@extends('layout')

@section('content')
    <div class="row">
        <div class="col-md-6">
            {!! HTML::panelOpen('My ATC Sessions', ['type' => 'vuk', 'key' => 'letter-a']) !!}
            <div class="row">
                <div class="col-md-12" style="margin-bottom: 15px;">
                    <span style="display: flex; justify-content: center;">{{ $atcSessions->appends('pilotSessions', Input::get('pilotSessions'))->links() }}</span>
                    <table class="table table-striped tabled-bordered table-hover">
                        <tr>
                            <th>Callsign</th>
                            <th>Type</th>
                            <th>Frequency</th>
                            <th>Duration</th>
                            <th>Logged On</th>
                        </tr>
                        @foreach($atcSessions as $atc)
                            <tr>
                                <td>{{ $atc->callsign }}</td>
                                <td>{{ $atc->type }}</td>
                                <td>{{ $atc->frequency }}</td>
                                <td>{{ $atc->human_duration }}</td>
                                <td>{{ HTML::fuzzyDate($atc->connected_at) }}</td>
                            </tr>
                        @endforeach
                    </table>
                    <span style="display: flex; justify-content: center;">{{ $atcSessions->appends('pilotSessions', Input::get('pilotSessions'))->links() }}</span>
                </div>

            </div>
            {!! HTML::panelClose() !!}
        </div>

        <div class="col-md-6">
            {!! HTML::panelOpen('My Pilot Sessions', ['type' => 'vuk', 'key' => 'letter-p']) !!}
            <div class="row">
                <div class="col-md-12" style="margin-bottom: 15px;">
                    <span style="display: flex; justify-content: center;">{{ $pilotSessions->appends('atcSessions', Input::get('atcSessions'))->links() }}</span>
                    <table class="table table-striped tabled-bordered table-hover">
                        <tr>
                            <th>Callsign</th>
                            <th>Departure</th>
                            <th>Arrival</th>
                            <th>Duration</th>
                            <th>Logged On</th>
                        </tr>
                        @foreach($pilotSessions as $pilot)
                            <tr>
                                <td>{{ $pilot->callsign }}</td>
                                <td>{{ $pilot->departure_airport }}</td>
                                <td>{{ $pilot->arrival_airport }}</td>
                                <td>{{ $pilot->human_duration }}</td>
                                <td>{{ HTML::fuzzyDate($pilot->connected_at) }}</td>
                            </tr>
                        @endforeach
                    </table>
                    <span style="display: flex; justify-content: center;">{{ $pilotSessions->appends('atcSessions', Input::get('atcSessions'))->links() }}</span>
                </div>

            </div>
            {!! HTML::panelClose() !!}
        </div>
    </div>
@stop
