<?php

namespace App\Policies\Training;

use App\Models\Mship\Account;
use App\Models\Training\Seminar\Seminar;

class SeminarPolicy
{
    public function viewAny(Account $account): bool
    {
        return $this->allowedTrainingGroups($account, 'view') !== [];
    }

    public function view(Account $account, Seminar $seminar): bool
    {
        return $this->trainingGroupIsPermitted($account, 'view', $seminar->training_group);
    }

    public function create(Account $account): bool
    {
        return $this->allowedTrainingGroups($account, 'manage') !== [];
    }

    public function update(Account $account, Seminar $seminar): bool
    {
        return $this->trainingGroupIsPermitted($account, 'manage', $seminar->training_group);
    }

    public function delete(Account $account, Seminar $seminar): bool
    {
        return $this->trainingGroupIsPermitted($account, 'manage', $seminar->training_group);
    }

    /**
     * Training groups the account may view or manage. The wildcard (`training.seminars.view.*`)
     * grants both departments.
     *
     * @return array<int, string>
     */
    public function allowedTrainingGroups(Account $account, string $ability): array
    {
        return array_values(array_filter(
            Seminar::ALLOWED_TRAINING_GROUPS,
            fn (string $trainingGroup): bool => $this->trainingGroupIsPermitted($account, $ability, $trainingGroup)
        ));
    }

    private function trainingGroupIsPermitted(Account $account, string $ability, string $trainingGroup): bool
    {
        $department = Seminar::TRAINING_GROUP_DEPARTMENTS[$trainingGroup] ?? null;

        return $department !== null && $account->hasPermissionTo("training.seminars.{$ability}.{$department}");
    }
}
