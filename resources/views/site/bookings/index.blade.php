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
                                                    } else {
                                                        $bookingTypeClass = 'booking';
                                                    }
                                                    $fromTime = Carbon::parse($booking->from)->format('H:i');
                                                    $toTime = Carbon::parse($booking->to)->format('H:i');
                                                    $tooltipHtml = !$booking->isEvent() ? view('site.bookings.partials.tooltip', compact('booking', 'dayDate', 'fromTime', 'toTime'))->render() : null;
                                                    // Decide a normalized kind for filtering
                                                    $kind = 'normal';
                                                    if ($booking->isEvent()) {
                                                        $kind = 'event';
                                                    } elseif ($booking->isExam()) {
                                                        $kind = 'exam';
                                                    } elseif ($booking->isMentoring()) {
                                                        $kind = 'mentoring';
                                                    } elseif (strtolower($booking->type ?? '') === 'seminar') {
                                                        $kind = 'seminar';
                                                    }
                                                @endphp
                                                <div class="tooltip-container booking-entry booking-{{ strtolower($booking->type) }} {{ $rowClass }} {{ $bookingTypeClass }}
                                                    "@if(Auth::check() && $booking->user_id === Auth::id())
                                                        border-4
                                                    @endif"
                                                    tabindex="0" data-kind="{{ $kind }}">
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

<div class="col-md-8 col-md-offset-2 mt-4">
    <div class="panel panel-ukblue">
        <div class="panel-heading">
            <i class="fa fa-list"></i> &thinsp; Calendar Legend / Filter
        </div>
        <div class="panel-body">
            <table class="table table-bordered table-sm legend-table" style="font-size: 14px;">
                <tbody>
                    <tr>
                        <td style="width: 40px; background-color: #336633;"></td>
                        <td><strong>Booking</strong></td>
                        <td>
                            <label>
                                <input type="checkbox" data-filter="normal" checked>
                                Position booking
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #7429C7;"></td>
                        <td><strong>Mentoring</strong></td>
                        <td>
                            <label>
                                <input type="checkbox" data-filter="mentoring" checked>
                                Confirmed session
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #FFA500;"></td>
                        <td><strong>Seminar</strong></td>
                        <td>
                            <label>
                                <input type="checkbox" data-filter="seminar" checked>
                                Confirmed seminar
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #993300;"></td>
                        <td><strong>Exam</strong></td>
                        <td>
                            <label>
                                <input type="checkbox" data-filter="exam" checked>
                                Confirmed exam
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #FF0000;"></td>
                        <td><strong>Event</strong></td>
                        <td>
                            <label>
                                <input type="checkbox" data-filter="event" checked>
                                Event position
                            </label>
                        </td>
                    </tr>
                </tbody>
            </table>

{{--             <div class="mt-3">
                <label><input type="checkbox" id="filter-old"> Display old bookings</label>
                &nbsp;&nbsp;
                <label><input type="checkbox" id="filter-home"> Display only home TG positions</label>
                <br>
                <label><input type="checkbox" id="filter-today-old"> Hide today's old bookings</label>
            </div>

            <div class="mt-3">
                <label for="order">Search Order:</label>
                <select id="order" name="order" class="form-control form-control-sm d-inline-block" style="width: auto;">
                    <option value="time">Time booked</option>
                    <option value="type">Type</option>
                    <option value="position">Position</option>
                </select>
            </div> --}}
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = Array.from(document.querySelectorAll('input[type="checkbox"][data-filter]'));
    const bookings = () => Array.from(document.querySelectorAll('.booking-entry'));

    function applyTypeFilters() {
        const allowed = new Set(
            checkboxes.filter(cb => cb.checked).map(cb => cb.getAttribute('data-filter'))
        );

        bookings().forEach(el => {
            const kind = el.getAttribute('data-kind') || 'normal';
            el.style.display = allowed.has(kind) ? '' : 'none';
        });
    }

    // Watch for changes
    checkboxes.forEach(cb => cb.addEventListener('change', applyTypeFilters));

    // Run once on load
    applyTypeFilters();
});
</script>

@stop
