<?php

namespace App\Console\Commands\Members;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList\Removal;
use App\Models\Training\WaitingList\RemovalReason;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class ImportStaleMembers extends Command
{
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
        // find members with a cert_checked_at not not within the past week
        // run an api request for each
        // stick through normal update procedure, this will remove from lists etc if necessary
        $accountIds = $this->getAccountIds();

        $accountCount = $accountIds->count();
        $updatedCount = 0;
        $skippedCount = 0;

        $this->info("Found {$accountCount} stale accounts to update.");

        foreach ($accountIds as $accountId) {
            $vAccount = $this->fetchAccount($accountId);
            if (empty($vAccount) || ! $this->validate($vAccount)) {
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

    private function fetchAccount(int $cid): ?array
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

        if (! $response->ok()) {
            throw new \RuntimeException('could not call vatsim net api');
        }

        return $response->json();
    }

    private function validate(array $vAccount): bool
    {
        $validator = \Validator::make($vAccount, [
            'region_id' => 'required|string',
            'division_id' => 'required|string',
        ]);

        return $validator->passes();
    }

    private function update(int $cid, array $vMember): void
    {
        $account = Account::findOrFail($cid);
        $account->cert_checked_at = now();

        // There's some gnarly-ness here, if we don't have before updating divison
        // the update division log will bounce the cert_checked_at time back to its non-dirty state
        // side effects!
        $account->save();
        $account->refresh();

        $account->updateDivision($vMember['division_id'], $vMember['region_id']);
    }

    public static function getAccountIds(): Collection
    {
        return Account::where('cert_checked_at', '<', now()->addDays(-2))
            ->whereHas('states', function ($q) {
                $q->where('code', 'DIVISION');
            })
            ->whereHas('waitingListAccounts')
            ->limit(1000)
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
}
