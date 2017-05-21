@extends('emails.messages.post')

@section('body')

<p>
    Why not come and join us on Slack?
</p>
<p>
    As of right now there are {{ \DB::table("mship_account")->whereNotNull("slack_id")->count() }} other UK Members
    sharing their love of Aviation and you could be too.
</p>
<p>
    If you've never heard of Slack, that's OK!  Slack is a new, 21st century chat platform which allows teams of people
    with a mutual interest to communicate in real-time.  If there's a burning question you want to ask about real world aviation,
    or a suggestion you've got for the division, or even a request for a flying buddy, there's bound to be someone around
    to support you.
</p>
<p>
    Registration for Slack is really simple.  Visit {!! link_to_route("slack.new", "the Slack registration page in Core") !!}
    to get started, or visit the link below:<br /><br />
    {{ route("slack.new") }}
</p>
<p>
    We look forward to seeing you there!
</p>
@stop
