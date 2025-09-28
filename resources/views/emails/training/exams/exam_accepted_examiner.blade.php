@extends('emails.messages.post')

@section('body')
<p>
You have been assigned as an examiner for a {{ $examType }} practical exam that has been accepted and scheduled.
</p>

<h4>Exam Details:</h4>
<ul>
    <li><strong>Student:</strong> {{ $studentName }} ({{ $studentCid }})</li>
    <li><strong>Exam Type:</strong> {{ $examType }}</li>
    <li><strong>Position:</strong> {{ $position }}</li>
    <li><strong>Scheduled Date & Time:</strong> {{ $examDateTime }}</li>
    <li><strong>Primary Examiner:</strong> {{ $primaryExaminer }}</li>
</ul>

@if($examBooking->examiners && $examBooking->examiners->secondaryExaminer)
<p>
<strong>Secondary Examiner:</strong> {{ $examBooking->examiners->secondaryExaminer->account->name ?? 'TBD' }}
</p>
@endif

@stop
