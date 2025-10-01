<?php

namespace App\Repositories\Cts;

use App\Models\Cts\TheoryResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TheoryExamResultRepository
{
    public function getRecentPassedTheoryExamsOfType(string $type, int $daysConsideredRecent = 3): Collection
    {
        return TheoryResult::where('exam', $type)
            ->where('pass', 1)
            ->where('submitted_time', '>=', now()->subDays($daysConsideredRecent))
            ->get();
    }

    /**
     * Get a query for theory exam results filtered by exam levels
     */
    public function getTheoryExamHistoryQueryForLevels(Collection $examLevels): Builder
    {
        return TheoryResult::query()
            ->with('student')
            ->whereIn('exam', $examLevels);
    }
}
