<?php

namespace Database\Seeders;

use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use App\Models\Roster;
use App\Models\Training\WaitingList;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WaitingListStressSeeder extends Seeder
{
    use WithoutModelEvents;

    private const SIZE = 200;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->withoutModelEvents(fn () => $this->seed())();
    }

    private function seed(): void
    {
        // Skip if waiting lists already exist (prevents duplicate account creation on re-runs)
        if (WaitingList::count() > 0) {
            echo "Waiting lists already exist, skipping seeding...\n";

            return;
        }

        // Only reach here if no waiting lists exist, so we need to create them
        // First, ensure admin account exists (only created if waiting lists don't exist)
        $admin = $this->seedS1();

        /** @var WaitingList $waitingList */
        $waitingList = WaitingList::create(['name' => 'Fake TWR List', 'slug' => 'fk-twr', 'department' => 'atc', 'requires_roster_membership' => true]);
        $waitingList->save();

        foreach (range(0, self::SIZE) as $index) {
            $s1 = $this->seedS1();
            $waitingList->addToWaitingList($s1, $admin, Carbon::now()->addSeconds($index));
        }
    }

    private function seedS1(): Account
    {
        $account = Account::factory()->create();
        $qualification = Qualification::code('S1')->firstOrFail();
        $account->addQualification($qualification);
        $account->save();

        $divisionState = State::findByCode('DIVISION')->firstOrFail();
        $account->addState($divisionState, 'EUR', 'GBR');
        Roster::create(['account_id' => $account->id])->save();

        return $account;
    }
}
