@extends('emails.messages.post')

@section('body')
<p>
    This email address has been added as a secondary email address for our web services.
</p>

<p>
    Before being able to use this address and receive notifications from our systems, you are required to verify it.
</p>

<p>
    To verify this account, please {!! link_to_route("mship.manage.email.verify", "click here", ["code" => $token->code]) !!}.  Alternatively, copy the link below into your browser:
</p>

<p>
    {{ route("mship.manage.email.verify", ["code" => $token->code]) }}
</p>
@stop
