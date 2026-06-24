<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        $cts = DB::connection('cts');

        $rtsMapping = [
            'stu_seshacc' => 'session_accepted_by_mentor',
            'stu_seshcancel' => 'session_cancelled_by_mentor',
            'stu_examacc' => 'exam_accepted',
            'stu_examcancel' => 'exam_cancelled',
            'men_seshcancel' => 'mentor_session_cancelled',
        ];

        $genMapping = [
            'exam_conf' => 'examiner_exam_accepted',
            'exam_cancel' => 'examiner_exam_cancelled',
        ];

        $total = 0;
        $buffer = [];
        $chunkSize = 500;

        $flush = function () use (&$buffer, &$total) {
            if (empty($buffer)) {
                return;
            }

            DB::table('mship_email_settings')->insertOrIgnore($buffer);
            $total += count($buffer);
            $buffer = [];
        };

        $process = function (iterable $rows, array $mapping) use (&$buffer, &$total, $chunkSize, $flush) {
            foreach ($rows as $row) {
                if (! $row->account_id) {
                    continue;
                }

                foreach ($mapping as $ctsField => $emailType) {
                    if (! isset($row->$ctsField) || $row->$ctsField != 0) {
                        continue;
                    }

                    $buffer[] = [
                        'account_id' => $row->account_id,
                        'email_type' => $emailType,
                        'enabled' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if (count($buffer) >= $chunkSize) {
                        $flush();
                    }
                }
            }
        };

        $process(
            $cts->table('email_settings_rts')
                ->join('members', 'members.id', '=', 'email_settings_rts.member_id')
                ->select('members.cid as account_id', 'email_settings_rts.*')
                ->cursor(),
            $rtsMapping
        );

        $process(
            $cts->table('email_settings_gen')
                ->join('members', 'members.id', '=', 'email_settings_gen.member_id')
                ->select('members.cid as account_id', 'email_settings_gen.*')
                ->cursor(),
            $genMapping
        );

        $flush();

        Log::info("CTS email settings migrated, {$total} preferences disabled.");
    }
};
