<p>
    Your reference from {{ $reference->account->name }} has been submitted.  It will now be reviewed by a member of our community team.
</p>

@if($application->is_pending_references)
    <p>
        There are still references on your application that have not been submitted.
        Your application will <strong>not</strong> be reviewed until all references have been submitted.
    </p>
@endif
<p>
    You can view your application at any point {!! link_to(route("visiting.application.view", [$application->public_id]), "from the VT website") !!}.
    Alternatively, copy the link below into your browser:</p>

<p>
    {!! route("visiting.application.view", [$application->public_id]) !!}
</p>