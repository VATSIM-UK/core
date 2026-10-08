<?php

declare(strict_types=1);

use App\Enums\PositionValidationStatusEnum;
use App\Models\Mship\Qualification;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const MENTOR_CALLSIGN = 'TFP_FLIGHT';

    private const QUALIFICATION_CODE = 'TFP';

    public function up(): void
    {
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
            ->select('position_validations.member_id', 'position_validations.changed_by', 'position_validations.date_changed')
            ->cursor();

        foreach ($mentorValidations as $validation) {
            $accountCid = DB::connection('cts')
                ->table('members')
                ->where('id', $validation->member_id)
                ->value('cid');

            if (! $accountCid || ! DB::table('mship_account')->where('id', $accountCid)->exists()) {
                continue;
            }

            $changedByCid = DB::connection('cts')
                ->table('members')
                ->where('id', $validation->changed_by)
                ->value('cid');

            $actorCid = $changedByCid && DB::table('mship_account')->where('id', $changedByCid)->exists() ? $changedByCid : $accountCid;

            $validatedAt = $validation->date_changed ? Carbon::parse($validation->date_changed) : now();

            DB::table('mentor_training_positions')->insertOrIgnore([
                'account_id' => $accountCid,
                'mentorable_type' => Qualification::class,
                'mentorable_id' => $qualificationId,
                'created_by' => $actorCid,
                'created_at' => $validatedAt,
                'updated_at' => $validatedAt,
            ]);
        }
    }

    public function down(): void
    {
        // Irreversible data migration
    }
};
