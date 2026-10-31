<?php

namespace App\Jobs\Training;

use App\Jobs\Concerns\LogsJobFailure;
use App\Jobs\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Copies finished Moodle theory exam attempts for one exam level into training_theory_results.
 */
class SyncTheoryExamFromMoodle extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, LogsJobFailure, SerializesModels;

    public function __construct(
        private readonly string $exam,
        private readonly int $quizId,
    ) {}

    public function handle(): void
    {
        $moodle = config('services.moodle.database');

        $attempts = DB::table($moodle.'.mdl_quiz_attempts as a')
            ->join($moodle.'.mdl_user as u', 'u.id', '=', 'a.userid')
            ->join($moodle.'.mdl_quiz_grades as g', function ($join) {
                $join->on('g.quiz', '=', 'a.quiz')->on('g.userid', '=', 'a.userid');
            })
            ->join($moodle.'.mdl_grade_items as gi', function ($join) {
                $join->on('gi.iteminstance', '=', 'a.quiz')
                    ->where('gi.itemtype', 'mod')
                    ->where('gi.itemmodule', 'quiz')
                    ->where('gi.itemnumber', 0);
            })
            ->where('a.quiz', $this->quizId)
            ->where('a.state', 'finished')
            ->whereNotNull('a.timefinish')
            ->where('a.timefinish', '>', 0)
            ->select([
                'u.username',
                'a.timefinish',
                'g.grade',
                'gi.gradepass',
            ])
            ->get();

        $rows = [];
        $now = now();

        foreach ($attempts as $attempt) {
            $accountId = (int) $attempt->username;

            if ($accountId <= 0) {
                continue;
            }

            $rows[] = [
                'account_id' => $accountId,
                'exam' => $this->exam,
                'exam_datetime' => date('Y-m-d H:i:s', (int) $attempt->timefinish),
                'result' => (float) $attempt->grade >= (float) $attempt->gradepass ? 'P' : 'F',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows === []) {
            return;
        }

        $existingAccounts = DB::table('mship_account')
            ->whereIn('id', array_unique(array_column($rows, 'account_id')))
            ->pluck('id')
            ->all();

        $rows = array_values(array_filter(
            $rows,
            fn (array $row) => in_array($row['account_id'], $existingAccounts, true),
        ));

        if ($rows !== []) {
            DB::table('training_theory_results')->insertOrIgnore($rows);
        }

        Log::info('Theory exam results synced from Moodle', ['exam' => $this->exam, 'quiz_id' => $this->quizId, 'rows' => count($rows)]);
    }

    protected function logJobContext(): array
    {
        return ['exam' => $this->exam, 'quiz_id' => $this->quizId];
    }
}
