@extends('emails.messages.post')

@section('body')

<p>
    <b>Warning: You will be removed from the {{$list_name}} waiting list in {{$remaining_days}} days</b>
</p>

<p>
    Your network activity is still below the requirements to remain on the {{$list_name}} waiting list. As a reminder, you must maintain a minimum of 12 hours controlling or mentoring (network sessions only) on UK positions as documented in the ATC Training Handbook section 5.3.
</p>

<p>
    As mentioned in our previous email and documented in the ATC Training Handbook section 5.4, you will be removed from the {{$list_name}} waiting list on {{$removal_date->format("l jS \\o\\f F Y")}} (in {{$remaining_days}} days).
</p>

<p>
    As a reminder, from any point from joining the waiting list to completing training you may 'defer' your training place. There is more information around this in the ATC Training Handbook section 5.9.
</p>

<p>
    If you have any issues with meeting this criteria and are still interested in training then please reach out to us by <a href='https://helpdesk.vatsim.uk/'>opening a ticket</a> with help topic 'Member Services'.
</p>
@stop