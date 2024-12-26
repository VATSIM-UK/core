@extends('emails.messages.post')

@section('body')

<p>You have been removed from VATSIM UK's controlling roster. This is likely because you do not meet our activity requirements.</p>

<p><strong>This means you are not currently allowed to control on VATSIM UK positions.</strong></p>

<p>If you were on a waiting list for ATC Training, you have now been removed as it is a requirement to be active on our controlling roster to be eligible for ATC Training.</p>

<p>You can renew your currency by following the instructions on our website: <a href="{{ route('site.roster.renew') }}">{{ route('site.roster.renew') }}</a>.</p>

@stop
