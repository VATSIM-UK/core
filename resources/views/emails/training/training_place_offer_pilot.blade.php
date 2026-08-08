@extends('emails.messages.post')

@section('body')

	<p>A training place is now available for you on our <strong>{{ $course_name }}</strong>.
		Training times vary from student to student, but you should expect to be training for the next three to five months.
		Please inform us before accepting this place if you will not be available to complete your training during this
		period.</p>

	<p>Pilot Training in VATSIM UK is challenging. You are expected to remain current with your flying skills, undertake
		independent theoretical study and remain engaged with VATSIM UK wherever possible. For rated pilots undertaking the
		P2, P3 or P4, this includes being proficient in all content from previous courses.</p>

	<p>If you are ready and able to begin your training, please let us know as soon as possible that you agree with the
		requirements set out in section 7 of the
		<a href="{{ config('training.pilot.handbook_url') }}">Pilot Training Handbook</a>.
	</p>

	<p>We’ll keep the training place open for you for the next 84 hours (3.5 days). If we’ve not heard from you by then,
		unfortunately, we will have to offer the place to another student.</p>

	<p>This offer expires at <strong>{{ $offer->expires_at->format('H:i') }}Z on
			{{ $offer->expires_at->format('d/m/Y') }}</strong>.</p>

	<p style="margin-top: 24px;">
		<a href="{{ $accept_url }}" class="btn btn-primary" style="margin-right: 12px;">Accept Training Place</a>
		<a href="{{ $decline_url }}" class="btn"
			style="color: #fff; background-color: #d9534f; border-color: #d43f3a; text-decoration: none; display: inline-block; padding: 6px 12px; font-size: 14px; border-radius: 4px; border: 1px solid #d43f3a;">Decline
			Training Place</a>
	</p>

@stop
