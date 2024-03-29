<?php

namespace Tests\Unit\Account\Roster;

use App\Events\Mship\Qualifications\QualificationAdded;
use App\Listeners\Mship\AddNewlyQualifiedS1ToRoster;
use App\Models\Cts\Member;
use App\Models\Cts\PracticalResult;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Repositories\Cts\ExamResultRepository;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class NewlyQualifiedControllerTest extends TestCase
{
    public function test_adds_newly_qualified_controller_to_roster()
    {
        $account = Account::factory()->create(['id' => 1111111]);
        // make sure the QualificationAdded event doesn't fire for test setup.
        Event::fakeFor(function () use ($account) {
            $account->addQualification(Qualification::code('OBS')->first());
        });

        $newQualification = Qualification::code('S1')->first();

        $ctsMember = factory(Member::class)->create([
            'cid' => $account->id,
        ]);
        PracticalResult::factory()->create([
            'student_id' => $ctsMember->id,
            'exam' => 'OBS',
            'result' => PracticalResult::PASSED,
            'date' => now(),
        ]);

        $event = new QualificationAdded($account->fresh(), $newQualification);

        $listener = new AddNewlyQualifiedS1ToRoster(new ExamResultRepository());
        $listener->handle($event);

        $this->assertDatabaseHas('roster', [
            'account_id' => $account->id,
        ]);
    }

    public function test_does_not_add_to_roster_if_member_logging_onto_core_without_recent_exam()
    {
        $account = Account::factory()->create(['id' => 1111112]);
        // make sure the QualificationAdded event doesn't fire for test setup.
        Event::fakeFor(function () use ($account) {
            $account->addQualification(Qualification::code('OBS')->first());
        });

        $newQualification = Qualification::code('S1')->first();

        $ctsMember = factory(Member::class)->create([
            'cid' => $account->id,
        ]);
        PracticalResult::factory([
            'student_id' => $ctsMember->id,
            'exam' => 'OBS',
            'result' => PracticalResult::PASSED,
            'date' => now()->subMonths(2),
        ])->create();

        $event = new QualificationAdded($account->fresh(), $newQualification);

        $listener = new AddNewlyQualifiedS1ToRoster(new ExamResultRepository());
        $listener->handle($event);

        $this->assertDatabaseMissing('roster', [
            'account_id' => $account->id,
        ]);
    }

    public function test_does_not_add_to_roster_when_new_qualification_is_not_s1()
    {
        $account = Account::factory()->create(['id' => 1111113]);
        // make sure the QualificationAdded event doesn't fire for test setup.
        Event::fakeFor(function () use ($account) {
            $account->addQualification(Qualification::code('OBS')->first());
        });

        $newQualification = Qualification::code('OBS')->first();

        $ctsMember = factory(Member::class)->create([
            'cid' => $account->id,
        ]);
        PracticalResult::factory()->create([
            'student_id' => $ctsMember->id,
            'exam' => 'OBS',
            'result' => PracticalResult::PASSED,
            'date' => now(),
        ]);

        $event = new QualificationAdded($account->fresh(), $newQualification);

        $listener = new AddNewlyQualifiedS1ToRoster(new ExamResultRepository());
        $listener->handle($event);

        $this->assertDatabaseMissing('roster', [
            'account_id' => $account->id,
        ]);
    }
}
