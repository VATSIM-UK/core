@extends('adm.layout')

@section('content')
    @include('adm.sys.timeline.widget', array('entries' => $entries))
@stop