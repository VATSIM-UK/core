<?php

namespace App\Services\Admin;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PilotTrainingStats
{
    public static function sessionCount(Carbon $startDate, Carbon $endDate, string $position)
    {
        return DB::connection('cts')
            ->table('sessions')
            ->whereBetween('taken_date', [$startDate, $endDate])
            ->where('position', '=', $position)
            ->whereNull('cancelled_datetime')
            ->where('noShow', '=', 0)
            ->count();
    }

    public static function examCount(Carbon $startDate, Carbon $endDate, string $position)
    {
        $result = DB::connection('cts')
            ->table('practical_results')
            ->selectRaw("
                COUNT(*) as total,
                COALESCE(SUM(CASE WHEN result = 'P' THEN 1 ELSE 0 END), 0) as passes
            ")
            ->whereBetween('date', [$startDate, $endDate])
            ->where('exam', '=', $position)
            ->first();

        return "{$result->total} / {$result->passes}";
    }

    public static function studentCount(Carbon $startDate, Carbon $endDate)
    {
        $sessionStudents = DB::connection('cts')
            ->table('sessions')
            ->whereBetween('taken_date', [$startDate, $endDate])
            ->whereNull('cancelled_datetime')
            ->distinct()
            ->pluck('student_id');

        $examStudents = DB::connection('cts')
            ->table('practical_results')
            ->whereBetween('date', [$startDate, $endDate])
            ->distinct()
            ->pluck('student_id');

        return $sessionStudents->merge($examStudents)->unique()->count();
    }

    public static function mentorCount(Carbon $startDate, Carbon $endDate)
    {
        return DB::connection('cts')
            ->table('sessions')
            ->whereBetween('taken_date', [$startDate, $endDate])
            ->whereNull('cancelled_datetime')
            ->where('noShow', '=', 0)
            ->distinct('mentor_id')
            ->count('mentor_id');
    }

    public static function mentorStats(Carbon $startDate, Carbon $endDate, string $position)
    {
        return DB::connection('cts')
            ->table('sessions')
            ->join('members', 'sessions.mentor_id', '=', 'members.id')
            ->select('mentor_id', 'members.cid', 'members.name', DB::raw('COUNT(*) as session_count'))
            ->whereBetween('taken_date', [$startDate, $endDate])
            ->whereNull('cancelled_datetime')
            ->where('noShow', '=', 0)
            ->where('position', '=', $position)
            ->groupBy('mentor_id', 'members.cid', 'members.name')
            ->orderByDesc('session_count')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->mentor_id => [
                    'cid' => $item->cid,
                    'name' => $item->name,
                    'session_count' => $item->session_count,
                ]];
            });
    }

    public static function studentStats(Carbon $startDate, Carbon $endDate, string $position)
    {
        return DB::connection('cts')
            ->table('sessions')
            ->join('members', 'sessions.student_id', '=', 'members.id')
            ->select('student_id', 'members.cid', 'members.name', DB::raw('COUNT(*) as session_count'))
            ->whereBetween('taken_date', [$startDate, $endDate])
            ->whereNull('cancelled_datetime')
            ->where('noShow', '=', 0)
            ->where('position', '=', $position)
            ->groupBy('student_id', 'members.cid', 'members.name')
            ->orderByDesc('session_count')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->student_id => [
                    'cid' => $item->cid,
                    'name' => $item->name,
                    'session_count' => $item->session_count,
                ]];
            });
    }
}
