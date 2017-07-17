@extends('adm.layout')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title ">
                    @if(isset($noteType))
                        Update Note Type :: {{ $noteType->name }}
                    @else
                        Create New Note Type
                    @endif
                </h3>
            </div><!-- /.box-header -->
            <div class="box-body">

                @if(isset($noteType))
                    {!! Form::model($noteType, ['route' => ['adm.mship.note.type.update.post', $noteType->id]]) !!}
                @else
                    {!! Form::open(["route" => "adm.mship.note.type.create.post"]) !!}
                @endif

                <div class="form-group">
                    {!! Form::label("name", "Name") !!}
                    {!! Form::text("name", null, ["class" => "form-control"]) !!}
                </div>

                <div class="form-group">
                    {!! Form::label("short_code", "Short Code") !!}
                    {!! Form::text("short_code", null, ["class" => "form-control"]) !!}
                </div>


                <div class="form-group">
                    {!! Form::label("colour_code", "Colour Code") !!}
                    {!! Form::select("colour_code", $colourCodes, "primary", ["class" => "form-control"]) !!}
                </div>


                    <div class="form-group">
                        {!! Form::label("is_available", "Available?") !!}

                        <div class="radio">
                            <label>
                                {!! Form::radio("is_available", 1, (isset($noteType) && $noteType->is_available)) !!}
                                YES - <span class="help-inline warning">Choosing this will disable users from using it.</span>
                            </label>
                        </div>

                        <div class="radio">
                            <label>
                                {!! Form::radio("is_available", 0, ((isset($noteType) && !$noteType->is_available)) OR !isset($noteType)) !!}
                                NO
                            </label>
                        </div>
                    </div>

                @if($_account->hasPermission("adm/mship/note/type/default"))
                    <div class="form-group">
                        {!! Form::label("is_default", "Default?") !!}

                        <div class="radio">
                            <label>
                                {!! Form::radio("is_default", 1, (isset($noteType) && $noteType->is_default)) !!}
                                YES - <span class="help-inline warning">Choosing this will disable the current default note type.</span>
                            </label>
                        </div>

                        <div class="radio">
                            <label>
                                {!! Form::radio("is_default", 0, ((isset($noteType) && !$noteType->is_default)) OR !isset($noteType)) !!}
                                NO
                            </label>
                        </div>
                    </div>
                @endif

                <div class="btn-toolbar">
                    <div class="btn-group pull-right">
                        {!! Form::submit((isset($noteType) ? "Update" : "Create")." Type", ["class" => "btn btn-primary"]) !!}
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