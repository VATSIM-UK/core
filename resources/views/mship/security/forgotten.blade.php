@extends('layout')

@section('breadcrumb')

@stop

@section('content')
    <div class="col-md-8 col-md-2 text-center">
        <a href='{{ URL::route("mship.manage.landing") }}'>Click here to return to the login page.</a>
    </div>
@stop