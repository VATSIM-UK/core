<?php

namespace App\Jobs\Discord;

use App\Jobs\Concerns\LogsJobFailure;
use App\Jobs\Job;
use App\Libraries\Discord;
use App\Models\Mship\Account;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Discord has no "remove this role from everyone" endpoint, so this walks every
 * Discord-linked account and removes the role from each (idempotent — Discord
 * accepts the remove even when the member does not hold the role). It processes
 * one chunk per job and dispatches the next, keeping each job short enough to
 * finish well within the queue retry_after (90s).
 */
class RemoveDiscordRoleFromMembers extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, LogsJobFailure, SerializesModels;

    private const CHUNK_SIZE = 200;

    public int $timeout = 60;

    public int $tries = 1;

    public function __construct(private int $roleId, private int $afterId = 0) {}

    public function handle(): void
    {
        $discord = app(Discord::class);
        $keyColumn = (new Account)->qualifyColumn('id');

        $accounts = Account::query()
            ->whereNotNull('discord_id')
            ->where($keyColumn, '>', $this->afterId)
            ->orderBy($keyColumn)
            ->limit(self::CHUNK_SIZE)
            ->get();

        foreach ($accounts as $account) {
            try {
                $discord->removeRoleById($account, $this->roleId);
            } catch (Throwable $e) {
                Log::warning('Failed to remove Discord role from member', [
                    'account_id' => $account->getKey(),
                    'discord_role_id' => $this->roleId,
                    'exception' => $e,
                ]);
            }
        }

        if ($accounts->count() === self::CHUNK_SIZE) {
            static::dispatch($this->roleId, (int) $accounts->last()->getKey());
        }
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
