@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">smartCARS Exercise</h3>
                </div>
                <div class="box-body">
                    @if(!isset($exercise))
                        {!! Form::open(['method'  => 'post', 'route' => ['adm.smartcars.exercises.store'], 'files' => true]) !!}
                    @else
                        {!! Form::open(['method'  => 'put', 'route' => ['adm.smartcars.exercises.update', $exercise->id], 'files' => true]) !!}
                    @endif

                    <div class="form-group">
                        <label for="image">Image</label>
                        <input type="file" id="image" name="image" class="form-control">
                    </div>

                    @if(isset($exercise) && $exercise->image)
                        <div class="form-group">
                            <label>Current Image</label>
                            <p class="form-control-static">
                                <a href="{{ $exercise->image->asset() }}">
                                    <img src="{{ $exercise->image->asset() }}">
                                </a>
                            </p>
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="code">Code</label>
                        <input type="text" id="code" name="code" class="form-control"
                               value="@isset($exercise){{ $exercise->code }}@endisset">
                    </div>

                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" class="form-control"
                               value="@isset($exercise){{ $exercise->name }}@endisset">
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <input type="text" id="description" name="description" class="form-control"
                               value="@isset($exercise){{ $exercise->description }}@endisset">
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox"
                                       name="featured"{{ isset($exercise->featured) ? ' checked' : '' }}>Featured
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="flightnum">Flight #</label>
                        <input type="text" id="flightnum" name="flightnum" class="form-control"
                               value="@isset($exercise){{ $exercise->flightnum }}@endisset">
                    </div>

                    <div class="form-group">
                        <label for="departure_id">Departure Airport</label>
                        <select id="departure_id" name="departure_id" class="form-control">
                            <option>TO DO - fill list</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="arrival_id">Arrival Airport</label>
                        <select id="arrival_id" name="arrival_id" class="form-control">
                            <option>TO DO - fill list</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="route">Route</label>
                        <input type="text" id="route" name="route" class="form-control"
                               value="@isset($exercise){{ $exercise->route }}@endisset">
                    </div>

                    <div class="form-group">
                        <label for="route_details">Route Details</label>
                        <input type="text" id="route_details" name="route_details" class="form-control"
                               value="@isset($exercise){{ $exercise->route_details }}@endisset">
                    </div>

                    <div class="form-group">
                        <label for="aircraft_id">Aircraft</label>
                        <select id="aircraft_id" name="aircraft_id" class="form-control">
                            <option>TO DO - fill list</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="cruise_altitude">Cruise Altitude</label>
                        <input type="text" id="cruise_altitude" name="cruise_altitude" class="form-control"
                               value="@isset($exercise){{ $exercise->cruise_altitude }}@endisset">
                    </div>

                    <div class="form-group">
                        <label for="distance">Distance (nm)</label>
                        <input type="number" id="distance" name="distance" class="form-control"
                               value="@isset($exercise){{ $exercise->distance }}@endisset">
                    </div>

                    <div class="form-group">
                        <label for="flight_time">Flight Time</label>
                        <input type="number" id="flight_time" name="flight_time" class="form-control"
                               value="@isset($exercise){{ $exercise->flight_time }}@endisset">
                    </div>

                    <div class="form-group">
                        <label for="cruise_altitude">Notes</label>
                        <input type="text" id="notes" name="notes" class="form-control"
                               value="@isset($exercise){{ $exercise->notes }}@endisset">
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox"
                                       name="enabled"{{ isset($exercise->enabled) ? ' checked' : '' }}>Enabled
                            </label>
                        </div>
                    </div>

                    <input class="btn btn-primary" type="submit" value="Submit">
                    <a class="btn btn-default" href="{{ route('adm.smartcars.exercises.index') }}">Cancel</a>

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@stop
