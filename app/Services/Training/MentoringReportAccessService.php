<?php

declare(strict_types=1);

namespace App\Services\Training;

use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

/**
 * Determines who may view historic mentoring reports.
 *
 * Access is granted through any of the following, evaluated in order:
 *
 *  1. The student who attended the session.
 *  2. Holders of `training.mentoring.view.*` or `training.mentoring.reports.view-all`
 *     (the latter is intended for Division Instructors, the ATC Training Manager and
 *     the ATC Training Director).
 *  3. Training Group Instructors, for their training group and below, no training
 *     place requirement.
 *  4. Mentors, for categories within their own category's ladder, but *only* while
 *     the student holds an active training place whose ladder covers the session's
 *     category.
 */
class MentoringReportAccessService
{
    /**
     * Categories whose reports a viewer/place category may see, including itself.
     *
     * @var array<string, array<int, string>>
     */
    public const CATEGORY_LADDER = [
        'C1 Training' => ['C1 Training', 'S3 Training', 'S2 Training', 'OBS to S1 Training'],
        'S3 Training' => ['S3 Training', 'S2 Training', 'OBS to S1 Training'],
        'S2 Training' => ['S2 Training', 'OBS to S1 Training'],
        'OBS to S1 Training' => ['OBS to S1 Training'],
        'Heathrow GMC' => ['OBS to S1 Training', 'Heathrow GMC'],
        'Heathrow AIR' => ['Heathrow GMC', 'Heathrow AIR', 'OBS to S1 Training', 'S2 Training'],
        'Heathrow APC' => ['Heathrow GMC', 'Heathrow AIR', 'Heathrow APC', 'OBS to S1 Training', 'S2 Training', 'S3 Training'],
        'TFP Training' => ['TFP Training'],
        'P1 Training' => ['TFP Training', 'P1 Training'],
        'P2 Training' => ['TFP Training', 'P1 Training', 'P2 Training'],
        'P3 Training' => ['TFP Training', 'P1 Training', 'P2 Training', 'P3 Training'],
    ];

    public const VIEW_ALL_PERMISSION = 'training.mentoring.reports.view-all';

    public function __construct(
        private readonly MentorPermissionService $mentorPermissionService,
    ) {}

    /**
     * Whether the user may see every mentoring report regardless of training places.
     */
    public function canViewAll(Account $user): bool
    {
        return $user->can('training.mentoring.view.*') || $user->can(self::VIEW_ALL_PERMISSION);
    }

    /**
     * The category ladder for a given category.
     */
    public function ladderForCategory(string $category): array
    {
        return self::CATEGORY_LADDER[$category] ?? [$category];
    }

    /**
     * Whether the given category is visible from within the ladder of the given origin.
     */
    public function categoryIsWithinLadder(string $originCategory, string $category): bool
    {
        return in_array($category, $this->ladderForCategory($originCategory), true);
    }

