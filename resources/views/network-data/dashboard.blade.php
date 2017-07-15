@extends('layout')

@section('content')
    <div class="row">
        <div class="col-md-6">
            {!! HTML::panelOpen('My ATC Sessions', ['type' => 'vuk', 'key' => 'letter-a']) !!}
            <div class="row">
                <div class="col-md-12" style="margin-bottom: 15px;">
                    <span style="display: flex; justify-content: center;">{{ $atcSessions->links() }}</span>
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
                                <td>{{ Carbon\Carbon::now()->subMinutes($atc->minutes_online)->diffForHumans(null, true) }}</td>
                                <td>{{ HTML::fuzzyDate($atc->connected_at) }}</td>
                            </tr>
                        @endforeach
                    </table>
                    <span style="display: flex; justify-content: center;">{{ $atcSessions->links() }}</span>
                </div>

            </div>
            {!! HTML::panelClose() !!}
        </div>

        <div class="col-md-6">
            {!! HTML::panelOpen('My Pilot Sessions', ['type' => 'vuk', 'key' => 'letter-p']) !!}
            <div class="row">
                <div class="col-md-12" style="margin-bottom: 15px;">
                    <p class="text-center">
                        Coming soon!
                    </p>
                </div>

            </div>
            {!! HTML::panelClose() !!}
        </div>
    </div>
@stop
