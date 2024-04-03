<?php

namespace App\Repositories\Cts;

use App\Models\Cts\PracticalResult;
use Illuminate\Support\Collection;

class ExamResultRepository
{
    public function getRecentPassedExamsOfType(string $type, int $daysConsideredRecent = 3): Collection
    {
        return PracticalResult::where('result', PracticalResult::PASSED)
            ->where('exam', $type)
            ->where('date', '>=', now()->subDays($daysConsideredRecent))
            ->get();
    }
}
