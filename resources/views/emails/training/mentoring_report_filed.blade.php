@extends('emails.messages.post')

@section('body')
    <p>
        This is a notification that your mentor has finished your report.<br>
        Position: {{ $session->position }}<br>
        Session date: {{ $session->taken_date }} {{ $session->taken_from }} - {{ $session->taken_to }}
    </p>
@stop

@section('signature')
    VATSIM UK Training Department
@stop
