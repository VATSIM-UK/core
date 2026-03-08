@extends('emails.messages.post')

@section('body')

<p>We write to inform you that the previous offer for a training place on {{ $position->name }} ({{ $position->callsign }}) has been rescinded.
    <br>The reasons for this is: {{ $reason }}.</p>

<p>Owing to the fact that the offer has been rescinded rather than you being removed from the waiting list, your position
    on the waiting list will remain and you will be offered another training place at an appropriate time.
    We apologise for the inconvenience, but please rest assured that the team are working hard to ensure that training
    continues to run smoothly and we anticipate that the wait for you to be offered a training place will not be long. 
    If you have any questions in the meantime, you can contact the ATC Training Team via the <a href="https://helpdesk.vatsim.uk">helpdesk</a>.</p>

@stop