@extends('emails.messages.post')

@section('body')
    <p>Dear {{ $recipient->name }},</p>

    <p>Confirmation of your re-scheduled mentoring session is as follows:</p>

    <ul>
        <li><strong>Position</strong>: {{ $position }}</li>
        <li><strong>New date</strong>: {{ $sessionDateTime }}</li>
    </ul>

    <p>Please note that the times displayed are in Zulu/GMT.</p>

    <p>To stop receiving these alerts, you can amend your email settings in the STUDENT menu.</p>
@stop

@section('signature')
    VATSIM UK Training Department
@stop
