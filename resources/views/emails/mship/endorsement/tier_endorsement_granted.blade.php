@extends('emails.messages.post')

@section('body')
<p>This email is formal notification that you have been granted a 'Tier' endorsement: <strong>{{ $endorsement_name }}</strong>.</p>

<p>This endorsement entitles you additional permissions to control the following positions:</p>
<ul>
	@foreach($positions as $position)
		<li>{{ $position }}</li>
	@endforeach
</ul>
<p>The listed positions are in addition to the positions you are already entitled to control via your controller rating and other Tier or Solo endorsements.
This has been reflected on your controller roster and is effective from the reciept of this email.</p>

<p>Congratulations!</p>
@endsection
