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
        foreach($this->loadAllApplications() as $application){
            if($application->updated_at->lt(\Carbon\Carbon::now()->subMinutes("30"))){
                //$application->cancel();
                continue;
            }
        }

        $this->runAutomatedChecks();
    }

    private function runAutomatedChecks(){
        $underReviewApplications = $this->loadSubmittedApplications()
                                        ->filter(function($application){
                                            return !$application->is_pending_references;
                                        });

        foreach($underReviewApplications as $application){

            if($application->facility->stage_checks){
                $application->markAsUnderReview();
                continue;
            }

            dispatch(new \App\Modules\Visittransfer\Jobs\AutomatedApplicationChecks($application));
        }
    }

    private function loadAllApplications(){
        return Application::all();
    }

    private function loadSubmittedApplications(){
        return Application::status(Application::STATUS_SUBMITTED)->get();
    }
}
