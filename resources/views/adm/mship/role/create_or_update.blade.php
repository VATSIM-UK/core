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

                    @can('use-permission', "adm/mship/role/default"))
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
                                    {!! Form::radio("default2", 0) !!}
                                    NO
                                </label>
                            </div>
                        </div>
                    @endcan

                    <div class="form-group">
                        <label for="mandatoryPassword" class="control-label">Mandatory Password</label>

                        @php
                            $mandatoryPassword = isset($role) && $role->hasMandatoryPassword();
                        @endphp
                        <div class="radio">
                            <label class="radio-inline">
                                <input name="password_mandatory" type="radio" value="1" id="mandatoryPassword"{{ $mandatoryPassword ? ' checked' : '' }}> Yes
                            </label>
                            <label class="radio-inline">
                                <input name="password_mandatory" type="radio" value="0"{{ !$mandatoryPassword ? ' checked' : '' }}> No
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="passwordLifetime">Password Lifetime (Days - for infinite, enter 0)</label>
                        <input type="number" class="form-control" id="passwordLifetime" name="password_lifetime" required value="{{ isset($role) ? $role->password_lifetime : '' }}">
                    </div>

                    <div class="form-group">
                        <label for="sessionTimeout">Session Timeout (Minutes - for infinite, leave blank)</label>
                        <input type="number" class="form-control" id="sessionTimeout" name="session_timeout" value="{{ isset($role) ? $role->session_timeout : '' }}">
                    </div>

                    @can('use-permission', "adm/mship/permission/attach"))
                        <div class="form-group">
                            {!! Form::label("permissions[]", "Permissions") !!}
                            <ul class="list-unstyled" style="column-count: 3;">
                                @foreach($permissions as $p)
                                    <li style="display: table;">
                                        @if(isset($role))
                                            {!! Form::checkbox("permissions[".$p->id."]", $p->id, ($role->hasPermissionTo($p) OR Input::old("permissions.".$p->id) ? "checked='checked'" : "")) !!}
                                        @else
                                            {!! Form::checkbox("permissions[".$p->id."]", $p->id, (Input::old("permissions.".$p->id) ? "checked='checked'" : "")) !!}
                                        @endif
                                        {{ $p->display_name }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endcan

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
