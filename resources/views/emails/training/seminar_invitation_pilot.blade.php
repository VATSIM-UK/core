@extends('emails.messages.post')

@section('body')
	<p>You have been invited to {{ $seminar->name }} as you have now reached the top of the waiting list. Session
		Details:</p>

	<ul>
		<li>Date: {{ $seminar->date->format('l, j F Y') }}</li>
		<li>Time: {{ $seminar->startsAt()->format('H:i') }}z</li>
	</ul>

	<p>In order to confirm your position in this next group session we require you to do the following:</p>

	<p>Respond to this e-mail by clicking the "Accept" button below as soon as possible confirming your attendance to the
		group seminar on the proposed date above. Your response must be received no later than
		<strong>{{ $invitation->expires_at->format('l, j F Y \a\t H:i') }}z</strong>.</p>

	<p><strong>PLEASE NOTE</strong>:</p>

	<p>If you no longer wish to receive training at all, please do let us know to prevent you from receiving such emails in
		the future and so that we can provide your place to someone else.</p>

	@if ($invitation->is_short_notice)
		<p>Given that this is a 'short notice' offer, you will not be penalised if you are unable to accept; the next
			invitation to a seminar will be treated as your first and you will have one further opportunity to attend
			beyond that.</p>

		<p><strong>If you confirm your attendance to this seminar, but fail to attend, you will be removed from the Waiting
				List.</strong></p>
	@else
		<p>Due to the huge demand for training, if you miss 2 of these sessions or fail to respond to this then we will
			remove you from the waiting list to be fair to our other students. If you want to be more familiar with the
			joining process you may view it here.</p>
	@endif

	<p>Finally, if your circumstances change, please ensure you contact us as soon as possible. We will try to accommodate
		your personal circumstances where possible, but we cannot do this after the event.</p>

	@unless ($invitation->is_short_notice)
		<p>If we’ve not heard from you within the next {{ $seminar->invitation_expiry_hours }} hours
			({{ round($seminar->invitation_expiry_hours / 24, 1) }} days) on your confirmation of attendance, unfortunately,
			we will have to offer your place to another student.</p>
	@endunless

	<p style="margin-top: 24px;">
		<a href="{{ $notInterestedUrl }}" class="btn"
			style="color: #fff; background-color: #d9534f; border-color: #d43f3a; text-decoration: none; display: inline-block; padding: 6px 12px; font-size: 14px; border-radius: 4px; border: 1px solid #d43f3a; margin-right: 12px;">I am no longer interested</a>
		<a href="{{ $cannotAttendUrl }}" class="btn"
			style="color: #fff; background-color: #f0ad4e; border-color: #eea236; text-decoration: none; display: inline-block; padding: 6px 12px; font-size: 14px; border-radius: 4px; border: 1px solid #eea236; margin-right: 12px;">I am interested but I cannot attend</a>
		<a href="{{ $acceptUrl }}" class="btn"
			style="color: #fff; background-color: #5cb85c; border-color: #4cae4c; text-decoration: none; display: inline-block; padding: 6px 12px; font-size: 14px; border-radius: 4px; border: 1px solid #4cae4c;">Accept Invitation</a>
	</p>
@stop
