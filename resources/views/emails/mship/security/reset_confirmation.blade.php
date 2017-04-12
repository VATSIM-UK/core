@extends('emails.messages.post')

@section('body')
<p>
    You, or somebody posing as you, has advised us that a new secondary password should be generated for your account.
</p>

<p>
    In order to verify this request, you need to click the link in this email.  This request will expire in 12 hours.
</p>

<p>To authorise this request, please {!! link_to(route("mship.security.forgotten.link", ["code" => $token->code]), "click here") !!}.  Alternatively, copy the link below into your browser:</p>

<p>
    {{ route("mship.security.forgotten.link", ["code" => $token->code]) }}
</p>
@stop