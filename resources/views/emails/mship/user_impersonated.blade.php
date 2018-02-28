@extends('emails.messages.post')

@section('body')

    <p>User {{ $impersonator->name }} ({{ $impersonator->id }}) has just impersonated {{ $target->name }} ({{ $target->id }}).</p>

    <p><span style="font-weight: bold;">The user provided the following reason:</span><br>{{ $reason }}</p>

@stop
