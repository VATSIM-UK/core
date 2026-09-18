<?php

namespace App\Services\VisitTransfer;

use App\Models\Mship\Account;
use App\Models\Roster;
use App\Models\RosterHistory;
use App\Notifications\VisitTransfer\VisitingStatusRevoked;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Detects visiting controllers who no longer meet the GCAP activity
 * requirements and removes their visiting rights.
 */
class VisitingControllerInactivity
{
    public const REASON_SIX_MONTHS = 'six_months_inactive';

    public const REASON_TWICE_IN_TWO_YEARS = 'twice_in_two_years';

    public const INACTIVE_MONTHS = 6;

    public const INSTANCE_WINDOW_YEARS = 2;

    /**
     * Visiting controllers who currently meet one of the removal criteria.
     */
    public function eligible(): Collection
    {
        return Account::query()
            ->whereHas('states', fn ($query) => $query->where('mship_state.code', 'VISITING'))
            ->get()
            ->map(fn (Account $account) => [
                'account' => $account,
                'reason' => $this->reasonFor($account),
            ])
            ->filter(fn (array $candidate) => $candidate['reason'] !== null)
            ->values();
    }

    /**
     * Determine why (if at all) a visiting controller is eligible for removal.
     */
    public function reasonFor(Account $account): ?string
    {
        if (! $account->hasState('VISITING')) {
            return null;
        }

        $instances = $this->inactivityInstances($account);

        if ($instances->count() >= 2) {
            return self::REASON_TWICE_IN_TWO_YEARS;
        }

        $mostRecent = $instances->first();

        if ($mostRecent && ! $account->onRoster() && $mostRecent->created_at->lte(now()->subMonths(self::INACTIVE_MONTHS))) {
            return self::REASON_SIX_MONTHS;
        }

        return null;
    }

    /**
     * Check every visiting controller and remove the rights of anyone eligible.
     */
    public function process(): array
    {
        $checked = Account::query()
            ->whereHas('states', fn ($query) => $query->where('mship_state.code', 'VISITING'))
            ->count();

        $eligible = $this->eligible();

        foreach ($eligible as $candidate) {
            $this->removeVisitingRights($candidate['account'], $candidate['reason']);
        }

        return [
            'checked' => $checked,
            'eligible' => $eligible->count(),
            'removed' => $eligible->count(),
        ];
    }

    /**
     * Remove an account's visiting rights and record why.
     */
    public function removeVisitingRights(Account $account, string $reason): void
    {
        DB::transaction(function () use ($account, $reason) {
            // Clear any lingering roster entry directly so we don't send a duplicate removal email.
            Roster::withoutGlobalScopes()
                ->where('account_id', $account->id)
                ->get()
                ->each
                ->delete();

            $visitingState = $account->states->firstWhere('code', 'VISITING');

            if ($visitingState) {
                $account->removeState($visitingState);
            }

            $account->visitingRemovals()->create([
                'reason' => $reason,
                'removed_at' => now(),
            ]);

            $account->notify(new VisitingStatusRevoked($account, $reason));
        });
    }

    /**
     * Automated roster removals for the account within the eligibility window,
     * counted only since the start of their current visiting state.
     */
    protected function inactivityInstances(Account $account): Collection
    {
        $visitingStateStart = $account->states->firstWhere('code', 'VISITING')?->pivot?->start_at;

        return RosterHistory::query()
            ->where('account_id', $account->id)
            ->whereNotNull('roster_update_id') // must be from the automated process
            ->where('created_at', '>=', now()->subYears(self::INSTANCE_WINDOW_YEARS))
            ->when($visitingStateStart, fn ($query, $start) => $query->where('created_at', '>=', $start))
            ->orderByDesc('created_at')
            ->get();
    }
}
