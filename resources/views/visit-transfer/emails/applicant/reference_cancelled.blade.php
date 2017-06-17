@extends("visit-transfer.emails.applicant._layout")

@section('email-content')
    <p>
        Your reference from {{ $reference->account->name }} has been cancelled. This is most likely because they referee indicated that they did not know you.
    </p>

    <p>
        You application is now under review. You are not required to do anything at this stage.
    </p>
@stop
