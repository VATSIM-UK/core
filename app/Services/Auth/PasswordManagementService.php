<?php

namespace App\Services\Auth;

use App\Models\Mship\Account;

class PasswordManagementService
{
    public function setPassword(Account $account, string $password): void
    {
        $account->setPassword($password);
    }

    public function removePassword(Account $account): void
    {
        $account->removePassword();
    }
}
