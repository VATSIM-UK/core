<?php

namespace App\Policies\Nova;

use App\Models\Mship\Account;
use App\Policies\BasePolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class QualificationPolicy extends BasePolicy
{
    use HandlesAuthorization;

    public function before(Account $account, $policy)
    {
    }

    public function viewAny(Account $account)
    {
        return true;
    }

    public function view(Account $account)
    {
        return false;
    }

    public function create(Account $account)
    {
        return false;
    }

    public function update(Account $account)
    {
        return false;
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
}
