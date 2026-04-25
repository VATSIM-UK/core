<?php

use App\Enums\PositionValidationStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $pilotMap = [
            'P1_MENTOR' => 'PPL',
            'P2_MENTOR' => 'IR',
            'P3_MENTOR' => 'CMEL',
        ];

        $mentorValidations = DB::connection('cts')
            ->table('position_validations')
            ->where('status', PositionValidationStatusEnum::Mentor->value)
            ->cursor();

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

            $changedByCid = DB::connection('cts')
                ->table('members')
                ->where('id', $validation->changed_by)
                ->value('cid');

            $actorId = DB::table('mship_account')->where('id', $changedByCid)->value('id');

            $position = DB::connection('cts')
                ->table('positions')
                ->where('id', $validation->position_id)
                ->first();

            if (! $position) {
                continue;
            }

            $mentorableType = null;
            $mentorableId = null;

            if (array_key_exists($position->callsign, $pilotMap)) {
                $mentorableType = App\Models\Mship\Qualification::class;
                $mentorableId = DB::table('mship_qualification')
                    ->where('code', $pilotMap[$position->callsign])
                    ->value('id');
            } else {
                $mentorableType = App\Models\Training\TrainingPosition\TrainingPosition::class;

                $mentorableId = DB::table('training_positions')
                    ->whereJsonContains('cts_positions', $position->callsign)
                    ->value('id');
            }

            if (! $mentorableId) {
                continue;
            }

            DB::table('mentor_training_positions')->insertOrIgnore([
                'account_id' => $accountId,
                'mentorable_type' => $mentorableType,
                'mentorable_id' => $mentorableId,
                'created_by' => $actorId,
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
