@extends('emails.messages.post')

@section('body')
    <p>
        Your {{ $examBooking->exam }} practical exam on <strong>{{ $examBooking->position_1 }}</strong> scheduled for
        <strong>{{ \Carbon\Carbon::parse($examBooking->taken_date)->format('l jS M Y') }}</strong> at
        <strong>{{ \Carbon\Carbon::parse($examBooking->taken_from)->format('H:i') }}Z &ndash;
            {{ \Carbon\Carbon::parse($examBooking->taken_to)->format('H:i') }}Z</strong>
        has been successfully cancelled.
    </p>

@stop

@section('signature')
    VATSIM UK Training Department
@stop
