<?php

namespace App\Modules\Visittransfer\Console\Commands;

use App\Console\Commands\Command;
use App\Modules\Visittransfer\Models\Application;
use App\Modules\Visittransfer\Exceptions\Application\ApplicationCannotBeExpiredException;

class ApplicationsCleanup extends Command
{
    /**
     * The console command signature.
     *
     * The name of the command, along with any expected arguments.
     *
     * @var string
     */
    protected $signature = 'visittransfer:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean-up the applications in the VT system.';

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

    private function cancelOldApplications()
    {
        foreach (Application::status(Application::STATUS_IN_PROGRESS)->get() as $application) {
            if ($application->expires_at->lt(\Carbon\Carbon::now())) {
                try {
                    $application->expire();
                } catch (ApplicationCannotBeExpiredException $e) {
                    dd($e);
                    // Do nothing here.  We don't care.  Maybe we were just late in catching them.
                }
            }
        }
    }

    private function runAutomatedChecks()
    {
        $submittedApplications = Application::submitted()
                                            ->get()
                                            ->filter(function ($application) {
                                                return !$application->is_pending_references;
                                            });

        foreach ($submittedApplications as $application) {
            if (!$application->should_perform_checks) {
                $application->markAsUnderReview('Automated checks have been disabled for this facility - requires manual checking.');
                continue;
            }

            dispatch((new \App\Modules\Visittransfer\Jobs\AutomatedApplicationChecks($application))->onQueue('med'));
        }
    }

    private function autoAcceptApplications()
    {
        $underReviewApplications = Application::underReview()
                                              ->where('will_auto_accept', '=', 1)
                                              ->get();

        foreach ($underReviewApplications as $application) {
            $application->accept('Application was automatically accepted as per the facility settings.');
            continue;
        }
    }

    private function autoCompleteNonTrainingApplications()
    {
        $acceptedApplications = Application::status(Application::STATUS_ACCEPTED)
                                           ->get()
                                           ->filter(function ($application) {
                                               return !$application->training_required;
                                           });

        foreach ($acceptedApplications as $application) {
            $application->complete('Application was automatically completed as there is no training requirement.');
            continue;
        }
    }
}
