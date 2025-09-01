<?php

namespace App\Repositories\Cts;

use App\Models\Cts\Session;

class SessionRepository
{
    public function getRecentCompletedSessionsForPosition(string $positionCallsign, int $daysConsideredRecent = 180)
    {
        return Session::where('position', $positionCallsign)
            ->where('taken_date', '>=', now()->subDays($daysConsideredRecent))
            ->whereNull('cancelled_datetime')
            ->where('noShow', '=', 0)
            ->where('session_done', '=', 1)
            ->get();

    }
}
