<?php

namespace App\Jobs\Mship;

use App\Jobs\Concerns\LogsJobFailure;
use App\Jobs\Job;
use App\Models\Mship\Account;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimitedWithRedis;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncToDiscord extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, LogsJobFailure, SerializesModels;

    private Account $account;

    public $tries = 3;

    public $backoff = 60;

    public $queue = 'discord';

    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    public function handle()
    {
        $this->account->syncToDiscord();

        Log::info('Member sync completed', ['service' => 'discord', 'account_id' => $this->account->id]);
    }

    protected function logJobContext(): array
    {
        return ['account_id' => $this->getAccountId()];
    }

    public function getAccountId(): int
    {
        return $this->account->id;
    }

    public function middleware(): array
    {
        return [
            new RateLimitedWithRedis('discord-sync'),
            (new WithoutOverlapping($this->getAccountId()))
                ->releaseAfter(30) // 30s retry
                ->expireAfter(120), // auto-release after 120s
        ];
    }
}
