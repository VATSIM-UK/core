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

                @if(isset($role))
                    {!! Form::model($role, ['route' => ['adm.mship.role.update', $role->id]]) !!}
                @else
                    {!! Form::open(["route" => "adm.mship.role.create"]) !!}
                @endif

                <div class="form-group">
                    {!! Form::label("name", "Name") !!}
                    {!! Form::text("name", null, ["class" => "form-control"]) !!}
                </div>



                @if($_account->hasPermission("adm/mship/role/default"))
                    <div class="form-group">
                        {!! Form::label("default", "Default?") !!}

                        <div class="radio">
                            <label>
                                {!! Form::radio("default", 1) !!}
                                YES - <span class="help-inline warning">Choosing this will disable the current default group.</span>
                            </label>
                        </div>

                        <div class="radio">
                            <label>
                                {!! Form::radio("default", 0) !!}
                                NO
                            </label>
                        </div>
                    </div>
                @endif

                @if($_account->hasPermission("adm/mship/permission/attach"))
                    <div class="form-group">
                        {!! Form::label("permissions[]", "Permissions") !!}
                        <div class="row">
                            @foreach($permissions as $p)
                                <div class="col-sm-4">
                                    <div class='checkbox'>
                                        @if(isset($role))
                                            {!! Form::checkbox("permissions[".$p->id."]", $p->id, ($role->hasPermission($p) OR Input::old("permissions.".$p->id) ? "checked='checked'" : "")) !!}
                                        @else
                                            {!! Form::checkbox("permissions[".$p->id."]", $p->id, (Input::old("permissions.".$p->id) ? "checked='checked'" : "")) !!}
                                        @endif
                                        {{ $p->display_name }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="btn-toolbar">
                    <div class="btn-group pull-right">
                        {!! Form::submit((isset($role) ? "Update" : "Create")." Role", ["class" => "btn btn-primary"]) !!}
                    </div>
                </div>

                {!! Form::close() !!}
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
</div>
@stop
