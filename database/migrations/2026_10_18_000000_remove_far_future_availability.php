<?php

use App\Models\Cts\Member;
use App\Models\Training\Availability;
use App\Services\Training\AvailabilityLogService;
use App\Services\Training\AvailabilityService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Remove availability that sits beyond the 31 day window students may now use.
     *
     * The cap is read from AvailabilityService so the boundary matches the frontend and
     * the availability check. Removals are written to the availability log first, so the
     * admin "Availability as of" review does not keep showing deleted slots. Revoked
     * places are reconciled too: their review page stays reachable, so their log must
     * not keep listing rows this migration removes.
     */
    public function up(): void
    {
        $lastAllowed = AvailabilityService::lastAllowedDate(now()->utc())->toDateString();

        $rows = Availability::query()
            ->where('type', 'S')
            ->where('date', '>', $lastAllowed)
            ->get();

        if ($rows->isEmpty()) {
            return;
        }

        $logService = app(AvailabilityLogService::class);

        foreach ($rows->groupBy('student_id') as $studentId => $studentRows) {
            $cid = Member::where('id', $studentId)->value('cid');

            if (! $cid) {
                Log::info('Availability cleanup: member has no account, log not reconciled', ['member_id' => $studentId]);

                continue;
            }

            $places = $logService->allTrainingPlacesForAccount((int) $cid);

            if ($places->isEmpty()) {
                Log::info('Availability cleanup: no training places to reconcile', ['cid' => $cid, 'member_id' => $studentId]);

                continue;
            }

            foreach ($studentRows as $row) {
                [$from, $to] = $row->bounds();
                $logService->recordRemoved($places, $from, $to);
            }
        }

        Availability::query()
            ->where('type', 'S')
            ->where('date', '>', $lastAllowed)
            ->delete();
    }

    public function down(): void
    {
        // The deleted rows cannot be restored.
    }
};
