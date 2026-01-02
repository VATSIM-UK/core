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

<p>A secondary examiner may be allocated either in support of complex exams or for training.
Please ensure that the secondary examiner is briefed before the conduct of the exam.
You should consider whether or not to ping for adjacent ATC or pilots to support the exam in the relevant Discord channel.
It is the responsibility of you as the primary examiner to arrange for these pings if required.
</p>

<p>You should brief the candidate at the beginning of the exam in accordance with the <a href="{{ __('atc.handbook.url') }}">ATC Training Handbook</a>.</p>

<p>Should the candidate fail to attend, please notify the relevant TGI. In the event of either failure or success, feedback on the exam should be summarised to the training group in question.</p>

@endsection

@section('signature')
VATSIM UK Training Department
@endsection
