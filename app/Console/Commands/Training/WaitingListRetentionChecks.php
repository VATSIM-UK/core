<?php

namespace App\Console\Commands\Training;

use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListRetentionChecks as WaitingListWaitingListRetentionChecks;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
            ->where('status', '==', WaitingListWaitingListRetentionChecks::STATUS_PENDING)
            ->get();

        foreach ($recordsToRemove as $record) {
            $record->status = WaitingListWaitingListRetentionChecks::STATUS_EXPIRED;
            $record->removal_actioned_at = now();
            $record->save();

            $waitingListAccount = WaitingList::findWaitingListAccount($record->waiting_list_account_id);
            if ($waitingListAccount) {
                WaitingList::removeAccountFromWaitingList($waitingListAccount->account, 'Expired retention check');
            }

        $recodsToSend = WaitingListWaitingListRetentionChecks::query()
            ->where('email_sent_at', '<', now()->subMonths(3))
            ->get();

        foreach ($recodsToSend as $record) {
            $verifyToken = bin2hex(random_bytes(16));
            // TODO: send email

            $record->status = WaitingListWaitingListRetentionChecks::STATUS_PENDING;
            $record->token = $verifyToken;
            $record->expires_at = now()->addDays(7);
            $record->email_sent_at = now();
            $record->save();
        }
    }
}
