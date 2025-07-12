<?php

namespace App\Console\Commands\Training;

use App\Jobs\Training\WaitingListRetentionEmail;
use App\Jobs\Training\WaitingListRetentionRemoval;
use App\Models\Training\WaitingList\WaitingListRetentionChecks as WaitingListWaitingListRetentionChecks;
use Illuminate\Console\Command;

class WaitingListRetentionChecks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waiting-lists:send-retention-checks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create jobs to send retention checks to members on the waiting lists';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $recordsToRemove = WaitingListWaitingListRetentionChecks::query()
            ->where('expires_at', '<', now())
            ->where('status', WaitingListWaitingListRetentionChecks::STATUS_PENDING)
            ->get();

        foreach ($recordsToRemove as $record) {
            WaitingListRetentionRemoval::dispatch($record);
        }

        $recodsToSend = WaitingListWaitingListRetentionChecks::query()
            ->where('email_sent_at', '<', now()->subMonths(3))
            ->get();

        foreach ($recodsToSend as $record) {
            WaitingListRetentionEmail::dispatch($record);
        }
    }
}
