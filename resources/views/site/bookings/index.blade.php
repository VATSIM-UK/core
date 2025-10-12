@extends('layout')

@section('content')

@php
    use Carbon\Carbon;
    use App\Libraries\Bookings;  // ← add this import

    if (is_string($date)) {
        $date = Carbon::parse($date);
    }

    $typeRowStyles = [
        'mentoring' => 'bg-blue-100 border-blue-400',
        'EX'        => 'color:red; bg-red-100 border-red-400 font-bold',
        'solo'      => 'bg-yellow-100 border-yellow-400',
        'normal'    => 'bg-gray-100 border-gray-300',
    ];

    // Current time in Zulu (UTC) – used server-side to mark past bookings
    $nowUtc = Carbon::now('UTC');
@endphp

<div class="col-md-4 col-md-offset-2">
    <div class="panel panel-ukblue">
        <div class="panel-heading">
            <i class="fa fa-cog"></i> &thinsp; Bookings Calendar
        </div>
        <div class="panel-body">
            <div class="mb-3 text-center">
                <p>
                    The bookings calendar shows the availability of our controllers for bookings.
                    You can navigate through the months using the links below.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="col-md-4">
    <div class="panel panel-ukblue">
        <div class="panel-heading">
            <i class="fa fa-cog"></i> &thinsp; Information
        </div>
        <div class="panel-body">
            <div class="mb-3 text-center">
                <p>All times on Bookings are in UTC (Zulu).</p>
                <p class="mt-2">
                    <strong>Your local time is:</strong>
                    <span id="local-time"></span> (Local)
                    <br>
                    <strong>The current Zulu time is</strong>
                    <span id="utc-time"></span> (UTC/Zulu)
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
                                    <td data-date="{{ $dayDate->format('Y-m-d') }}"
                                        class="align-top border p-2
                                            {{ $dayDate->month !== $date->month ? 'bg-gray-100' : '' }}
                                            {{ $dayDate->isToday() ? 'today-cell' : '' }}">
                                        <strong>{{ $dayDate->day }}</strong>
                                        <br>

                                        @foreach($day['bookings'] as $booking)
                                            @php
                                                $type = $booking->type ?? 'normal';
                                                $rowClass = $typeRowStyles[$type] ?? 'bg-white border-gray-200';

                                                $bookingTypeClass = '';
                                                if ($booking->isMentoring())      $bookingTypeClass = 'booking-mentoring';
                                                elseif ($booking->isEvent())      $bookingTypeClass = 'booking-event';
                                                elseif ($booking->isExam())       $bookingTypeClass = 'booking-exam';
                                                elseif ($booking->isSeminar())    $bookingTypeClass = 'booking-seminar';
                                                else                              $bookingTypeClass = 'booking';

                                                $fromTime   = Carbon::parse($booking->from)->format('H:i');
                                                $toTime     = Carbon::parse($booking->to)->format('H:i');
                                                $tooltipHtml = !$booking->isEvent()
                                                    ? view('site.bookings.partials.tooltip', compact('booking', 'dayDate', 'fromTime', 'toTime'))->render()
                                                    : null;

                                                // Normalized kind for legend filters
                                                $kind = 'normal';
                                                if     ($booking->isEvent())     $kind = 'event';
                                                elseif ($booking->isExam())      $kind = 'exam';
                                                elseif ($booking->isMentoring()) $kind = 'mentoring';
                                                elseif ($booking->isSeminar())   $kind = 'seminar';

                                                // Server-side "is past?" (UTC) via pure function
                                                $isPast = Bookings::isPastUtc($dayDate->copy(), $booking->to, $nowUtc);
                                            @endphp

                                            <div class="tooltip-container booking-entry booking-{{ strtolower($booking->type) }} {{ $rowClass }} {{ $bookingTypeClass }} {{ $isPast ? 'is-past' : '' }}"
                                                 data-kind="{{ $kind }}"
                                                 tabindex="0">
                                                {{ strtoupper($booking->position) ?? 'Booking' }}<br>

                                                <span class="booking-time"
                                                      data-start="{{ \Carbon\Carbon::parse($booking->from)->setTimezone('UTC')->toIso8601String() }}"
                                                      data-end="{{ \Carbon\Carbon::parse($booking->to)->setTimezone('UTC')->toIso8601String() }}">
                                                    {{ $fromTime }}z - {{ $toTime }}z
                                                </span>

                                                @if(!$booking->isEvent())
                                                    <div class="tooltip-content">{!! $tooltipHtml !!}</div>
                                                @endif
                                            </div>
                                        @endforeach
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

<div class="col-md-4 col-md-offset-2">
    <div class="panel panel-ukblue">
        <div class="panel-heading">
            <i class="fa fa-list"></i> &thinsp; Calendar Legend / Filter
        </div>
        <div class="panel-body">
            <div class="row">
                <!-- Legend -->
                <div class="col-xs-9 col-sm-9">
                    <table class="table table-bordered legend-table" style="font-size:14px; margin-bottom:0; width:auto;">
                        <tbody>
                            <tr>
                                <td style="width: 40px; background-color: #336633;"></td>
                                <td><strong>Booking</strong></td>
                                <td>
                                    <label><input type="checkbox" data-filter="normal" checked> Position booking</label>
                                </td>
                            </tr>
                            <tr>
                                <td style="background-color: #7429C7;"></td>
                                <td><strong>Mentoring</strong></td>
                                <td>
                                    <label><input type="checkbox" data-filter="mentoring" checked> Confirmed session</label>
                                </td>
                            </tr>
                            <tr>
                                <td style="background-color: #FFA500;"></td>
                                <td><strong>Seminar</strong></td>
                                <td>
                                    <label><input type="checkbox" data-filter="seminar" checked> Confirmed seminar</label>
                                </td>
                            </tr>
                            <tr>
                                <td style="background-color: #993300;"></td>
                                <td><strong>Exam</strong></td>
                                <td>
                                    <label><input type="checkbox" data-filter="exam" checked> Confirmed exam</label>
                                </td>
                            </tr>
                            <tr>
                                <td style="background-color: #FF0000;"></td>
                                <td><strong>Event</strong></td>
                                <td>
                                    <label><input type="checkbox" data-filter="event" checked> Event position</label>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Toggles -->
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

<div class="col-md-4">
    <div class="panel panel-ukblue">
        <div class="panel-heading">
            <i class="fa fa-info"></i> &thinsp; Booking Information
        </div>
        <div class="panel-body text-center">
            <p>
                Hover over a booking to view more information about the session.
                Displayed information may vary depending on booking type.
            </p>
        </div>
    </div>
</div>

@section('scripts')
    @vite(['resources/assets/js/bookings.js'])
@endsection

@stop
