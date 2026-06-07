<?php

namespace App\Jobs\Discord;

use App\Jobs\Job;
use App\Services\Discord\HoneypotService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class HandleHoneypotTrigger extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public $tries = 3;

    public $backoff = 30;

    public $queue = 'discord';

    public function __construct(
        private readonly string $discordUserId,
        private readonly string $discordUsername,
    ) {}

    public function handle(HoneypotService $service): void
    {
        $service->handleTrigger(
            discordUserId: $this->discordUserId,
            discordUsername: $this->discordUsername,
        );
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping($this->discordUserId))
                ->releaseAfter(30)
                ->expireAfter(300),
        ];
    }
}
