@extends('emails.messages.post')

@section('body')

    <p>On {{ $last_check_sent_at }}, we asked you to indicate that you were still interested in receiving ATC Training
        within VATSIM UK. Because you have not replied, effective from {{ $removal_date }}, you have been removed from the
        {{ $waiting_list_name }} waiting list, due to failing to engage in a quarterly member checks conducted via email.
    </p>

    <p>If you believe this to be in error, then please get in contact with the VATSIM UK ATC Training team via the helpdesk:
        <a href="https://helpdesk.vatsim.uk">https://helpdesk.vatsim.uk</a>. Alternatively, you can self-enrol for training
        <a href="https://www.vatsim.uk/mship/waiting-lists">here.</a></p>

@stop
