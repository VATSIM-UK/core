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

        $rows = [];

        $rtsRows = $cts->table('email_settings_rts')
            ->join('members', 'members.id', '=', 'email_settings_rts.member_id')
            ->select('members.cid as account_id', 'email_settings_rts.*')
            ->get();

        foreach ($rtsRows as $row) {
            if (! $row->account_id) {
                continue;
            }

            foreach ($rtsMapping as $ctsField => $emailType) {
                if (! isset($row->$ctsField)) {
                    continue;
                }

                if ($row->$ctsField == 0) {
                    $rows[] = [
                        'account_id' => $row->account_id,
                        'email_type' => $emailType,
                        'enabled' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        $genRows = $cts->table('email_settings_gen')
            ->join('members', 'members.id', '=', 'email_settings_gen.member_id')
            ->select('members.cid as account_id', 'email_settings_gen.*')
            ->get();

        foreach ($genRows as $row) {
            if (! $row->account_id) {
                continue;
            }

            foreach ($genMapping as $ctsField => $emailType) {
                if (! isset($row->$ctsField)) {
                    continue;
                }

                if ($row->$ctsField == 0) {
                    $rows[] = [
                        'account_id' => $row->account_id,
                        'email_type' => $emailType,
                        'enabled' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // Chunk insert to avoid memory issues
        $chunks = array_chunk($rows, 500);

        foreach ($chunks as $chunk) {
            DB::table('mship_email_settings')->insertOrIgnore($chunk);
        }

        Log::info('CTS email settings migrated, '.count($rows).' preferences disabled.');
    }
};
