@extends('emails.default')

@section('content')
    <p>
        Dear {!! $recipient->name !!},
    </p>

    {!! $body !!}
@stop