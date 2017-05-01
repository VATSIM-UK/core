@extends('layout')

@section('content')
    <div class="panel panel-ukblue">
        <div class="panel-heading"> Secondary Password</div>
        <div class="panel-body">
            <p>
                To change your secondary password, complete the form below.
            </p>

            <div class="row">
                <div class="col-md-7 col-md-offset-2">
                    {!! Form::open(["route" => "password.change", "class" => "form-horizontal"]) !!}
                    @include('auth.passwords.partials._old')
                    @include('auth.passwords.partials._new')
                    @include('auth.passwords.partials._submit')
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@stop
