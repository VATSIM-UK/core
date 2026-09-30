<?php

declare(strict_types=1);

namespace Database\Seeders\LocalDevelopment;

use App\Models\Mship\Account;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Creates all the sandbox accounts and grants each the privacc role
 */
class SandboxAccountsSeeder extends Seeder
{
    /**
     * CID => [first name, last name]
     */
    private const ACCOUNTS = [
        10000000 => ['Web', 'Zero'],
        10000001 => ['Web', 'One'],
        10000002 => ['Web', 'Two'],
        10000003 => ['Web', 'Three'],
        10000004 => ['Web', 'Four'],
        10000005 => ['Web', 'Five'],
        10000006 => ['Web', 'Six'],
        10000007 => ['Web', 'Seven'],
        10000008 => ['Web', 'Eight'],
        10000009 => ['Web', 'Nine'],
        10000010 => ['Web', 'Ten'],
    ];

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('SandboxAccountsSeeder may only be run in the local or testing environment.');

            return;
        }

        $superman = Role::findByName('privacc');

        $created = 0;
        $granted = 0;

        foreach (self::ACCOUNTS as $cid => [$firstName, $lastName]) {
            $account = Account::find($cid);

            if ($account === null) {
                $account = new Account([
                    'id' => $cid,
                    'name_first' => $firstName,
                    'name_last' => $lastName,
                    'email' => sprintf('%d@sandbox.vatsim.test', $cid),
                    'joined_at' => now(),
                ]);

                // Quietly, to avoid firing the Discord/TeamSpeak sync listeners.
                $account->saveQuietly();
                $created++;
            }

            if (! $account->hasRole($superman)) {
                $account->assignRole($superman);
                $granted++;
            }
        }

        $this->command?->info(sprintf(
            'Sandbox accounts: %d created, %d granted the superman role (%d total).',
            $created,
            $granted,
            count(self::ACCOUNTS),
        ));
    }
}
