@extends('emails.messages.post')

@section('body')
    <p>{{ $cancelledByMentor->name }} has cancelled your mentoring session, which was due to take place on {{ $session->position }} on {{ \Carbon\Carbon::parse($session->taken_date)->format('l jS M y') }} at
    {{ \Carbon\Carbon::parse($session->taken_from)->format('H:i') }}Z.</p>

    @if(!empty($reason))
        <p>Your mentor left this message for you:</p>
        <p>{{ $reason }}</p>
    @endif

    <p>We apologise for any inconvenience caused. It is possible that another mentor will pick your mentoring session up, so you should ensure that your ongoing availability is up-to-date.</p>

    <p>To stop receiving these alerts, you can amend your email settings in the STUDENT menu.</p>
@stop

@section('signature')
    VATSIM UK Training Department
@stop
