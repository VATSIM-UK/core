@extends('emails.messages.post')

@section('body')
    <p>
        Two-factor authentication on your VATSIM UK account has been reset by
        {{ $administrator->name }} ({{ $administrator->id }}) at the request of our helpdesk.
    </p>

    <p>
        Your previous authenticator application and all of your existing recovery codes have
        stopped working. The next time you sign in you will be asked to set up two-factor
        authentication again, and you will be given a fresh set of recovery codes.
    </p>

    <p>
        <strong>If you did not request this, contact us immediately</strong> - someone may have
        obtained access to your account.
    </p>
@stop
