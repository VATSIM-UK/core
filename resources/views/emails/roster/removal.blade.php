@extends('emails.messages.post')

@section('body')

<p>You have been removed from VATSIM UK's controlling roster. This is likely because you do not meet our activity requirements.</p>

<p><strong>This means you are not currently allowed to control on VATSIM UK positions.</strong></p>

<p>You can renew your currency by following the instructions on our website: <a href="{{ route('site.roster.renew') }}">{{ route('site.roster.renew') }}</a>.</p>

@stop
