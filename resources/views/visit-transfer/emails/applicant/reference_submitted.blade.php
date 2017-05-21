@extends("visit-transfer.emails.applicant._layout")

@section('email-content')
    <p>
        Your reference from {{ $reference->account->name }} has been submitted.
    </p>

    @if($application->is_pending_references)
        <p>
            There are still {{ $application->references_not_written->count() }} references on your application that have not been submitted.
            Your application will <strong>not</strong> be reviewed until all references have been submitted.
        </p>
    @else
        <p>
            As you have no more pending references, your application will undergo some automated checks to ensure that you are
            compliant with the Visiting &amp; Transferring policy.
        </p>
    @endif
@stop