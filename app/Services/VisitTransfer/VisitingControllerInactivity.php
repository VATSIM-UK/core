<?php

namespace App\Services\VisitTransfer;

use App\Models\Atc\Position;
use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Endorsement;
use App\Models\Roster;
use App\Models\RosterHistory;
use App\Models\VisitTransfer\Application;
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

    public const SHANWICK_POSITION_GROUP = 'Shanwick Oceanic (EGGX)';

    public static function reasonText(string $reason): string
    {
        return match ($reason) {
            self::REASON_SIX_MONTHS => 'you have remained inactive on the UK controller roster for at least six consecutive months',
            self::REASON_TWICE_IN_TWO_YEARS => 'you have fallen inactive on the UK controller roster at least twice in a two year period',
            default => $reason,
        };
    }

    /**
     * Visiting controllers who currently meet one of the removal criteria.
     */
    public function eligible(): Collection
    {
        return Account::query()
            ->whereHas('states', fn ($query) => $query->where('mship_state.code', 'VISITING'))
            ->with(['states', 'visitTransferApplications.facility', 'endorsements.endorsable'])
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

        if (! $this->isAtcVisitor($account)) {
            return null;
        }

        if ($account->hasOpenVisitingTransferApplication()) {
            return null;
        }

        if ($this->onlyHoldsShanwickEndorsement($account)) {
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

            $account->notify(new VisitingStatusRevoked($reason));
        });
    }

    /**
     * Whether the account's visiting status originates from an ATC visit.
     */
    protected function isAtcVisitor(Account $account): bool
    {
        return $account->visitApplications()
            ->whereHas('facility', fn ($query) => $query->where('training_team', 'atc'))
            ->statusIn([Application::STATUS_COMPLETED])
            ->exists();
    }

    /**
     * Whether every active endorsement the account holds is for the Shanwick
     * Oceanic (EGGX) position group, meaning they only control oceanic.
     */
    protected function onlyHoldsShanwickEndorsement(Account $account): bool
    {
        $endorsements = $account->endorsements->filter(fn (Endorsement $endorsement) => ! $endorsement->hasExpired());

        if ($endorsements->isEmpty()) {
            return false;
        }

        $shanwick = PositionGroup::where('name', self::SHANWICK_POSITION_GROUP)->first();

        if (! $shanwick) {
            return false;
        }

        $shanwickPositions = $shanwick->positions()->pluck('positions.id');

        return $endorsements->every(function (Endorsement $endorsement) use ($shanwick, $shanwickPositions) {
            if ($endorsement->endorsable_type === PositionGroup::class) {
                return (int) $endorsement->endorsable_id === (int) $shanwick->id;
            }

            if ($endorsement->endorsable_type === Position::class) {
                return $shanwickPositions->contains((int) $endorsement->endorsable_id);
            }

            return false;
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
