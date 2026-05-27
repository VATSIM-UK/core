@extends('emails.messages.post')

@section('body')
    <p>
        A student has been marked as a no-show for a mentoring session in your training group.
    </p>

    <h4>Session Details:</h4>
    <ul>
        <li><strong>Student:</strong> {{ $session->student?->name ?? 'Unknown student' }}
            ({{ $session->student?->cid ?? 'Unknown' }})</li>
        <li><strong>Position:</strong> {{ $session->position }}</li>
        <li><strong>Date &amp; Time:</strong> {{ \Carbon\Carbon::parse($session->taken_date)->format('d/m/Y') }}
            {{ \Carbon\Carbon::parse($session->taken_from)->format('H:i') }} &ndash;
            {{ \Carbon\Carbon::parse($session->taken_to)->format('H:i') }}</li>
    </ul>
@stop

@section('signature')
    VATSIM UK Training Department
@stop
