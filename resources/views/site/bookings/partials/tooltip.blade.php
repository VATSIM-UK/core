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
$showMentor = false;
$showExaminer = false;
$mentor = 'N/A';
$examiner = 'N/A';
$requestTime = 'N/A';
$takenTime = 'N/A';

// EX (Practical Exam)
if ($booking->type === 'EX') {
    $displayType = 'Confirmed Practical Exam';
    $displayName = 'HIDDEN';
    $bookedLabel = 'Requested on';
    $showExaminer = true;

    if ($booking->exams && $booking->exams->examiner) {
        $examiner = $booking->exams->examiner->name . ' (' . $booking->exams->examiner->cid . ')';
    }

    $requestTime = $booking->exams ? formatDate($booking->exams->time_book) : 'N/A';
    $takenTime = $booking->exams ? formatDate($booking->exams->time_taken) : 'N/A';
}

// ME (Mentoring Session)
if ($booking->type === 'ME') {
    $displayType = 'Confirmed Mentoring Session';
    $displayName = 'HIDDEN';
    $bookedLabel = 'Requested on';
    $showMentor = true;

    if ($booking->session && $booking->session->mentor) {
        $mentor = $booking->session->mentor->name . ' (' . $booking->session->mentor->cid . ')';
    }

    $requestTime = $booking->session ? formatDate($booking->session->request_time) : 'N/A';
    $takenTime = $booking->session ? formatDate($booking->session->taken_time) : 'N/A';
}

if ($booking->type === 'GS') {
    $displayType = 'Seminar Booking';
    $bookedLabel = 'Booked on';
    $showMentor = false;

    if ($booking->session && $booking->session->mentor) {
        $mentor = $booking->session->mentor->name . ' (' . $booking->session->mentor->cid . ')';
    }

    $requestTime = $booking->session ? formatDate($booking->session->request_time) : 'N/A';
    $takenTime = $booking->session ? formatDate($booking->session->taken_time) : 'N/A';
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
    @if($showExaminer)
        <p>Requested on: <strong>{{ $requestTime }}</strong></p>
        <br>
        <p>Examiner: <strong>{{ $examiner }}</strong></p>
    @elseif($showMentor)
        <p>Requested on: <strong>{{ $requestTime }}</strong></p>
        <br>
        <p>Mentor: <strong>{{ $mentor }}</strong></p>
        <p>Accepted on: <strong>{{ $takenTime }}</strong></p>
    @else
        <p>{{ $bookedLabel }}: <strong>{{ $timeBooked }}</strong></p>
    @endif

    @if(!empty($booking->notes))
        <strong>Notes:</strong> {{ $booking->notes }}
    @endif
</div>