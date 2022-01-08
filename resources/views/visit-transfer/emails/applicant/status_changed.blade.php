@extends("visit-transfer.emails.applicant._layout")

@section('email-content')
<p>
    Your {{ $application->type_string }} {{ $application->facility ? "(" . $application->facility_name. ")" : "" }} application to VATSIM UK has changed status to '{{ $application->status_string }}'.
</p>

@if($application->is_submitted)
@if($application->references_required > 0)
<p>
    We will now contact your references and request that they complete a reference for you within 14 days. If the details you have provided are invalid, unacceptable,
    or your referees fail to complete their reference within the given time frame, your application will be automatically rejected. Following completion of these, your
    application will undergo a series of automated checks to ensure you are compliant with the Visiting &amp; Transferring policy.
</p>
@else
<p>
    Your application will now undergo a series of automated checks to ensure you are compliant with the Visiting &amp; Transferring policy.
</p>
@endif
@elseif($application->is_under_review)
<p>
    Your application will now be reviewed by a member of the Community Department. You do not need to do anything further at this stage.
</p>
@elseif($application->is_accepted)
<p>
    Your application has been accepted by the community department. It is important to note that <strong>this does not</strong> mean you
    have completed your application, it simply means the details of your application have been checked and deemed valid.
    You will be informed when your application is <strong>completed</strong>.
</p>
@if($application->training_required)
<p>
    In order for your application to be deemed completed, you will be required to undergo training. The training department will contact you
    to outline the bespoke training plan we will put together for you.
</p>
<p>
    To remain compliant with the Visiting &amp; Transferring policy, you must engage with training <strong>within</strong> 90 days of the date of application.
    As such, if you <strong>do not</strong> engage with training before {{ $application->submitted_at->addDays(90)->toDateString() }} then your
    application will lapse and your {{ $application->type_string }} request denied.
</p>
@endif
@elseif($application->is_rejected)
<p>
    Your application to {{ $application->type_string }} {{ $application->facility->name }} has been rejected. Your application
    will not progress any further.
</p>
<p>
    The reason provided for this is as follows:<br />
    {!! nl2br($application->status_note) !!}
</p>
<p>
    We would ask that you do not open a new application until the above points have been addressed.
</p>
@elseif($application->is_completed)
<p>
    Congratulations, your {{ $application->type_string }} application for {{ $application->facility_name }} has been completed successfully.

    @if($application->training_required)
    The {{ strtoupper($application->facility->training_team) }} training team have notified us that you have satisfied their requirements.
    @endif

    No additional training is required.
</p>
@if($application->is_transfer)
<p>
    You will receive notification from VATSIM.net regarding your region/division change in addition to this confirmation email.
</p>
@elseif($application->facility->training_team == "atc")
<p>
    Please remember that as a visitor to our division, you can only conduct 49% of your ATC activities within the UK. Should you exceed this, then your
    visiting status will be revoked.
</p>
@endif
@elseif($application->is_cancelled)
<p>
    The reason provided for this is as follows:<br />
    {!! nl2br($application->status_note) !!}
</p>
@elseif($application->is_withdrawn)
<p>
    Your application will not be processed any further and no further action will be taken. Your referee(s) have been notified that their reference is no longer required.
</p>
@endif
@stop