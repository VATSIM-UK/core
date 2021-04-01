<?php

namespace App\Jobs\Mship;

use App\Jobs\Job;
use App\Jobs\Middleware\RateLimited;
use App\Models\Mship\Account;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncToDiscord extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    private $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    public function handle()
    {
        $this->account->syncToDiscord();
    }

    public function middleware()
    {
        return [new RateLimited('discord_api_call', 3, 10)];
    }
}
