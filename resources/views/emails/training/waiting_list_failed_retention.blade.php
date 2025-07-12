@extends('emails.messages.post')

@section('body')

    <p>On {{ $removal_date }} you have been removed from {{ $waiting_list_name }} due to failing to engage in a quarterly
        retention checks conducted via email. Your last check was sent on {{ $last_check_sent_at }}.</p>

    <p>If you believe this to be in error, then please get in contact with the VATSIM UK ATC Training team via the helpdesk.
    </p>

@stop
