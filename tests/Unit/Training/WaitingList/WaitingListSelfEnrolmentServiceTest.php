<?php

namespace Tests\Unit\Training\WaitingList;

use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use App\Models\Roster;
use App\Models\Training\WaitingList;
use App\Services\Training\WaitingListSelfEnrolment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WaitingListSelfEnrolmentServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // reset roster between tests.
        Roster::truncate();
    }

    public function test_returns_empty_collection_when_no_lists_marked_as_enrollable()
    {
        $account = Account::factory()->create();
        factory(WaitingList::class)->create([
            'self_enrolment_enabled' => false,
        ]);

        $this->assertEmpty(WaitingListSelfEnrolment::getListsAccountCanSelfEnrol($account));
    }

    public function test_cannot_enrol_if_already_on_list_when_enabled()
    {
        $account = Account::factory()->create();
        $waitingList = factory(WaitingList::class)->create([
            'self_enrolment_enabled' => true,
        ]);

        $waitingList->addToWaitingList($account, $this->privacc);

        $this->assertFalse(WaitingListSelfEnrolment::canAccountEnrolOnList($account, $waitingList));
    }

    public function test_can_enrol_when_only_home_membership_required()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));

        $waitingList = factory(WaitingList::class)->create([
            'self_enrolment_enabled' => true,
            'home_members_only' => true,
            'requires_roster_membership' => false,
        ]);

        $this->assertTrue(WaitingListSelfEnrolment::canAccountEnrolOnList($account, $waitingList));
    }

    public function test_can_enrol_on_home_member_list_when_on_roster_if_configured()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));

        Roster::create(['account_id' => $account->id]);

        $waitingList = factory(WaitingList::class)->create([
            'self_enrolment_enabled' => true,
            'home_members_only' => true,
            'requires_roster_membership' => true,
        ]);

        $this->assertTrue(WaitingListSelfEnrolment::canAccountEnrolOnList($account, $waitingList));
    }

    public function test_cannot_enrol_on_home_member_list_when_not_on_roster_if_configured()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('VISITING'));

        $waitingList = factory(WaitingList::class)->create([
            'self_enrolment_enabled' => true,
            'home_members_only' => true,
            'requires_roster_membership' => true,
        ]);

        $this->assertFalse(WaitingListSelfEnrolment::canAccountEnrolOnList($account, $waitingList));
    }

    public function test_cannot_enrol_when_home_membership_required()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('VISITING'));

        $waitingList = factory(WaitingList::class)->create([
            'self_enrolment_enabled' => true,
            'home_members_only' => true,
        ]);

        $this->assertFalse(WaitingListSelfEnrolment::canAccountEnrolOnList($account, $waitingList));
    }

    public function test_can_enrol_when_home_membership_and_qualification_required()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));

        $qualification = Qualification::code('OBS')->first();
        $account->addQualification($qualification);

        $waitingList = factory(WaitingList::class)->create([
            'self_enrolment_enabled' => true,
            'home_members_only' => true,
            'self_enrolment_maximum_qualification_id' => $qualification->id,
        ]);

        $this->assertTrue(WaitingListSelfEnrolment::canAccountEnrolOnList($account, $waitingList));
    }

    public function test_cannot_enrol_when_home_member_but_not_qualification()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));

        $qualification = Qualification::code('OBS')->first();
        $account->addQualification($qualification);

        $waitingList = factory(WaitingList::class)->create([
            'self_enrolment_enabled' => true,
            'home_members_only' => true,
            'self_enrolment_maximum_qualification_id' => Qualification::code('S1')->first()->id,
        ]);

        $this->assertFalse(WaitingListSelfEnrolment::canAccountEnrolOnList($account, $waitingList));
    }

    public function test_can_enrol_if_no_requirements_set_but_enabled()
    {
        $account = Account::factory()->create();

        $waitingList = factory(WaitingList::class)->create([
            'self_enrolment_enabled' => true,
            'home_members_only' => false,
            'self_enrolment_maximum_qualification_id' => null,
        ]);

        $this->assertTrue(WaitingListSelfEnrolment::canAccountEnrolOnList($account, $waitingList));
    }

    public function test_cannot_enrol_when_active_qualification_not_maximum()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));

        $qualification = Qualification::code('OBS')->first();
        $account->addQualification($qualification);

        $qualification = Qualification::code('S1')->first();
        $account->addQualification($qualification);

        $waitingList = factory(WaitingList::class)->create([
            'self_enrolment_enabled' => true,
            'home_members_only' => false,
            'self_enrolment_maximum_qualification_id' => Qualification::code('OBS')->first()->id,
        ]);

        $this->assertFalse(WaitingListSelfEnrolment::canAccountEnrolOnList($account, $waitingList));
    }
}
