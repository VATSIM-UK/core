<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maknz\Slack\Facades\Slack;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Email;
use App\Models\Mship\Account\State;
use App\Models\Mship\Qualification as QualificationData;
use App\Models\Mship\Account\Qualification;
use App\Models\Statistic;

class SysStatisticsDaily extends aCommand
{
    /**
     * Errors:
     * 1 - Start period is invalid.
     * 2 - End period is invalid.
     * 3 - Unable to update NEW MEMBER STATISTICS
     * 4 - Unable to update CURRENT MEMBER STATISTICS
     * 5 - Unable to update NEW DIVISION MEMBER STATISTICS
     * 6 - Unable to update CURRENT DIVISION MEMBER STATISTICS
     */

    /**
     * The console command signature.
     *
     * The name of the command, along with any expected arguments.
     *
     * @var string
     */
    protected $signature = 'sys:statistics:daily
                            {startPeriod=yesterday : The date from which to start calculating statistics}
                            {endPeriod=yesterday : The date on which statistic calculation should terminate - inclusive}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create all statistics for a given day.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $currentPeriod = $this->getStartPeriod();

        while ($currentPeriod->lte($this->getEndPeriod())) {
            $this->addNewMembersStatistic($currentPeriod);
            $this->addCurrentMembersStatistic($currentPeriod);

            $this->addNewDivisionMembersStatistic($currentPeriod);
            $this->addCurrentDivisionMembersStatistic($currentPeriod);

            $currentPeriod = $currentPeriod->addDay();
        }

        $startTimestamp = $this->getStartPeriod()->toDateString();
        $endTimestamp = $this->getEndPeriod()->toDateString();
        $this->sendSlackSuccess("System Statistics for " . $startTimestamp . " to " . $endTimestamp . " have been updated.");
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
            $this->sendSlackError(1,
                "Invalid startPeriod specified.  " . $this->argument("startPeriod") . " is invalid.");
        }

        if ($startPeriod->isFuture()) {
            $startPeriod = \Carbon\Carbon::now();
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
            $this->sendSlackError(2, "Invalid endPeriod specified.  " . $this->argument("endPeriod") . " is invalid.");
        }

        if ($endPeriod->isFuture()) {
            $endPeriod = \Carbon\Carbon::now();
        }

        if ($endPeriod->gt($this->getStartPeriod())) {
            $endPeriod = $this->getStartPeriod();
        }

        return $endPeriod;
    }

    /**
     * Add a statistic for the members that were created on a given date.
     *
     * @param $currentPeriod
     */
    private function addNewMembersStatistic($currentPeriod)
    {
        try {
            $membersNew = Account::where("created_at", "LIKE", $currentPeriod->toDateString() . "%")->count();
            Statistic::setStatistic($currentPeriod->toDateString(), "members.new", $membersNew);
        } catch (\Exception $e) {
            $this->sendSlackError(3, "Unable to update NEW MEMBER statistics.");
        }
    }

    /**
     * Add a statistic for the number of current members at the end of the current day.
     *
     * @param $currentPeriod
     */
    private function addCurrentMembersStatistic($currentPeriod)
    {
        try {
            $membersCurrent = Account::where("created_at", "<=", $currentPeriod->toDateString() . " 23:59:59")->count();
            Statistic::setStatistic($currentPeriod->toDateString(), "members.current", $membersCurrent);
        } catch (\Exception $e) {
            $this->sendSlackError(3, "Unable to update CURRENT MEMBER statistics.");
        }
    }

    /**
     * Add a statistic for the number of new division members created on the current day.
     *
     * @param $currentPeriod
     */
    private function addNewDivisionMembersStatistic($currentPeriod)
    {
        try {
            $divisionCreated = State::where("state", "=", \App\Models\Mship\Account\State::STATE_DIVISION)
                                    ->where("created_at", "LIKE", $currentPeriod->toDateString() . "%")
                                    ->count();
            Statistic::setStatistic($currentPeriod->toDateString(), "members.division.new", $divisionCreated);
        } catch (\Exception $e) {
            $this->sendSlackError(3, "Unable to update NEW DIVISION MEMBER statistics.");
        }
    }

    /**
     * Add a statistic for the number of current division members created on the current day.
     *
     * @param $currentPeriod
     */
    private function addCurrentDivisionMembersStatistic($currentPeriod)
    {
        try {
            $divisionCurrent = State::where("state", "=", \App\Models\Mship\Account\State::STATE_DIVISION)
                                    ->where("created_at", "<=", $currentPeriod->toDateString() . " 23:59:59")
                                    ->count();
            Statistic::setStatistic($currentPeriod->toDateString(), "members.division.current", $divisionCurrent);
        } catch (\Exception $e) {
            $this->sendSlackError(3, "Unable to update CURRENT DIVISION MEMBER statistics.");
        }
    }
}
