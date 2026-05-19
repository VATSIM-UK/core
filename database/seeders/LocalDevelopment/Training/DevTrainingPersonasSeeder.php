<?php

declare(strict_types=1);

namespace Database\Seeders\LocalDevelopment\Training;

use App\Models\Mship\Account;
use Database\Seeders\LocalDevelopment\Training\Concerns\AssignsDevTrainingRoles;
use Database\Seeders\LocalDevelopment\Training\Concerns\CreatesLinkedAccount;
use Illuminate\Database\Seeder;

/**
 * Creates fictional mship accounts (with CTS members) and assigns dev training roles.
 *
 * @see database/seeders/LocalDevelopment/README.md
 * @see DevTrainingPersonas
 */
class DevTrainingPersonasSeeder extends Seeder
{
    use AssignsDevTrainingRoles;
    use CreatesLinkedAccount;

    public function run(): void
    {
        DevTrainingFoundation::$staff = $this->seedStaff();
        DevTrainingFoundation::$student = $this->seedStudent();

        $this->command?->info('Dev training staff and student personas created.');
    }

    private function seedStaff(): Account
    {
        $account = $this->createLinkedAccount(
            DevTrainingPersonas::STAFF_CID,
            'Dev',
            'Training Staff',
            DevTrainingPersonas::STAFF_EMAIL,
        );

        $this->assignDevTrainingStaffRole($account);

        return $account;
    }

    private function seedStudent(): Account
    {
        $account = $this->createLinkedAccount(
            DevTrainingPersonas::STUDENT_CID,
            'Dev',
            'Training Student',
            DevTrainingPersonas::STUDENT_EMAIL,
        );

        $this->assignDevTrainingStudentRole($account);

        return $account;
    }
}
