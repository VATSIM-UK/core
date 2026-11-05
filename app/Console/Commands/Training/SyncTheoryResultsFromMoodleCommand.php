<?php

namespace App\Console\Commands\Training;

use App\Jobs\Training\SyncTheoryResultsFromMoodle;
use Illuminate\Console\Command;

class SyncTheoryResultsFromMoodleCommand extends Command
{
    protected $signature = 'training:sync-theory-results-from-moodle';

    protected $description = 'Queue a sync of new Moodle theory exam results into training_theory_results';

    public function handle(): int
    {
        SyncTheoryResultsFromMoodle::dispatch();

        return Command::SUCCESS;
    }
}
