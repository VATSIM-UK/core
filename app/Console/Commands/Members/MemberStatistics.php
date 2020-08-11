<?php

namespace App\Console\Commands\Members;

use App\Console\Commands\Command;
use App\Models\Mship\Account;
use App\Models\Mship\State;
use App\Models\Statistic;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Carbon\Carbon;
use DB;

class MemberStatistics extends Command
{
    /**
     * Errors:
     * 1 - Start period is invalid.
     * 2 - End period is invalid.
     * 3 - Unable to update NEW MEMBER STATISTICS
     * 4 - Unable to update CURRENT MEMBER STATISTICS
     * 5 - Unable to update NEW DIVISION MEMBER STATISTICS
     * 6 - Unable to update CURRENT DIVISION MEMBER STATISTICS.
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
                            {endPeriod=yesterday : The date on which statistic calculation should terminate - inclusive}
                            {--modules : Call statistics on any enabled modules, if available.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create all statistics for a given day.';

    protected $progressBar = null;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $daysOfStatistics = $this->getEndPeriod()->diffInDays($this->getStartPeriod()) + 1;
        $this->progressBar = $this->output->createProgressBar($daysOfStatistics);
        $this->progressBar->start();

        $currentPeriod = $this->getStartPeriod();

        while ($currentPeriod->lte($this->getEndPeriod())) {
            $this->addNewMembersStatistic($currentPeriod);
            $this->addCurrentMembersStatistic($currentPeriod);

            $this->addNewDivisionMembersStatistic($currentPeriod);
            $this->addCurrentDivisionMembersStatistic($currentPeriod);

            $currentPeriod = $currentPeriod->addDay();
            $this->progressBar->advance();
        }

        $this->progressBar->finish();
    }

    /**
     * Add a statistic for the members that were created on a given date.
     *
     * @param $currentPeriod
     */
    private function addNewMembersStatistic($currentPeriod)
    {
        try {
            $membersNew = Account::where('created_at', 'LIKE', $currentPeriod->toDateString().'%')->count();
            Statistic::setStatistic($currentPeriod->toDateString(), 'members.new', $membersNew);
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
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
            $membersCurrent = Account::where('created_at', '<=', $currentPeriod->toDateString().' 23:59:59')
                ->count();
            Statistic::setStatistic($currentPeriod->toDateString(), 'members.current', $membersCurrent);
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
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
            $divisionCreated = DB::table('mship_account_state')
                ->where('state_id', '=', State::findByCode('DIVISION')->id)
                ->where('start_at', 'LIKE', $currentPeriod->toDateString().'%')
                ->count();
            Statistic::setStatistic($currentPeriod->toDateString(), 'members.division.new', $divisionCreated);
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
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
            $divisionCurrent = DB::table('mship_account_state')
                ->where('state_id', '=', State::findByCode('DIVISION')->id)
                ->where('start_at', '<=', $currentPeriod->toDateString().' 23:59:59')
                ->count();
            Statistic::setStatistic($currentPeriod->toDateString(), 'members.division.current', $divisionCurrent);
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
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
            $startPeriod = \Carbon\Carbon::parse($this->argument('startPeriod'), 'UTC');
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
        }

        if ($startPeriod->isFuture()) {
            $startPeriod = \Carbon\Carbon::parse('yesterday', 'UTC');
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
            $endPeriod = \Carbon\Carbon::parse($this->argument('endPeriod'), 'UTC');
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
        }

        if ($endPeriod->isFuture()) {
            $endPeriod = \Carbon\Carbon::parse('yesterday', 'UTC');
        }

        if ($endPeriod->lt($this->getStartPeriod())) {
            $endPeriod = $this->getStartPeriod();
        }

        return $endPeriod;
    }
}
