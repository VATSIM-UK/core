@extends('emails.messages.post')

@section('body')
<p>
    We wanted to let you know that your controlling hours in the last 3 months now exceeds 12 hours, making you eligible for the waiting list: <b>{{$list_name}}</b> again.
</p>

<p>
    Your position on this waiting list remains as it was before you originally became ineligible, when you reach the top of the list you will be offered a training place.
</p>

<p>
    To avoid being removed from this list in the future, please ensure that you continue to meet the controlling activity requirements of 12 hours in the last 3 months.
</p>
@stop
