<?php

namespace App\Modules\Visittransfer\Console\Commands;

use App\Models\Mship\Account;
use App\Models\Mship\Account\State;
use App\Models\Statistic;
use App\Modules\Visittransfer\Models\Application;

class CleanupApplications extends aCommand
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
    }

    private function loadAllApplications(){
        return Application::all();
    }
}
