@extends('emails.messages.post')

@section('body')
<p>
    We are getting in touch to let you know that you now meet the activity requirements to remain on the <b>{{$list_name}}</b> waiting list once again.
</p>

<p>
    As a reminder, in order to remain on the list, you must maintain a minimum of 12 hours controlling or network mentoring over a rolling 3 month period.
</p>

<p>
    You can track your position and your recent controlling hours <a href="https://www.vatsim.uk/mship/waiting-lists">here</a>.
</p>
@stop
