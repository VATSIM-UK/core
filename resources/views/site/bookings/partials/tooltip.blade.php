@php
use Carbon\Carbon;

if (!function_exists('formatDate')) {
    function formatDate($datetime) {
        return $datetime ? Carbon::parse($datetime)->format('d/m/Y H:i:s') : 'N/A';
    }
}

// Default values
$displayType = 'Position Booking';
$displayName = $booking->member ? $booking->member->name : 'Unknown';
$bookedLabel = 'Booked on';
$sessionRoles = []; // Will hold dynamic roles (mentor, examiner, etc.)
$requestTime = 'N/A';
$takenTime = 'N/A';

// Handle session types dynamically
if ($booking->session) {
    switch ($booking->type) {
        case 'EX':
            $displayType = 'Confirmed Practical Exam';
            $displayName = 'HIDDEN';
            $bookedLabel = 'Requested on';
            //if ($booking->session->examiner) {
            //    $sessionRoles['Examiner'] = $booking->session->examiner->name . ' (' . $booking->session->examiner->cid . ')';
            //}
            break;

        case 'ME':
            $displayType = 'Confirmed Mentoring Session';
            $displayName = 'HIDDEN';
            $bookedLabel = 'Requested on';
            if ($booking->session->mentor) {
                $sessionRoles['Mentor'] = $booking->session->mentor->name . ' (' . $booking->session->mentor->cid . ')';
            }
            break;
    }

    // Common times for all session types
    $requestTime = formatDate($booking->session->request_time);
    $takenTime = formatDate($booking->session->taken_time);
}

$timeBooked = formatDate($booking->time_booked);
@endphp

<div>
    <p><strong><big>Booking Information</big></strong></p>
    <p>Booking Type: <strong class="cal_bk">{{ $displayType }}</strong></p>
    <p>Position: <strong>{{ $booking->position }}</strong></p>
    <p>Date: <strong>{{ $dayDate->format('D jS M Y') }}</strong></p>
    <p>Book Time: <strong>{{ $fromTime }} - {{ $toTime }}</strong></p>
    <br>
    <p>Booked By: <strong>{{ $displayName }}</strong></p>

    @if(!empty($sessionRoles))
        <p>Requested on: <strong>{{ $requestTime }}</strong></p>
        <br>
        @foreach($sessionRoles as $role => $name)
            <p>{{ $role }}: <strong>{{ $name }}</strong></p>
            <p>Accepted on: <strong>{{ $takenTime }}</strong></p>
        @endforeach
    @else
        <p>{{ $bookedLabel }}: <strong>{{ $timeBooked }}</strong></p>
    @endif

    @if(!empty($booking->notes))
        <strong>Notes:</strong> {{ $booking->notes }}
    @endif
</div>
