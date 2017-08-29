@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">smartCARS Flights</h3>
                </div>
                <div class="box-body table-responsive">
                    {{ $flights->render() }}
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th>Bid</th>
                            <th>Aircraft</th>
                            <th>Route</th>
                            <th>Flight Time</th>
                            <th>Landing Rate</th>
                            <th>Comments</th>
                            <th>Fuel Used</th>
                            <th>Log</th>
                            <th>Status</th>
                        </tr>
                        @foreach($flights as $flight)
                            <tr>
                                <td>{{ $flight->bid_id }}</td>
                                <td>{{ $flight->aircraft_id }}</td>
                                <td>{{ $flight->route }}</td>
                                <td>{{ $flight->flight_time }}</td>
                                <td>{{ $flight->landing_rate }}</td>
                                <td>{{ $flight->comments }}</td>
                                <td>{{ $flight->fuel_used }}</td>
                                <td>
                                    <button class="btn btn-primary" data-toggle="collapse"
                                            data-target="#{{ $flight->id }}-log">
                                        Show/Hide
                                    </button>
                                    <div id="{{ $flight->id }}-log" class="collapse">
                                        {!! str_replace('[', '<br>[', htmlspecialchars($flight->log)) !!}
                                    </div>
                                </td>
                                <td>{{ $flight->status }}</td>
                            </tr>
                        @endforeach
                    </table>
                    {{ $flights->render() }}
                </div>
            </div>
        </div>
    </div>
@stop
