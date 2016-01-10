<p>
    Your account ban has been modified.  Details of the ban and the timeframe are included below.
</p>

<h3>Details</h3>
<p>
    {!! nl2br($ban->reason ) !!}
    @if($ban->reason_extra)
        <br />
        {!! nl2br($ban->reason_extra) !!}
    @endif
</p>

<p>
    Start: {{ $ban->period_start->format("l jS \\of F Y H:i:s\\z") }}
    Finish: {{ $ban->period_finish->format("l jS \\of F Y H:i:s\\z") }}
</p>

<p>
    Your account will be automatically unbanned at {{ $ban->period_finish->format("l jS \\of F Y H:i:s \\z") }}.  Please do not attempt to connect to any VATSIM UK service whilst your account
    is banned.
</p>

@if($ban->is_local)
    <p>
        <strong>This ban only applies to VATSIM UK services.  You will be notified separately if you are also banned from network services.</strong>
    </p>
@endif

<p>
    If you believe this change has been made in error, please contact the community department {!! link_to("http://helpdesk.vatsim-uk.co.uk", "our helpdesk") !!}.
</p>