@extends('adm.layout')

@section('content')
    @include('adm.system.timeline.widget', array('entries' => $entries))
@stop