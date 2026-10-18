<?php

namespace App\Jobs\Discord;

use App\Jobs\Concerns\LogsJobFailure;
use App\Jobs\Job;
use App\Jobs\Mship\SyncToDiscord;
use App\Models\Discord\DiscordRoleRule;
use App\Models\Mship\Account;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Re-syncs the members a rule matches, one chunk at a time. Each chunk
 * dispatches the next, so a large sweep is spread over many short jobs that
 * each finish well within the queue retry_after (90s) rather than one
 * long-running job that can be re-reserved mid-flight.
 */
class SyncDiscordRoleRuleMembers extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, LogsJobFailure, SerializesModels;

    private const CHUNK_SIZE = 200;

    public int $timeout = 60;

    public int $tries = 1;

    public function __construct(private int $ruleId, private int $afterId = 0) {}

    public function handle(): void
    {
        $rule = DiscordRoleRule::find($this->ruleId);

        if (! $rule) {
            return;
        }

        $keyColumn = (new Account)->qualifyColumn('id');

        $accounts = $rule->candidateAccountsQuery()
            ->with(['qualifications', 'states', 'endorsements'])
            ->where($keyColumn, '>', $this->afterId)
            ->orderBy($keyColumn)
            ->limit(self::CHUNK_SIZE)
            ->get();

        foreach ($accounts as $account) {
            try {
                if ($rule->accountSatisfies($account)) {
                    SyncToDiscord::dispatch($account);
                }
            } catch (Throwable $e) {
                Log::warning('Failed to evaluate Discord role rule for member', [
                    'account_id' => $account->getKey(),
                    'discord_role_rule_id' => $this->ruleId,
                    'exception' => $e,
                ]);
            }
        }

        if ($accounts->count() === self::CHUNK_SIZE) {
            static::dispatch($this->ruleId, (int) $accounts->last()->getKey());
        }
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
