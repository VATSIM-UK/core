<?php

declare(strict_types=1);

namespace App\Services\Training;

use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Training\Mentoring\MentorTrainingPosition;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Notifications\Training\Mentoring\MentoringPermissionsRemovedForInactivityNotification;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class MentorInactivityService
{
    /**
     * Staff roles that are exempt from automatic removal regardless of their
     * mentoring activity.
     */
    public const EXEMPT_STAFF_ROLES = [
        'ATC Training Director',
        'ATC Training Manager',
        'ATC Division Instructor',
        'ATC Instructor',
        'Training Assistant',
        'Pilot Training Director',
        'Pilot Training Manager',
    ];

    public function __construct(private MentorPermissionService $permissionService) {}

    public function cutoff(): Carbon
    {
        return now()->subMonths(6);
    }

    public function isExempt(Account $account): bool
    {
        return $account->hasRole(array_merge(
            self::EXEMPT_STAFF_ROLES,
            array_values(MentorPermissionService::ATC_TGI_CATEGORY_ROLE_MAP),
            array_values(MentorPermissionService::PILOT_TGI_CATEGORY_ROLE_MAP),
        ));
    }

    /**
     * Whether a mentor has to be considered inactive in a training group.
     */
    public function isInactive(?Carbon $lastMentoredAt, ?Carbon $assignedAt = null): bool
    {
        $reference = match (true) {
            // Never mentored in this group
            $lastMentoredAt === null => $assignedAt,
            // Assigned date unknown
            $assignedAt === null => $lastMentoredAt,
            // Permissions regranted at some point after their last session, so they should be given another 6 months of grace
            $assignedAt->greaterThan($lastMentoredAt) => $assignedAt,
            // Mentored since being assigned, so the session is the later date.
            default => $lastMentoredAt,
        };

        return $reference === null || $reference->lt($this->cutoff());
    }

    /**
     * Every inactive mentor, paired with the training group they are inactive in.
     */
    public function inactiveMentoringAssignments(): Collection
    {
        return Account::query()
            ->whereHas('mentorTrainingPositions')
            ->with('mentorTrainingPositions.mentorable')
            ->get()
            ->reject(fn (Account $account) => $this->isExempt($account))
            ->flatMap(function (Account $account) {
                $assignedAt = $this->assignedAtByCategory($account);

                return collect($this->categoriesForAccount($account))
                    ->map(fn (string $category) => [
                        'account' => $account,
                        'category' => $category,
                        'assigned_at' => $assignedAt[$category] ?? null,
                        'last_mentored_at' => $this->permissionService->getLastMentoredDate($account, $category),
                    ])
                    ->filter(fn (array $assignment) => $this->isInactive($assignment['last_mentored_at'], $assignment['assigned_at']));
            })
            ->values();
    }

    /**
     * The date the mentor was first granted mentoring rights in each of their
     * training groups.
     */
    private function assignedAtByCategory(Account $account): array
    {
        return $account->mentorTrainingPositions
            ->map(fn (MentorTrainingPosition $permission) => [
                'category' => $this->categoryForMentorable($permission->mentorable),
                'assigned_at' => $permission->created_at,
            ])
            ->filter(fn (array $permission) => $permission['category'] !== null && $permission['assigned_at'] !== null)
            ->groupBy('category')
            ->map(fn (Collection $permissions) => $permissions->min('assigned_at'))
            ->all();
    }

    public function categoriesForAccount(Account $account): array
    {
        return $account->mentorTrainingPositions
            ->map(fn (MentorTrainingPosition $permission) => $this->categoryForMentorable($permission->mentorable))
            ->filter()
            ->unique()
            // Inactivity removal currently only applies to ATC mentoring.
            ->reject(fn (string $category) => MentorPermissionService::categoryType($category) === 'pilot')
            ->values()
            ->all();
    }

    /**
     * Remove all mentoring permissions for every inactive mentor and notify them.
     */
    public function removeInactiveMentoringPermissions(): Collection
    {
        $removed = $this->inactiveMentoringAssignments();

        $removed->each(function (array $assignment): void {
            $account = $assignment['account'];
            $category = $assignment['category'];

            $this->permissionService->revokeFromCategory($account, $category);

            $account->notify(new MentoringPermissionsRemovedForInactivityNotification($category));

            Log::info('Inactive mentoring permissions removed', [
                'account_id' => $account->id,
                'category' => $category,
                'last_mentored_at' => $assignment['last_mentored_at']?->toDateString(),
            ]);
        });

        return $removed;
    }

    private function categoryForMentorable(mixed $mentorable): ?string
    {
        if ($mentorable instanceof TrainingPosition) {
            return $mentorable->category;
        }

        if ($mentorable instanceof Qualification) {
            return MentorPermissionService::categoryForQualificationCode($mentorable->code);
        }

        return null;
    }
}
