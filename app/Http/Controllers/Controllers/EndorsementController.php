<?php

namespace App\Http\Controllers\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Redirect;

class EndorsementController extends \App\Http\Controllers\BaseController
{
    public function getGatwickGroundIndex()
    {
        $groupone = $this->account->networkDataAtc()
            ->withCallsignIn(["EGPF_%","EGBB_%","EGGD_%","EGGW_%"])
            ->whereBetween('connected_at', [Carbon::now()->subMonth(3), Carbon::now()])
            ->get()
            ->sum('minutes_online');
        $g1 = round($groupone / 60, 1);

        $grouptwo = $this->account->networkDataAtc()
            ->withCallsignIn(["EGPF_%","EGBB_%","EGGD_%","EGGW_%"])
            ->whereBetween('connected_at', [Carbon::now()->subMonth(3), Carbon::now()])->get()
            ->sum('minutes_online');
        $g2 = round($grouptwo / 60, 1);

        $groupthree = $this->account->networkDataAtc()
            ->withCallsignIn(["EGJJ_%","EGAA_%","EGNT_%","EGNX_%"])
            ->whereBetween('connected_at', [Carbon::now()->subMonth(3), Carbon::now()])
            ->get()
            ->sum('minutes_online');
        $g3 = round($groupthree / 60, 1);

        if ($this->account->qualificationAtc->isOBS) {
            return Redirect::back()
                ->withError('Only S1 rated controllers are eligible for a Gatwick Ground endorsement.');
        } elseif(!$this->account->qualificationAtc->isS1) {
            return Redirect::back()
                ->withError('You hold a controller rating above S1 and do not require an endorsement to control at Gatwick.');
        }

        return $this->viewMake('controllers.endorsements.gatwick_ground')
            ->with('groupone', $g1)
            ->with('grouptwo', $g2)
            ->with('groupthree', $g3)
            ->with('divisionmember', $this->account->primary_state->isDivision);
    }
}
