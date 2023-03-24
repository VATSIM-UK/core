@extends('emails.messages.post')

@section('body')
<p>
    Our systems show that you have fallen below the required activity to remain on a waiting list as described in the ATC Training Handbook section 5.3.
</p>

<p>
    In order to ensure that only active members of our division remain on the <b>{{$list_name}}</b> waiting list, all members on the list must maintain a minimum of 12 hours controlling or network mentoring over a rolling 3 month period.
</p>

<p>
    As defined in ATC Training Handbook section 5.4, you now have 30 days to regain the above activity requirements. If you do not meet the activity requirements above by {{$removal_date->format("l jS \\o\\f F Y")}} ({{$remaining_days}} days) then you will be removed from the waiting list.
</p>

<p>
    As a reminder, from any point from joining the waiting list to completing training you may 'defer' your training place. There is more information around this in the ATC Training Handbook section 5.9.
</p>

<p>
    If you have any issues with meeting this criteria and are still interested in training then please reach out to us by <a href='https://helpdesk.vatsim.uk/'>opening a ticket</a> with help topic 'Member Services'
</p>
@stop