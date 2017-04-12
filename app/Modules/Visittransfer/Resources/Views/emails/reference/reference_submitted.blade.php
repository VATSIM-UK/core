@extends('emails.messages.post')

@section('body')
<p>
    Thank you for taking the time to submit a reference for {{ $application->account->name }}'s {{ $application->type_string }} application to VATSIM United Kingdom.
</p>

<p>
    We have notified {{ $application->account->name }} that you have completed their reference but <strong>have not</strong> shared the contents
    of your reference with them.
</p>

<p>
    Your reference will now be reviewed by our Community Department.  You will not be informed of the outcome.
</p>
@stop