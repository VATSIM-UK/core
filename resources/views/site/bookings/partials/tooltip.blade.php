@php
    if ($booking->type === 'EX') {
        $displayType = 'Confirmed Practical Exam';
        $displayName = 'HIDDEN';
        $bookedLabel = 'Requested on';
        $showMentor = true;
    }  elseif ($booking->type === 'ME') {
        $displayType = 'Confirmed Mentoring Session';
        $displayName = 'HIDDEN';
        $bookedLabel = 'Requested on';
        $showMentor = true;
    } else {
        $displayType = 'Position Booking';
        $displayName = $booking->member->name ?? 'Unknown';
        $bookedLabel = 'Booked on';
        $showMentor = false;
    }
@endphp

<div>
    <p><strong><big>Booking Information</big></strong></p>
    <p>Booking Type: <strong class="cal_bk">{{ $displayType }}</strong></p>
    <p>Position: <strong>{{ $booking->position }}</strong></p>
    <p>Date: <strong>{{ $dayDate->format('D jS M Y') }}</strong></p>
    <p>Book Time: <strong>{{ $fromTime }} - {{ $toTime }}</strong></p>
    <br>
    <p>Booked By: <strong>{{ $displayName }}</strong></p>
    <p>{{ $bookedLabel }}: <strong>{{ $booking->requested_at ? Carbon::parse($booking->requested_at)->format('d/m/Y H:i:s') : 'N/A' }}</strong></p>
    @if($showMentor)
        <br>
        <p>Mentor: <strong>{{ $booking->mentor->name ?? 'Unknown' }}</strong></p>
        <p>Accepted on: <strong>{{ $booking->accepted_at ? Carbon\Carbon::parse($booking->accepted_at)->format('d/m/Y H:i:s') : 'N/A' }}</strong></p>
    @endif
    @if(!empty($booking->notes))
        <strong>Notes:</strong> {{ $booking->notes }}
    @endif
</div>