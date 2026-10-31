<?php

declare(strict_types=1);

namespace App\Services\Mship;

use App\Models\Mship\Account;
use App\Models\Mship\RoleStaffPosition;
use Illuminate\Support\Collection;

class StaffDirectoryService
{
    public function forDepartment(string $department): StaffDepartmentDirectory
    {
        $byMember = $this->positionsFor($department)
            ->flatMap(fn (RoleStaffPosition $position): Collection => $this->holdersOf($position))
            ->groupBy(fn (array $holder): int => $holder['account']->id);

        [$leads, $team] = $byMember->partition(
            fn (Collection $holders): bool => $holders->contains(
                fn (array $holder): bool => $holder['position']->is_department_lead,
            ),
        )->all();

        return new StaffDepartmentDirectory(
            leads: $this->sorted($leads->map(fn (Collection $holders): StaffDirectoryEntry => $this->entryFor($holders))),
            team: $this->sorted($team->map(fn (Collection $holders): StaffDirectoryEntry => $this->entryFor($holders))),
        );
    }

    /**
     * @return Collection<int, RoleStaffPosition>
     */
    private function positionsFor(string $department): Collection
    {
        return RoleStaffPosition::query()
            ->where('department', $department)
            ->with(['role.users' => function ($query): void {
                $query->orderBy('name_last')->orderBy('name_first');
            }])
            ->orderBy('sort_order')
            ->orderBy('job_title')
            ->get();
    }

    /**
     * @return Collection<int, array{account: Account, position: RoleStaffPosition}>
     */
    private function holdersOf(RoleStaffPosition $position): Collection
    {
        return ($position->role?->users ?? collect())->map(fn (Account $account): array => [
            'account' => $account,
            'position' => $position,
        ]);
    }

    /**
     * @param  Collection<int, array{account: Account, position: RoleStaffPosition}>  $holders
     */
    private function entryFor(Collection $holders): StaffDirectoryEntry
    {
        /** @var Account $account */
        $account = $holders->first()['account'];

        $positions = $holders
            ->map(fn (array $holder): RoleStaffPosition => $holder['position'])
            ->sortBy([
                ['sort_order', 'asc'],
                ['job_title', 'asc'],
            ])
            ->values();

        return new StaffDirectoryEntry(
            name: $account->real_name,
            jobTitles: $positions->pluck('job_title')->all(),
            sortOrder: (int) $positions->min('sort_order'),
            sortName: "{$account->name_last} {$account->name_first}",
        );
    }

    /**
     * @param  Collection<int|string, StaffDirectoryEntry>  $entries
     * @return list<StaffDirectoryEntry>
     */
    private function sorted(Collection $entries): array
    {
        return $entries
            ->sortBy([
                fn (StaffDirectoryEntry $left, StaffDirectoryEntry $right): int => $left->sortOrder <=> $right->sortOrder,
                fn (StaffDirectoryEntry $left, StaffDirectoryEntry $right): int => ($left->jobTitles[0] ?? '') <=> ($right->jobTitles[0] ?? ''),
                fn (StaffDirectoryEntry $left, StaffDirectoryEntry $right): int => $left->sortName <=> $right->sortName,
            ])
            ->values()
            ->all();
    }
}
