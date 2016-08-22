<p>
    A new {{ $application->type_string }} application has been submitted for your review.  Details are included below:
</p>

<h3>Application Details</h3>

<p>
    <strong>ID:</strong> {{ $application->public_id }}<br />
    <strong>Name:</strong> {{ $application->account->name }} ({{ $application->account_id }})<br />
    <strong>Facility:</strong> {{ $application->facility->name }}<br />
    <strong>Statement:</strong> {{ $application->statement }}
</p>

<h3>References</h3>

@foreach($application->referees as $reference)
    <p>
        <strong>Name:</strong> {{ $reference->account->name }} ({{ $reference->account_id }})<br />
        <strong>Relationship:</strong> {{ $reference->relationship }}<br />
        <strong>Reference:</strong> {{ $reference->reference }}
    </p>
@endforeach

<p>
    Please login to the administrative panel to review this application, along with many others.
</p>