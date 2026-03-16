@extends('emails.messages.post')

@section('body')
<p>
    A new {{ $application->type_string }} application has been submitted for your review.  Details are included below:
</p>

<h3>Application Details</h3>

<p>
    <strong>ID:</strong> {{ $application->public_id }}<br />
    <strong>Name:</strong> {{ $application->account->name }} ({{ $application->account_id }})<br />
    <strong>Facility:</strong> {{ $application->facility->name }}<br />
    <strong>Statement:</strong> {{ $application->statement_required ? $application->statement : "None required for this facility" }}
</p>

<p>
    Please login to the administrative panel to review this application, along with many others.
</p>
@stop