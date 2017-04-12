@extends('emails.messages.post')

@section('body')

<p>
    This email serves as confirmation that your central account with VATSIM UK has been created. We have received the
    following details about you:
</p>

<p>
    CID: {!! $account->id !!}<br/>
    Full Name: {!! $account->name !!}<br/>
    Primary Email: {!! $account->email !!}<br/>
    Secondary Emails:<br/>
    @foreach($account->secondaryEmails as $e)
        -- {!! $e->email !!}<br/>
    @endforeach
    @if(count($account->secondaryEmails) < 1)
        No secondary emails registered.<br/>
    @endif
</p>

<p>
    Status: {!! $account->status_string !!}<br/>
    State: {!! $account->primary_state !!}<br/>
</p>

<p>
    ATC Qualification: {!! $account->qualification_atc !!}<br/>
    Pilot Qualification(s): {!! $account->qualifications_pilot_string !!}<br/>
</p>

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
    contact {!! link_to('mailto:community@vatsim-uk.co.uk', 'our community department') !!} who will be able to help you
    further.
</p>

<h2>What next?</h2>

<p>
    If you're interested in flying, there are a number of 'How to....' guides and training available:
    {!! link_to("https://www.vatsim.uk/pilot-info/", "https://www.vatsim.uk/pilot-info/") !!}
</p>
<p>
    Pilot Training
    {!! link_to("https://www.vatsim.uk/pilot-training/", "https://www.vatsim.uk/pilot-training/") !!}
</p>

<p>
    If you're interested in providing ATC:
    {!! link_to("https://www.vatsim.uk/becoming-a-controller/", "https://www.vatsim.uk/becoming-a-controller/") !!}
</p>

<p>
    For both Pilot and ATC training our online system will allow you to book mentoring (once you are enrolled as per the above links):
    {!! link_to("https://cts.vatsim.uk/", "https://cts.vatsim.uk/") !!}
</p>

<p>
    The UK TeamSpeak Server, Slack and Forum - these are used for voice and written communications for the entire UK community. Feel free to join the community and ask questions and meet others:
    <li> TeamSpeak &amp; Slack - {!! link_to("https://www.vatsim.uk/tsreg/", "https://www.vatsim.uk/tsreg/") !!}</li>
    <li> Forum - {!! link_to("https://community.vatsim.uk/", "https://community.vatsim.uk/") !!}</li>
</p>


On behalf of the entire team, welcome to the United Kingdom.
@stop