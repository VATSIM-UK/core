<?php

namespace App\Http\Controllers\Site;

use Carbon\Carbon;

class BookingsPagesController extends \App\Http\Controllers\BaseController
{
    public function index($year = null, $month = null)
    {
        $date = Carbon::createFromDate($year ?? now()->year, $month ?? now()->month, 1)->startOfMonth();

        $start = $date->copy()->startOfWeek();
        $end = $date->copy()->endOfMonth()->endOfWeek();

        $calendar = [];
        $current = $start->copy();

        while ($current <= $end) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $week[] = $current->copy();
                $current->addDay();
            }
            $calendar[] = $week;
        }

        $prevMonth = $date->copy()->subMonth();
        $nextMonth = $date->copy()->addMonth();

        return view('site.bookings.calendar', compact('calendar', 'date', 'prevMonth', 'nextMonth'));
    }
}
