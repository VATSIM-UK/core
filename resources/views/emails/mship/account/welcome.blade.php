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
VATSIM UK provides many different ways to get to know and keep in touch with members, activities in the division and more:

<p>TeamSpeak - We use TeamSpeak for voice communication for things like coordination whilst controlling, mentoring sessions and general chat</p>
<div style="margin-left:2em; margin-bottom: 2em;"><a href="{{route('site.community.teamspeak')}}" class="btn btn-primary">Register for TeamSpeak</a></div>

<p>Community Forum - Our community forum is a place for text discussion; from questions to expressions of interest for events, this is the main place to go for formal help and discussion</p>
<div style="margin-left:2em; margin-bottom: 2em;"><a href="https://community.vatsim.uk" class="btn btn-primary">Visit our forum</a></div>

<p>Discord - Our Discord server provides an opportunity for more instant communication with members. Come here to introduce yourself, find someone to help you with a software problem, and get the latest news</p>
<div style="margin-left:2em; margin-bottom: 2em;"><a href="{{route('discord.show')}}" class="btn btn-primary">Register for Discord</a></div>

<p>Division Website - Our division website hosts all the information you need, including our policies, staff team and information on how to enrol in our training courses. It is also where you should go to configure settings such as secondary emails and passwords for your VATSIM UK account</p>
<div style="margin-left:2em; margin-bottom: 0.5em;"><a href="{{route('discord.show')}}" class="btn btn-primary">Visit our Site</a></div>
<div style="margin-left:2em; margin-bottom: 2em;"><a href="{{route('dashboard')}}" class="btn btn-primary">Edit your details</a></div>


<h2>✈️️ Training</h2>
<p>
    If you're interested in flying, VATSIM UK offers multiple pilot training courses. Browse our current offering at
    {!! link_to_route('site.pilots.landing') !!}
</p>

<p>
    If you're interested in learning about Air Traffic Control, with the goal of controlling on the VATSIM network, check out
    {!! link_to_route('site.atc.newController') !!}
</p>

<h2>❓ Getting help</h2>
<p>
    We recommend making use of our Discord, community forum, or TeamSpeak to get instant help from members and division staff.
</p>
<p>
    If you have any concerns or difficulties getting setup, {!! link_to('mailto:member-services@vatsim.uk', 'our Member Services team') !!} would be happy to help you.
</p>


On behalf of the entire staff team and our members, welcome to the United Kingdom.
@stop
