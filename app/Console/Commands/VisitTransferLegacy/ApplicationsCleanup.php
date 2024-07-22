<?php

namespace App\Console\Commands\VisitTransferLegacy;

use App\Console\Commands\Command;
use App\Exceptions\VisitTransferLegacy\Application\ApplicationCannotBeExpiredException;
use App\Models\VisitTransferLegacy\Application;
use Carbon\Carbon;

class ApplicationsCleanup extends Command
{
    /**
     * The console command signature.
     *
     * The name of the command, along with any expected arguments.
     *
     * @var string
     */
    protected $signature = 'visit-transfer:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up the applications in the VT system.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->cancelOldApplications();
        $this->runAutomatedChecks();
        $this->autoAcceptApplications();
        $this->autoCompleteNonTrainingApplications();
    }

    /**
     * Cancel any applications that have exceeded their expiry time.
     */
    private function cancelOldApplications()
    {
        foreach (Application::status(Application::STATUS_IN_PROGRESS)->get() as $application) {
            if ($application->expires_at->lt(\Carbon\Carbon::now())) {
                try {
                    $application->expire();
                } catch (ApplicationCannotBeExpiredException $e) {
                    continue;
                }
            }
        }

        /* @var Application[] $applications */
        $applications = Application::status(Application::STATUS_SUBMITTED)
            ->where('references_required', '!=', 0)
            ->with('referees')
            ->get();
        foreach ($applications as $application) {
            foreach ($application->referees as $referee) {
                if (! $referee->is_submitted && $referee->contacted_at && $referee->contacted_at->addDays(14)->lt(new Carbon)) {
                    $application->lapse();

                    continue;
                }
            }
        }
    }

    /**
     * If applicable, dispatch automated checks for submitted applications, otherwise progress them.
     */
    private function runAutomatedChecks()
    {
        $submittedApplications = Application::submitted()->get()->filter(function ($application) {
            return ! $application->is_pending_references;
        });

        foreach ($submittedApplications as $application) {
            if ($application->should_perform_checks) {
                $application->setCheckOutcome('90_day', $application->check90DayQualification());
                $application->setCheckOutcome('50_hours', $application->check50Hours());

                $application->markAsUnderReview('Automated checks have completed.');
            } else {
                $application->markAsUnderReview('Automated checks have been disabled for this facility - requires manual checking.');
            }
        }
    }

    /**
     * If an application under review can be accepted automatically, accept it.
     */
    private function autoAcceptApplications()
    {
        $underReviewApplications = Application::underReview()->where('will_auto_accept', 1)->get();

        foreach ($underReviewApplications as $application) {
            $application->accept('Application was automatically accepted as per the facility settings.');
        }
    }

    /**
     * If an accepted application doesn't require training, complete it.
     */
    private function autoCompleteNonTrainingApplications()
    {
        $acceptedApplications = Application::status(Application::STATUS_ACCEPTED)->get()
            ->filter(function ($application) {
                return ! $application->training_required;
            });

        foreach ($acceptedApplications as $application) {
            $application->complete('Application was automatically completed as there is no training requirement.');
        }
    }
}
