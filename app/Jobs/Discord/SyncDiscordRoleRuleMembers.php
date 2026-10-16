<?php

namespace App\Jobs\Discord;

use App\Jobs\Concerns\LogsJobFailure;
use App\Jobs\Job;
use App\Jobs\Mship\SyncToDiscord;
use App\Models\Discord\DiscordRoleRule;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class SyncDiscordRoleRuleMembers extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, LogsJobFailure, SerializesModels;

    public int $timeout = 900;

    public int $tries = 1;

    public function __construct(private int $ruleId) {}

    public function handle(): void
    {
        $rule = DiscordRoleRule::find($this->ruleId);

        if (! $rule) {
            return;
        }

        $rule->candidateAccountsQuery()
            ->with(['qualifications', 'states', 'endorsements'])
            ->chunkById(200, function ($accounts) use ($rule) {
                foreach ($accounts as $account) {
                    if ($rule->accountSatisfies($account)) {
                        SyncToDiscord::dispatch($account);
                    }
                }
            });
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping('sync-discord-role-rule-'.$this->ruleId))->dontRelease()];
    }

    public function getRuleId(): int
    {
        return $this->ruleId;
    }

    protected function logJobContext(): array
    {
        return ['discord_role_rule_id' => $this->ruleId];
    }
}
