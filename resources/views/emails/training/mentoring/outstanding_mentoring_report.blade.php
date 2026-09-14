@extends('emails.messages.post')

@section('body')
    You performed a mentoring session on {{ $sessionDate }} at {{ $sessionTime }}, but have yet to complete your mentoring report.
    <br><br>
    Please could we ask that you complete the report at your earliest convenience, or let the appropriate Training Group Instructor know if you cannot? Unsubmitted session reports impede other mentors from training students based on the strengths and weaknesses of their last session, so it's incredibly important that session reports are submitted promptly.
    <br><br>
    Many thanks,
@stop

@section('signature')
    The ATC Training Management Team
@stop
