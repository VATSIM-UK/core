@extends('emails.messages.post')

@section('body')

    <p>Owing to high demand, VATSIM UK periodically checks that members on our waiting lists are still engaged and willing
        to continue to wait for training. Should you fail to respond to this message within 7 days, you will be removed
        from the {{ $waiting_list_name }} waiting list.</p>

    <p>In order to maintain your place on the {{ $waiting_list_name }} waiting list, you must click the link below.
        Alternatively, if you have any questions, please raise a ticket for the attention of ATC Training at the VATSIM UK
        Helpdesk: <a href="https://helpdesk.vatsim.uk">https://helpdesk.vatsim.uk</a></p>

    <p><a href="{{ $retention_check_url }}">Confirm my place on the waiting list.</a></p>

@stop
