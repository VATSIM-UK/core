<?php

declare(strict_types=1);

namespace App\Livewire\Training\Support;

use App\Models\Training\TrainingPlace\TrainingPlace;

final class TrainingPlaceAvailabilityGraceNotice
{
    /**
     * @param  bool  $onlyForActivePlace  When true, no copy is shown for soft-deleted training places.
     */
    public static function message(TrainingPlace $trainingPlace, bool $onlyForActivePlace = false): ?string
    {
        if ($onlyForActivePlace && $trainingPlace->trashed()) {
            return null;
        }

        if (! $trainingPlace->isWithinAvailabilityCheckGracePeriod()) {
            return null;
        }

        $hours = TrainingPlace::AVAILABILITY_CHECK_GRACE_PERIOD_HOURS;
        $endsAt = $trainingPlace->availabilityCheckGracePeriodEndsAt()->format('d/m/Y, H:i');

        return "Automated checks for CTS availability and session requests. No automated availability checks are run during the first {$hours} hours after this training place was created. Checks begin on {$endsAt}.";
    }
}
