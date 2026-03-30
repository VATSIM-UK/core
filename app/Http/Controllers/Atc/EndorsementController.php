<?php

namespace App\Http\Controllers\Atc;

use App\Http\Controllers\BaseController;
use App\Models\NetworkData\Atc;
use App\Models\Roster;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Redirect;

class EndorsementController extends BaseController
{
    const HEATHROW_S1_HOURS_TOTAL = 100;

    const HEATHROW_S1_HOURS_GATWICK = 30;

    const HEATHROW_S1_HOURS_MANCHESTER = 30;

    public function getHeathrowGroundS1Index()
    {
        if (! $this->account->fully_defined || ! $this->account->qualification_atc->isS1) {
            return Redirect::route('mship.manage.dashboard')
                ->withError('Only S1 rated controllers are eligible for a Heathrow Ground (S1) endorsement.');
        }

        // active on roster
        $onRoster = Roster::where('account_id', $this->account->id)->exists();

        $baseQuery = fn () => $this->account->networkDataAtc()
            ->isUK()
            ->where(function (Builder $builder) {
                $builder->where('facility_type', Atc::TYPE_GND)
                    ->orWhere('facility_type', Atc::TYPE_DEL);
            });

        $gatwickMinutes = (clone $baseQuery())->where('callsign', 'like', 'EGKK%')->sum('minutes_online');
        $manchesterMinutes = (clone $baseQuery())->where('callsign', 'like', 'EGCC%')->sum('minutes_online');
        $totalMinutes = $baseQuery()->sum('minutes_online');

        $gatwickHours = $gatwickMinutes / 60;
        $manchesterHours = $manchesterMinutes / 60;
        $totalHours = $totalMinutes / 60;

        $gatwickMet = $gatwickHours >= self::HEATHROW_S1_HOURS_GATWICK;
        $manchesterMet = $manchesterHours >= self::HEATHROW_S1_HOURS_MANCHESTER;
        $totalMet = $totalHours >= self::HEATHROW_S1_HOURS_TOTAL;
        $hoursMet = $gatwickMet && $manchesterMet && $totalMet;

        $this->setTitle('Heathrow Ground (S1) Endorsement');

        return $this->viewMake('controllers.endorsements.heathrow_ground_s1')
            ->with('gatwickHours', $gatwickHours)
            ->with('manchesterHours', $manchesterHours)
            ->with('totalHours', $totalHours)
            ->with('gatwickMet', $gatwickMet)
            ->with('manchesterMet', $manchesterMet)
            ->with('totalMet', $totalMet)
            ->with('hoursMet', $hoursMet)
            ->with('onRoster', $onRoster)
            ->with('conditionsMet', $hoursMet && $onRoster);
    }
}
