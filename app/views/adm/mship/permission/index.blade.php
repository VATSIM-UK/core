@extends('adm.layout')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-info">
            <div class="box-header">
                <div class="box-title">Options</div>
            </div>
            <div class="box-body">
                <div class="btn-toolbar">
                    <div class="btn-group pull-right">
                        {{ link_to_route("adm.mship.permission.create", "Create Permission", [], ["class" => "btn btn-success"]) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="box box-warning">
            <div class="box-header">
                <div class="box-title">Search Criteria</div>
            </div>
            <div class="box-body">

            </div>
        </div>

        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title ">
                    Search Results
                </h3>
            </div><!-- /.box-header -->
            <div class="box-body">
                <div align="center">
                    {{ $permissions->links() }}
                </div>
                <table id="mship-roles" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Display Name</th>
                            <th>Name</th>
                            <th># Roles</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($permissions as $p)
                        <tr>
                            <td>{{ link_to_route('adm.mship.permission.update', $p->permission_id, [$p->permission_id]) }}</td>
                            <td>{{ $p->display_name }}</td>
                            <td>{{ $p->name }}</td>
                            <td>{{ count($p->roles) }}</td>
                            <td>{{ $p->updated_at->toDateTimeString() }}</td>
                        </tr>
                        @endforeach
                        @if(count($permissions) < 1)
                        <tr>
                            <td colspan="5" align="center">No permissions to display :(</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
                <div align="center">
                    {{ $permissions->links() }}
                </div>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
</div>
@stop

@section('scripts')
@parent
{{ HTML::script('/assets/js/plugins/datatables/dataTables.bootstrap.js') }}
@stop