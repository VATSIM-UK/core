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

    public function getAreaIndex()
    {
        $endorsements = Endorsement::whereIn('name', ['LON_S_CTR', 'LON_C_CTR', 'LON_N_CTR', 'SCO_CTR'])->get();

        if ($endorsements->count() < 1) {
            return Redirect::route('mship.manage.dashboard')
                ->withError('Endorsements improperly configured');
        }

        if (! $this->account->qualificationAtc->isS3) {
            return Redirect::route('mship.manage.dashboard')
                ->withError('Only S3 rated controllers can see their C1 Training Place eligibility.');
        }

        $endorsements = $endorsements->load('conditions')->map(function ($endorsement) {
            $conditions = $endorsement->conditions->map(function ($condition) use ($endorsement) {
                return [
                    'endorsement_id' => $endorsement->id,
                    // extract the likely position name from the criterion loaded into the database.
                    'position' => str_replace('%', '_', $condition->positions[0]),
                    'required_hours' => $condition->required_hours,
                    'within_months' => $condition->within_months,
                    'progress' => round($condition->progressForUser($this->account)->sum(), 1),
                    'complete' => $condition->isMetForUser($this->account),
                ];
            });

            return [
                'name' => $endorsement->name,
                'conditions' => $conditions,
            ];
        });

        return $this->viewMake('controllers.endorsements.area')
            ->with('endorsements', $endorsements);
    }
}
