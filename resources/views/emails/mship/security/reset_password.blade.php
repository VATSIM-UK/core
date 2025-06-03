@extends('emails.messages.post')

@section('body')
<p>
    You, or somebody posing as you, requested a new secondary password. Your new password is as follows.
</p>

<p>
    Password: {!! $password !!}
</p>

<p>
    When you next login to the VATSIM UK system, you will be required to change this password.
</p>

<p>
    <strong>If you did not request this password</strong> <a href="mailto:community@vatsim.uk">let us know</a>!
</p>
@stop
