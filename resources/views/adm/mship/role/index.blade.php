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
                        {!! link_to_route("adm.mship.role.create", "Create Role", [], ["class" => "btn btn-success"]) !!}
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
                <table id="mship-roles" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Default</th>
                            <th># Members</th>
                            <th># Permissions</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $r)
                        <tr>
                            <td>{!! link_to_route('adm.mship.role.update', $r->id, [$r->id]) !!}</td>
                            <td>
                                {{ $r->name }}
                                @if($r->default)
                                    <span class="label label-success">Default</span>
                                @endif
                            </td>
                            <td>{{ $r->default }}</td>
                            <td>{{ $r->accounts()->count() }}</td>
                            <td><span class="{{ $r->permissions->isEmpty() ?: 'btn-link' }}" data-toggle="popover" data-trigger="hover" data-html="true" data-content="@foreach($r->permissions as $permission){{$permission->name}}<br> @endforeach">{{ $r->permissions->count() }}</span></td>
                            <td>{{ $r->updated_at->toDateTimeString() }}</td>
                            <td>
                                @if($_account->hasPermission("adm/mship/role/*/update"))
                                    {!! link_to_route("adm.mship.role.update", "Edit", [$r->id], ["class" => "btn btn-xs btn-primary"]) !!}
                                @endif
                                @if($_account->hasPermission("adm/mship/role/*/delete"))
                                    {!! Form::button("Delete", ["data-href" => URL::route("adm.mship.role.delete", [$r->id]), "data-toggle" => "confirmation", "class" => "btn btn-xs btn-danger"]) !!}
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        @if(count($roles) < 1)
                        <tr>
                            <td colspan="6" align="center">No roles to display :(</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
</div>
@stop

@section('scripts')
@parent
{!! HTML::script('/AdminLTE/js/plugins/datatables/jquery.dataTables.js') !!}
{!! HTML::script('/AdminLTE/js/plugins/datatables/dataTables.bootstrap.js') !!}
    <script type="text/javascript">
            $('[data-toggle="popover"]').popover();
    </script>
@stop
