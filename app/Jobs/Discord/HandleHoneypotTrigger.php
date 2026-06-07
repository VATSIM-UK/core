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

    /**
     * @param  string  $discordUserId  Snowflake of the Discord user who triggered the honeypot
     * @param  string  $discordUsername  Username (for logging if the account isn't linked)
     * @param  string  $messageContent  Content of the message (for the discipline note)
     */
    public function __construct(
        private readonly string $discordUserId,
        private readonly string $discordUsername,
        private readonly string $messageContent,
    ) {}

    public function handle(HoneypotService $service): void
    {
        $service->handleTrigger(
            discordUserId: $this->discordUserId,
            discordUsername: $this->discordUsername,
            messageContent: $this->messageContent,
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
