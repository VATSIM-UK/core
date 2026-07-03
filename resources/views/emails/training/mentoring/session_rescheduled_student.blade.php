@extends('emails.messages.post')

@section('body')
    <p>{{ $mentorName }} has <strong>rescheduled</strong> your mentoring session. The session will now take place as follows:</p>

    <ul>
        <li><strong>Position</strong>: {{ $position }}</li>
        <li><strong>New date</strong>: {{ $sessionDateTime }}</li>
    </ul>

    <p>Please note that the times displayed are in Zulu/GMT.</p>

    <p>Please try to be punctual and ensure that you are well prepared for the session. If you need to cancel, please do so in plenty of time. Sessions can be cancelled via Manage Sessions.</p>

    <p>To stop receiving these alerts, you can amend your email settings in the <a href="{{ route('filament.training.pages.email-settings') }}">Training Panel</a>.</p>
@stop

@section('signature')
    VATSIM UK Training Department
@stop
