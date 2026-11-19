<?php

namespace App\Models\Mship\Concerns;

use App\Models\Training\TheoryExamResult;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasTheoryExamResults
{
    public function theoryExamResults(): HasMany
    {
        return $this->hasMany(TheoryExamResult::class, 'account_id', 'id');
    }

    /**
     * Whether the account has a pass recorded at the given theory exam level.
     */
    public function hasPassedTheoryExam(?string $exam): bool
    {
        if (blank($exam)) {
            return false;
        }

        $results = $this->relationLoaded('theoryExamResults') ? $this->theoryExamResults : $this->theoryExamResults()->get();

        return $results->contains(fn (TheoryExamResult $result): bool => $result->exam === $exam && $result->result === TheoryExamResult::PASS);
    }
}
