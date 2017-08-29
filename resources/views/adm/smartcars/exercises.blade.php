@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">
                        smartCARS Exercises
                    </h3>
                </div>
                <div class="box-body table-responsive">
                    {{ $exercises->render() }}
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th>Image</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Featured</th>
                            <th>Flight #</th>
                            <th>Dep</th>
                            <th>Arr</th>
                            <th>Route</th>
                            <th>Route Details</th>
                            <th>Aircraft</th>
                            <th>Cruise Altitude</th>
                            <th>Distance</th>
                            <th>Flight Time</th>
                            <th>Notes</th>
                            <th>Enabled</th>
                        </tr>
                        @foreach($exercises as $exercise)
                            <tr>
                                <td>
                                    @if(Storage::drive('public')->has("smartcars/exercises/$exercise->id.jpg"))
                                        <a href="{{ asset("storage/smartcars/exercises/$exercise->id.jpg") }}">
                                            <img src="{{ asset("storage/smartcars/exercises/$exercise->id.jpg") }}"
                                                 style="max-width: 150px;">
                                        </a>
                                    @endif
                                </td>
                                <td>{{ $exercise->code }}</td>
                                <td>{{ $exercise->name }}</td>
                                <td>{{ $exercise->description }}</td>
                                <td>{{ $exercise->featured ? 'Yes' : 'No' }}</td>
                                <td>{{ $exercise->flightnum }}</td>
                                <td>{{ $exercise->departure->icao }}</td>
                                <td>{{ $exercise->arrival->icao }}</td>
                                <td>{{ $exercise->route }}</td>
                                <td>{{ $exercise->route_details }}</td>
                                <td>{{ $exercise->aircraft->fullname }}</td>
                                <td>{{ $exercise->cruise_altitude }}</td>
                                <td>{{ $exercise->distance }}</td>
                                <td>{{ $exercise->flight_time }}</td>
                                <td>{{ $exercise->notes }}</td>
                                <td>{{ $exercise->enabled ? 'Yes' : 'No' }}</td>
                            </tr>
                        @endforeach
                    </table>
                    {{ $exercises->render() }}
                </div>
            </div>
        </div>
    </div>
@stop
