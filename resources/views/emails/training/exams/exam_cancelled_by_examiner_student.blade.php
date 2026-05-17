@extends('emails.messages.post')

@section('body')
<p>
{{ $cancelledByExaminer->name }} ({{ $cancelledByExaminer->id }}) has cancelled your {{ $examBooking->exam }} practical exam. Details are shown below.
</p>

<h4>Position</h4>
<p>{{ $examBooking->position_1 }}</p>

<h4>Exam date</h4>
<p>
{{ \Carbon\Carbon::parse($examBooking->taken_date)->format('l jS M y') }}<br>
{{ \Carbon\Carbon::parse($examBooking->taken_from)->format('H:i') }}Z &ndash; {{ \Carbon\Carbon::parse($examBooking->taken_to)->format('H:i') }}Z
</p>

<p>
However, your exam request will remain in the system so it may be picked up by another examiner.
</p>
@stop

@section('signature')
VATSIM UK Training Department
@stop
