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
 * Copies submitted CTS theory exam attempts into training_theory_results.
 */
class SyncTheoryResultsFromCts extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, LogsJobFailure, SerializesModels;

    public function handle(): void
    {
        $results = DB::connection('cts')
            ->table('theory_results as t')
            ->join('members as m', 'm.id', '=', 't.student_id')
            ->where('t.submitted', 1)
            ->whereNotNull('t.submitted_time')
            ->where('t.submitted_time', '>', '0000-00-00 00:00:00')
            ->select(['m.cid', 't.exam', 't.submitted_time', 't.pass'])
            ->orderBy('t.id')
            ->get();

        $rows = [];
        $now = now();

        foreach ($results as $result) {
            $accountId = (int) $result->cid;

            if ($accountId <= 0) {
                continue;
            }

            $rows[] = [
                'account_id' => $accountId,
                'exam' => (string) $result->exam,
                'exam_datetime' => (string) $result->submitted_time,
                'result' => (int) $result->pass === 1 ? 'P' : 'F',
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

        Log::info('Theory exam results synced from CTS', ['rows' => count($rows)]);
    }
}
