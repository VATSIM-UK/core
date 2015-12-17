@extends('layout')

@section('breadcrumb')

@stop

@section('content')
<a href='{{ URL::route("mship.manage.landing") }}'>Click here to return to the login page.</a>
@stop