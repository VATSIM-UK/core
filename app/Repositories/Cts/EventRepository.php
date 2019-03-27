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
}
