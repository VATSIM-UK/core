@extends("visit-transfer.emails.applicant._layout")

@section('email-content')
    <p>
        Your reference from {{ $reference->account->name }} has been accepted.  This means that the content of this reference
        <strong>will</strong> count towards your {{ $application->type_string }} application to VATSIM UK.
    </p>

    <p>
        You are not required to do anything at this stage.
    </p>
@stop