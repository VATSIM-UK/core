<?php

declare(strict_types=1);

namespace Database\Seeders\LocalDevelopment\Training\Concerns;

use App\Models\Mship\Account;
use Database\Seeders\LocalDevelopment\Training\DevTrainingPersonas;

trait AssignsDevTrainingRoles
{
    protected function assignDevTrainingStaffRole(Account $account): void
    {
        $account->syncRoles([DevTrainingPersonas::STAFF_ROLE]);
    }

    protected function assignDevTrainingStudentRole(Account $account): void
    {
        $account->syncRoles([DevTrainingPersonas::STUDENT_ROLE]);
    }
}
