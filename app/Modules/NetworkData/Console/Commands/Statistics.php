<?php

namespace App\Modules\NetworkData\Console\Commands;

use Cache;
use App\Models\Statistic;
use App\Console\Commands\Command;
use App\Modules\NetworkData\Models\Atc;

class Statistics extends Command
{
    /**
     * The console command signature.
     *
     * The name of the command, along with any expected arguments.
     *
     * @var string
     */
    protected $signature = 'networkdata:statistics
                            {startPeriod? : The period to start generating statistics from (inclusive), defaulted to yesterday.}
                            {endPeriod? : The period to stop generating statistics on (inclusive), defaulted to yesterday.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate NetworkData statistics for the given time frame.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $currentPeriod = $this->getStartPeriod();
        $this->log('Start Period: '.$currentPeriod->toDateString());

        while ($currentPeriod->lte($this->getEndPeriod())) {
            $this->log('=========== START OF CYCLE '.$currentPeriod->toDateString().' ===========');

            $this->addTotalAtcSessionsCount($currentPeriod);

            $this->log('============ END OF CYCLE '.$currentPeriod->toDateString().'  ===========');

            $currentPeriod = $currentPeriod->addDay();
        }

        $this->log('Emptying cache... ');
        Cache::forget('networkdata::statistics');
        Cache::forget('networkdata::statistics.graph');
        $this->log('Done!');

        $this->log('Completed');
    }

    /**
     * Add statistics for the total number of complete ATC sessions in this period.
     * This statistic does not specify if the session should be a UK position
     * nor does it cater for sessions that span the midnight hour.
     *
     * @param $currentPeriod
     */
    private function addTotalAtcSessionsCount($currentPeriod)
    {
        $this->log('Counting total completed ATC sessions for given day');

        try {
            $count = Atc::where('connected_at', 'LIKE', $currentPeriod->toDateString().' %')
                                ->where('disconnected_at', 'LIKE', $currentPeriod->toDateString().' %')
                                ->count();

            Statistic::setStatistic($currentPeriod->toDateString(), 'networkdata::atc.global.total', $count);

            $this->log('Done. '.$count.' total ATC sessions.');
        } catch (\Exception $e) {
            $this->log('Error: '.$e->getMessage());
            $this->sendSlackError(
                'Unable to update TOTAL ATC SESSIONS (NETWORKDATA) statistics.',
                ['Error Code' => 3]
            );
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
            $this->log('Error: '.$e->getMessage());
            $this->sendSlackError(
                'Invalid startPeriod specified.  '.$this->argument('startPeriod').' is invalid.',
                ['Error Code' => 1]
            );
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
            $this->log('Error: '.$e->getMessage());
            $this->sendSlackError(
                'Invalid endPeriod specified.  '.$this->argument('endPeriod').' is invalid.',
                ['Error Code' => 2]
            );
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
