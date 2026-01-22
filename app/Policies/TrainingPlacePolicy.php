<?php

namespace App\Policies;

use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlace;

class TrainingPlacePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Account $account): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Account $account, TrainingPlace $trainingPlace): bool
    {
        return false;
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
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Account $account, TrainingPlace $trainingPlace): bool
    {
        return false;
    }
}
