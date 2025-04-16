@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">smartCARS Aircraft</h3>
                    <a href="{{ route('adm.smartcars.aircraft.create') }}" class="btn btn-primary pull-right"><i
                                class="fa fa-plus"></i> Create New</a>
                </div>
                <div class="box-body table-responsive">
                    {{ $aircraft->render() }}
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th>ICAO</th>
                            <th>Manufacturer</th>
                            <th>Full Name</th>
                            <th>Registration</th>
                            <th>Range (nm)</th>
                            <th>Weight (kg)</th>
                            <th>Service Ceiling</th>
                            <th>Max Passengers</th>
                            <th>Max Cargo (kg)</th>
                            <th colspan="2">Actions</th>
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
                            <td>
                                <a href="{{ route('adm.smartcars.aircraft.edit', $ac->id) }}" class="btn btn-xs btn-warning">Edit</a>
                            </td>
                            <td>
                                <form id="delete-{{ $ac->id }}" method="post"
                                      action="{{ route('adm.smartcars.aircraft.destroy', $ac->id) }}">
                                    @csrf
                                    @method('DELETE')
                                <button class="btn btn-xs btn-danger" data-toggle="confirmation">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                    {{ $aircraft->render() }}
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
