<?php

namespace App\Console\Commands\Training;

use App\Jobs\Training\SyncTheoryResultsFromCts;
use Illuminate\Console\Command;

class SyncTheoryResultsFromCtsCommand extends Command
{
    protected $signature = 'training:sync-theory-results-from-cts';

    protected $description = 'Queue a sync of new CTS theory exam results into training_theory_results';

    public function handle(): int
    {
        SyncTheoryResultsFromCts::dispatch();

        return Command::SUCCESS;
    }
}
