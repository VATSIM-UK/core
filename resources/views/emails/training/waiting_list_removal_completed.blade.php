@extends('emails.messages.post')

@section('body')
<p>
    You have been automatically removed from the waiting list: <b>{{$list_name}}</b> because you have not met the required number of hours controlling on the network for 30 days.
</p>

<p>
    If you wish to re-join the waiting list you may do so when you meet the eligibility requirements.
</p>
@stop
