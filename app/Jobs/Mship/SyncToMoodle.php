<?php

namespace App\Jobs\Mship;

use App\Jobs\Concerns\LogsJobFailure;
use App\Jobs\Job;
use App\Models\Mship\Account;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncToMoodle extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, LogsJobFailure, SerializesModels;

    private $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    public function handle()
    {
        $this->account->syncUserToMoodle();

        Log::info('Member sync completed', ['service' => 'moodle', 'account_id' => $this->account->id]);
    }

    protected function logJobContext(): array
    {
        return ['account_id' => $this->account->id];
    }
}
