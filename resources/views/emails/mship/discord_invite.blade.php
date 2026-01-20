@extends('emails.messages.post')

@section('body')

<p>Why not come and <a href="{{ route('mship.manage.dashboard') }}">join us on Discord</a>?</p>

<p>If you've never heard of <a href="https://www.discord.com/">Discord</a>, that's fine! Discord is a new, 21st century chat platform which allows groups of people with a mutual interest to communicate in real-time. If there's a burning question you want to ask about real world aviation, or a suggestion you've got for the division, or even a request for a flying buddy, there's bound to be someone around to support you.</p>

<p><strong>As of right now there are {{ $discordCount }} other VATSIM members sharing their love of aviation in our Discord server; and you could be too!</strong></p>

<p>Registration is really simple. Visit <a href="{{route('mship.manage.dashboard')}}">the membership management page on our website, where you can register your discord account</a> to get started, or visit {{ route('mship.manage.dashboard') }}</p>

@stop