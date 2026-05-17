@extends('emails.messages.post')

@section('body')
<p>
{{ $examBooking->student?->account?->name ?? 'Unknown' }} ({{ $examBooking->student?->account?->id ?? 'Unknown' }}) has cancelled their {{ $examBooking->exam }} practical exam. The details are shown below.
</p>

<h4>Exam Details:</h4>
<ul>
    <li><strong>Exam Type:</strong> {{ $examBooking->exam }}</li>
    <li><strong>Position:</strong> {{ $examBooking->position_1 }}</li>
    <li><strong>Scheduled Date:</strong> {{ \Carbon\Carbon::parse($examBooking->taken_date)->format('l jS M Y') }}</li>
    <li><strong>Scheduled Time:</strong> {{ \Carbon\Carbon::parse($examBooking->taken_from)->format('H:i') }}Z &ndash; {{ \Carbon\Carbon::parse($examBooking->taken_to)->format('H:i') }}Z</li>
</ul>

<h4>Reason for cancellation:</h4>
<p>{{ $reason }}</p>
@stop

@section('signature')
VATSIM UK Training Department
@stop
