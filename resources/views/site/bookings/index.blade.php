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

<div class="col-md-4 col-md-offset-2">
    <div class="panel panel-ukblue">
        <div class="panel-heading">
            <i class="fa fa-cog"></i> &thinsp; Bookings Calendar
        </div>
        <div class="panel-body">
            <div class="mb-3 text-center">
            <p>
                The bookings calendar shows the availability of our controllers for bookings. You can navigate through the months using the links below.
            </p>


            </div>
        </div>
    </div>
</div>

<div class="col-md-4 ">
    <div class="panel panel-ukblue">
        <div class="panel-heading">
            <i class="fa fa-cog"></i> &thinsp; Information
        </div>
        <div class="panel-body">
            <div class="mb-3 text-center">
            <p>
                All times on Bookings are in UTC (Zulu).
            </p>

            <p class="mt-2">
                <strong>Your local time is:</strong>
                <span id="local-time"></span> (Local) 
                <br>
                <strong>The current Zulu time is</strong> 
                <span id="utc-time"></span> (UTC/Zulu)
                <br>
                <span id="tz-offset"></span>
            </p>
            <p class="mt-2" id="tz-message"></p>
            </div>
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
                                    <td data-date="{{ $dayDate->format('Y-m-d') }}" class="align-top border p-2
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
                                                    } elseif ($booking->isSeminar()) {
                                                        $bookingTypeClass = 'booking-seminar';
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
                                                    } elseif ($booking->isSeminar()) {
                                                        $kind = 'seminar';
                                                    }
                                                @endphp
                                                <div class="tooltip-container booking-entry booking-{{ strtolower($booking->type) }} {{ $rowClass }} {{ $bookingTypeClass }}
                                                    "@if(Auth::check() && $booking->user_id === Auth::id())
                                                        border-4
                                                    @endif"
                                                    tabindex="0" data-kind="{{ $kind }}">
                                                    📌 {{ strtoupper($booking->position) ?? 'Booking' }}<br>
                                                    🕒 <span class="booking-time"
                                                        data-start="{{ \Carbon\Carbon::parse($booking->from)->toIso8601String() }}"
                                                        data-end="{{ \Carbon\Carbon::parse($booking->to)->toIso8601String() }}">
                                                        {{ $fromTime }}z - {{ $toTime }}z
                                                    </span>
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
            <div class="row">
                <!-- Legend table on the left -->
                <div class="col-xs-9 col-sm-9">
                    <table class="table table-bordered legend-table" style="font-size:14px; margin-bottom:0; width:auto;">
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
                </div>

                <!-- “Display old bookings” and local time toggle on the right -->
                <div class="col-xs-3 col-sm-3 text-right" style="padding-top:6px;">
                    <div class="checkbox">
                        <label style="font-weight:normal; display:block;">
                            <input type="checkbox" id="filter-old">
                            Display old bookings
                        </label>
                        <label style="font-weight:normal; display:block; margin-top:5px;">
                            <input type="checkbox" id="toggle-localtime">
                            Show times in local time
                        </label>
                    </div>
                </div>
            </div>
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
document.addEventListener("DOMContentLoaded", function () {
    function updateTimes() {
        const now = new Date();

        // Local time + zone
        const local = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;

        // UTC time
        const utc = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', timeZone: 'UTC' });

        // Offset
        const offsetMinutes = -now.getTimezoneOffset(); 
        const absMinutes = Math.abs(offsetMinutes);
        const hours = Math.floor(absMinutes / 60);
        const minutes = absMinutes % 60;

        let relation = "";
        if (offsetMinutes === 0) {
            relation = "the same as Zulu.";
        } else if (offsetMinutes > 0) {
            relation = `${hours} hour${hours !== 1 ? "s" : ""}${minutes ? ` ${minutes} min` : ""} ahead of Zulu.`;
        } else {
            relation = `${hours} hour${hours !== 1 ? "s" : ""}${minutes ? ` ${minutes} min` : ""} behind Zulu.`;
        }

        // Conversion hint
        let conversionHint = "";
        if (offsetMinutes === 0) {
            conversionHint = "No conversion is needed.";
        } else if (offsetMinutes > 0) {
            conversionHint = `To convert any time on Bookings to your local time, simply add ${hours} hour${hours !== 1 ? "s" : ""}${minutes ? ` ${minutes} min` : ""}.`;
        } else {
            conversionHint = `To convert any time on Bookings to your local time, simply subtract ${hours} hour${hours !== 1 ? "s" : ""}${minutes ? ` ${minutes} min` : ""}.`;
        }

        document.getElementById("tz-message").innerHTML =
            `Your local time (${tz}) is ${relation}<br>${conversionHint}`;
    }

    updateTimes();
    setInterval(updateTimes, 1000);
});
document.addEventListener("DOMContentLoaded", function () {
    function updateTimes() {
        const now = new Date();

        // Local time
        const local = now.toLocaleTimeString([], { 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit' 
        });

        // UTC time
        const utc = now.toLocaleTimeString([], { 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit', 
            timeZone: 'UTC' 
        });

        document.getElementById("local-time").textContent = local;
        document.getElementById("utc-time").textContent = utc;
    }

    updateTimes();
    setInterval(updateTimes, 1000);
});
document.addEventListener("DOMContentLoaded", function () {
    function filterBookings() {
        const nowUtc = new Date(); 
        const nowDate = nowUtc.toISOString().split("T")[0]; 
        const nowMinutes = nowUtc.getUTCHours() * 60 + nowUtc.getUTCMinutes();
        const showPast = document.getElementById("filter-old").checked;

        document.querySelectorAll(".booking-entry").forEach(entry => {
            const parentCell = entry.closest("td");
            if (!parentCell) return;

            const bookingDate = parentCell.getAttribute("data-date");
            if (!bookingDate) return;

            const timeMatch = entry.innerText.match(/(\d{2}):(\d{2})\s*-\s*(\d{2}):(\d{2})/);
            if (timeMatch) {
                const [, , , toH, toM] = timeMatch.map(Number);
                const bookingEnd = toH * 60 + toM;

                // Hide if booking is in the past, unless checkbox is ticked
                if (!showPast && (bookingDate < nowDate || (bookingDate === nowDate && bookingEnd < nowMinutes))) {
                    entry.style.display = "none";
                } else {
                    entry.style.display = "";
                }
            }
        });
    }

    // Initial run + auto refresh
    filterBookings();
    setInterval(filterBookings, 60000);

    // Checkbox toggle
    document.getElementById("filter-old").addEventListener("change", filterBookings);
});
document.addEventListener("DOMContentLoaded", function () {
    const toggleLocalTime = document.getElementById("toggle-localtime");

    function updateBookingTimes() {
        const useLocal = toggleLocalTime.checked;

        document.querySelectorAll(".booking-time").forEach(span => {
            const startUtc = new Date(span.dataset.start);
            const endUtc   = new Date(span.dataset.end);

            if (useLocal) {
                let startLocal = startUtc.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                let endLocal   = endUtc.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                let tzName     = Intl.DateTimeFormat().resolvedOptions().timeZone;

                span.textContent = `${startLocal} - ${endLocal} (${tzName})`;
            } else {
                let startZulu = startUtc.toISOString().substr(11,5);
                let endZulu   = endUtc.toISOString().substr(11,5);
                span.textContent = `${startZulu} - ${endZulu}z`;
            }
        });
    }

    // Run once at page load so it's consistent with checkbox state
    updateBookingTimes();

    toggleLocalTime.addEventListener("change", updateBookingTimes);
});
</script>

@stop
