@extends('adm.layout')

@section('content')
    @include('adm.sys.activity._stream', array('activities' => $activities))
@stop