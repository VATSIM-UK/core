@extends('emails.messages.post')

@section('body')
	<p>
		Our records indicate that {{ $reason }}. As a result, your visiting rights in the VATSIM UK Division have been
		removed. Should you wish to control UK positions in the future, you must make a new application as a Visiting
		Controller.
	</p>

	<p>
		If you believe this email has been sent in error, please contact the Community Department via the VATSIM UK
		Helpdesk.
	</p>
@stop

@section('signature')
	VATSIM UK Community Department
@stop
