@extends('layout')

@section('content')
    <div class="col-md-8 col-md-offset-2">
        {!! HTML::panelOpen("Single Sign-On") !!}

            <div class="row">
                <div class="col-md-12">
                    <p>You are currently <strong>not</strong> logged in to our Core system.  VATSIM.net now have their own Single-Sign On system for use by divisions so you <strong>will not</strong> be presented with the standard VATSIM UK login page.</p>
                    {!! Form::open(['route' => 'login', 'class' => 'form-horizontal']) !!}
                    <p style="text-align: center;"><button type="submit" class="btn btn-lg btn-primary">Login</button></p>
                    {!! Form::close() !!}
                </div>
            </div>

        {!! HTML::panelClose() !!}
    </div>
@stop
