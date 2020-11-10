@extends('layout')

@section('content')
    <div class="col-md-8 col-md-offset-2">
        {!! HTML::panelOpen("Single Sign-On") !!}
            <div class="row">
                <div class="col-md-12" style="text-align: center;">
                    <p>You are currently <strong>not</strong> logged in to our Core system.<br>We use VATSIM.net's Single-Sign On system
                        to log you in.
                        After clicking the button below, you will be taken there to login.
                    </p>
                    {!! Form::open(['route' => 'login', 'class' => 'form-horizontal']) !!}
                    <p style="text-align: center;"><button type="submit" class="btn btn-lg btn-primary">Login</button></p>
                    {!! Form::close() !!}
                </div>
            </div>
        {!! HTML::panelClose() !!}
    </div>
@stop
