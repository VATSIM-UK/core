<?php

namespace App\Http\Controllers\Atc;

use App\Models\Atc\Endorsement;
use Carbon\Carbon;
use Redirect;

class EndorsementController extends \App\Http\Controllers\BaseController
{
    public function getGatwickGroundIndex()
    {
        $requirements = Endorsement::where('endorsement', '=', 'EGKK_GND')->get();

        $hours = $requirements->map(function ($r) {
            $data = $this->account->networkDataAtc()
                ->withCallsignIn(json_decode($r->required_airfields))
                ->whereBetween('connected_at', [Carbon::now()->subMonth($r->hours_months), Carbon::now()])
                ->get(['minutes_online', 'callsign'])
                ->mapToGroups(function ($item) {
                    return [substr($item['callsign'], 0, 4) => ($item['minutes_online'] / 60)];
                })->transform(function ($item) {
                    return $item->sum();
                })->sortByDesc(function ($value, $key) {
                    return $value;
                });

            return $data;
        });

        if (!$this->account->qualificationAtc->isS1) {
            return Redirect::route('mship.manage.dashboard')
                ->withError('Only S1 rated controllers are eligible for a Gatwick Ground endorsement.');
        }

        return $this->viewMake('controllers.endorsements.gatwick_ground')
            ->with('requirements', $requirements)
            ->with('hours', $hours->all());
    }
}
