@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">{{ $flight->name }} - Resources</h3>
                    <a href="{{ route('adm.smartcars.resources.create', $flight) }}" class="btn btn-primary pull-right">
                        <i class="fa fa-plus"></i> Create New
                    </a>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th>Type</th>
                            <th>Name</th>
                            <th>Resource</th>
                            <th colspan="2">Actions</th>
                        </tr>
                        @foreach($resources as $resource)
                            <tr>
                                <td>{{ $resource->type }}</td>
                                <td>{{ $resource->display_name }}</td>
                                <td><a href="{{ $resource->asset() }}">{{ $resource->resource }}</a></td>
                                <td>
                                    <a href="{{ route('adm.smartcars.resources.edit', [$flight, $resource]) }}" class="btn btn-xs btn-warning">Edit</a>
                                </td>
                                <td>
                                    {!! Form::open(['id' => "delete-$resource->id", 'method'  => 'delete', 'route' => ['adm.smartcars.resources.destroy', $flight, $resource]]) !!}
                                    <button class="btn btn-xs btn-danger" data-toggle="confirmation">Delete</button>
                                    {!! Form::close() !!}
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    @parent
    <script type="text/javascript">
        d = '';
        function onConfirm(event, element) {
            element.parent().submit();
        }
    </script>
@endsection
