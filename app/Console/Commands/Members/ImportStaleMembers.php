<?php

namespace App\Console\Commands\Members;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList\Removal;
use App\Models\Training\WaitingList\RemovalReason;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Sleep;

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

        $updatedCount = $skippedCount = 0;

        $this->info("Found {$accountIds->count()} stale accounts to update.");

        foreach ($accountIds as $accountId) {
            $vAccount = $this->fetchDetailsRateLimit($accountId);

            if ($vAccount === null || ! $this->validateAccountApiResponse($vAccount)) {
                $this->info("Could not retrieve {$accountId} from .net, they will be removed from waiting lists");
                $this->removeFromAllLists($accountId);
                $skippedCount++;

                continue;
            }

            $this->update($accountId, $vAccount);
            $updatedCount++;
        }

        $this->info("Updated {$updatedCount} accounts, skipped {$skippedCount}.");
    }

    private function fetchDetailsRateLimit(int $cid, bool $alreadySlept = false): ?array
    {
        if (RateLimiter::tooManyAttempts(self::RATE_LIMIT_KEY, $perMinute = 5)) {
            if ($alreadySlept) {
                throw new \RuntimeException('could not recover from rate limiting');
            }

            $this->sleep();

            return $this->fetchDetailsRateLimit($cid, true);
        }

        $member = $this->fetchDetails($cid);

        RateLimiter::increment(self::RATE_LIMIT_KEY);

        return $member;
    }

    private function fetchDetails(int $cid, bool $alreadySlept = false): ?array
    {
        $token = config('services.vatsim-net.api.key');

        $response = Http::withHeaders([
            'Authorization' => "Token {$token}",
        ])
            ->withUserAgent('VATSIMUK')
            ->get(config('services.vatsim-net.api.base')."members/{$cid}");

        if ($response->notFound()) {
            return null;
        }

        // .net applies somewhat variable rate limits, so we can't rely on throttling ourselves
        if ($response->tooManyRequests() && $alreadySlept) {
            throw new \RuntimeException('could not recover from rate limiting');
        }

        if ($response->tooManyRequests()) {
            $this->sleep();

            return $this->fetchDetails($cid, true);
        }

        if (! $response->ok()) {
            throw new \RuntimeException('could not call vatsim net api');
        }

        return $response->json();
    }

    private function update(int $cid, array $vMember): void
    {
        $account = Account::findOrFail($cid);
        $account->cert_checked_at = now();

        // Update division logic refreshes the account, so any unsaved changes before calling it will be lost!
        $account->save();

        // This will trigget e.g waiting list removals for leavers
        $account->updateDivision($vMember['division_id'], $vMember['region_id']);
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
            ->limit(50) // limit to 50 accounts to allow 10 mins run time at 5 req/min
            ->select('id')
            ->get()
            ->pluck('id');
    }

    private function removeFromAllLists(int $accountId): void
    {
        $account = Account::findOrFail($accountId);
        foreach ($account->currentWaitingLists() as $list) {
            $list->removeFromWaitingList($account, new Removal(RemovalReason::Other, null, '[system] could not find in core api'));
        }
    }

    private function validateAccountApiResponse(array $vAccount): bool
    {
        $validator = \Validator::make($vAccount, [
            'region_id' => 'required|string',
            'division_id' => 'required|string',
        ]);

        return $validator->passes();
    }

    public function sleep(): void
    {
        Sleep::for(60)->seconds();
    }
}
