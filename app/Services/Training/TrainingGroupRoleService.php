<?php

declare(strict_types=1);

namespace App\Services\Training;

use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class TrainingGroupRoleService
{
    public function syncStudentRoles(Account $account): void
    {
        $desiredRoleNames = TrainingPlace::query()
            ->where('account_id', $account->id)
            ->with('trainable')
            ->get()
            ->map(fn (TrainingPlace $place): ?string => $this->studentRoleNameFor($place))
            ->filter()
            ->unique()
            ->values();

        foreach (MentorPermissionService::studentRoleNames() as $roleName) {
            $this->ensureRole($account, $roleName, $desiredRoleNames->contains($roleName));
        }
    }

    private function studentRoleNameFor(TrainingPlace $place): ?string
    {
        $category = $place->category;

        if (! is_string($category) || $category === '') {
            Log::info('Training place has no category for student role sync', [
                'training_place_id' => $place->id,
                'account_id' => $place->account_id,
            ]);

            return null;
        }

        $roleName = MentorPermissionService::studentRoleForCategory($category);

        if ($roleName === null) {
            Log::info('Training place category has no student role', [
                'training_place_id' => $place->id,
                'account_id' => $place->account_id,
                'category' => $category,
            ]);
        }

        return $roleName;
    }

    private function ensureRole(Account $account, string $roleName, bool $shouldHave): void
    {
        $role = Role::query()->where('name', $roleName)->where('guard_name', 'web')->first();

        if ($role === null) {
            Log::error('Training group role missing', [
                'role' => $roleName,
                'account_id' => $account->id,
            ]);

            return;
        }

        $hasRole = $account->hasRole($role);

        if ($shouldHave && ! $hasRole) {
            $account->assignRole($role);

            Log::info('Training group role assigned', [
                'account_id' => $account->id,
                'role' => $roleName,
            ]);

            return;
        }

        if (! $shouldHave && $hasRole) {
            $account->removeRole($role);

            Log::info('Training group role removed', [
                'account_id' => $account->id,
                'role' => $roleName,
            ]);
        }
    }
}
