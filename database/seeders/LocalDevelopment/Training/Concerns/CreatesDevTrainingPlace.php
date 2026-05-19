<?php

declare(strict_types=1);

namespace Database\Seeders\LocalDevelopment\Training\Concerns;

use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Services\Training\TrainingPlaceService;
use Database\Seeders\LocalDevelopment\Training\DevTrainingFoundation;
use RuntimeException;

/**
 * Creates ad-hoc training places via {@see TrainingPlaceService} (runs observers / CTS validations).
 *
 * @see database/seeders/LocalDevelopment/README.md
 */
trait CreatesDevTrainingPlace
{
    protected function createDevTrainingPlace(
        Account $account,
        string $callsign,
        string $reason = 'Local development seed data.',
    ): TrainingPlace {
        $trainingPosition = DevTrainingFoundation::$trainingPositionsByCallsign[$callsign] ?? null;
        $staff = DevTrainingFoundation::$staff;

        if ($trainingPosition === null) {
            throw new RuntimeException("Training position for callsign [{$callsign}] has not been seeded.");
        }

        if ($staff === null) {
            throw new RuntimeException('Staff persona has not been seeded.');
        }

        $existing = TrainingPlace::query()
            ->where('account_id', $account->id)
            ->where('training_position_id', $trainingPosition->id)
            ->whereNull('deleted_at')
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        return app(TrainingPlaceService::class)->createAdhocTrainingPlace(
            $account,
            $trainingPosition,
            $reason,
            $staff,
        );
    }
}
