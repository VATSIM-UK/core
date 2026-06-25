@extends('emails.messages.post')

@section('body')
    <p>{{ $session->student->name }} has cancelled your mentoring session, which was due to take place on {{ $session->position }} on {{ \Carbon\Carbon::parse($session->taken_date)->format('l jS M y') }} at {{ \Carbon\Carbon::parse($session->taken_from)->format('H:i') }}Z.</p>

    @if(!empty($reason))
        <p>Your student left this message for you:</p>
        <p>{{ $reason }}</p>
    @endif

    <p>We apologise for any inconvenience caused. If you are still available:</p>

    <ul>
        <li>Please consider picking up another session if there is sufficient availability in the system.</li>
        <li>If the session was due to take place within the next 24 hours, please solicit the availability of students in the training group's students channel in the VATSIM UK Discord.</li>
        <li>If no students are available, please try to support other mentoring by other controlling adjacent positions or flying.</li>
    </ul>

    <p>To stop receiving these alerts, you can amend your email settings in the <a href="{{ route('filament.training.pages.email-settings') }}">Training Panel</a>.</p>
@stop

@section('signature')
    VATSIM UK Training Department
@stop
