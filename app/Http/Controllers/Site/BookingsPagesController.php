<?php

namespace App\Http\Controllers\Site;

use App\Models\Cts\Booking;
use App\Repositories\Cts\BookingRepository;
use Carbon\Carbon;

class BookingsPagesController extends \App\Http\Controllers\BaseController
{
    protected BookingRepository $bookingRepo;

    public function __construct(BookingRepository $bookingRepo)
    {
        $this->bookingRepo = $bookingRepo;
    }

    public function show($id)
    {
        $booking = Booking::findOrFail($id);

        return response()->json([
            'id' => $booking->id,
            'position' => $booking->position,
            'controller_name' => $booking->member_id,
            'start_time' => $booking->from,
            'end_time' => $booking->to,
        ]);
    }

    /**
     * Display the bookings calendar for a given month and year.
     *
     * @param  int|null  $year
     * @param  int|null  $month
     * @return \Illuminate\View\View
     */
    public function index($year = null, $month = null)
    {
        $date = Carbon::createFromDate($year ?? now()->year, $month ?? now()->month, 1)->startOfMonth();

        $start = $date->copy()->startOfWeek();
        $end = $date->copy()->endOfMonth()->endOfWeek();

        $calendar = [];
        $current = $start->copy();

        // Fetch Bookings for the entire Calendar Range
        $bookings = $this->bookingRepo->getBookingsBetween($start, $end);

        while ($current <= $end) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $dayBookings = $bookings->filter(function ($booking) use ($current) {
                    return $booking->date->isSameDay($current);
                });

                $week[] = [
                    'date' => $current->copy(),
                    'bookings' => $dayBookings,
                ];
                $current->addDay();
            }
            $calendar[] = $week;
        }

        $prevMonth = $date->copy()->subMonth();
        $nextMonth = $date->copy()->addMonth();

        return view('site.bookings.index', compact('calendar', 'date', 'prevMonth', 'nextMonth'));
    }
}
