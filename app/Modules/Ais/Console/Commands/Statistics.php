<?php

namespace App\Modules\Ais\Console\Commands;

use Cache;
use App\Console\Commands\Command;

class Statistics extends Command
{
    /**
     * The console command signature.
     *
     * The name of the command, along with any expected arguments.
     *
     * @var string
     */
    protected $signature = 'ais:statistics
                            {startPeriod? : The period to start generating statistics from (inclusive), defaulted to yesterday.}
                            {endPeriod? : The period to stop generating statistics on (inclusive), defaulted to yesterday.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate AIS statistics for the given time frame.';

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

            $this->log('============ END OF CYCLE '.$currentPeriod->toDateString().'  ===========');

            $currentPeriod = $currentPeriod->addDay();
        }

        $this->log('Emptying cache... ');
        Cache::forget('ais::statistics');
        Cache::forget('ais::statistics.graph');
        $this->log('Done!');

        $this->log('Completed');
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
