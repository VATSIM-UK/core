<?php

namespace App\Jobs\Mship;

use App\Jobs\Job;
use App\Models\Mship\Account;
use App\Jobs\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SyncToDiscord extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    private $account;
    public $queue = 'discord';

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
        return [new RateLimited('discord_api_call')];
    }
}
