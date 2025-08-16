<?php

namespace App\Console\Commands\Training;

use App\Jobs\Training\ActionWaitingListRetentionCheckRemoval;
use App\Services\Training\WaitingListRetentionChecks as WaitingListRetentionChecksService;
use Illuminate\Console\Command;

class CheckForExpiredWaitingListRetentionChecks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waiting-lists:check-for-expired-retention-checks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expired waiting list retention checks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredRetentionChecks = WaitingListRetentionChecksService::getExpiredChecks(now());

        foreach ($expiredRetentionChecks as $retentionCheck) {
            ActionWaitingListRetentionCheckRemoval::dispatch($retentionCheck);
        }

        return Command::SUCCESS;
    }
}
