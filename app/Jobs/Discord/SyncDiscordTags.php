<?php

declare(strict_types=1);

namespace App\Jobs\Discord;

use App\Jobs\Job;
use App\Libraries\Discord;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class SyncDiscordTags extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public $tries = 3;

    public $backoff = 30;

    public $queue = 'discord';

    public function handle(Discord $discord): void
    {
        $discord->syncTagCommands();
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('sync-discord-tags'))
                ->releaseAfter(30)
                ->expireAfter(300),
        ];
    }
}
