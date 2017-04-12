@extends('emails.messages.post')

@section('body')
<p>
    Your access to all of the VATSIM United Kingdom services (Forum/TeamSpeak/CT System) has been reinstated with immediate effect.
</p>

<p>
    {!! $ban->reason  !!}<br />
    {!! nl2br($ban->reason->reason_text) !!}
    @if($ban->reason_extra)
        <br />
        {!! nl2br($ban->reason_extra) !!}
    @endif
</p>

@if($ban->is_local)
    <p>
        <strong>This ban only applies to VATSIM UK services.  You will be notified separately if any network bans were also lifted.</strong>
    </p>
@endif
@stop