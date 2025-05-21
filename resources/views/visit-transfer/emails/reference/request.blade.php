@extends('emails.messages.post')

@section('body')
<p>
    {{ $application->account->name }} has named you as one of their referees for their {{ $application->type_string }} application to VATSIM United Kingdom.
</p>

<p>
    In order for their application to proceed you must click the URL below and complete the application. If you do not know the applicant, you are able to state so at the following link. This request will expire
    in 14 days and {{ $application->account->name }}'s application will be automatically rejected.
</p>

<p>
    You can complete the reference by <a href="{{ route('visiting.reference.complete', [$token->code]) }}">visiting the VT website</a>.
    Alternatively, copy the link below into your browser:</p>

<p>
    {!! route("visiting.reference.complete", ["token" => $token->code]) !!}
</p>
@stop
