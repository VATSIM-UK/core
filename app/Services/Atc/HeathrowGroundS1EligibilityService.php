<?php

namespace App\Services\Atc;

use App\Models\Mship\Account;
use App\Models\NetworkData\Atc;
use App\Models\Roster;
use Illuminate\Database\Eloquent\Builder;

class HeathrowGroundS1EligibilityService
{
    private const HOURS_REQUIREMENT = 50;

    /**
     * @return array{totalHours: float, progress: float|int, hoursMet: bool, onRoster: bool, conditionsMet: bool}
     */
    public function getEligibility(Account $account): array
    {
        $onRoster = Roster::where('account_id', $account->id)->exists();

        $minutesOnline = $account->networkDataAtc()
            ->isUK()
            ->where(function (Builder $builder) {
                $builder->where('facility_type', Atc::TYPE_GND)
                    ->orWhere('facility_type', Atc::TYPE_DEL);
            })
            ->sum('minutes_online');

        $totalHours = $minutesOnline / 60;
        $hoursMet = $totalHours >= self::HOURS_REQUIREMENT;

        return [
            'totalHours' => $totalHours,
            'progress' => ($totalHours / self::HOURS_REQUIREMENT) * 100,
            'hoursMet' => $hoursMet,
            'onRoster' => $onRoster,
            'conditionsMet' => $hoursMet && $onRoster,
        ];
    }
}
