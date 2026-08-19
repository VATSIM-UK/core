@extends('emails.messages.post')

@section('body')
    <p>
        Somebody has just signed in to your VATSIM UK account using one of your two-factor
        recovery codes instead of your authenticator application.
    </p>

    <p>
        That code has now been used up and replaced with a new one, so if you keep a saved copy
        of your recovery codes it is now out of date. You can view and download your current
        codes from your account settings.
    </p>

    <p>
        <strong>If this was not you, contact us immediately</strong> - someone else may have
        access to your recovery codes.
    </p>
@stop
