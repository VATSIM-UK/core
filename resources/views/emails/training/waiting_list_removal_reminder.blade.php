@extends('emails.messages.post')

@section('body')

<p>
    <b>Warning: You only have {{$remaining_days}} days remaining to meet the hour check requirements for the waiting list: {{$list_name}} before you are removed.
</p>

<p>
    Your network activity has remained below the requirement to remain eligible on a waiting list for training that you are on.
</p>

<p>
    To be eligible for the waiting list: <b>{{$list_name}}</b>, you must maintain a minimum of 12 hours controlling or mentoring in the UK divison over a 3 month rolling period.
</p>

<p>
    You have {{$remaining_days}} days to meet the above eligibility criteria. If you do not take steps to meet the criteria then you will be automatically removed from the waiting list on <b>{{$removal_date->format("l jS \\o\\f F Y")}}</b>.
</p>

<p>
    If there are circumstances preventing you from meeting the activity requirement for this waiting list, please open a ticket to the member services department on the <a href='https://helpdesk.vatsim.uk/'>helpdesk</a> as soon as possible.
</p>
@stop
