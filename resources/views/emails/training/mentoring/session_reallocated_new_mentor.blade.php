@extends('emails.messages.post')

@section('body')
    <p>A Training Administrator has re-allocated a mentoring session to you from another mentor. The details of your session are as follows:</p>

    <ul>
        <li><strong>Position</strong>: {{ $position }}</li>
        <li><strong>New date</strong>: {{ $sessionDateTime }}</li>
        <li><strong>Student Name</strong>: {{ $studentName }}</li>
    </ul>

    <p>Please note that the times displayed are in Zulu/GMT.</p>

    <p>The following reason was specified:</p>
    <p>{{ $reason }}</p>

    <p>To stop receiving these alerts, you can amend your email settings in the <a href="{{ route('filament.training.pages.email-settings') }}">Training Panel</a>.</p>
@stop

@section('signature')
    VATSIM UK Training Department
@stop
