@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">smartCARS Airport</h3>
                </div>
                <div class="box-body">
                    @if(!$airport->exists)
                        <form method="POST" action="{{ route('adm.smartcars.airports.store') }}">
                            @csrf
                    @else
                                <form method="POST" action="{{ route('adm.smartcars.airports.update', $airport) }}">
                                    @csrf
                                    @method('PUT')
                    @endif

                    <div class="form-group">
                        <label for="icao">ICAO Code</label>
                        <input type="text" id="icao" name="icao" class="form-control"
                               value="{{ old('icao') ?: $airport->icao }}" placeholder="EGBB" required>
                    </div>

                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" class="form-control"
                               value="{{ old('name') ?: $airport->name }}" placeholder="Birmingham Airport" required>
                    </div>

                    <div class="form-group">
                        <label for="country">Country</label>
                        <input type="text" id="country" name="country" class="form-control"
                               value="{{ old('country') ?: $airport->country }}" placeholder="United Kingdom" required>
                    </div>

                    <div class="form-group">
                        <label for="latitude">Latitude</label>
                        <input type="number" step="any" id="latitude" name="latitude" class="form-control"
                               value="{{ old('latitude') ?: $airport->latitude }}" placeholder="52.453889" required>
                    </div>

                    <div class="form-group">
                        <label for="longitude">Longitude</label>
                        <input type="number" step="any" id="longitude" name="longitude" class="form-control"
                               value="{{ old('longitude') ?: $airport->longitude }}" placeholder="-1.748056" required>
                    </div>

                    <input class="btn btn-primary" type="submit" value="Submit">
                    <a class="btn btn-default" href="{{ route('adm.smartcars.airports.index') }}">Cancel</a>

                                </form>
                </div>
            </div>
        </div>
    </div>
@stop
