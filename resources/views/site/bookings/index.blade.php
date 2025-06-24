@extends('layout')

@section('content')


<style>
    .calendar {
        width: 100%; /* or set a fixed width like 800px */
        max-width: 800px;
        margin: 0 auto;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .calendar th, .calendar td {
        width: 14.28%; /* 100% ÷ 7 days */
        height: 100px; /* Adjust as needed */
        text-align: center;
        vertical-align: top;
        padding: 5px;
        border: 1px solid #ccc;
        box-sizing: border-box;
    }

    .outside-month {
        background-color: #f0f0f0;
    }
</style>

        <div class="col-md-8 col-md-offset-2 ">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-cog"></i> &thinsp; Bookings Calendar
                </div>
                <div class="panel-body">
                    <p>
                        The bookings calendar shows the availability of our controllers for bookings. You can navigate through the months using the links below.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-8 col-md-offset-2 ">
            <div class="panel panel-ukblue">
                <div class="panel-heading">{{ $date->format('F Y') }}</div>
                <div class="panel-body">
                    <div style="margin-bottom: 15px;">
                        <p class="text-center">Use the links below to navigate through the months.</p>
                        <a href="{{ route('site.bookings.index', ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}">
                            ← Previous</a>
                            &nbsp;|&nbsp;
                        <a href="{{ route('site.bookings.index', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}">
                            Next →</a>
                    </div>
                    <div class="table-responsive">
                        <table border="1" cellpadding="5" cellspacing="0" class="calendar">
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
                                    <td class="align-top border p-2 {{ $day['date']->month !== $date->month ? 'bg-gray-100' : '' }}">
                                        <strong>{{ $day['date']->day }}</strong>
                                        @foreach($day['bookings'] as $booking)
                                        <div
                                        class="text-xs mt-1 cursor-pointer text-blue-600 hover:underline"
                                        title="👤 {{ $booking->member->name ?? 'Unknown' }} | ⏰ {{ \Carbon\Carbon::parse($booking->from)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->to)->format('H:i') }}"
                                        onclick="openBookingModal({{ $booking->id }})"
                                        style="font-size: 12px; margin-top: 5px;">
                                        📌 {{ strtoupper($booking->position) ?? 'Booking' }}
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

<div id="booking-modal" class="fixed inset-0 z-50 bg-black bg-opacity-50 hidden items-center justify-center">
    <div class="bg-white p-6 rounded shadow-lg w-full max-w-md relative">
        <button class="absolute top-2 right-3 text-gray-600" onclick="closeBookingModal()">✖</button>
        <h2 class="text-xl font-semibold mb-2" id="modal-position">Loading...</h2>
        <p><strong>Controller:</strong> <span id="modal-controller"></span></p>
        <p><strong>Time:</strong> <span id="modal-time"></span></p>
        <p class="mt-2"><strong>Notes:</strong> <span id="modal-notes"></span></p>
    </div>
</div>

<script>
    function openBookingModal(id) {
        document.getElementById('modal-position').innerText = 'Loading...';
        document.getElementById('modal-controller').innerText = '';
        document.getElementById('modal-time').innerText = '';
        document.getElementById('modal-notes').innerText = '';

        fetch(`/bookings/${id}`)
            .then(response => {
                if (!response.ok) throw new Error("Booking not found.");
                return response.json();
            })
            .then(booking => {
                document.getElementById('modal-position').innerText = booking.position;
                document.getElementById('modal-controller').innerText = booking.controller_name;
                document.getElementById('modal-time').innerText = formatTime(booking.from) + ' - ' + formatTime(booking.to);
                document.getElementById('modal-notes').innerText = booking.notes || 'None';
            })
            .catch(error => {
                document.getElementById('modal-position').innerText = 'Error loading booking';
                document.getElementById('modal-notes').innerText = error.message;
            });

        document.getElementById('booking-modal').classList.remove('hidden');
        document.getElementById('booking-modal').classList.add('flex');
    }

    function closeBookingModal() {
        document.getElementById('booking-modal').classList.add('hidden');
        document.getElementById('booking-modal').classList.remove('flex');
    }
    
    function formatTime(timeStr) {
        // Assumes timeStr is in "HH:mm" or "HH:mm:ss" and UTC
        const [hour, minute] = timeStr.split(':');
        // Create a UTC date
        const date = new Date(Date.UTC(1970, 0, 1, hour, minute));
        // Format as HH:mmZ
        return date.toISOString().substr(11, 5) + 'Z';  
    }
</script>
@stop