@extends('emails.messages.post')

@section('body')

@yield("email-content")

<p>
    You will be notified of any future updates or changes to your application as it progresses.
    You can view your application at any point {!! link_to(route("visiting.application.view", [$application->public_id]), "from the VT website") !!}.
    Alternatively, copy the link below into your browser:</p>

<p>
    {!! route("visiting.application.view", [$application->public_id]) !!}
</p>

@stop