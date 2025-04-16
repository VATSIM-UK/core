@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">smartCARS Exercises</h3>
                    <a href="{{ route('adm.smartcars.exercises.create') }}" class="btn btn-primary pull-right"><i
                                class="fa fa-plus"></i> Create New</a>
                </div>
                <div class="box-body table-responsive">
                    {{ $exercises->render() }}
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th>Code</th>
                            <th>Flight #</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Featured</th>
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
                            <th colspan="3">Actions</th>
                        </tr>
                        @foreach($exercises as $exercise)
                            <tr>
                                <td>{{ $exercise->code }}</td>
                                <td>{{ $exercise->flightnum }}</td>
                                <td>
                                    @if($exercise->image)
                                        <a href="{{ $exercise->image }}">
                                            <img src="{{ $exercise->image }}" style="max-width: 150px;">
                                        </a>
                                    @endif
                                </td>
                                <td>{{ $exercise->name }}</td>
                                <td>{{ $exercise->description }}</td>
                                <td>{{ $exercise->featured ? 'Yes' : 'No' }}</td>
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
                                <td>
                                    <a href="{{ route('adm.smartcars.exercises.edit', $exercise->id) }}" class="btn btn-xs btn-warning">Edit</a>
                                </td>
                                <td>
                                    <form id="delete-{{ $exercise->id }}" method="POST"
                                          action="{{ route('adm.smartcars.exercises.destroy', $exercise->id) }}">
                                        @csrf
                                        @method('DELETE')
                                    <button class="btn btn-xs btn-danger" data-toggle="confirmation">Delete</button>
                                    </form>
                                </td>
                                <td>
                                    <a href="{{ route('adm.smartcars.exercises.resources.index', $exercise) }}" class="btn btn-xs btn-primary">Resources</a>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                    {{ $exercises->render() }}
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script type="text/javascript">
        d = '';
        function onConfirm(event, element) {
            element.parent().submit();
        }
    </script>
@endsection
