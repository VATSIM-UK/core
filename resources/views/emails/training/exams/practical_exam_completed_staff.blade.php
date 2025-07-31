@extends('emails.messages.post')

@section('body')
<p>
The practical exam for {{$practicalResult->student->account->name}} ({{$practicalResult->student->account->id}}) has been completed.
The result of the exam was <strong>{{$practicalResult->resultHuman()}}</strong>.
</p>

<ul>
    <li>
        <strong>Examiner:</strong> {{$practicalResult->examBooking->examiners->primaryExaminer->account->name}} ({{$practicalResult->examBooking->examiners->primaryExaminer->account->id}})
    </li>
    @if($practicalResult->secondaryExaminer)
    <li>
        <strong>Secondary Examiner:</strong> {{$practicalResult->examBooking->examiners->secondaryExaminer->account->name}} ({{$practicalResult->examBooking->examiners->secondaryExaminer->account->id}})
    </li>
    @endif
    @if($practicalResult->traineeExaminer)
    <li>
        <strong>Trainee Examiner:</strong> {{$practicalResult->examBooking->examiners->traineeExaminer->account->name}} ({{$practicalResult->examBooking->examiners->traineeExaminer->account->id}})
    </li>
    @endif
    <li>
        <strong>Student:</strong> {{$practicalResult->student->account->name}} ({{$practicalResult->student->account->id}})
    </li>
    <li>
        <strong>Position:</strong> {{$practicalResult->examBooking->position_1}}
    </li>
    <li>
        <strong>Date:</strong> {{$practicalResult->examBooking->startDate}}
    </li>
</ul>
@stop
