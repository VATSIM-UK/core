@extends('emails.messages.post')

@section('body')

<p>Dear <Firstname>,</p>

<p>A training place is now available for you on {{$position->name}} ({{$position->callsign}}). 
    Training times vary from student to student, but you should expect to be training for a number of months. 
    Please inform us before accepting this place if you will not be available to complete your training during this period.</p>

<p>If you are ready and able to begin your training, please let us know as soon as possible that you agree with the requirements set-out in section 5 of the [ATC Training Handbook](https://whereisthisnow.com).</p>

<p>If we’ve not heard from you within the next 84 hours (3.5 days), unfortunately, we will have to offer the place to another student.</p>

<p><a href="{{ $offer_url }}" class="btn btn-primary">Manage Training Place</a></p>

<p>Kind Regards,
The ATC Training Team</p>

@stop