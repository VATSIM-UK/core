@extends('emails.messages.post')

@section('body')

<h2>👋 Welcome to VATSIM United Kingdom!</h2>
<p>
    We are glad to have you on board, and by picking VATSIM UK as your home division,
    you are now part of one of the busiest divisions on the network. Whether you are here to learn about Air Traffic Control,
    to improve your pilot skills or just to meet new people, you are sure to find something new.
</p>
<p>
    Now that your account has been created and we have received your details, you have access to a whole range of optional
    training courses, events and activities to make the most of your time on the network.
</p>


<h2>💬 Explore the community</h2>
<p>We have a thriving community, why not come and join us?</p>

<p><strong>Discord</strong> - Our Discord server provides an opportunity for instant communication with members through chat. Why not come and introduce yourself?</p>
<div style="margin-bottom: 2em;"><a href="{{route('discord.show')}}" class="btn btn-primary">Register for Discord</a></div>

<p><strong>TeamSpeak</strong> - We use TeamSpeak for voice communication. This includes coordination whilst controlling, one-to-one mentoring sessions, group seminars and general chat.</p>
<div style="margin-bottom: 2em;"><a href="{{route('site.community.teamspeak')}}" class="btn btn-primary">Join us on TeamSpeak</a></div>

<p><strong>Community Forum</strong> - Our community forum is a place for text discussion; express your interest in events, share screenshots of your latest flight and keep up to date on the latest news.</p>
<div style="margin-bottom: 2em;"><a href="https://community.vatsim.uk" class="btn btn-primary">Take a look around our Forum</a></div>

<p><strong>Division Website</strong> - Our website hosts all the information you might need whilst a member of the network. There's lot to digest, and we'll help you find what you need!</p>
<div style="margin-bottom: 0.5em;"><a href="{{route('site.home')}}" class="btn btn-primary">Visit our website</a></div>
<div style="margin-bottom: 2em;"><a href="{{route('mship.manage.dashboard')}}" class="btn btn-primary">Check your personal details</a></div>


<h2>✈️️ Training</h2>
<p>
    If you're interested in flying, VATSIM UK offers multiple pilot training courses. Browse our current offering at
    <a href="{{ route('site.pilots.landing') }}">{{ route('site.pilots.landing') }}</a>.
</p>

<p>
    If you're interested in learning about Air Traffic Control, with the goal of controlling on the VATSIM network, check out
    <a href="{{ route('site.atc.newController') }}">{{ route('site.atc.newController') }}</a>.
</p>

<h2>❓ Getting help</h2>
<p>
    We recommend making use of our <a href="{{route('discord.show')}}">Discord</a>, <a href="https://community.vatsim.uk">community forum</a>, or <a href="{{route('site.community.teamspeak')}}">TeamSpeak</a> to get help from members and division staff.
</p>
<p>
    Although there is a lot to learn, you will be in good company, surrounded by like-minded simming enthusiasts ready to help you get started.
</p>
<p style="margin-bottom: 2em;">
    If you have any concerns or difficulties getting setup, <a href="mailto:member-services@vatsim.uk">our Member Services team</a> would be happy to help you.
</p>


<p>On behalf of the entire staff team and our members, welcome to the United Kingdom.</p>
@stop
