<?php

namespace Tests\Unit\Training\WaitingList;

use App\Models\Mship\Account;
use App\Models\Mship\State;
use App\Models\Training\WaitingList;
use App\Services\Training\WaitingListSelfEnrolment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WaitingListSelfEnrolmentCapacityTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_allows_self_enrollment_when_below_capacity()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));

        $waitingList = WaitingList::factory()->create([
            'self_enrolment_enabled' => true,
            'max_capacity' => 3,
            'home_members_only' => false,
            'requires_roster_membership' => false,
        ]);

        // Add only 1 user (below capacity of 3)
        $account1 = Account::factory()->create();
        $waitingList->addToWaitingList($account1, $this->privacc);

        // Account should be able to self-enroll
        $this->assertTrue(WaitingListSelfEnrolment::canAccountEnrolOnList($account, $waitingList));

        // List should appear in available lists for self-enrollment
        $availableLists = WaitingListSelfEnrolment::getListsAccountCanSelfEnrol($account);
        $this->assertCount(1, $availableLists);
        $this->assertEquals($waitingList->id, $availableLists->first()->id);
    }

    #[Test]
    public function it_allows_self_enrollment_when_no_capacity_limit()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));

        $waitingList = WaitingList::factory()->create([
            'self_enrolment_enabled' => true,
            'max_capacity' => null, // No capacity limit
            'home_members_only' => false,
            'requires_roster_membership' => false,
        ]);

        // Add many users to the list
        for ($i = 0; $i < 10; $i++) {
            $user = Account::factory()->create();
            $waitingList->addToWaitingList($user, $this->privacc);
        }

        // Account should still be able to self-enroll
        $this->assertTrue(WaitingListSelfEnrolment::canAccountEnrolOnList($account, $waitingList));

        // List should appear in available lists for self-enrollment
        $availableLists = WaitingListSelfEnrolment::getListsAccountCanSelfEnrol($account);
        $this->assertCount(1, $availableLists);
    }
}
