@extends('layout')

@section('content')
    <div class="panel panel-ukblue">
        <div class="panel-heading"> Secondary Password</div>
        <div class="panel-body">
            <p>To disable your secondary password, please enter your current password below.</p>

            <div class="row">
                <div class="col-md-7 col-md-offset-2">
                    {!! Form::open(['route' => 'password.delete', 'class' => 'form-horizontal']) !!}
                    @include('auth.passwords.partials._old')
                    @include('auth.passwords.partials._submit')
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@stop
