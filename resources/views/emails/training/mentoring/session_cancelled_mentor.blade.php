@extends('emails.messages.post')

@section('body')
    <p>Dear {{ $recipient->name }},</p>

    <p>You have cancelled your mentoring session, which was due to take place on {{ $session->position }} on {{ \Carbon\Carbon::parse($session->taken_date)->format('l jS M y') }} at {{ \Carbon\Carbon::parse($session->taken_from)->format('H:i') }}Z.</p>

    @if(!empty($reason))
        <p>The reason you provided:</p>
        <p>{{ $reason }}</p>
    @endif

    <p>To stop receiving these alerts, you can amend your email settings in the STUDENT menu.</p>
@stop

@section('signature')
    VATSIM UK Training Department
@stop
