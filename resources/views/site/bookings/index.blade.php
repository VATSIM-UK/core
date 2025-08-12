@extends('layout')

@section('content')

@php
    use Carbon\Carbon;

    if (is_string($date)) {
        $date = Carbon::parse($date);
    }

    $typeRowStyles = [
        'mentoring' => 'bg-blue-100 border-blue-400',
        'EX' => 'color:red; bg-red-100 border-red-400 font-bold',
        'solo' => 'bg-yellow-100 border-yellow-400',
        'normal' => 'bg-gray-100 border-gray-300',
    ];
@endphp

<style>
    .calendar {
        width: 100%;
        max-width: 850px;
        margin: 0 auto;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .calendar th, .calendar td {
        width: 14.28%;
        height: 100px;
        text-align: center;
        font-weight: bold;
        vertical-align: top;
        padding: 5px;
        border: 1px solid #ccc;
        box-sizing: border-box;
    }
    .bg-gray-100 { background-color: #f0f0f0; }
    .booking-entry {
        cursor: pointer;
        text-decoration: none;
        font-size: 15px;
        margin-top: 5px;
    }
    .booking-entry:hover {
        text-decoration: underline;
    }
    .today-cell {
        background-color: #FFFFCC !important;
        border: 2px solid #FFCC00 !important;
    }
    .booking { padding: 4px; border: 2px solid transparent; border-radius: 4px; }
    .booking-mentoring { color: #7429C7; border-color: #7429C7; }
    .booking-event { color: #FF0000; border-color: #FF0000; }
    .booking-exam { color: #993300; border-color: #993300; }
    .tooltip-container { position: relative; display: inline-block; }
    .tooltip-content {
        display: none;
        position: absolute;
        z-index: 10;
        left: 100%;
        top: 0;
        min-width: 250px;
        background: #fff;
        color: #222;
        border: 1px solid #888;
        border-radius: 6px;
        padding: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        white-space: normal;
        font-size: 13px;
    }
    .tooltip-container:hover .tooltip-content,
    .tooltip-container:focus-within .tooltip-content {
        display: block;
    }
    .no-x-overflow { overflow-x: visible !important; }
</style>

<div class="col-md-8 col-md-offset-2">
    <div class="panel panel-ukblue">
        <div class="panel-heading">
            <i class="fa fa-cog"></i> &thinsp; Bookings Calendar
        </div>
        <div class="panel-body">
            <p>
                The bookings calendar shows the availability of our controllers for bookings. You can navigate through the months using the links below.
            </p>
        </div>
    </div>
</div>

<div class="col-md-8 col-md-offset-2">
    <div class="panel panel-ukblue">
        <div class="panel-heading">{{ $date->format('F Y') }}</div>
        <div class="panel-body">
            <div class="mb-3 text-center">
                <p>Use the links below to navigate through the months.</p>
                <a href="{{ route('site.bookings.index', ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}">← Previous</a>
                &nbsp;|&nbsp;
                <a href="{{ route('site.bookings.index', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}">Next →</a>
            </div>
            <div class="table-responsive no-x-overflow">
                <table class="calendar">
                    <thead>
                        <tr>
                            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                                <th>{{ $day }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($calendar as $week)
                            <tr>
                                @foreach($week as $day)
                                    @php
                                        $dayDate = is_string($day['date']) ? Carbon::parse($day['date']) : $day['date'];
                                    @endphp
                                    <td class="align-top border p-2
                                        {{ $dayDate->month !== $date->month ? 'bg-gray-100' : '' }}
                                        {{ $dayDate->isToday() ? 'today-cell' : '' }}">
                                        <strong>{{ $dayDate->day }}</strong>
                                        @if(!$dayDate->isBefore(Carbon::today()))
                                            @foreach($day['bookings'] as $booking)
                                                @php
                                                    $type = $booking->type ?? 'normal';
                                                    $rowClass = $typeRowStyles[$type] ?? 'bg-white border-gray-200';
                                                    $bookingTypeClass = '';
                                                    if ($booking->isMentoring()) {
                                                        $bookingTypeClass = 'booking-mentoring';
                                                    } elseif ($booking->isEvent()) {
                                                        $bookingTypeClass = 'booking-event';
                                                    } elseif ($booking->isExam()) {
                                                        $bookingTypeClass = 'booking-exam';
                                                    }
                                                    $fromTime = Carbon::parse($booking->from)->format('H:i');
                                                    $toTime = Carbon::parse($booking->to)->format('H:i');
                                                    $tooltipHtml = !$booking->isEvent() ? view('site.bookings.partials.tooltip', compact('booking', 'dayDate', 'fromTime', 'toTime'))->render() : null;
                                                @endphp
                                                <div class="tooltip-container booking-entry booking-{{ strtolower($booking->type) }} {{ $rowClass }} {{ $bookingTypeClass }}" tabindex="0">
                                                    📌 {{ strtoupper($booking->position) ?? 'Booking' }}<br>
                                                    🕒 {{ $fromTime }} - {{ $toTime }}
                                                    @if(!$booking->isEvent())
                                                    <div class="tooltip-content">
                                                        {!! $tooltipHtml !!}
                                                    </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop
