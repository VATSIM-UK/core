@extends('emails.messages.post')

@section('body')
<p>
    You have been removed from the <b>{{$list_name}}</b> waiting list due to not maintaining the activity requirements to stay on the {{$list_name}} waiting list.
</p>

<p>
    As mentioned in previous emails and documented in the ATC Training Handbook section 5.3, you must maintain a minimum of 12 hours controlling or mentoring (network sessions only) on UK positions in order to remain on the list.
</p>

<p>
    If you would like to rejoin the waiting list then please <a href='https://helpdesk.vatsim.uk/'>open a ticket</a> with help topic 'Member Services' and one of the team will add you back to the list.
</p>
@stop
