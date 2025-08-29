<?php

namespace App\Jobs\Mship;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList\Removal;
use App\Models\Training\WaitingList\RemovalReason;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

class ScanStaleMembers implements ShouldQueue
{
    use Queueable;

    const string RATE_LIMIT_KEY = 'vatsim-api:member-details';

    const int BATCH_SIZE = 5;

    const int API_LIMIT_PER_MINUTE = 5;

    /** @var Collection<int> */
    public Collection $accountIds;

    /**
     * @param  Collection<int>  $accountIds
     */
    public function __construct(Collection $accountIds)
    {
        $this->accountIds = clone $accountIds;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Log::info('Scanning stale members', ['total_remaining' => count($this->accountIds), 'batch_size' => self::BATCH_SIZE]);

        if (count($this->accountIds) === 0) {
            $this->delete();

            return;
        }

        foreach (range(0, self::BATCH_SIZE) as $i) {
            /** @var int $accountId */
            $accountId = $this->accountIds->pop();
            if (is_null($accountId)) {
                break;
            }

            try {
                $this->scanAccount($accountId);
            } catch (NetRateLimitingException $e) {
                $this->rePushIncluding($accountId);
                $this->delete();

                return;
            }
        }

        // If we've reached the end of the batch, we repush after some waiting time
        $this->rePushIncluding(null);
        $this->delete();
    }

    /**
     * Repush this job with remaining accounts, including one currently being worked if we've hit a rate limit somewhere
     */
    private function rePushIncluding(?int $accountId): void
    {
        if ($accountId) {
            $this->accountIds->push($accountId);
        }

        ScanStaleMembers::dispatch($this->accountIds)
            ->delay(now()->addMinutes(1));
    }

    private function scanAccount(int $accountId): void
    {
        $vAccount = $this->fetchDetailsRateLimit($accountId);

        if ($vAccount === null || ! $this->validateAccountApiResponse($vAccount)) {
            \Log::info("Could not retrieve {$accountId} from .net, they will be removed from waiting lists");
            $this->removeFromAllLists($accountId);

            return;
        }

        \Log::info("Updating {$accountId}");
        $this->update($accountId, $vAccount);
    }

    private function fetchDetailsRateLimit(int $cid): ?array
    {
        if (RateLimiter::tooManyAttempts(key: self::RATE_LIMIT_KEY, maxAttempts: self::API_LIMIT_PER_MINUTE)) {
            throw new NetRateLimitingException("hit rate limit, repushing {$cid}");
        }

        $member = $this->fetchDetails($cid);

        RateLimiter::increment(self::RATE_LIMIT_KEY);

        return $member;
    }

    private function fetchDetails(int $cid): ?array
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
        if ($response->tooManyRequests()) {
            throw new NetRateLimitingException("hit rate limit, repushing {$cid}");
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

        // This will trigger e.g waiting list removals for leavers
        $account->updateDivision($vMember['division_id'], $vMember['region_id']);
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
}
