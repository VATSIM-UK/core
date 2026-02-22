<?php

namespace App\Http\Controllers\Atc;

use App\Http\Controllers\BaseController;
use App\Services\Atc\HeathrowGroundS1EligibilityService;
use Illuminate\Support\Facades\Redirect;

class EndorsementController extends BaseController
{
    public function __construct(private HeathrowGroundS1EligibilityService $heathrowGroundS1EligibilityService)
    {
        parent::__construct();
    }

    public function getHeathrowGroundS1Index()
    {
        $eligibility = $this->heathrowGroundS1EligibilityService->getEligibilityForDisplay($this->account);

        if ($eligibility === null) {
            return Redirect::route('mship.manage.dashboard')
                ->withError('Only S1 rated controllers are eligible for a Heathrow Ground (S1) endorsement.');
        }

        $this->setTitle('Heathrow Ground (S1) Endorsement');

        return $this->viewMake('controllers.endorsements.heathrow_ground_s1')
            ->with('totalHours', $eligibility['totalHours'])
            ->with('progress', $eligibility['progress'])
            ->with('hoursMet', $eligibility['hoursMet'])
            ->with('onRoster', $eligibility['onRoster'])
            ->with('conditionsMet', $eligibility['conditionsMet']);
    }
}
