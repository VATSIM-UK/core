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
            ->where('callsign', 'LIKE', 'EG%')
            ->where(function (Builder $builder) {
                $builder->where('facility_type', Atc::TYPE_GND)
                    ->orWhere('facility_type', Atc::TYPE_DEL);
            })
            ->sum('minutes_online');

        $totalHours = $minutesOnline / 60;
        $hoursMet = $totalHours >= self::GATWICK_HOURS_REQUIREMENT;

        return $this->viewMake('controllers.endorsements.gatwick_ground')
            ->with('totalHours', $totalHours)
            ->with('progress', ($totalHours / self::GATWICK_HOURS_REQUIREMENT) * 100)
            ->with('hoursMet', $hoursMet)
            ->with('conditionsMet', $hoursMet && $onRoster);

        // S1
        // active on roster
        // 50 hours on _GND _DEL

        //        $endorsement = PositionGroup::with('conditions')->where('name', 'Gatwick S1 (DEL/GND)')->first();
        //
        //        $hours = $endorsement->conditions->map(function ($condition) {
        //            return $condition->progressForUser($this->account);
        //        });
        //
        //        if (! $this->account->fully_defined || ! $this->account->qualificationAtc->isS1) {
        //            return Redirect::route('mship.manage.dashboard')
        //                ->withError('Only S1 rated controllers are eligible for a Gatwick Ground endorsement.');
        //        }
        //
        //        return $this->viewMake('controllers.endorsements.gatwick_ground')
        //            ->with('endorsment', $endorsement)
        //            ->with('conditions', $endorsement->conditions)
        //            ->with('hours', $hours->all());
    }

    public function getAreaIndex()
    {
        return Redirect::route('mship.manage.dashboard')
            ->withError("We're making some changes to this page, please check back later.");

        //        $positionGroups = PositionGroup::whereIn('name', ['LON_S_CTR', 'LON_C_CTR', 'LON_N_CTR', 'SCO_CTR'])->get();
        //
        //        if ($positionGroups->count() < 1) {
        //            return Redirect::route('mship.manage.dashboard')
        //                ->withError('Endorsements improperly configured');
        //        }
        //
        //        if (! $this->account->fully_defined || ! $this->account->qualificationAtc->isS3) {
        //            return Redirect::route('mship.manage.dashboard')
        //                ->withError('Only S3 rated controllers can see their C1 Training Place eligibility.');
        //        }
        //
        //        $positionGroups = $positionGroups->load('conditions')->map(function ($positionGroup) {
        //            $conditions = $positionGroup->conditions->map(function ($condition) use ($positionGroup) {
        //                return [
        //                    'position_group_id' => $positionGroup->id,
        //                    // extract the likely position name from the criterion loaded into the database.
        //                    'position' => str_replace('%', '_', $condition->positions[0]),
        //                    'required_hours' => $condition->required_hours,
        //                    'within_months' => $condition->within_months,
        //                    'progress' => round($condition->progressForUser($this->account)->sum(), 1),
        //                    'complete' => $condition->isMetForUser($this->account),
        //                ];
        //            });
        //
        //            return [
        //                'name' => $positionGroup->name,
        //                'conditions' => $conditions,
        //            ];
        //        });
        //
        //        return $this->viewMake('controllers.endorsements.area')
        //            ->with('positionGroups', $positionGroups);
    }
}
