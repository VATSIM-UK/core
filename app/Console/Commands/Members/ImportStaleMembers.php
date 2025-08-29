<?php

namespace App\Console\Commands\Members;

use App\Jobs\Mship\ScanStaleMembers;
use App\Models\Mship\Account;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ImportStaleMembers extends Command
{
    const RATE_LIMIT_KEY = 'vatsim-api:member-details';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:stale-members';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scans for members without a recent vatsim api check and pulls their details. For tracking division leavers.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accountIds = $this->findHomeAccountsWithStaleChecks();

        $this->info("Found {$accountIds->count()} accounts to check, pushing to queue");
        ScanStaleMembers::dispatch($accountIds);
    }

    /**
     * Find division members, who are on waiting lists, who have not been seen in div members API for 2 days.
     * A maximum of 50 accounts will be returned for timing reasons, the API needed to check details is heavily rate limited.
     *
     * Returns a collection of account ids.
     *
     * @return Collection<int, int>
     */
    public static function findHomeAccountsWithStaleChecks(): Collection
    {
        return Account::where('cert_checked_at', '<', now()->addDays(-2))
            ->whereHas('states', function ($q) {
                $q->where('code', 'DIVISION');
            })
            ->whereHas('waitingListAccounts')
            ->limit(100) // limit to 100 accounts to avoid massive queue jobs, we'll get the rest later / tomorrow
            ->select('id')
            ->get()
            ->pluck('id');
    }
}
