@extends('emails.messages.post')

@section('body')
    <p>
        {{ $cancelledByMentor->name }} ({{ $cancelledByMentor->id }}) has cancelled your mentoring session. Details are shown below.
    </p>

    <h4>Position</h4>
    <p>{{ $session->position }}</p>

    <h4>Session date</h4>
    <p>
        {{ \Carbon\Carbon::parse($session->taken_date)->format('l jS M y') }}<br>
        {{ \Carbon\Carbon::parse($session->taken_from)->format('H:i') }}Z &ndash;
        {{ \Carbon\Carbon::parse($session->taken_to)->format('H:i') }}Z
    </p>

    <h4>Reason for cancellation</h4>
    <p>{{ $reason }}</p>

    <p>
        Your mentoring request will remain in the system so it may be picked up by another mentor.
    </p>
@stop

@section('signature')
    VATSIM UK Training Department
@stop
