@extends('emails.messages.post')

@section('body')
<p>
    Your network activity has fallen below the requirement to remain eligible on a waiting list for training that you are on.
</p>

<p>
    To be eligible for the waiting list: <b>{{$list_name}}</b>, you must maintain a minimum of 12 hours controlling or mentoring in the UK divison over a 3 month rolling period.
</p>

<p>
    You now have 30 days to meet the above eligibility criteria. If you do not take steps to meet the criteria then you will be automatically removed from the waiting list on <b>{{$removal_date->format("l jS \\o\\f F Y")}}</b>, which is in <b>{{$remaining_days}}</b> days.
</p>

<p>
    If there are circumstances preventing you from meeting the activity requirement for this waiting list, please open a ticket to the member services department on the <a href='https://helpdesk.vatsim.uk/'>helpdesk</a> as soon as possible.
</p>
@stop
