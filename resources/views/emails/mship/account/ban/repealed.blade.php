<p>
    Your account ban has been repealed.  Details of the ban are included below for reference only.  You are now permitted to login to any VATSIM UK service once again.
</p>

<h3>Details</h3>
<p>
    {!! $ban->reason  !!}<br />
    {!! nl2br($ban->reason->reason_text) !!}
    @if($ban->reason_extra)
        <br />
        {!! nl2br($ban->reason_extra) !!}
    @endif
</p>

<p>
    Start: {{ $ban->period_start->format("l jS \\of F Y H:i:s\\z") }}<br />
    Finish: {{ $ban->period_finish->format("l jS \\of F Y H:i:s\\z") }}
</p>

@if($ban->is_local)
    <p>
        <strong>This ban only applies to VATSIM UK services.  You will be notified separately if any network bans were also lifted.</strong>
    </p>
@endif

<p>
    If you require further assistance, please contact the community department {!! link_to("http://helpdesk.vatsim-uk.co.uk", "our helpdesk") !!}.
</p>