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

    public function getTotalSessionsForPosition(string $positionCallsign)
    {
        return Session::where('position', $positionCallsign)
            ->where('taken_date', '>=', now()->subDays(180))
            ->whereNull('cancelled_datetime')
            ->where('noShow', '=', 0)
            ->where('session_done', '=', 1)
            ->count();
    }

    public function getTotalSessionsForPositions(array $positionCallsigns, int $studentId)
    {
        return Session::whereIn('position', $positionCallsigns)
            ->where('student_id', $studentId)
            ->whereNull('cancelled_datetime')
            ->where('noShow', '=', 0)
            ->where('session_done', '=', 1)
            ->count();
    }

    public function getTotalNoShowSessionsForPositions(array $positionCallsigns)
    {
        return Session::whereIn('position', $positionCallsigns)
            ->where('taken_date', '>=', now()->subDays(180))
            ->whereNull('cancelled_datetime')
            ->where('noShow', '=', 1)
            ->count();
    }
}
