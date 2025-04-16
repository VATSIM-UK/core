@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">smartCARS Aircraft</h3>
                </div>
                <div class="box-body">
                    @if(!$aircraft->exists)
                        <form method="POST" action="{{ route('adm.smartcars.aircraft.store') }}">
                            @csrf
                    @else
                        <form method="POST" action="{{ route('adm.smartcars.aircraft.update', $aircraft) }}">
                            @csrf
                            @method('PUT')
                    @endif

                    <div class="form-group">
                        <label for="icao">ICAO Code<i class="fa fa-asterisk text-danger"></i></label>
                        <input type="text" id="icao" name="icao" class="form-control"
                               value="{{ old('icao') ?: $aircraft->icao }}" placeholder="C172" required>
                    </div>

                    <div class="form-group">
                        <label for="name">Manufacturer<i class="fa fa-asterisk text-danger"></i></label>
                        <input type="text" id="name" name="name" class="form-control"
                               value="{{ old('name') ?: $aircraft->name }}" placeholder="Cessna" required>
                    </div>

                    <div class="form-group">
                        <label for="fullname">Full Name<i class="fa fa-asterisk text-danger"></i></label>
                        <input type="text" id="fullname" name="fullname" class="form-control"
                               value="{{ old('fullname') ?: $aircraft->fullname }}" placeholder="Cessna 172" required>
                    </div>

                    <div class="form-group">
                        <label for="registration">Registration<i class="fa fa-asterisk text-danger"></i></label>
                        <input type="text" id="registration" name="registration" class="form-control"
                               value="{{ old('registration') ?: $aircraft->registration }}" placeholder="GABCD" required>
                    </div>

                    <div class="form-group">
                        <label for="range_nm">Range (nm)</label>
                        <input type="number" id="range_nm" name="range_nm" class="form-control"
                               value="{{ old('range_nm') ?: $aircraft->range_nm }}" placeholder="0">
                    </div>

                    <div class="form-group">
                        <label for="weight_kg">Weight (kg)</label>
                        <input type="number" id="weight_kg" name="weight_kg" class="form-control"
                               value="{{ old('weight_kg') ?: $aircraft->weight_kg }}" placeholder="0">
                    </div>

                    <div class="form-group">
                        <label for="cruise_altitude">Service Ceiling</label>
                        <input type="number" id="cruise_altitude" name="cruise_altitude" class="form-control"
                               value="{{ old('cruise_altitude') ?: $aircraft->cruise_altitude }}" placeholder="0">
                    </div>

                    <div class="form-group">
                        <label for="max_passengers">Max Passengers</label>
                        <input type="number" id="max_passengers" name="max_passengers" class="form-control"
                               value="{{ old('max_passengers') ?: $aircraft->max_passengers }}" placeholder="0">
                    </div>

                    <div class="form-group">
                        <label for="max_cargo_kg">Max Cargo (kg)</label>
                        <input type="number" id="max_cargo_kg" name="max_cargo_kg" class="form-control"
                               value="{{ old('max_cargo_kg') ?: $aircraft->max_cargo_kg }}" placeholder="0">
                    </div>

                    <input class="btn btn-primary" type="submit" value="Submit">
                    <a class="btn btn-default" href="{{ route('adm.smartcars.aircraft.index') }}">Cancel</a>

                        </form>
                </div>
            </div>
        </div>
    </div>
@stop
