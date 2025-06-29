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
    public function it_prevents_self_enrollment_when_at_capacity()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));

        $waitingList = WaitingList::factory()->create([
            'self_enrolment_enabled' => true,
            'max_capacity' => 2,
            'home_members_only' => false,
            'requires_roster_membership' => false,
        ]);

        // Fill the waiting list to capacity
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();
        $waitingList->addToWaitingList($account1, $this->privacc);
        $waitingList->addToWaitingList($account2, $this->privacc);

        // Account should not be able to self-enroll
        $this->assertFalse(WaitingListSelfEnrolment::canAccountEnrolOnList($account, $waitingList));

        // List should not appear in available lists for self-enrollment
        $availableLists = WaitingListSelfEnrolment::getListsAccountCanSelfEnrol($account);
        $this->assertEmpty($availableLists);
    }

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

    #[Test]
    public function it_dynamically_updates_available_lists_as_capacity_changes()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));

        $waitingList = WaitingList::factory()->create([
            'self_enrolment_enabled' => true,
            'max_capacity' => 2,
            'home_members_only' => false,
            'requires_roster_membership' => false,
        ]);

        // Initially, list should be available
        $availableLists = WaitingListSelfEnrolment::getListsAccountCanSelfEnrol($account);
        $this->assertCount(1, $availableLists);

        // Fill to capacity
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();
        $waitingList->addToWaitingList($account1, $this->privacc);
        $waitingList->addToWaitingList($account2, $this->privacc);

        // Now list should not be available
        $availableLists = WaitingListSelfEnrolment::getListsAccountCanSelfEnrol($account);
        $this->assertEmpty($availableLists);

        // Remove one user to free up space
        $removal = new \App\Models\Training\WaitingList\Removal(
            \App\Models\Training\WaitingList\RemovalReason::TRANSFERRED_TO_TRAINING,
            $this->privacc->id
        );
        $waitingList->removeFromWaitingList($account1, $removal);

        // List should be available again
        $availableLists = WaitingListSelfEnrolment::getListsAccountCanSelfEnrol($account);
        $this->assertCount(1, $availableLists);
    }

    #[Test]
    public function it_filters_capacity_limited_lists_correctly_with_multiple_lists()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));

        // Create multiple waiting lists with different capacity situations
        $fullList = WaitingList::factory()->create([
            'self_enrolment_enabled' => true,
            'max_capacity' => 1,
            'home_members_only' => false,
            'requires_roster_membership' => false,
        ]);

        $availableList = WaitingList::factory()->create([
            'self_enrolment_enabled' => true,
            'max_capacity' => 3,
            'home_members_only' => false,
            'requires_roster_membership' => false,
        ]);

        $unlimitedList = WaitingList::factory()->create([
            'self_enrolment_enabled' => true,
            'max_capacity' => null,
            'home_members_only' => false,
            'requires_roster_membership' => false,
        ]);

        // Fill the first list to capacity
        $user1 = Account::factory()->create();
        $fullList->addToWaitingList($user1, $this->privacc);

        // Add one user to the available list (still has space)
        $user2 = Account::factory()->create();
        $availableList->addToWaitingList($user2, $this->privacc);

        // Account should only see the available and unlimited lists
        $availableLists = WaitingListSelfEnrolment::getListsAccountCanSelfEnrol($account);
        $this->assertCount(2, $availableLists);

        $listIds = $availableLists->pluck('id')->toArray();
        $this->assertContains($availableList->id, $listIds);
        $this->assertContains($unlimitedList->id, $listIds);
        $this->assertNotContains($fullList->id, $listIds);
    }
}
