@extends('emails.default')

@section('content')
    <p>
        Dear {!! $recipientName ? $recipientName : $recipient->name !!},
    </p>

    {!! $body !!}
@stop