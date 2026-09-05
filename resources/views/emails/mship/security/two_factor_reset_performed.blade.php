@extends('emails.messages.post')

@section('body')
    <p>
        {{ $administrator->name }} ({{ $administrator->id }}) has reset two-factor authentication for
        {{ $target->name }} ({{ $target->id }}).
    </p>

    <p><span style="font-weight: bold;">The administrator provided the following reason:</span><br>{{ $reason }}</p>
@stop
