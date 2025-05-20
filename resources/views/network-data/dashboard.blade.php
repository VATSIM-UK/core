@extends('layout')

@section('content')
    <div class="row">
        <div class="col-md-6">
            @include('components.html.panel_open', [
                'title' => 'My ATC Sessions',
                'icon' => ['type' => 'vuk', 'key' => 'letter-a'],
                'attr' => []
            ])
            <div class="row">
                <div class="col-md-12" style="margin-bottom: 15px;">
                    <table class="table table-striped tabled-bordered table-hover">
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Callsign</th>
                            <th>Position</th>
                            <th>Frequency</th>
                            <th>Duration</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($atcSessions as $atc)
                            <tr>
                                <td>{{ $atc->connected_at->format('d/m/Y') }}</td>
                                <td>{{ $atc->callsign }}</td>
                                <td>{{ $atc->type }}</td>
                                <td>{{ $atc->frequency }}</td>
                                <td>{{ $atc->human_duration }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        @if($atcSessions->isEmpty())
                            <tr>
                                <th colspan="5" class="text-center">
                                    <span style="display: flex; justify-content: center;">You have not made any connections to the network as a controller.</span>
                                </th>
                            </tr>
                        @else
                            <tr>
                                <th colspan="5" class="text-center">
                                    <span style="display: flex; justify-content: center;">{{ $atcSessions->appends(request()->query())->links() }}</span>
                                </th>
                            </tr>
                        @endif
                        </tfoot>
                    </table>
                </div>
            </div>
            @include('components.html.panel_close')
        </div>

        <div class="col-md-6">
            @include('components.html.panel_open', [
                'title' => 'My Pilot Sessions',
                'icon' => ['type' => 'vuk', 'key' => 'letter-p'],
                'attr' => []
            ])
            <div class="row">
                <div class="col-md-12" style="margin-bottom: 15px;">
                    <table class="table table-striped tabled-bordered table-hover">
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Callsign</th>
                            <th>Departure</th>
                            <th>Arrival</th>
                            <th>Duration</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($pilotSessions as $pilot)
                            <tr>
                                <td>{{ $pilot->connected_at->format('d/m/Y') }}</td>
                                <td>{{ $pilot->callsign }}</td>
                                <td>{{ $pilot->departure_airport }}</td>
                                <td>{{ $pilot->arrival_airport }}</td>
                                <td>{{ $pilot->human_duration }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        @if($pilotSessions->isEmpty())
                            <tr>
                                <th colspan="5" class="text-center">
                                    {{-- <span style="display: flex; justify-content: center;">You have not made any connections to the network as a pilot.</span> --}}
                                    <span style="display: flex; justify-content: center;">VATSIM UK does not currently track connections to the network as a pilot.</span>
                                </th>
                            </tr>
                        @else
                            <tr>
                                <th colspan="5" class="text-center">
                                    <span style="display: flex; justify-content: center;">{{ $pilotSessions->appends(request()->query())->links() }}</span>
                                </th>
                            </tr>
                        @endif
                        </tfoot>
                    </table>
                </div>

            </div>
            @include('components.html.panel_close')
        </div>
    </div>
@stop
