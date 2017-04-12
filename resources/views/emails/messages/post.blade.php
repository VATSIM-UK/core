@extends('emails.default')

@section('content')
    <p>
        Dear {!! isset($recipientName) ? $recipientName : $recipient->name !!},
    </p>

    @yield('body')
@stop
