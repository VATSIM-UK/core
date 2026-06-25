@extends('emails.messages.post')

@section('body')
    <p>{{ $mentorName }} has accepted your mentoring session request. The details are as follows:</p>

    <ul>
        <li><strong>Position</strong>: {{ $position }}</li>
        <li><strong>Date/Time</strong>: {{ $sessionDateTime }}</li>
        <li><strong>Mentor Name</strong>: {{ $mentorName }}</li>
    </ul>

    <p>Please note that all times used are in Zulu/GMT.</p>

    <p>In preparation for your session, you should:</p>
    <ul>
        <li>Refer to any previous mentoring reports and ensure that any actions required by the mentoring team have been carried out.</li>
        <li>Ensure that your software is up-to-date.</li>
        <li>Try to ensure that you will be uninterrupted for the duration of your mentoring session.</li>
        <li>Have any required reference material available to you before the session begins.</li>
        <li>Consider what you want to achieve from the session - are there particular areas that you want to focus on?</li>
    </ul>

    <p>When you arrive for your mentoring session, please:</p>
    <ul>
        <li>Try to be punctual - late sessions impact you, adjacent mentoring/bookings and your mentor.</li>
        <li>Ensure that you are fit and well to perform the session; if you are not, please inform your mentor <strong>in advance</strong>.</li>
        <li>Join Teamspeak in good time and wait for your mentor to start the session.</li>
    </ul>

    <p>If you need to cancel, please do so in plenty of time. Sessions can be cancelled via Manage Sessions. You should also ensure that your ongoing availability is up-to-date for future mentoring sessions.</p>

    <p>To stop receiving these alerts, you can amend your email settings in the <a href="{{ route('filament.training.pages.email-settings') }}">Training Panel</a>.</p>
@stop

@section('signature')
    VATSIM UK Training Department
@stop
