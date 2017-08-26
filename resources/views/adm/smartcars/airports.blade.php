@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">
                        smartCARS Airports
                    </h3>
                </div>
                <div class="box-body table-responsive">
                    {{ $airports->render() }}
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th>ICAO</th>
                            <th>Name</th>
                            <th>Country</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                        </tr>
                        @foreach($airports as $airport)
                            <tr>
                                <td>{{ $airport->icao }}</td>
                                <td>{{ $airport->name }}</td>
                                <td>{{ $airport->country }}</td>
                                <td>{{ $airport->latitude }}</td>
                                <td>{{ $airport->longitude }}</td>
                            </tr>
                        @endforeach
                    </table>
                    {{ $airports->render() }}
                </div>
            </div>
        </div>
    </div>
@stop
