<?php

namespace App\Repositories\Cts;

use App\Models\Cts\Event;
use Carbon\Carbon;

class EventRepository
{
    public function getTodaysEvents()
    {
        $bookings = Event::where('date', '=', Carbon::now()->toDateString())
            ->orderBy('from')
            ->get();

        return $bookings;
    }

    public function getNextEvent()
    {
        return Event::where('date', '>=', Carbon::now()->toDateString())
            ->where('gone', '=', 0)
            ->orderBy('date')
            ->orderBy('from')
            ->first();
    }
}
