@extends("visit-transfer.emails.applicant._layout")

@section('email-content')
    <p>
        Your reference from {{ $reference->account->name }} has been rejected.  This means that the content of this reference
        <strong>will not</strong> count towards your {{ $application->type_string }} application to VATSIM UK.
    </p>

    <p>
        The following reason was provided for this rejection:<br />
        {!! nl2br($reference->status_note) !!}
    </p>

    <p>
        You are not required to do anything at this stage.
    </p>
@stop