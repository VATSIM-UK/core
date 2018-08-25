<?php

namespace App\Policies\Nova;

use App\Models\Mship\Account;
use App\Policies\BasePolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccountPolicy extends BasePolicy
{
    use HandlesAuthorization;

    public function create(Account $account)
    {
        return false;
    }

    public function update(Account $account)
    {
        // TODO: Implement update() method.
    }

    public function view(Account $account)
    {
        // TODO: Implement view() method.
    }

    public function delete(Account $account)
    {
        return false;
    }

    public function restore(Account $account)
    {
        // TODO: Implement restore() method.
    }

    public function forceDelete(Account $account)
    {
        return false;
    }

    public function viewAny(Account $account)
    {
        // TODO: Implement viewAny() method.
    }
}
