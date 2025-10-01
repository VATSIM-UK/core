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

<p>A secondary examiner may be appointed to assist in the conduct of the exam.
The primary examiner remains responsible for the conduct of the exam and will brief you if a secondary examiner attends.
</p>

<p>You should remember that this is an <strong>open book exam</strong>, both for the practical and theoretical elements of the exam.
You should feel confident with the knowledge, but you are permitted to look things up if you require.
</p>

<p>You should not seek assistance during the exam. Doing so may lead to the exam being cancelled and re-arranged.</p>

<p>Most importantly - good luck!</p>

@stop

@section('signature')
VATSIM UK Training Department
@stop
