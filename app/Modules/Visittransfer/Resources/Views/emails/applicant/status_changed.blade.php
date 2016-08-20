@extends("visittransfer::emails.applicant._layout")

@section('email-content')
    <p>
        Your {{ $application->type_string }} application to VATSIM UK has changed status to '{{ $application->status_string }}'.
    </p>

    @if($application->status == \App\Modules\Visittransfer\Models\Application::STATUS_SUBMITTED)
        @if($application->facility->stage_reference_enabled)
            <p>
                We will now contact your references and request that they complete a reference for you within 14 days.  If the details you have provided are invalid, unacceptable,
                or your referees fail to complete their reference within the given time frame, your application will be automatically rejected.  Following completion of these, your
                application will undergo a series of automated checks to ensure you are compliant with the Visiting &amp; Transferring policy.
            </p>
        @else
            <p>
                Your application will now undergo a series of automated checks to ensure you are compliant with the Visiting &amp; Transferring policy.
            </p>
        @endif
    @elseif($application->status == \App\Modules\Visittransfer\Models\Application::STATUS_UNDER_REVIEW)
        <p>
            Your application will now be reviewed by a member of the Community Department.  You do not need to do anything further at this stage.
        </p>
    @endif
@stop