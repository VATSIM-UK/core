<?php

namespace App\Repositories\Cts;

use App\Models\Cts\TheoryResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TheoryExamResultRepository
{
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
