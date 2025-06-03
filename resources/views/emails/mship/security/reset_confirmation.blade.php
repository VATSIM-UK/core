@extends('emails.messages.post')

@section('body')
<p>
    You, or somebody posing as you, has advised us that a new secondary password should be generated for your account.
</p>

<p>
    In order to verify this request, you need to click the link in this email.  This request will expire in 1 hour.
</p>

<p>To authorise this request, please <a href="{{ $token }}">click here</a>.  Alternatively, copy the link below into your browser:</p>

<p>
    {{ $token }}
</p>
@stop
