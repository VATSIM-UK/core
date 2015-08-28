<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Email;
use App\Models\Mship\Account\State;
use App\Models\Mship\Qualification as QualificationData;
use App\Models\Mship\Account\Qualification;
use App\Models\Statistic;

class StatisticsDaily extends aCommand {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'Statistics:Daily';

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
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire() {
        $period = \Carbon\Carbon::parse($this->argument("startPeriod"), "UTC");
        while ($period->lte(\Carbon\Carbon::parse($this->argument("endPeriod"), "UTC"))) {
            // Number of members created on a given date (CREATED within our database)
            $membersNew = Account::where("created_at", "LIKE", $period->toDateString() . "%")->count();
            Statistic::setStatistic($period->toDateString(), "members.new", $membersNew);

            // Number of members created before a given date.
            $membersCurrent = Account::where("created_at", "<=", $period->toDateString() . " 23:59:59")->count();
            Statistic::setStatistic($period->toDateString(), "members.current", $membersCurrent);

            // Number of division members CREATED on a given day (i.e. number of members joining our division).
            $divisionCreated = State::where("state", "=", Models\Mship\Account\State::STATE_DIVISION)
                                    ->where("created_at", "LIKE", $period->toDateString() . "%")
                                    ->count();
            Statistic::setStatistic($period->toDateString(), "members.division.new", $divisionCreated);

            // Number of division members on a given day (i.e. number of members currently within the division).
            $divisionCurrent = State::where("state", "=", Models\Mship\Account\State::STATE_DIVISION)
                                    ->where("created_at", "<=", $period->toDateString() . " 23:59:59")
                                    ->count();
            Statistic::setStatistic($period->toDateString(), "members.division.current", $divisionCurrent);

            // Next day!
            $period = $period->addDay();
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments() {
        return array(
            array("startPeriod", InputArgument::OPTIONAL, "The date period to start processing statistics from. If not passed, yesterday will be used.", \Carbon\Carbon::parse("yesterday")->toDateString()),
            array("endPeriod", InputArgument::OPTIONAL, "The date period to stop processing statistics from. If not passed, yesterday will be used.", \Carbon\Carbon::parse("yesterday")->toDateString()),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions() {
        return array(
        );
    }
}
