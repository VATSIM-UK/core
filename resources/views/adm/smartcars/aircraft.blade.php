@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">
                        smartCARS Aircraft
                    </h3>
                </div>
                <div class="box-body table-responsive">
                    {{ $aircraft->render() }}
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th>ICAO</th>
                            <th>Name</th>
                            <th>Full Name</th>
                            <th>Registration</th>
                            <th>Range (nm)</th>
                            <th>Weight (kg)</th>
                            <th>Cruise Altitude</th>
                            <th>Max Passengers</th>
                            <th>Max Cargo (kg)</th>
                        </tr>
                        @foreach($aircraft as $ac)
                        <tr>
                            <td>{{ $ac->icao }}</td>
                            <td>{{ $ac->name }}</td>
                            <td>{{ $ac->fullname }}</td>
                            <td>{{ $ac->registration }}</td>
                            <td>{{ $ac->range_nm }}</td>
                            <td>{{ $ac->weight_kg }}</td>
                            <td>{{ $ac->cruise_altitude }}</td>
                            <td>{{ $ac->max_passengers }}</td>
                            <td>{{ $ac->max_cargo_kg }}</td>
                        </tr>
                        @endforeach
                    </table>
                    {{ $aircraft->render() }}
                </div>
            </div>
        </div>
    </div>
@stop
