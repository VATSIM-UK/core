<?php

use App\Models\Cts\Availability;
use App\Models\Cts\Member;
use App\Services\Training\AvailabilityLogService;
use App\Services\Training\AvailabilityService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Remove availability that sits beyond the 31 day window students may now use.
     *
     * The cap is read from AvailabilityService so the boundary matches the frontend and
     * the availability check. Removals are written to the availability log first, so the
     * admin "Availability as of" review does not keep showing deleted slots.
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
                continue;
            }

            $places = $logService->activeTrainingPlacesForAccount((int) $cid);

            if ($places->isEmpty()) {
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
