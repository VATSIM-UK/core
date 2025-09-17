<?php

namespace App\Console\Commands\Training;

use App\Jobs\Training\SendWaitingListRetentionCheck;
use App\Models\Training\WaitingList;
use Illuminate\Console\Command;

class CreateWaitingListRetentionChecks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waiting-lists:create-retention-checks';

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
        $waitingListsToCheck = WaitingList::withRetentionChecksEnabled()->get();

        foreach ($waitingListsToCheck as $waitingList) {
            $waitingListAccounts = $waitingList->waitingListAccounts()->get();

            foreach ($waitingListAccounts as $waitingListAccount) {
                $retentionChecks = $waitingListAccount->retentionChecks()->get();

                /**
                 * If the waiting list account has no retention checks and the account is older than the retention checks in months,
                 * determined by when the account was added to the waiting list,
                 * create the first retention check.
                 */
                if ($retentionChecks->isEmpty() && $waitingListAccount->created_at->diffInMonths(now()) >= $waitingList->retention_checks_months) {
                    SendWaitingListRetentionCheck::dispatch($waitingListAccount);

                    continue;
                }

                $mostRecentRetentionCheck = $retentionChecks->sortByDesc('created_at')->first();

                if (! $mostRecentRetentionCheck) {
                    // If there are no retention checks, and it hasn't been 3 months since the account was added to the waiting list,
                    // we don't need to create a new retention check.
                    continue;
                }

                /**
                 * If the most recent retention check is older than the retention checks in months,
                 * determined by when the account was added to the waiting list,
                 * create a new retention check.
                 */
                if (min($mostRecentRetentionCheck->created_at, $mostRecentRetentionCheck->email_sent_at)->diffInMonths(now()) >= $waitingList->retention_checks_months) {
                    SendWaitingListRetentionCheck::dispatch($waitingListAccount);
                }
            }
        }
    }
}
