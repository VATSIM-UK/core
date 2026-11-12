@extends('emails.messages.post')

@section('body')
    <p>
        We have locked two-factor authentication on your VATSIM UK account for the next 15 minutes
        after five failed attempts to complete the challenge.
    </p>

    <p>
        If this was not you, somebody else may know your password. We recommend changing it and
        contacting us immediately.
    </p>
@stop
