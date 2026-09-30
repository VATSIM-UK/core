<?php

declare(strict_types=1);

namespace App\Jobs\VisitTransfer;

use App\Models\Atc\Position;
use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Endorsement;
use App\Models\Roster;
use App\Models\RosterHistory;
use App\Models\VisitTransfer\Application;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * One-off sweep which removes the visiting state from members who are no longer
 * eligible to hold it, and returns visitors who only control Shanwick to the roster.
 *
 * A visitor is kept when they:
 *  - hold only the Shanwick Oceanic endorsement, in which case they are also put
 *    back on the roster if they have fallen off it;
 *  - have an application at the ACCEPTED status;
 *  - have completed an ATC visit, have held a place on the roster, and do not meet
 *    the inactivity criteria of the scheduled check.
 *
 * Everyone else loses the visiting state, is cleared from the roster and has a note
 * added to their account explaining why. No notification is sent; the number of
 * removals is returned and logged alongside the reason breakdown.
 *
 * When constructed with `$dryRun = true` the sweep reports exactly what it would do
 * without writing anything: no roster changes, no state removals and no notes.
 */
class RemoveIneligibleVisitors implements ShouldQueue
{
    use Queueable;

    /**
     * Populated by `handle()` with the run totals, so callers (such as the artisan
     * command) can report a breakdown without duplicating the sweep logic.
     *
     * @var array{removed: int, returned_to_roster: int, reasons: array<string, int>}
     */
    public array $summary = [];

    public function __construct(public bool $dryRun = false) {}

    public const SHANWICK_POSITION_GROUP = 'Shanwick Oceanic (EGGX)';

    public const INACTIVE_MONTHS = 6;

    public const INSTANCE_WINDOW_YEARS = 2;

    public const REASON_NO_APPLICATION = 'no_application';

    public const REASON_NON_ATC_APPLICATION = 'non_atc_application';

    public const REASON_NEVER_ON_ROSTER = 'never_on_roster';

    public const REASON_SIX_MONTHS = 'six_months_inactive';

    public const REASON_TWICE_IN_TWO_YEARS = 'twice_in_two_years';

    public static function reasonText(string $reason): string
    {
        return match ($reason) {
            self::REASON_NO_APPLICATION => 'having no accepted or completed ATC visiting application',
            self::REASON_NON_ATC_APPLICATION => 'only holding a non-ATC visiting application',
            self::REASON_NEVER_ON_ROSTER => 'never having been added to the controller roster after completing their visit',
            self::REASON_SIX_MONTHS => 'being inactive on the UK controller roster for at least six consecutive months',
            self::REASON_TWICE_IN_TWO_YEARS => 'falling inactive on the UK controller roster at least twice in a two year period',
            default => $reason,
        };
    }

    /**
     * @return int the number of visitors removed
     */
    public function handle(): int
    {
        $removed = 0;
        $returnedToRoster = 0;
        $reasons = [];

        Account::query()
            ->whereHas('states', fn ($query) => $query->where('mship_state.code', 'VISITING'))
            ->with(['states', 'endorsements.endorsable'])
            ->chunkById(100, function (Collection $accounts) use (&$removed, &$returnedToRoster, &$reasons) {
                foreach ($accounts as $account) {
                    if ($this->onlyHoldsShanwickEndorsement($account)) {
                        if (! $account->onRoster()) {
                            if (! $this->dryRun) {
                                Roster::firstOrCreate(['account_id' => $account->id]);
                            }

                            $returnedToRoster++;
                        }

                        continue;
                    }

                    $reason = $this->reasonForRemoval($account);

                    if ($reason === null || ! $this->removeVisitingState($account)) {
                        continue;
                    }

                    if (! $this->dryRun) {
                        $account->addNote('visittransfer', 'Removed as visitor for '.self::reasonText($reason).'.');
                    }

                    $removed++;
                    $reasons[$reason] = ($reasons[$reason] ?? 0) + 1;
                }
            });

        $this->summary = [
            'removed' => $removed,
            'returned_to_roster' => $returnedToRoster,
            'reasons' => $reasons,
        ];

        Log::info('RemoveIneligibleVisitors: sweep complete.', [
            'dry_run' => $this->dryRun,
            'removed' => $removed,
            'returned_to_roster' => $returnedToRoster,
            'reasons' => $reasons,
        ]);

        return $removed;
    }

