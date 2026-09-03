@extends('layout')

@section('content')
	<div class="panel panel-ukblue">
		<div class="panel-heading">Seminar Invitation - {{ $invitation->seminar->name }}</div>
		<div class="panel-body">
			@if ($result === 'accepted')
				<div class="alert alert-success">
					<strong>You're all set!</strong> Your place at the <strong>{{ $invitation->seminar->name }}</strong> has been
					confirmed.
					We look forward to seeing you on {{ $invitation->seminar->date->format('j F Y') }}.
				</div>
			@elseif($result === 'not_interested')
				<div class="alert alert-danger">
					<strong>Invitation declined.</strong> You have been removed from the waiting list for
					<strong>{{ $invitation->seminar->name }}</strong>.
					If you change your mind, you can rejoin the waiting list <a href="{{ route('mship.waiting-lists.index') }}">here</a>.
				</div>
			@elseif($result === 'cannot_attend')
				@if ($invitation->status === \App\Enums\SeminarInvitationStatus::RemovedTwoCannotAttend)
					<div class="alert alert-danger">
						<strong>Marked as unable to attend.</strong> You have been noted as unavailable for
						<strong>{{ $invitation->seminar->name }}</strong>.
						This is the second time you have been unable to attend a seminar on this waiting list,
						so you have been removed from the waiting list. You can rejoin the waiting list <a
							href="{{ route('mship.waiting-lists.index') }}">here</a>.
					</div>
				@else
					<div class="alert alert-warning">
						<strong>Marked as unable to attend.</strong> You have been noted as unavailable for
						<strong>{{ $invitation->seminar->name }}</strong>.
						You will remain on the waiting list and may be invited to future seminars.
					</div>
				@endif
			@endif

			<a href="{{ route('mship.manage.dashboard') }}" class="btn btn-link">Return to Dashboard</a>

		</div>
	</div>
@endsection
