<?php

namespace App\Repositories\Cts;

use App\Models\Cts\Session;
use Illuminate\Database\Eloquent\Builder;

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

    public function getAllAcceptedSessionsForPositionsQuery(array $positionCallsigns, ?int $studentId = null)
    {
        return Session::query()
            ->with(['student', 'mentor'])
            ->when($studentId, function ($query, $studentId) {
                return $query->where('student_id', $studentId);
            })
            ->whereIn('position', $positionCallsigns)
            ->where('taken', 1);
    }

    public function getPastAcceptedSessionsForStudentQuery(int $studentId): Builder
    {
        return Session::query()
            ->with(['student', 'mentor'])
            ->where('student_id', $studentId)
            ->where('taken', 1)
            ->where('taken_date', '<', now());
    }

    /**
     * @return array<int, string>
     */
    public function getPositionsForStudent(int $studentId): array
    {
        return Session::query()
            ->where('student_id', $studentId)
            ->where('taken', 1)
            ->distinct()
            ->orderBy('position')
            ->pluck('position')
            ->all();
    }

    public function getUpcomingAcceptedSessionsForPositionsQuery(array $positionCallsigns): Builder
    {
        return $this->getAllAcceptedSessionsForPositionsQuery($positionCallsigns)
            ->whereNotNull('mentor_id')
            ->whereNull('filed')
            ->whereNull('cancelled_datetime')
            ->where('noShow', 0)
            ->where('taken_date', '>=', now());
    }

    public function getPendingReportSessionsForPositionsQuery(array $positionCallsigns): Builder
    {
        return $this->getAllAcceptedSessionsForPositionsQuery($positionCallsigns)
            ->whereNotNull('mentor_id')
            ->whereNull('filed')
            ->whereNull('cancelled_datetime')
            ->where('noShow', 0)
            ->where('taken_date', '<', now());
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

    public function getTotalNoShowSessionsForPositions(array $positionCallsigns, int $studentId)
    {
        return Session::whereIn('position', $positionCallsigns)
            ->where('student_id', $studentId)
            ->whereNull('cancelled_datetime')
            ->where('noShow', '=', 1)
            ->count();
    }

    public function getTotalCancelledSessionsForPositions(array $positionCallsigns, int $studentId)
    {
        return Session::where('student_id', $studentId)
            ->whereIn('position', $positionCallsigns)
            ->whereNotNull('cancelled_datetime')
            ->count();
    }
}
