<?php

namespace App\Policies;

use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\WaitingList;

class TrainingPlacePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Account $account): bool
    {
        return $this->canViewDepartment($account, WaitingList::ATC_DEPARTMENT)
            || $this->canViewDepartment($account, WaitingList::PILOT_DEPARTMENT);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Account $account, TrainingPlace $trainingPlace): bool
    {
        return $this->canViewDepartment($account, $trainingPlace->department);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Account $account): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Account $account, TrainingPlace $trainingPlace): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Account $account, TrainingPlace $trainingPlace): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Account $account, TrainingPlace $trainingPlace): bool
    {
        return $account->hasPermissionTo('training-places.restore.*');
    }

    public function createAdhoc(Account $account, ?string $department = null): bool
    {
        if ($department !== null) {
            return $account->hasAnyPermission([
                'training-places.create-adhoc',
                sprintf('training-places.create-adhoc.%s', $department),
            ]);
        }

        return $account->hasAnyPermission([
            'training-places.create-adhoc',
            'training-places.create-adhoc.atc',
            'training-places.create-adhoc.pilot',
        ]);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Account $account, TrainingPlace $trainingPlace): bool
    {
        return false;
    }

    /**
     * Wildcard (`training-places.view.*`) grants both departments; otherwise require the
     * department-specific permission.
     */
    public function canViewDepartment(Account $account, string $department): bool
    {
        return $account->hasAnyPermission([
            'training-places.view.*',
            sprintf('training-places.view.%s', $department),
        ]);
    }
}
