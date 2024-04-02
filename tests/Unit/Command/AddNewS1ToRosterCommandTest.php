<?php

namespace Tests\Unit\Command;

use App\Models\Cts\Member;
use App\Models\Cts\PracticalResult;
use App\Models\Mship\Account;
use App\Models\Mship\State;
use App\Models\Roster;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AddNewS1ToRosterCommandTest extends TestCase
{
    public function test_detects_recent_s1_exams_and_adds_when_not_on_roster_home_member()
    {
        $account = Account::factory()->create(['id' => 1111111]);
        $account->addState(State::findByCode('DIVISION'));

        $ctsMember = factory(Member::class)->create(['cid' => $account->id]);
        PracticalResult::factory()->create(['student_id' => $ctsMember->id, 'result' => PracticalResult::PASSED, 'exam' => 'OBS', 'date' => now()->subDays(1)]);

        Artisan::call('roster:check-new-s1-exams');

        $this->assertDatabaseHas('roster', [
            'account_id' => $account->id,
        ]);
    }

    public function test_maintains_roster_when_already_on_roster()
    {
        $account = Account::factory()->create(['id' => 1111111]);
        $account->addState(State::findByCode('DIVISION'));

        $ctsMember = factory(Member::class)->create(['cid' => $account->id]);
        PracticalResult::factory()->create(['student_id' => $ctsMember->id, 'result' => PracticalResult::PASSED, 'exam' => 'OBS', 'date' => now()->subDays(1)]);

        Roster::create(['account_id' => $account->id]);

        Artisan::call('roster:check-new-s1-exams');

        $this->assertDatabaseHas('roster', [
            'account_id' => $account->id,
        ]);
    }

    public function test_does_not_add_when_not_on_roster_and_missing_division_state()
    {
        $account = Account::factory()->create(['id' => 1111111]);
        $account->addState(State::findByCode('VISITING'));

        $ctsMember = factory(Member::class)->create(['cid' => $account->id]);
        PracticalResult::factory()->create(['student_id' => $ctsMember->id, 'result' => PracticalResult::PASSED, 'exam' => 'OBS', 'date' => now()->subDays(1)]);

        Artisan::call('roster:check-new-s1-exams');

        $this->assertDatabaseMissing('roster', [
            'account_id' => $account->id,
        ]);
    }
}
