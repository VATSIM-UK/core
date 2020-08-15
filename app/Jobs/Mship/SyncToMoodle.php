<?php

namespace App\Jobs\Mship;

use App\Jobs\Job;
use App\Models\Mship\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncToMoodle extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $account;
    public $queue = 'user_sync';

    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    public function handle()
    {
        $this->account->syncUserToMoodle();
    }
}
