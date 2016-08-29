<?php

namespace App\Modules\Visittransfer\Console\Commands;

use App\Console\Commands\aCommand;
use App\Models\Mship\Account;
use App\Models\Mship\Account\State;
use App\Models\Statistic;
use App\Modules\Visittransfer\Models\Application;

class ApplicationsCleanup extends aCommand
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
        foreach ($this->loadAllApplications() as $application) {
            if ($application->updated_at->lt(\Carbon\Carbon::now()->subMinutes("30"))) {
                //$application->cancel();
                continue;
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
                $application->markAsUnderReview();
                continue;
            }

            dispatch(new \App\Modules\Visittransfer\Jobs\AutomatedApplicationChecks($application));
        }
    }

    private function autoAcceptApplications()
    {
        $acceptedApplications = Application::submitted()
                                           ->where("will_auto_accept", "=", 1)
                                           ->get()
                                           ->filter(function ($application) {
                                               return $application->will_auto_accept;
                                           });

        foreach ($acceptedApplications as $application) {
            $application->accept("Application was automatically accepted as per the facility settings.");
            continue;
        }
    }

    private function autoCompleteNonTrainingApplications()
    {
        $acceptedApplications = $this->loadAcceptedApplications()
                                     ->filter(function ($application) {
                                         return !$application->training_required;
                                     });

        foreach ($acceptedApplications as $application) {
            $application->complete("Application was automatically completed as there is no training requirement.");
            continue;
        }
    }

    private function loadAllApplications()
    {
        return Application::all();
    }

    private function loadAcceptedApplications()
    {
        return Application::status(Application::STATUS_ACCEPTED)->get();
    }
}
