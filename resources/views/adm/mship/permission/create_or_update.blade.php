@extends('adm.layout')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title ">
                    {{ (isset($permission) ? "Update" : "Create")." Permission" }}
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
                    {!! Form::label("guard_name", "Guard Name") !!}
                    {!! Form::text("guard_name", null, ["class" => "form-control"]) !!}
                </div>

                <div class="form-group">
                    {!! Form::label("roles[]", "Roles") !!}
                    <div class="row">
                        @foreach($roles as $r)
                            <div class="col-sm-4">
                                <div class='checkbox'>
                                    @if(isset($permission))
                                        {!! Form::checkbox("roles[".$r->id."]", $r->id, ($r->hasPermissionTo($permission) OR Request::old("roles.".$r->id) ? "checked='checked'" : "")) !!}
                                    @else
                                        {!! Form::checkbox("roles[".$r->id."]", $r->id, (Request::old("roles.".$r->id) ? "checked='checked'" : "")) !!}
                                    @endif
                                    {{ $r->name }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="btn-toolbar">
                    <div class="btn-group pull-right">
                        {!! Form::submit((isset($permission) ? "Update" : "Create")." Permission", ["class" => "btn btn-primary"]) !!}
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
<script src='/AdminLTE/js/plugins/datatables/dataTables.bootstrap.js'></script>
@stop
