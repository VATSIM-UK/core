@extends('emails.messages.post')

@section('body')
<p>
    You have now reached the top 10 of the {{$list_name}} waiting list. You will soon be reaching the top of the list where providing you meet the eligibility criteria we will be offering you a training place. The criteria to be eligible for a training place can be found in the ATC Training Handbook sections 5.15.1 (S2 training), 5.16.1 (S3 training) and 5.17.1 (C1 training).
</p>

<p>
    For all ratings you must have completed the relevant CTS theory exam to be eligible for a place. If you haven't already we recommend that you start looking through the theory material now and when you feel ready you attempt the theory exam.
</p>

<p>
    If you have not attempted the theory exam when you reach the top of the list, you may be removed from the waiting list and have to rejoin again at the start. This process is documented in the ATC Training Handbook section 5.4.3 which is included below.
</p>

<pre>
        If you reach the top of the waiting list and a training place becomes available, but you have not yet passed the relevant CTS theory exam, you will be contacted by a member of the ATC Training staff. You will be given 7 days to attempt the theory exam. If unsuccessful, there is a 7 day cool down period, after which you will be given a further 8 days to complete the exam. This will continue until you pass the exam.

        Should you not attempt the exam within the 7 days, you will be removed from the waiting list.
</pre>

<p>
    If you have any issues with this and are still interested in training then reach out to us via the helpdesk with help topic 'Member Services'.
</p>
@stop
