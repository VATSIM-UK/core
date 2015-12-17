@extends('emails.default')

@section('content')
    <p>
        Dear {!! $recipient->name_first !!},
    </p>

    {!! $body !!}
@stop