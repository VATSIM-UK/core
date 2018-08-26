<?php

namespace App\Policies\Nova;

use App\Models\Mship\Account;
use App\Policies\BasePolicy;

class RolePolicy extends BasePolicy
{

    public function viewAny(Account $account)
    {
        // TODO: Implement viewAny() method.
    }

    public function view(Account $account)
    {
        // TODO: Implement view() method.
    }

    public function create(Account $account)
    {
        // TODO: Implement create() method.
    }

    public function update(Account $account)
    {
        // TODO: Implement update() method.
    }

    public function delete(Account $account)
    {
        // TODO: Implement delete() method.
    }

    public function restore(Account $account)
    {
        // TODO: Implement restore() method.
    }

    public function forceDelete(Account $account)
    {
        // TODO: Implement forceDelete() method.
    }
}