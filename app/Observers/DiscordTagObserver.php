<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\Discord\SyncDiscordTags;
use App\Models\Discord\DiscordTag;

class DiscordTagObserver
{
    public function created(DiscordTag $discordTag): void
    {
        SyncDiscordTags::dispatch();
    }

    public function updated(DiscordTag $discordTag): void
    {
        SyncDiscordTags::dispatch();
    }

    public function deleted(DiscordTag $discordTag): void
    {
        SyncDiscordTags::dispatch();
    }
}
