@extends('emails.messages.post')

@section('body')

    <p>In order to maintain your place on the {{ $waiting_list_name }} waiting list. You must confirm your spot by clicking
        the link below. </p>

    <p><a href="{{ $retention_check_url }}">Confirm my place on the waiting list</a></p>

@stop
