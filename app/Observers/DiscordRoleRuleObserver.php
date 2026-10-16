<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\Discord\RemoveDiscordRoleFromMembers;
use App\Models\Discord\DiscordRoleRule;

class DiscordRoleRuleObserver
{
    public function deleted(DiscordRoleRule $rule): void
    {
        if (! $rule->discord_id || ! config('services.discord.token')) {
            return;
        }

        RemoveDiscordRoleFromMembers::dispatch((int) $rule->discord_id);
    }
}
