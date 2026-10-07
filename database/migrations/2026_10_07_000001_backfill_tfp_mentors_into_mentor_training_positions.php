<?php

declare(strict_types=1);

use App\Enums\PositionValidationStatusEnum;
use App\Models\Mship\Qualification;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const MENTOR_CALLSIGN = 'TFP_FLIGHT';

    private const QUALIFICATION_CODE = 'TFP';

    public function up(): void
    {
        $now = now();

        $qualificationId = DB::table('mship_qualification')
            ->where('code', self::QUALIFICATION_CODE)
            ->value('id');

        if (! $qualificationId) {
            return;
        }

        $mentorValidations = DB::connection('cts')
            ->table('position_validations')
            ->join('positions', 'positions.id', '=', 'position_validations.position_id')
            ->where('positions.callsign', self::MENTOR_CALLSIGN)
            ->where('position_validations.status', PositionValidationStatusEnum::Mentor->value)
            ->select('position_validations.member_id', 'position_validations.changed_by')
            ->cursor();

        foreach ($mentorValidations as $validation) {
            $accountId = DB::connection('cts')
                ->table('members')
                ->where('id', $validation->member_id)
                ->value('cid');

            if (! $accountId || ! DB::table('mship_account')->where('id', $accountId)->exists()) {
                continue;
            }

            $changedByCid = DB::connection('cts')
                ->table('members')
                ->where('id', $validation->changed_by)
                ->value('cid');

            $actorId = $changedByCid && DB::table('mship_account')->where('id', $changedByCid)->exists() ? $changedByCid : $accountId;

            DB::table('mentor_training_positions')->insertOrIgnore([
                'account_id' => $accountId,
                'mentorable_type' => Qualification::class,
                'mentorable_id' => $qualificationId,
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
