@extends('emails.messages.post')

@section('body')
<p>
You have been awarded a Solo Endorsement due to your recent performance in mentoring sessions. Please read this email carefully so that you
are aware of the rules of this validation.
</p>

<p>
This validation allows you to control <strong>{{ $endorsement_callsign }}</strong> solo, without a mentor present from now until {{ $endorsement_expiry }}.
When logging onto a position, you must include "Solo Endorsement"; in your controller information.
The purpose of this validation is to allow you to practice prior to your exam.
Please take the opportunity to log on at times when you are comfortable with the traffic levels to gain this experience.
Should you end up in situations you are unable to cope with, seek help and consider logging off until you can receive help from a mentor.
The maximum total time a Solo Endorsement can be granted to a member on a single position is 90 days, so their use should be maximised.
</p>

<h2>Exams</h2>
<p>
Members with Solo Endorsements shall not control adjacent to exams without the permission of the examiner and candidate.
Please respect the importance of a candidate having an experienced controller adjacent to them in order to give them the best chance of passing.
We appreciate that exam traffic may be enticing, however please give priority to more experienced controllers and do not book the position.
Should the exam be lacking an adjacent controller immediately prior to the exam, contact the examiner and request permission to control.
Additionally request that the examiner obtains permission of the student on your behalf before you log in.
</p>

<h2>Events</h2>
<p>
Equally you are not permitted to control during events without the permission of the Training Group staff and Marketing Director.
You should get in touch with the relevant members of staff if you wish to control.
Usage of a Solo Endorsement during an event, however, is strongly discouraged.
</p>

<h2>Bookings</h2>
<p>
Should you choose to book this position on the CTS, you should only book for the period of this validation and may not assume the validation will be renewed.
As with all ATC bookings, the length of a booking should be of appropriate length and bookings of execessive length are discouraged.
In addition, gaps should be present between bookings to avoid becoming overwhelmed.
</p>

<h2>Renewals</h2>

<p>
Renewals of solo endorsements are at the discretion of the ATC Training Department - you should not request renewals from your mentors or ask them to request a renewal
from the Training Group Staff.
Renewals are conditional upon you making good use of your validation, having received further mentoring and shown progress since the last time a Solo Endorsement was issued.
A Solo Endorsement is not a substitute for mentoring - you should still be putting in mentoring requests and availability as often as you can.
</p>


<p>
If you have any questions regarding your solo endorsement you should get in touch with the Training Group staff via the helpdesk.
</p>

@endsection
