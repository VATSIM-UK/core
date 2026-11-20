@extends('emails.messages.post')

@section('body')
	<p>We have identified that you are near the top of the {{ $seminar?->waitingList?->name ?? 'Pilot Training' }} waiting
		list, but have not yet passed the theory exam. Passing this exam is a condition of you being invited to a seminar.</p>

	<p>This means you have <strong>NOT</strong> been invited to the next Training Seminar.</p>

	<p>In order to be eligible for the next seminar you must complete the relevant Theory Exam on the moodle, although there
		is no current date set for the next seminar. You must now ensure that you pass the exam before the next seminar;
		please note that failure to do so will see you removed from the Pilot Training waiting list.</p>

	<p>If you attempt the exam but fail, you will be subject to a 24 hour cool down to revise before you can re-attempt the
		exam.</p>

	<p>This ticket counts as your first attempt at attending a seminar. If, when we are planning the next seminar, you are
		found to have not passed the theory exam, you will have your place on the Waiting List removed.</p>

	<p>If you have any questions, please feel free to open a ticket in <a href="https://helpdesk.vatsim.uk">VATSIM UK's
			helpdesk</a>.</p>
@stop
