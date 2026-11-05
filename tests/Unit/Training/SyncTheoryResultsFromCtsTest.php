<?php

namespace Tests\Unit\Training;

use App\Jobs\Training\SyncTheoryResultsFromCts;
use App\Models\Mship\Account;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SyncTheoryResultsFromCtsTest extends TestCase
{
    public function test_copies_submitted_cts_results_as_pass_or_fail_and_is_idempotent(): void
    {
        $account = Account::factory()->createQuietly();

        DB::connection('cts')->table('members')->insert([
            'old_rts_id' => 0,
            'id' => 9100001,
            'cid' => $account->id,
            'name' => 'Theory Test',
            'joined' => now(),
            'joined_div' => now(),
        ]);

        DB::connection('cts')->table('theory_results')->insert([
            [
                'exam' => 'S1',
                'student_id' => 9100001,
                'pass' => 1,
                'submitted' => 1,
                'submitted_time' => '2026-10-01 10:00:00',
                'started' => '2026-10-01 09:30:00',
            ],
            [
                'exam' => 'S2',
                'student_id' => 9100001,
                'pass' => 0,
                'submitted' => 1,
                'submitted_time' => '2026-10-02 11:00:00',
                'started' => '2026-10-02 10:30:00',
            ],
            [
                'exam' => 'S3',
                'student_id' => 9100001,
                'pass' => 1,
                'submitted' => 0,
                'submitted_time' => '2026-10-03 12:00:00',
                'started' => '2026-10-03 11:30:00',
            ],
        ]);

        (new SyncTheoryResultsFromCts)->handle();
        (new SyncTheoryResultsFromCts)->handle();

        $rows = DB::table('training_theory_results')
            ->where('account_id', $account->id)
            ->orderBy('exam')
            ->get(['exam', 'exam_datetime', 'result']);

        $this->assertCount(2, $rows);
        $this->assertSame(['S1', 'S2'], $rows->pluck('exam')->all());
        $this->assertSame('P', $rows[0]->result);
        $this->assertSame('F', $rows[1]->result);
        $this->assertSame('2026-10-01 10:00:00', $rows[0]->exam_datetime);
    }
}
