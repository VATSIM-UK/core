@extends('emails.messages.post')

@section('body')
<p>
Your {{ $examType }} practical exam has been scheduled.
</p>

<h4>Exam Details:</h4>
<ul>
    <li><strong>Position:</strong> {{ $position }}</li>
    <li><strong>Primary Examiner:</strong> {{ $primaryExaminer }}</li>
    <li><strong>Scheduled Date & Time:</strong> {{ $examDateTime }}</li>
</ul>

@if($examBooking->examiners && $examBooking->examiners->secondaryExaminer)
<p>
<strong>Secondary Examiner:</strong> {{ $examBooking->examiners->secondaryExaminer->account->name ?? 'TBD' }}
</p>
@endif

@stop
