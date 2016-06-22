<?php

namespace App\Modules\Visittransfer\Console\Commands;

use App\Console\Commands\aCommand;
use App\Models\Mship\Account;
use App\Models\Mship\Account\State;
use App\Models\Statistic;
use App\Modules\Visittransfer\Models\Application;
use Cache;

class StatisticsDaily extends aCommand
{
    /**
     * The console command signature.
     *
     * The name of the command, along with any expected arguments.
     *
     * @var string
     */
    protected $signature = 'visittransfer:statistics:daily
                            {startPeriod? : The period to start generating statistics from (inclusive), defaulted to yesterday.}
                            {endPeriod? : The period to stop generating statistics on (inclusive), defaulted to yesterday.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate statistics for the given time frame.';

    protected $totalApplications = 0;
    protected $acceptedApplications = 0;
    protected $newApplications = 0;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $currentPeriod = $this->getStartPeriod();

        while ($currentPeriod->lte($this->getEndPeriod())) {
            $this->addTotalApplicationCount($currentPeriod);

            $this->addOpenApplicationsCount($currentPeriod);

            $this->addRejectedApplicationCount($currentPeriod);

            $this->addNewApplicationCount($currentPeriod);

            $currentPeriod = $currentPeriod->addDay();
        }

        Cache::forget("visittransfer::statistics");
        Cache::forget("visittransfer::statistics.graph");
    }

    /**
     * Add statistics for the total number of applications in the system.
     *
     * @param $currentPeriod
     */
    private function addTotalApplicationCount($currentPeriod)
    {
        try {
            $count = Application::where("created_at", "<=", $currentPeriod->toDateString() . " 23:59:59")
                                            ->count();
            $count = $this->totalApplications;

            Statistic::setStatistic($currentPeriod->toDateString(), "visittransfer::applications.total", $count);

            $this->newApplications = rand(1, 80);
            $this->totalApplications += $this->newApplications;
        } catch (\Exception $e) {
            $this->sendSlackError("Unable to update TOTAL APPLICATIONS (VISITTRANSFER) statistics.",
                ['Error Code' => 3]);
        }
    }

    /**
     * Add statistics for the total number of accepted applications in the system.
     *
     * @param $currentPeriod
     */
    private function addOpenApplicationsCount($currentPeriod)
    {
        try {
            $count = Application::where("created_at", "<=", $currentPeriod->toDateString() . " 23:59:59")
                                ->statusIn(Application::$APPLICATION_IS_CONSIDERED_OPEN)->count();
            $count = $this->acceptedApplications;

            Statistic::setStatistic($currentPeriod->toDateString(), "visittransfer::applications.open", $count);

            $this->acceptedApplications += rand(1, ceil(($this->totalApplications-$this->acceptedApplications)*0.2));
        } catch (\Exception $e) {
            $this->sendSlackError("Unable to update OPEN APPLICATIONS (VISITTRANSFER) statistics.",
                ['Error Code' => 3]);
        }
    }

    /**
     * Add statistics for the total number of applications submitted on a given day.
     *
     * @param $currentPeriod
     */
    private function addRejectedApplicationCount($currentPeriod)
    {
        try {
            $count = Application::where("created_at", "<=", $currentPeriod->toDateString() . " 23:59:59")
                                ->statusIn(Application::$APPLICATION_IS_CONSIDERED_CLOSED)->count();
            $count = $this->rejectedApplications;

            Statistic::setStatistic($currentPeriod->toDateString(), "visittransfer::applications.closed", $count);

            $this->rejectedApplications += rand(1, ceil(($this->totalApplications-$this->acceptedApplications)*0.2));
        } catch (\Exception $e) {
            $this->sendSlackError("Unable to update CLOSED APPLICATIONS (VISITTRANSFER) statistics.",
                ['Error Code' => 3]);
        }
    }

    /**
     * Add statistics for the total number of rejected applications in the system.
     *
     * @param $currentPeriod
     */
    private function addNewApplicationCount($currentPeriod)
    {
        try {
            $count = Application::where("created_at", "LIKE", $currentPeriod->toDateString() . " %")->count();
            $count = $this->newApplications;

            Statistic::setStatistic($currentPeriod->toDateString(), "visittransfer::applications.new", $count);
        } catch (\Exception $e) {
            $this->sendSlackError("Unable to update ACCEPTED APPLICATIONS (VISITTRANSFER) statistics.",
                ['Error Code' => 3]);
        }
    }

    /**
     * Get the start period from the arguments passed.
     *
     * This will also validate those arguments.
     *
     * @return Carbon
     */
    private function getStartPeriod()
    {
        try {
            $startPeriod = \Carbon\Carbon::parse($this->argument("startPeriod"), "UTC");
        } catch (\Exception $e) {
            $this->sendSlackError(
                "Invalid startPeriod specified.  " . $this->argument("startPeriod") . " is invalid.",
                ['Error Code' => 1]
            );
        }

        if ($startPeriod->isFuture()) {
            $startPeriod = \Carbon\Carbon::parse("yesterday", "UTC");
        }

        return $startPeriod;
    }

    /**
     * Get the end period from the arguments passed.
     *
     * This will also validate those arguments.
     *
     * @return Carbon
     */
    private function getEndPeriod()
    {
        try {
            $endPeriod = \Carbon\Carbon::parse($this->argument("endPeriod"), "UTC");
        } catch (\Exception $e) {
            $this->sendSlackError("Invalid endPeriod specified.  " . $this->argument("endPeriod") . " is invalid.",
                ['Error Code' => 2]);
        }

        if ($endPeriod->isFuture()) {
            $endPeriod = \Carbon\Carbon::parse("yesterday", "UTC");
        }

        if ($endPeriod->lt($this->getStartPeriod())) {
            $endPeriod = $this->getStartPeriod();
        }

        return $endPeriod;
    }
}
