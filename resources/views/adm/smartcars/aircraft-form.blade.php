@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">smartCARS Aircraft</h3>
                </div>
                <div class="box-body">
                    @if(!isset($aircraft))
                        {!! Form::open(['method'  => 'post', 'route' => ['adm.smartcars.aircraft.store']]) !!}
                    @else
                        {!! Form::open(['method'  => 'put', 'route' => ['adm.smartcars.aircraft.update', $aircraft->id]]) !!}
                    @endif

                    <div class="form-group">
                        <label for="icao">ICAO Code</label>
                        <input type="text" id="icao" name="icao" class="form-control"
                               value="@isset($aircraft){{ $aircraft->icao }}@endisset" placeholder="C172">
                    </div>

                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" class="form-control"
                               value="@isset($aircraft){{ $aircraft->name }}@endisset" placeholder="Cessna">
                    </div>

                    <div class="form-group">
                        <label for="fullname">Full Name</label>
                        <input type="text" id="fullname" name="fullname" class="form-control"
                               value="@isset($aircraft){{ $aircraft->fullname }}@endisset" placeholder="Cessna 172">
                    </div>

                    <div class="form-group">
                        <label for="registration">Registration</label>
                        <input type="text" id="registration" name="registration" class="form-control"
                               value="@isset($aircraft){{ $aircraft->registraton }}@endisset" placeholder="GABCD">
                    </div>

                    <div class="form-group">
                        <label for="range_nm">Range (nm)</label>
                        <input type="number" id="range_nm" name="range_nm" class="form-control"
                               value="@isset($aircraft){{ $aircraft->range_nm }}@endisset" placeholder="0">
                    </div>

                    <div class="form-group">
                        <label for="weight_kg">Weight (kg)</label>
                        <input type="number" id="weight_kg" name="weight_kg" class="form-control"
                               value="@isset($aircraft){{ $aircraft->weight_kg }}@endisset" placeholder="0">
                    </div>

                    <div class="form-group">
                        <label for="cruise_altitude">Cruise Altitude</label>
                        <input type="number" id="cruise_altitude" name="cruise_altitude" class="form-control"
                               value="@isset($aircraft){{ $aircraft->cruise_altitude }}@endisset" placeholder="0">
                    </div>

                    <div class="form-group">
                        <label for="max_passengers">Max Passengers</label>
                        <input type="number" id="max_passengers" name="max_passengers" class="form-control"
                               value="@isset($aircraft){{ $aircraft->max_passengers }}@endisset" placeholder="0">
                    </div>

                    <div class="form-group">
                        <label for="max_cargo_kg">Max Cargo (kg)</label>
                        <input type="number" id="max_cargo_kg" name="max_cargo_kg" class="form-control"
                               value="@isset($aircraft){{ $aircraft->max_cargo_kg }}@endisset" placeholder="0">
                    </div>

                    <input class="btn btn-primary" type="submit" value="Submit">
                    <a class="btn btn-default" href="{{ route('adm.smartcars.aircraft.index') }}">Cancel</a>

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@stop
