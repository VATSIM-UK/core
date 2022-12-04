@extends('emails.messages.post')

@section('body')
<p>
    The wait is nearly over! You have reached the top 10 of the {{$list_name}} waiting list. If you haven't already, now would be a great time to start looking through the theory content for your {{$list_name}} training. Everything that you need to know is available on our <a href="https://moodle.vatsim.uk/">Moodle platform</a>.
</p>

<p>
    In order to make the most out of your practical training, it's important that you learn the theory material prior to being offered a training place. This helps you get the maximum from your mentoring while also reducing the amount of time our mentors spend teaching material that's available on Moodle. This allows us to train more efficiently helping to reduce the length of the waiting lists.
</p>

<p>
    To keep our training system efficient, we require that all students complete the relevant CTS theory exam to be eligible for a training place. If you reach the top of the list and have not yet completed the theory exam, you may be removed from the waiting list and have to rejoin at the start. The eligibility requirements and policy for removal from waiting lists for failure to complete the theory exam can be found in the ATC Training Handbook sections 5.15.1 (S2 training), 5.16.1 (S3 training), 5.17.1 (C1 training) and 5.4.3 (theory exam). Section 5.4.3 is included below for easy reference:
</p>

<pre>
    If you reach the top of the waiting list and a training place becomes available, but you have not yet passed the relevant CTS theory exam, you will be contacted by a member of the ATC Training staff. You will be given 7 days to attempt the theory exam. If unsuccessful, there is a 7 day cool down period, after which you will be given a further 8 days to complete the exam. This will continue until you pass the exam. 

    Should you not attempt the exam within the 7 days, you will be removed from the waiting list.    
</pre>

<p>
    If you have any issues with this and are still interested in training then reach out to us via the helpdesk with help topic 'Member Services'.
</p>
@stop
