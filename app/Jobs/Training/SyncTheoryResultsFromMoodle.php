<?php

namespace App\Jobs\Training;

use App\Jobs\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Dispatches one SyncTheoryExamFromMoodle job per configured theory exam level.
 * Levels with a null quiz ID in services.moodle.theory_exams are skipped.
 */
class SyncTheoryResultsFromMoodle extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue;

    public function handle(): void
    {
        foreach (config('services.moodle.theory_exams', []) as $exam => $quizId) {
            if ($quizId === null) {
                continue;
            }

            SyncTheoryExamFromMoodle::dispatch((string) $exam, (int) $quizId);
        }
    }
}
