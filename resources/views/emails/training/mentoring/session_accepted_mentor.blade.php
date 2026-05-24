@extends('emails.messages.post')

@section('body')
    <p>
        You have accepted a mentoring session.
    </p>

    <h4>Session Details:</h4>
    <ul>
        <li><strong>Student:</strong> {{ $studentName }} ({{ $studentCid }})</li>
        <li><strong>Position:</strong> {{ $position }}</li>
        <li><strong>Scheduled Date & Time:</strong> {{ $sessionDateTime }}</li>
    </ul>
@stop

@section('signature')
    VATSIM UK Training Department
@stop
