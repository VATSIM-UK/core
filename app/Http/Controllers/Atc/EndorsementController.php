<?php

namespace App\Http\Controllers\Atc;

use App\Http\Controllers\BaseController;
use App\Models\Atc\Endorsement;
use Illuminate\Support\Facades\Redirect;

class EndorsementController extends BaseController
{
    public function getGatwickGroundIndex()
    {
        $endorsment = Endorsement::with('conditions')->where('name', 'EGKK_GND')->first();

        $hours = $endorsment->conditions->map(function ($condition) {
            return $condition->positionProgress($this->account);
        });

        if (!$this->account->qualificationAtc->isS1) {
            return Redirect::route('mship.manage.dashboard')
                ->withError('Only S1 rated controllers are eligible for a Gatwick Ground endorsement.');
        }

        return $this->viewMake('controllers.endorsements.gatwick_ground')
            ->with('endorsment', $endorsment)
            ->with('conditions', $endorsment->conditions)
            ->with('hours', $hours->all());
    }
}
