@extends('emails.messages.post')

@section('body')
    <p>Please could you check that you have both a session request and up-to-date availability entered into the CT System? Without both, mentors will be unable to accept your mentoring sessions.</p>

    <p>To be fair to those students who are still waiting for training, if after three of these reminders, or after five days of this email, you have no session request/availability in the CT System, we will have to re-assign your training place to the next person on the waiting list.</p>

    <p>To continue your training, please submit your availability in CT System as soon as possible.
        You have <strong>{{ $days_to_expire }}</strong>
        (until <strong>{{ $expires_at->format('d/m/Y H:i') }} Zulu</strong>) to submit your availability.</p>

    <p>To submit your availability, please visit the Central Training System: <a href="https://cts.vatsim.uk">https://cts.vatsim.uk</a></p>

    <p>
        If you don’t have the time/don’t wish to continue with your training for the time being, please let us know by opening a ticket with the ATC Training team via the helpdesk:
        <a href="https://helpdesk.vatsim.uk">https://helpdesk.vatsim.uk</a>
    </p>
@stop
