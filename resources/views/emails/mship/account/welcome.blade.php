@extends('emails.messages.post')

@section('body')

<p>
    This email serves as confirmation that your central account with VATSIM UK has been created. We have received the
    following details about you:
</p>
<hr>
<table>
    <caption style="display: none">Table showing member's details</caption>
    <tr>
        <th scope="row">CID</th>
        <td>{!! $account->id !!}</td>
    </tr>
    <tr>
        <th scope="row">Full Name</th>
        <td>{!! $account->name !!}</td>
    </tr>
    <tr>
        <th scope="row">Primary Email</th>
        <td>{!! $account->email !!}</td>
    </tr>
    <tr>
        <th scope="row">Secondary Emails</th>
        <td>
            @forelse($account->secondaryEmails as $e)
                {!! $e->email !!}<br/>
            @empty

                No secondary emails registered.
            @endforelse
        </td>
    </tr>
    <tr>
        <th scope="row">Status</th>
        <td>{!! $account->status_string !!}</td>
    </tr>
    <tr>
        <th scope="row">State</th>
        <td>{!! $account->primary_state !!}</td>
    </tr>
    <tr>
        <th scope="row">ATC Qualification</th>
        <td>{!! $account->qualification_atc !!}</td>
    </tr>
    <tr>
        <th scope="row">Pilot Qualification(s)</th>
        <td>{!! $account->qualifications_pilot_string !!}</td>
    </tr>
</table>

<hr>
<p>
    Now that your account has been created, you can login to any of our web services and these details will be
    transferred automatically.
</p>

<p>
    Furthermore, any updates you make to your details via the vatsim.net membership portal will be synchronised with our
    database in a timely manner, to ensure all our systems remain up to date.
</p>

<p>
    If any details are incorrect, or you have any concerns, please
    contact {!! link_to('mailto:member-services@vatsim.uk', 'our Member Services team') !!} who will be able to help you
    further.
</p>


<h2>Explore the community</h2>
VATSIM UK provides many different ways to get to know and keep in touch with members, activities in the division and more:

<p>TeamSpeak - We use TeamSpeak for voice communication for things like coordination whilst controlling, mentoring sessions and general chat</p>
<div style="margin-left:2em; margin-bottom: 2em;"><a href="{{route('site.community.teamspeak')}}" class="btn btn-primary">Register for TeamSpeak</a></div>

<p>Community Forum - Our community forum is a place for text discussion; from questions to expressions of interest for events, this is the main place to go for formal help and discussion</p>
<div style="margin-left:2em; margin-bottom: 2em;"><a href="https://community.vatsim.uk" class="btn btn-primary">Visit our forum</a></div>

<p>Discord - Our Discord server provides an opportunity for more instant communication with members. Come here to introduce yourself, find someone to help you with a software problem, and get the latest news</p>
<div style="margin-left:2em; margin-bottom: 2em;"><a href="{{route('discord.show')}}" class="btn btn-primary">Register for Discord</a></div>


<h2>What next?</h2>

<p>
    If you're interested in flying, VATSIM UK offers multiple pilot training courses:
    {!! link_to_route('site.pilots.landing') !!}
</p>

<p>
    If you're interested in providing ATC:
    {!! link_to_route('site.atc.newController') !!}
</p>

<p>
    For both Pilot and ATC training our online system will allow you to book mentoring (once you are enrolled as per the above links):
    {!! link_to("https://cts.vatsim.uk/", "https://cts.vatsim.uk/") !!}
</p>


On behalf of the entire team, welcome to the United Kingdom.
@stop
