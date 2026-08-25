@extends('emails.messages.post')

@section('body')
	<p>Please could you check that you have both a session request and up-to-date availability entered into the CT System?
		Without both, mentors will be unable to accept your mentoring sessions.</p>

	<p>To be fair to those students who are still waiting for training, if after three of these reminders, or after
		{{ $availability_window }} of this email, you have no session request/availability in the CTS System, we will have to
		reassign your training place to the next person on the waiting list.</p>

	<p>To continue your training, please submit your availability in the CTS System as soon as possible.
		You have <strong>{{ $days_to_expire }}</strong>
		(until <strong>{{ $expires_at->format('d/m/Y H:i') }} Zulu</strong>) to submit your availability.</p>

	<p>To submit your availability, please visit the Central Training System:
		<a href="{{ config('training.pilot.cts_url') }}">{{ config('training.pilot.cts_url') }}</a>
	</p>

	<p>
		If you don’t have the time/don’t wish to continue with your training for the time being, please let us know by
		opening a ticket with the Pilot Training team via the helpdesk:
		<a href="{{ config('training.pilot.helpdesk_url') }}">{{ config('training.pilot.helpdesk_url') }}</a>
	</p>
@stop
