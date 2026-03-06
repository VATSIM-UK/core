<?php

namespace App\Services\Training;

use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Services\Training\DTO\ManualAtcUpgradeResult;
use Carbon\CarbonImmutable;

class ManualAtcUpgradeService
{
    public static function getNextAtcQualification(Account $account): ?Qualification
    {
        $currentMax = $account->qualifications()
            ->where('type', 'atc')
            ->max('vatsim');

        return Qualification::query()
            ->where('type', 'atc')
            ->where('vatsim', '>', ($currentMax ?? 0))
            ->orderBy('vatsim')
            ->first();
    }

    public static function hasAdministrativeRating(Account $account): bool
    {
        return $account->qualifications()
            ->whereIn('type', ['training_atc', 'admin'])
            ->exists();
    }

    public static function awardNextAtcQualification(Account $account, CarbonImmutable $awardedOn, int $writerId): ?Qualification
    {
        return self::awardNextAtcQualificationResult($account, $awardedOn, $writerId)->qualification;
    }

    public static function awardNextAtcQualificationResult(Account $account, CarbonImmutable $awardedOn, int $writerId): ManualAtcUpgradeResult
    {
        $qualification = self::getNextAtcQualification($account);

        if (! $qualification) {
            return ManualAtcUpgradeResult::noUpgradeAvailable();
        }

        self::persistQualificationAward($account, $qualification, $awardedOn, $writerId);

        return ManualAtcUpgradeResult::upgraded($qualification);
    }

    private static function persistQualificationAward(Account $account, Qualification $qualification, CarbonImmutable $awardedOn, int $writerId): void
    {
        $account->addQualification($qualification);
        $account->qualifications()->updateExistingPivot($qualification->getKey(), [
            'created_at' => $awardedOn,
            'updated_at' => $awardedOn,
        ]);

        $account->addNote('training', sprintf(
            'Manual ATC rating upgrade processed in VATSIM UK systems: assigned %s with awarded date %s.',
            $qualification->name_long,
            $awardedOn->toDateString(),
        ),
            $writerId,
        );
    }
}
