@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">smartCARS Exercise</h3>
                </div>
                    @if(!$exercise->exists)
                    <form method="POST" action="{{ route('adm.smartcars.exercises.store') }}"
                          enctype="multipart/form-data">
                        @csrf
                    @else
                            <form method="POST" action="{{ route('adm.smartcars.exercises.update', $exercise) }}"
                                  enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                    @endif

                <div class="box-body">
                    <div class="form-group">
                        <label for="code">Code</label>
                        <input type="text" id="code" name="code" class="form-control"
                               value="{{ old('code') ?: $exercise->code }}">
                    </div>

                    <div class="form-group">
                        <label for="flightnum">Flight #</label>
                        <input type="text" id="flightnum" name="flightnum" class="form-control"
                               value="{{ old('flightnum') ?: $exercise->flightnum }}">
                    </div>

                    <div class="form-group">
                        <label for="image">Image</label>
                        <input type="file" id="image" name="image" class="form-control">
                        <p>Please ensure the image has an aspect ratio of 3:1 to display correctly.</p>
                    </div>

                    @if(isset($exercise) && $exercise->image)
                        <div class="form-group">
                            <label>Current Image</label>
                            <p class="form-control-static">
                                <a href="{{ $exercise->image }}">
                                    <img src="{{ $exercise->image }}">
                                </a>
                            </p>
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" class="form-control"
                               value="{{ old('name') ?: $exercise->name }}">
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <input type="text" id="description" name="description" class="form-control"
                               value="{{ old('description') ?: $exercise->description }}">
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox"
                                       name="featured"{{ old('featured') || $exercise->featured ? ' checked' : '' }}>Featured
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="departure_id">Departure Airport</label>
                        <select id="departure_id" name="departure_id" class="form-control">
                            @foreach(\App\Models\Smartcars\Airport::all() as $airport)
                                <option value="{{ $airport->id }}"
                                        {{ (old('departure_id') ?: $exercise->departure_id) == $airport->id ? 'selected' : ''}}>
                                    {{ $airport->icao }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="arrival_id">Arrival Airport</label>
                        <select id="arrival_id" name="arrival_id" class="form-control">
                            @foreach(\App\Models\Smartcars\Airport::all() as $airport)
                                <option value="{{ $airport->id }}"
                                        {{ (old('arrival_id') ?: $exercise->arrival_id) == $airport->id ? 'selected' : ''}}>
                                    {{ $airport->icao }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="route">Route</label>
                        <input type="text" id="route" name="route" class="form-control"
                               value="{{ old('route') ?: $exercise->route }}">
                    </div>

                    <div class="form-group">
                        <label for="route_details">Route Details</label>
                        <input type="text" id="route_details" name="route_details" class="form-control"
                               value="{{ old('route_details') ?: $exercise->route_details }}">
                    </div>

                    <div class="form-group">
                        <label for="aircraft_id">Aircraft</label>
                        <select id="aircraft_id" name="aircraft_id" class="form-control">
                            @foreach(\App\Models\Smartcars\Aircraft::all() as $aircraft)
                                <option value="{{ $aircraft->id }}"
                                        {{ (old('aircraft_id') ?: $exercise->aircraft_id) == $aircraft->id ? 'selected' : ''}}>
                                    {{ $aircraft->icao }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="cruise_altitude">Cruise Altitude</label>
                        <input type="text" id="cruise_altitude" name="cruise_altitude" class="form-control"
                               value="{{ old('cruise_altitude') ?: $exercise->cruise_altitude }}">
                    </div>

                    <div class="form-group">
                        <label for="distance">Distance (nm)</label>
                        <input type="number" id="distance" name="distance" class="form-control"
                               value="{{ old('distance') ?: $exercise->distance }}">
                    </div>

                    <div class="form-group">
                        <label for="flight_time">Flight Time (HH:MM)</label>
                        <input type="number" step="any" id="flight_time" name="flight_time" class="form-control"
                               value="{{ old('flight_time') ?: $exercise->flight_time }}">
                    </div>

                    <div class="form-group">
                        <label for="cruise_altitude">Notes</label>
                        <input type="text" id="notes" name="notes" class="form-control"
                               value="{{ old('notes') ?: $exercise->notes }}">
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox"
                                       name="enabled"{{ old('enabled') || $exercise->enabled ? ' checked' : '' }}>Enabled
                            </label>
                        </div>
                    </div>

                    <input class="btn btn-primary" type="submit" value="Submit">
                    <a class="btn btn-default" href="{{ route('adm.smartcars.exercises.index') }}">Cancel</a>

                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
