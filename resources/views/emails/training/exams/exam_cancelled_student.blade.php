@extends('emails.messages.post')

@section('body')
<p>
Your {{ $examType }} practical exam on <strong>{{ $position }}</strong> scheduled for
<strong>{{ \Carbon\Carbon::parse($takenDate)->format('l jS M Y') }}</strong> at
<strong>{{ \Carbon\Carbon::parse($takenFrom)->format('H:i') }}Z &ndash; {{ \Carbon\Carbon::parse($takenTo)->format('H:i') }}Z</strong>
has been successfully cancelled.
</p>

@stop

@section('signature')
VATSIM UK Training Department
@stop