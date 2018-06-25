@extends('emails.messages.post')

@section('body')
<p>
    There has been a change in circumstances with {{ $application->account->name }}'s {{ $application->type_string }} application to VATSIM United Kingdom, or you have decided to cancel your reference for them.
</p>

<p>
    As such, your reference is no longer required.  We thank you for your support on this matter.
</p>

@stop