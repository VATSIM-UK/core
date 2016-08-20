<p>
    Your {{ $application->type_string }} application to VATSIM United Kingdom has been submitted.
</p>

@if($application->facility->stage_reference_enabled)
    <p>
        We will now contact your references and request that they complete a reference for you within 14 days.  If the details you have provided are invalid, unacceptable,
        or your referees fail to complete their reference within the given time frame, your application will be automatically rejected.
    </p>
@else
    <p>
        Your application will now be reviewed by a member of the Community Department and you will be informed of any updates.
    </p>
@endif

<p>
    You can view your application at any point {!! link_to(route("visiting.application.view", [$application->public_id]), "from the VT website") !!}.
    Alternatively, copy the link below into your browser:</p>

<p>
    {!! route("visiting.application.view", [$application->public_id]) !!}
</p>