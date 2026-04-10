@extends('emails.messages.post')

@section('body')
<p>
{{ $studentName }} ({{ $studentCid }}) has cancelled their {{ $examType }} practical exam. The details are shown below.
</p>

<h4>Exam Details:</h4>
<ul>
    <li><strong>Exam Type:</strong> {{ $examType }}</li>
    <li><strong>Position:</strong> {{ $position }}</li>
    <li><strong>Scheduled Date:</strong> {{ \Carbon\Carbon::parse($takenDate)->format('l jS M Y') }}</li>
    <li><strong>Scheduled Time:</strong> {{ \Carbon\Carbon::parse($takenFrom)->format('H:i') }}Z &ndash; {{ \Carbon\Carbon::parse($takenTo)->format('H:i') }}Z</li>
</ul>

<h4>Reason for cancellation:</h4>
<p>{{ $reason }}</p>
@stop

@section('signature')
VATSIM UK Training Department
@stop