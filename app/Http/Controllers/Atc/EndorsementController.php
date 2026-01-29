<?php

namespace App\Http\Controllers\Atc;

use App\Http\Controllers\BaseController;
use App\Models\Atc\PositionGroup;
use App\Models\NetworkData\Atc;
use App\Models\Roster;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Redirect;

class EndorsementController extends BaseController
{
    const GATWICK_HOURS_REQUIREMENT = 50;

    const HEATHROW_S1_HOURS_REQUIREMENT = 50;

    const GATWICK_ENDORSEMENT_NAME = 'Gatwick S1 (DEL/GND)';

    public function getGatwickGroundIndex()
    {
        if (! $this->account->fully_defined || ! $this->account->qualification_atc->isS1) {
            return Redirect::route('mship.manage.dashboard')
                ->withError('Only S1 rated controllers are eligible for a Gatwick Ground endorsement.');
        }

        // active on roster
        $onRoster = Roster::where('account_id', $this->account->id)->exists();

        // 50 hours on _GND or _DEL
        $minutesOnline = $this->account->networkDataAtc()
            ->isUK()
            ->where(function (Builder $builder) {
                $builder->where('facility_type', Atc::TYPE_GND)
                    ->orWhere('facility_type', Atc::TYPE_DEL);
            })
            ->sum('minutes_online');

        $totalHours = $minutesOnline / 60;
        $hoursMet = $totalHours >= self::GATWICK_HOURS_REQUIREMENT;

        $this->setTitle('Gatwick Ground Endorsement');

        return $this->viewMake('controllers.endorsements.gatwick_ground')
            ->with('totalHours', $totalHours)
            ->with('progress', ($totalHours / self::GATWICK_HOURS_REQUIREMENT) * 100)
            ->with('hoursMet', $hoursMet)
            ->with('onRoster', $onRoster)
            ->with('conditionsMet', $hoursMet && $onRoster);
    }

    public function getHeathrowGroundS1Index()
    {
        if (! $this->account->fully_defined || ! $this->account->qualification_atc->isS1) {
            return Redirect::route('mship.manage.dashboard')
                ->withError('Only S1 rated controllers are eligible for a Heathrow Ground (S1) endorsement.');
        }

        // active on roster
        $onRoster = Roster::where('account_id', $this->account->id)->exists();

        $egkkEndorsement = $this->account->endorsements()->where(function (Builder $builder) {
            $builder->whereHasMorph('endorsable', [PositionGroup::class], function (Builder $builder) {
                $builder->where('name', self::GATWICK_ENDORSEMENT_NAME);
            });
        })->first();

        $hasEgkkEndorsement = (bool) $egkkEndorsement;

        $minutesOnline = 0.0;

        // 50 hours on EGKK_GND or EGKK_DEL
        // AFTER getting an EGKK endorsement
        if ($hasEgkkEndorsement) {
            $minutesOnline = $this->account->networkDataAtc()
                ->isUK()
                ->where('callsign', 'LIKE', 'EGKK_%')
                ->where(function (Builder $builder) {
                    $builder->where('facility_type', Atc::TYPE_GND)
                        ->orWhere('facility_type', Atc::TYPE_DEL);
                })
            ->where('connected_at', '>=', $egkkEndorsement->created_at)
            ->sum('minutes_online');
        }

        $totalHours = $minutesOnline / 60;
        $hoursMet = $totalHours >= self::HEATHROW_S1_HOURS_REQUIREMENT;

        $this->setTitle('Heathrow Ground (S1) Endorsement');

        return $this->viewMake('controllers.endorsements.heathrow_ground_s1')
            ->with('totalHours', $totalHours)
            ->with('progress', ($totalHours / self::HEATHROW_S1_HOURS_REQUIREMENT) * 100)
            ->with('hoursMet', $hoursMet)
            ->with('onRoster', $onRoster)
            ->with('hasEgkkEndorsement', $hasEgkkEndorsement)
            ->with('conditionsMet', $hoursMet && $onRoster);
    }
}
