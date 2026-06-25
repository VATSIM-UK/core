@extends('emails.messages.post')

@section('body')
    <p>You have cancelled your mentoring session, which was due to take place on {{ $session->position }} on {{ \Carbon\Carbon::parse($session->taken_date)->format('l jS M y') }} at {{ \Carbon\Carbon::parse($session->taken_from)->format('H:i') }}Z.</p>

    <p>To stop receiving these alerts, you can amend your email settings in the <a href="{{ route('filament.training.pages.email-settings') }}">Training Panel</a>.</p>
@stop

@section('signature')
    VATSIM UK Training Department
@stop
