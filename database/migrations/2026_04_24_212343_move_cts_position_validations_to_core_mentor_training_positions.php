<?php

use App\Enums\PositionValidationStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $mentorValidations = DB::connection('cts')
            ->table('position_validations')
            ->where('status', PositionValidationStatusEnum::Mentor->value)
            ->get();

        $now = now();

        foreach ($mentorValidations as $validation) {
            $cid = DB::connection('cts')
                ->table('members')
                ->where('id', $validation->member_id)
                ->value('cid');

            $accountId = DB::table('mship_account')
                ->where('id', $cid)
                ->value('id');

            if (! $accountId) {
                continue;
            }

            $trainingPositionId = DB::table('training_positions')
                ->where('position_id', $validation->position_id)
                ->value('id');

            if (! $trainingPositionId) {
                continue;
            }

            $changedByCid = DB::connection('cts')
                ->table('members')
                ->where('id', $validation->changed_by)
                ->value('cid');

            $changedByCid = DB::table('mship_account')->where('id', $changedByCid)->value('id');

            DB::table('mentor_training_positions')->insertOrIgnore([
                'account_id' => $accountId,
                'training_position_id' => $trainingPositionId,
                'created_by' => $changedByCid,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Irreversible data migration
    }
};
