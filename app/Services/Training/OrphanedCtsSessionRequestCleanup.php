<?php

declare(strict_types=1);

namespace App\Services\Training;

use App\Models\Cts\Session as CtsSession;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Illuminate\Support\Facades\Log;

class OrphanedCtsSessionRequestCleanup
{
    /**
     * Delete untaken, unfiled CTS session requests for members who hold an active training place,
     * limited to that place's matching callsigns.
     */
    public function deleteForActiveTrainingPlaces(): int
    {
        $deleted = 0;

        TrainingPlace::query()
            ->with(['trainable', 'account'])
            ->get()
            ->each(function (TrainingPlace $trainingPlace) use (&$deleted): void {
                $deleted += $this->deleteForTrainingPlace($trainingPlace);
            });

        Log::info('Deleted orphaned CTS session requests for active training places', [
            'deleted' => $deleted,
        ]);

        return $deleted;
    }

    public function deleteForTrainingPlace(TrainingPlace $trainingPlace): int
    {
        $trainingPlace->loadMissing(['trainable', 'account']);

        $member = $trainingPlace->account?->member;

        if (! $member) {
            return 0;
        }

        $callsigns = $this->matchingCallsigns($trainingPlace);

        if ($callsigns === []) {
            return 0;
        }

        return CtsSession::query()
            ->where('student_id', $member->id)
            ->whereIn('position', $callsigns)
            ->whereNull('taken_date')
            ->where('session_done', 0)
            ->delete();
    }

    /**
     * Callsigns the automatic session-request job used for this place: every trainable
     * CTS position, plus the primary (or first trainable) callsign when it is not already included.
     *
     * @return list<string>
     */
    private function matchingCallsigns(TrainingPlace $trainingPlace): array
    {
        return collect($trainingPlace->trainableCtsPositions())
            ->push($trainingPlace->primaryCtsPosition())
            ->filter(fn ($callsign) => is_string($callsign) && trim($callsign) !== '')
            ->map(fn (string $callsign) => trim($callsign))
            ->unique()
            ->values()
            ->all();
    }
}
