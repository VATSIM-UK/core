@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">smartCARS Airports</h3>
                    <a href="{{ route('adm.smartcars.airports.create') }}" class="btn btn-primary pull-right"><i
                                class="fa fa-plus"></i> Create New</a>
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
                            <th colspan="2">Actions</th>
                        </tr>
                        @foreach($airports as $airport)
                            <tr>
                                <td>{{ $airport->icao }}</td>
                                <td>{{ $airport->name }}</td>
                                <td>{{ $airport->country }}</td>
                                <td>{{ $airport->latitude }}</td>
                                <td>{{ $airport->longitude }}</td>
                                <td>
                                    <a href="{{ route('adm.smartcars.airports.edit', $airport->id) }}" class="btn btn-xs btn-warning">Edit</a>
                                </td>
                                <td>
                                    {!! Form::open(['method'  => 'delete', 'route' => ['adm.smartcars.airports.destroy', $airport->id]]) !!}
                                    <input class="btn btn-xs btn-danger" type="submit" value="Delete">
                                    {!! Form::close() !!}
                                </td>
                            </tr>
                        @endforeach
                    </table>
                    {{ $airports->render() }}
                </div>
            </div>
        </div>
    </div>
@stop
