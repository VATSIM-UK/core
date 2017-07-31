@extends('adm.layout')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title ">
                    Create New Member Role
                </h3>
            </div><!-- /.box-header -->
            <div class="box-body">

                @if(isset($permission))
                    {!! Form::model($permission, ['route' => ['adm.mship.permission.update', $permission->id]]) !!}
                @else
                    {!! Form::open(["route" => "adm.mship.permission.create"]) !!}
                @endif

                <div class="form-group">
                    {!! Form::label("name", "Name") !!}
                    {!! Form::text("name", null, ["class" => "form-control"]) !!}
                </div>

                <div class="form-group">
                    {!! Form::label("display_name", "Display Name") !!}
                    {!! Form::text("display_name", null, ["class" => "form-control"]) !!}
                </div>

                <div class="form-group">
                    {!! Form::label("roles[]", "Roles") !!}
                    <div class="row">
                        @foreach($roles as $r)
                            <div class="col-sm-4">
                                <div class='checkbox'>
                                    @if(isset($permission))
                                        {!! Form::checkbox("roles[".$r->id."]", $r->id, ($r->hasPermission($permission) OR Input::old("roles.".$r->id) ? "checked='checked'" : "")) !!}
                                    @else
                                        {!! Form::checkbox("roles[".$r->id."]", $r->id, (Input::old("roles.".$r->id) ? "checked='checked'" : "")) !!}
                                    @endif
                                    {{ $r->name }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="btn-toolbar">
                    <div class="btn-group pull-right">
                        {!! Form::submit("Create Role", ["class" => "btn btn-primary"]) !!}
                    </div>
                </div>

                {!! Form::close() !!}
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
</div>
@stop

@section('scripts')
@parent
{!! HTML::script('/AdminLTE/js/plugins/datatables/dataTables.bootstrap.js') !!}
@stop