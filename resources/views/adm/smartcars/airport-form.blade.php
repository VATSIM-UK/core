@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">smartCARS Airport</h3>
                </div>
                <div class="box-body">
                    @if(!isset($airport))
                        {!! Form::open(['method'  => 'post', 'route' => ['adm.smartcars.airports.store']]) !!}
                    @else
                        {!! Form::open(['method'  => 'put', 'route' => ['adm.smartcars.airports.update', $airport->id]]) !!}
                    @endif

                    <div class="form-group">
                        <label for="icao">ICAO Code</label>
                        <input type="text" id="icao" name="icao" class="form-control"
                               value="@isset($airport){{ $airport->icao }}@endisset" placeholder="EGBB">
                    </div>

                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" class="form-control"
                               value="@isset($airport){{ $airport->name }}@endisset" placeholder="Birmingham Airport">
                    </div>

                    <div class="form-group">
                        <label for="country">Country</label>
                        <input type="text" id="country" name="country" class="form-control"
                               value="@isset($airport){{ $airport->country }}@endisset" placeholder="United Kingdom">
                    </div>

                    <div class="form-group">
                        <label for="latitude">Latitude</label>
                        <input type="number" id="latitude" name="latitude" class="form-control"
                               value="@isset($airport){{ $airport->latitude }}@endisset" placeholder="52.453889">
                    </div>

                    <div class="form-group">
                        <label for="longitude">Longitude</label>
                        <input type="number" id="longitude" name="longitude" class="form-control"
                               value="@isset($airport){{ $airport->longitude }}@endisset" placeholder="-1.748056">
                    </div>

                    <input class="btn btn-primary" type="submit" value="Submit">
                    <a class="btn btn-default" href="{{ route('adm.smartcars.airports.index') }}">Cancel</a>

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@stop
