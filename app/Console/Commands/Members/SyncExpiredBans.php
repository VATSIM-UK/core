<?php

namespace App\Console\Commands\Members;

use App\Console\Commands\Command;
use App\Jobs\UpdateMember;
use App\Models\Mship\Account\Ban;
use Carbon\Carbon;

class SyncExpiredBans extends Command
{
    protected $signature = 'mship:sync-expired-bans';

    protected $description = 'Run an account sync for accounts whose bans have recently expired';

    public function handle(): int
    {
        // Look at bans in the last 24 hours
        $since = Carbon::now()->subHours(24);

        $expiredBans = Ban::isNotRepealed()
            ->where('period_finish', '>=', $since)
            ->where('period_finish', '<=', Carbon::now())
            ->with('account')
            ->get();

        $syncedCount = 0;

        foreach ($expiredBans as $ban) {
            $account = $ban->account;

            if (! $account) {
                continue;
            }

            UpdateMember::dispatch($account->id);
            $syncedCount++;
        }

        $this->log("Dispatched {$syncedCount} account sync(s) for accounts with expired bans in the last 13h.");

        return Command::SUCCESS;
    }
}