    /**
     * Categories of the active training places held by the account.
     */
    public function activeTrainingPlaceCategoriesFor(Account $account): array
    {
        return TrainingPlace::query()
            ->where('account_id', $account->id)
            ->whereNull('deleted_at')
            ->with('trainable')
            ->get()
            ->map(fn (TrainingPlace $place) => $place->category)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Whether the student holds an active training place whose ladder covers the
     * given session category.
     */
    public function studentHasQualifyingActiveTrainingPlace(Account $student, string $category): bool
    {
        return collect($this->activeTrainingPlaceCategoriesFor($student))
            ->contains(fn (string $placeCategory) => $this->categoryIsWithinLadder($placeCategory, $category));
    }

    /**
     * Training group categories for which the account holds a TGI role.
     */
    public function tgiCategoriesFor(Account $account): array
    {
        return collect(array_merge(
            MentorPermissionService::ATC_TGI_CATEGORY_ROLE_MAP,
            MentorPermissionService::PILOT_TGI_CATEGORY_ROLE_MAP,
        ))
            ->filter(fn (string $roleName) => $this->accountHasRole($account, $roleName))
            ->keys()
            ->unique()
            ->values()
            ->all();
    }

    private function accountHasRole(Account $account, string $roleName): bool
    {
        try {
            return $account->hasRole($roleName);
        } catch (RoleDoesNotExist) {
            return false;
        }
    }

    /**
     * Mentoring categories in which the account holds mentor assignments.
     */
    public function mentoringCategoriesFor(Account $account): array
    {
        return collect(MentorPermissionService::atcCategories())
            ->merge(MentorPermissionService::pilotCategories())
            ->filter(fn (string $category) => $this->mentorPermissionService->getAssignedCtsCallsigns($account, $category) !== [])
            ->values()
            ->all();
    }

    /**
     * Categories the account may see mentoring reports for, considering mentoring
     * assignments and TGI roles. This is the union of the ladders of the account's
     * mentoring categories and TGI categories.
     */
    public function visibleCategoriesFor(Account $account): array
    {
        if ($this->canViewAll($account)) {
            return collect(MentorPermissionService::atcCategories())
                ->merge(MentorPermissionService::pilotCategories())
                ->values()
                ->all();
        }

        $originCategories = collect($this->mentoringCategoriesFor($account))
            ->merge($this->tgiCategoriesFor($account))
            ->unique()
            ->values();

        return $originCategories
            ->flatMap(fn (string $category) => $this->ladderForCategory($category))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Whether the user may view the given filed mentoring report.
     */
    public function canViewReport(Account $user, Session $session): bool
    {
        if ($session->filed === null) {
            return false;
        }

        $studentAccount = $session->studentAccount();

        if ($studentAccount !== null && $studentAccount->id === $user->id) {
            return true;
        }

        if ($this->canViewAll($user)) {
            return true;
        }

        $category = $this->mentorPermissionService->resolveCategoryForCtsCallsign($session->position);

        if ($category === null) {
            Log::debug('Mentoring report access denied: category could not be resolved for position', [
                'session_id' => $session->id,
                'position' => $session->position,
            ]);

            return false;
        }

        if (collect($this->tgiCategoriesFor($user))->contains(fn (string $tgiCategory) => $this->categoryIsWithinLadder($tgiCategory, $category))) {
            return true;
        }

        if ($studentAccount === null || ! $this->studentHasQualifyingActiveTrainingPlace($studentAccount, $category)) {
            return false;
        }

        if (collect($this->mentoringCategoriesFor($user))->contains(fn (string $mentorCategory) => $this->categoryIsWithinLadder($mentorCategory, $category))) {
            return true;
        }

        return $this->isConductingMentor($user, $session);
    }

    private function isConductingMentor(Account $user, Session $session): bool
    {
        return $session->mentor?->cid === $user->id;
    }

    /**
     * Query of sessions the account may see, honouring the same category, ladder
     * and training-place rules as {@see canViewReport()} so the history listing
     * cannot show sessions the permission model denies.
     */
    public function visibleSessionsQueryFor(Account $user): Builder
    {
        if ($this->canViewAll($user)) {
            return Session::query();
        }

        $arms = collect();

        $tgiCallsigns = $this->tgiCategoryCallsignsFor($user);

        if ($tgiCallsigns->isNotEmpty()) {
            $arms->push(fn () => Session::query()
                ->whereIn('position', $tgiCallsigns->values()->all()));
        }

        $mentored = $this->mentoredSessionsQueryFor($user);

        if ($mentored !== null) {
            $arms->push(fn () => $mentored);
        }

        $conducted = $this->conductedSessionsQueryFor($user);

        if ($conducted !== null) {
            $arms->push(fn () => $conducted);
        }

        if ($arms->isEmpty()) {
            return Session::query()->whereRaw('0 = 1');
        }

        $union = $arms
            ->map(fn (callable $arm) => $arm())
            ->reduce(fn (?Builder $carry, Builder $arm) => $carry === null ? $arm : $carry->union($arm));

        return Session::query()
            ->fromSub($union, 'sessions')
            ->select('sessions.*');
    }

    /**
     * Sessions at callsigns covered by the ladders of the user's mentoring
     * categories, where the session's category sits inside the ladder of one of the
     * student's active training places. Filed and pending sessions are treated
     * identically.
     */
    private function mentoredSessionsQueryFor(Account $user): ?Builder
    {
        $mentoringCategories = collect($this->mentoringCategoriesFor($user));

        if ($mentoringCategories->isEmpty()) {
            return null;
        }

        $ladderCallsigns = $mentoringCategories
            ->flatMap(fn (string $category) => $this->mentorPermissionService->getAllCtsCallsignsForCategories($this->ladderForCategory($category)))
            ->unique()
            ->values();

        $placeGroups = $this->activePlaceGroups($mentoringCategories);

        if ($ladderCallsigns->isEmpty() || $placeGroups->isEmpty()) {
            return null;
        }

        return Session::query()
            ->whereIn('position', $ladderCallsigns->all())
            ->where($this->studentPlaceLadderConstraint($placeGroups));
    }

    /**
     * Sessions the user personally conducted, limited to positions covered by the
     * ladder of the student's own active training places.
     */
    private function conductedSessionsQueryFor(Account $user): ?Builder
    {
        $memberId = Member::query()->where('cid', $user->id)->value('id');

        if ($memberId === null) {
            return null;
        }

        if (! Session::query()->where('mentor_id', $memberId)->exists()) {
            return null;
        }

        return Session::query()
            ->where('mentor_id', $memberId)
            ->where($this->studentPlaceLadderConstraint($this->activePlaceGroups()));
    }

    /**
     * Active training places grouped by place category, each with the
     * CTS member IDs of the place holders and the callsigns covered by that place
     * category's ladder.
     */
    private function activePlaceGroups(?Collection $mentoringCategories = null): Collection
    {
        return TrainingPlace::query()
            ->whereNull('deleted_at')
            ->with('trainable')
            ->get()
            ->filter(fn (TrainingPlace $place) => $place->category !== null
                && ($mentoringCategories === null || $mentoringCategories->contains(
                    fn (string $category) => $this->categoryIsWithinLadder($category, $place->category)
                )))
            ->groupBy(fn (TrainingPlace $place) => $place->category)
            ->map(fn (Collection $places, string $category) => [
                'memberIds' => Member::query()
                    ->whereIn('cid', $places->pluck('account_id')->unique()->all())
                    ->pluck('id')
                    ->all(),
                'callsigns' => $this->mentorPermissionService->getAllCtsCallsignsForCategories(
                    $this->ladderForCategory($category)
                ),
            ])
            ->filter(fn (array $group) => $group['memberIds'] !== [] && $group['callsigns'] !== [])
            ->values();
    }

    /**
     * Constraint matching sessions whose category is covered by the ladder of one of
     * the student's own active training places. Scoping is per student — a student's
     * place never opens another student's sessions.
     */
    private function studentPlaceLadderConstraint(Collection $placeGroups): \Closure
    {
        return function (Builder $query) use ($placeGroups): void {
            $query->where(function (Builder $inner) use ($placeGroups): void {
                if ($placeGroups->isEmpty()) {
                    $inner->whereRaw('0 = 1');

                    return;
                }

                foreach ($placeGroups as ['memberIds' => $memberIds, 'callsigns' => $callsigns]) {
                    $inner->orWhere(function (Builder $pair) use ($memberIds, $callsigns): void {
                        $pair->whereIn('student_id', $memberIds)
                            ->whereIn('position', $callsigns);
                    });
                }
            });
        };
    }

    /**
     * Callsigns of all categories covered by the account's TGI roles.
     */
    private function tgiCategoryCallsignsFor(Account $user): Collection
    {
        return collect($this->tgiCategoriesFor($user))
            ->flatMap(fn (string $category) => $this->mentorPermissionService->getAllCtsCallsignsForCategories($this->ladderForCategory($category)))
            ->unique()
            ->values();
    }
}
