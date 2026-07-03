@extends('layout')

@section('content')
	<div class="panel panel-ukblue">
		<div class="panel-heading">Seminar Invitation - {{ $invitation->seminar->name }}</div>
		<div class="panel-body">
			<div class="alert alert-danger">
				<strong>This invitation is no longer valid.</strong>
				@if ($invitation->status->isResponded())
					You have already responded to this invitation.
				@elseif($invitation->seminar->isClosed())
					The seminar has already started or been closed.
				@elseif($invitation->expires_at->isPast())
					The invitation expired on {{ $invitation->expires_at->format('l, j F Y \a\t H:i') }}Z without a response.
				@else
					It may have been cancelled or is no longer accepting responses.
				@endif
			</div>

			<a href="{{ route('mship.manage.dashboard') }}" class="btn btn-link">Return to Dashboard</a>
		</div>
	</div>
@endsection
