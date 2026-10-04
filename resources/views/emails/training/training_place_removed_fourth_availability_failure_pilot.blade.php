@extends('emails.messages.post')

@section('body')
	<p>In accordance with the <a href="{{ config('training.pilot.policy_url') }}">Pilot Training Policy</a>, you currently
		do not have either a session request and availability entered into the CTS System. As you have already had three
		reminders to do so, your training place has been removed and will shortly be reallocated.</p>

	<p>You have previously had three instances where you failed the availability check but resolved it within the seven-day period.
		On this fourth occasion of failing the availability check, your training place has been removed as of
		<strong>{{ $removal_date }}</strong> and will shortly be reallocated. This allows the next person on the waiting list
		to receive training.</p>

	<p>It is essential that you maintain a session request and up-to-date availability, as without it mentors are unable to
		accept your mentoring sessions. Failure to do so delays the training process, both for you and others on the waiting
		list and in order to ensure that training continues to move within the division, we enforce this policy strictly.
	</p>

	<p>If you would like to continue with Pilot training in the future, you can self-enrol for the waiting list again:
		<a href="{{ config('training.pilot.waiting_lists_url') }}">{{ config('training.pilot.waiting_lists_url') }}</a>
	</p>

	<p>If you believe this to be in error, please contact the VATSIM UK Pilot Training team via the Helpdesk:
		<a href="{{ config('training.pilot.helpdesk_url') }}">{{ config('training.pilot.helpdesk_url') }}</a>
	</p>
@stop
