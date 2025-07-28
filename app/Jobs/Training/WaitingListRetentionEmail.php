<?php

namespace App\Jobs\Training;

use App\Models\Training\WaitingList\WaitingListRetentionChecks;
use App\Notifications\Training\WaitinglistRetentionCheckNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WaitingListRetentionEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public WaitingListRetentionChecks $retentionCheck) {}

    private function generateToken(): string
    {
        do {
            $token = bin2hex(random_bytes(16));
        } while (WaitingListRetentionChecks::where('token', $token)->exists());

        return $token;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $oldRecord = $this->retentionCheck;
        $verifyToken = $this->generateToken();

        $record = WaitingListRetentionChecks::create([
            'waiting_list_account_id' => $oldRecord->waitingListAccount->id,
            'status' => WaitingListRetentionChecks::STATUS_PENDING,
            'token' => $verifyToken,
            'expires_at' => now()->addDays(7),
            'email_sent_at' => now(),
        ]);

        $this->retentionCheck->waitingListAccount->account->notify(new WaitinglistRetentionCheckNotification($record, $verifyToken));
    }
}
