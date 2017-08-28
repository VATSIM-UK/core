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
                        {!! link_to_route("adm.mship.note.type.create", "Create Note Type", [], ["class" => "btn btn-success"]) !!}
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
                            <th>Short Code</th>
                            <th>Colour Code</th>
                            <th>Is Available</th>
                            <th>Is System</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($types as $t)
                        <tr>
                            <td>{!! link_to_route('adm.mship.note.type.update', $t->id, [$t->id]) !!}</td>
                            <td>
                                {{ $t->name }}
                                @if($t->is_default)
                                    <span class="label label-success">Default</span>
                                @endif
                            </td>
                            <td>{{ $t->short_code }}</td>
                            <td>{{ $t->colour_code }}</td>
                            <td>{!! ($t->is_available ? '<span class="label label-success">YES</span>' : '<span class="label label-danger">NO</span>') !!}</td>
                            <td>{!! ($t->is_system ? '<span class="label label-success">YES</span>' : '<span class="label label-danger">NO</span>') !!}</td>
                            <td>
                                @if($_account->hasPermission("adm/mship/note/type/*/update"))
                                    {!! link_to_route("adm.mship.note.type.update", "Edit", [$t->id], ["class" => "btn btn-xs btn-primary"]) !!}
                                @endif
                                @if($_account->hasPermission("adm/mship/note/type/*/delete"))
                                    {!! Form::button("Delete", ["data-href" => URL::route("adm.mship.note.type.delete", [$t->id]), "data-toggle" => "confirmation", "class" => "btn btn-xs btn-danger"]) !!}
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        @if(count($types) < 1)
                        <tr>
                            <td colspan="6" align="center">No note types to display :(</td>
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
{!! HTML::script('/AdminLTE/js/plugins/datatables/dataTables.bootstrap.js') !!}
@stop