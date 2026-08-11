<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    private const REJECT_REASON = 'Rejected automatically: the controller was not rated as a controller at the time of this submission and therefore cannot be the correct recipient.';

    public function up(): void
    {
        $atcFormId = DB::table('mship_feedback_forms')->where('slug', 'atc')->value('id');

        if (! $atcFormId) {
            Log::info('No ATC Feedback form found, data backfill could not be conducted');

            return;
        }

        [$updated, $rejected] = DB::transaction(function () use ($atcFormId) {
            $updated = 0;
            $rejected = 0;

            $feedbackRecords = DB::table('mship_feedback')
                ->where('form_id', $atcFormId)
                ->whereNull('account_atc_qualification_id')
                ->whereNull('deleted_at')
                ->get(['id', 'account_id', 'created_at']);

            foreach ($feedbackRecords as $feedback) {
                $qualificationId = $this->atcQualificationIdAt($feedback->account_id, $feedback->created_at);

                if ($qualificationId) {
                    DB::table('mship_feedback')
                        ->where('id', $feedback->id)
                        ->update(['account_atc_qualification_id' => $qualificationId]);
                    $updated++;
                } else {
                    DB::table('mship_feedback')
                        ->where('id', $feedback->id)
                        ->update(['deleted_at' => now(), 'reject_reason' => self::REJECT_REASON]);
                    $rejected++;
                }
            }

            return [$updated, $rejected];
        });

        Log::info('Backfilled ATC feedback ratings', [
            'updated' => $updated,
            'rejected' => $rejected,
        ]);
    }

    public function down(): void
    {
        // Data migration so non-reversable
    }

    private function atcQualificationIdAt(int $accountId, ?string $at): ?int
    {
        return DB::table('mship_account_qualification as aq')
            ->join('mship_qualification as q', 'q.id', '=', 'aq.qualification_id')
            ->where('aq.account_id', $accountId)
            ->where('q.type', 'atc')
            ->where('q.vatsim', '>', 1)
            ->where(function ($query) use ($at) {
                $query->whereNull('aq.created_at')
                    ->orWhere('aq.created_at', '<=', $at);
            })
            ->orderByDesc('q.vatsim')
            ->value('q.id');
    }
};
