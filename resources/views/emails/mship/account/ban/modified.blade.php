@extends('emails.messages.post')

@section('body')
<p>
    Your current suspension of access to all of the VATSIM United Kingdom services (Forum/TeamSpeak/CT System) has been changed to a total length of {{ $ban_total_length }} (including time served).
</p>

<p>
    {!! $ban->reason  !!}<br />
    {!! nl2br($ban->reason->reason_text) !!}
    @if($ban->reason_extra)
        <br />
        {!! nl2br($ban->reason_extra) !!}
    @endif
</p>

<p>
    Your account will automatically regain access to all of the VATSIM United Kingdom services at {{ $ban->period_finish->format("l jS \\of F Y H:i:s \\z") }}.
    Please do not attempt to access any services before this time.
</p>

@if($ban->is_local)
    <p>
        <strong>This ban only applies to VATSIM UK services.  You will be notified separately if you are also banned from network services.</strong>
    </p>
@endif
@stop