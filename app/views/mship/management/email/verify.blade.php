@extends('layout')

@section('breadcrumb')

@stop

@section('content')
<a href='{{ URL::route("mship.manage.dashboard") }}'>Click here to return to your account dashboard.</a>
@stop
