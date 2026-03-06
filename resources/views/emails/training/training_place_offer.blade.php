@extends('emails.messages.post')

@section('body')

<p>Hi {{ $account->name_first }},</p>

<p>A training place is now available for you on <strong>{{ $position->name }} ({{ $position->callsign }})</strong>.
    Training times vary from student to student, but you should expect to be training for the next six to nine months.
    Please inform us before accepting this place if you will not be available to complete your training during this period.</p>

<p>ATC Training in VATSIM UK is challenging. You should expect to remain current with local procedures, undertake
    independent theoretical study and remain engaged with VATSIM UK wherever possible. For rated controllers, this
    includes continuing to control on relevant positions throughout your training.</p>

<p>You can either accept this offer by clicking 'Accept' below, or reject it by clicking the 'Decline' link below.
    Please note that you may not reject an offer in the hope of receiving an offer on another position, nor does the
    department routinely permit the deferral of training places. Should you decline this offer, you will be
    removed from the waiting list.</p>

<p>If you are ready and able to begin your training, please let us know as soon as possible. By clicking 'Accept',
    you indicate that you agree with the requirements set out in section 5 of the
    <!-- NEED TO FIND LINKS FOR THESE -->
    <a href="unknown">ATC Training Handbook</a> as well as Section 2 of the 
    <a href="unknown">ATC Training Policy</a>.</p>

<p>If we've not heard from you within the next 84 hours (3.5 days), unfortunately, we will have to offer the place
    to another student and your place on the waiting list will be forfeit.</p>

<p>This offer expires at {{ $offer->expires_at->format('H:i') }} UTC on {{ $offer->expires_at->format('d/m/Y') }}.</p>

<p style="margin-top: 24px;">
    <a href="{{ $accept_url }}" class="btn btn-primary" style="margin-right: 12px;">Accept Training Place</a>
    <a href="{{ $decline_url }}" style="btn btn-danger" style="margin-right: 12px;">Decline Training Place</a>
</p>

<p>Kind Regards,<br>
The ATC Training Team</p>

@stop