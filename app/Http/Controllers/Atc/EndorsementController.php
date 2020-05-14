<?php

namespace App\Http\Controllers\Atc;

use App\Http\Controllers\BaseController;
use App\Models\Atc\Endorsement;
use Illuminate\Support\Facades\Redirect;

class EndorsementController extends BaseController
{
    public function getGatwickGroundIndex()
    {
        $endorsement = Endorsement::with('conditions')->where('name', 'EGKK_GND')->first();

        $hours = $endorsement->conditions->map(function ($condition) {
            return $condition->progressForUser($this->account);
        });

        if (! $this->account->qualificationAtc->isS1) {
            return Redirect::route('mship.manage.dashboard')
                ->withError('Only S1 rated controllers are eligible for a Gatwick Ground endorsement.');
        }

        return $this->viewMake('controllers.endorsements.gatwick_ground')
            ->with('endorsment', $endorsement)
            ->with('conditions', $endorsement->conditions)
            ->with('hours', $hours->all());
    }
}
