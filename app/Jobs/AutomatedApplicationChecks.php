<?php

namespace App\Jobs;

use App\Models\VisitTransfer\Application;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class AutomatedApplicationChecks extends Job implements ShouldQueue
{
    use SerializesModels;

    private $application = null;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * Run all of the automated checks on this application, storing the results for the administrators to review.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->application->is_submitted) {
            return;
        }

        if ($this->application->submitted_at == null) {
            return;
        }

        $this->checkCurrentRatingOver90Days();
        $this->checkCurrentRating50Hours();

        $this->application->markAsUnderReview('Automated checks have completed.');
    }

    private function checkCurrentRatingOver90Days()
    {
        $currentATCQualification = $this->application->account->qualification_atc;
        $application90DayCutOff = $this->application->submitted_at->subDays(90);

        $hasPassed = $currentATCQualification->pivot->created_at->lt($application90DayCutOff);

        $this->application->setCheckOutcome('90_day', $hasPassed);
    }

    private function checkCurrentRating50Hours()
    {
        // TODO: Figure this out.
    }
}
