<?php

namespace App\Jobs\Discord;

use App\Jobs\Concerns\LogsJobFailure;
use App\Jobs\Job;
use App\Libraries\Discord;
use App\Models\Mship\Account;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

/**
 * Discord has no "remove this role from everyone" endpoint, so this walks every
 * Discord-linked account and removes the role from each (idempotent — Discord
 * accepts the remove even when the member does not hold the role).
 */
class RemoveDiscordRoleFromMembers extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, LogsJobFailure, SerializesModels;

    public int $timeout = 900;

    public int $tries = 1;

    public function __construct(private int $roleId) {}

    public function handle(): void
    {
        $discord = app(Discord::class);

        Account::query()
            ->whereNotNull('discord_id')
            ->chunkById(200, function ($accounts) use ($discord) {
                foreach ($accounts as $account) {
                    $discord->removeRoleById($account, $this->roleId);
                }
            });
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping('remove-discord-role-'.$this->roleId))->dontRelease()];
    }

    public function getRoleId(): int
    {
        return $this->roleId;
    }

    protected function logJobContext(): array
    {
        return ['discord_role_id' => $this->roleId];
    }
}
