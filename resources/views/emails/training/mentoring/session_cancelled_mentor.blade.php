@extends('emails.messages.post')

@section('body')
    <p>
        You have cancelled your mentoring session with {{ $session->student?->account?->name ?? 'Unknown' }} ({{ $session->student?->cid ?? 'Unknown' }}). The details are shown below.
    </p>

    <h4>Session Details:</h4>
    <ul>
        <li><strong>Position:</strong> {{ $session->position }}</li>
        <li><strong>Scheduled Date:</strong> {{ \Carbon\Carbon::parse($session->taken_date)->format('l jS M Y') }}</li>
        <li><strong>Scheduled Time:</strong> {{ \Carbon\Carbon::parse($session->taken_from)->format('H:i') }}Z &ndash;
            {{ \Carbon\Carbon::parse($session->taken_to)->format('H:i') }}Z</li>
    </ul>

    <h4>Reason for cancellation:</h4>
    <p>{{ $reason }}</p>
@stop

@section('signature')
    VATSIM UK Training Department
@stop
