@extends('emails.messages.post')

@section('body')
    <p>In accordance with the <a href="https://www.vatsim.uk/policy/atc-training-policy">ATC Training Policy</a>, as we’ve not heard from you on the above and you still do not have either a Session Request or Availability in the CTS, your training place on  has been removed and will shortly be reallocated.</p>

    <p>As no session request and availability were submitted within the five-day period, your training place has been removed as of {{ $removal_date }}. This allows the next person on the waiting list to receive training.</p>

    <p>It is essential that you maintain a session request and up-to-date availability, as without it mentors are unable to accept your mentoring sessions. Failure to do so delays the training process, both for you and others on the waiting list and in order to ensure that training continues to move within the division, we enforce this policy strictly.</p>

    <p>If you would like to continue with ATC training in the future, you can self-enrol for the waiting list again: <a href="https://www.vatsim.uk/mship/waiting-lists">https://www.vatsim.uk/mship/waiting-lists</a></p>

    <p>If you believe this to be in error, please contact the VATSIM UK ATC Training team via the Helpdesk: <a href="https://helpdesk.vatsim.uk">https://helpdesk.vatsim.uk</a></p>
@stop