    /**
     * The reason the account should lose its visiting state, or null if it should keep
     * it. Only called for accounts which do not hold only the Shanwick endorsement.
     */
    private function reasonForRemoval(Account $account): ?string
    {
        if ($this->hasAcceptedApplication($account)) {
            return null;
        }

        if (! $this->hasCompletedAtcApplication($account)) {
            return $this->onlyHasNonAtcApplications($account)
                ? self::REASON_NON_ATC_APPLICATION
                : self::REASON_NO_APPLICATION;
        }

        if ($this->hasNeverBeenOnRoster($account)) {
            return self::REASON_NEVER_ON_ROSTER;
        }

        $instances = $this->inactivityInstances($account);

        if ($instances->count() >= 2) {
            return self::REASON_TWICE_IN_TWO_YEARS;
        }

        $mostRecent = $instances->first();

        if ($mostRecent !== null && ! $account->onRoster() && $mostRecent->created_at->lte(now()->subMonths(self::INACTIVE_MONTHS))) {
            return self::REASON_SIX_MONTHS;
        }

        return null;
    }

    /**
     * Whether the account has a visit application which is still at the ACCEPTED status.
     */
    private function hasAcceptedApplication(Account $account): bool
    {
        return $account->visitApplications()
            ->statusIn([Application::STATUS_ACCEPTED])
            ->exists();
    }

    /**
     * Whether the account has completed an ATC visit application.
     */
    private function hasCompletedAtcApplication(Account $account): bool
    {
        return $account->visitApplications()
            ->whereHas('facility', fn ($query) => $query->where('training_team', 'atc'))
            ->statusIn([Application::STATUS_COMPLETED])
            ->exists();
    }

    /**
     * Whether every visit application the account holds is for a non-ATC team, such as
     * a completed pilot visit. Accounts with no visit application at all are not
     * considered non-ATC.
     */
    private function onlyHasNonAtcApplications(Account $account): bool
    {
        $applications = $account->visitApplications()->with('facility')->get();

        return $applications->isNotEmpty()
            && $applications->every(fn (Application $application) => $application->facility?->training_team !== 'atc');
    }

    /**
     * Whether the account has never held a place on the controller roster, meaning the
     * scheduled inactivity check can never consider them.
     */
    private function hasNeverBeenOnRoster(Account $account): bool
    {
        return ! RosterHistory::query()->where('account_id', $account->id)->exists()
            && ! Roster::withoutGlobalScopes()->where('account_id', $account->id)->exists();
    }

    /**
     * Whether every active endorsement the account holds is for the Shanwick Oceanic
     * (EGGX) position group, meaning they only control oceanic.
     */
    private function onlyHoldsShanwickEndorsement(Account $account): bool
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
     * Automated roster removals for the account within the eligibility window, counted
     * only since the start of their current visiting state.
     */
    private function inactivityInstances(Account $account): Collection
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

    /**
     * Whether the account holds the visiting state, removing it and any roster entry
     * unless the sweep is a dry run. Returns false when the state is already gone.
     */
    private function removeVisitingState(Account $account): bool
    {
        $visitingState = $account->states->firstWhere('code', 'VISITING');

        if (! $visitingState) {
            return false;
        }

        if ($this->dryRun) {
            return true;
        }

        Roster::withoutGlobalScopes()
            ->where('account_id', $account->id)
            ->get()
            ->each
            ->delete();

        $account->removeState($visitingState);

        return true;
    }
}
