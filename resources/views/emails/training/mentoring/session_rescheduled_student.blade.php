@extends('emails.messages.post')

@section('body')
    <p>
        Your mentoring session has been rescheduled by {{ $mentorName }}.
    </p>

    <h4>Position</h4>
    <p>{{ $position }}</p>

    <h4>Previous date & time</h4>
    <p>{{ $previousDateTime }}</p>

    <h4>New date & time</h4>
    <p>{{ $sessionDateTime }}</p>
@stop

@section('signature')
    VATSIM UK Training Department
@stop
