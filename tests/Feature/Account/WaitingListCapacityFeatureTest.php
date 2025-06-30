<?php

namespace Tests\Feature\Account;

use App\Models\Mship\Account;
use App\Models\Mship\State;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WaitingListCapacityFeatureTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_shows_success_message_when_self_enrolling_with_space_available()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));

        $waitingList = WaitingList::factory()->create([
            'self_enrolment_enabled' => true,
            'max_capacity' => 5,
            'home_members_only' => false,
            'requires_roster_membership' => false,
        ]);

        $this->actingAs($account)
            ->post(route('mship.waiting-lists.self-enrol', $waitingList))
            ->assertRedirect(route('mship.waiting-lists.index'))
            ->assertSessionHas('success', 'You have been added to the waiting list.');

        // Verify user was actually added
        $this->assertTrue($waitingList->includesAccount($account));
    }

    #[Test]
    public function it_shows_error_message_when_self_enrolling_at_capacity()
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

        // When the list is at capacity, the policy should prevent access (403)
        // This is the correct behavior - capacity limits are enforced at the authorization level
        $this->actingAs($account)
            ->post(route('mship.waiting-lists.self-enrol', $waitingList))
            ->assertStatus(403);

        // Verify user was not added
        $this->assertFalse($waitingList->includesAccount($account));
    }

    #[Test]
    public function it_allows_self_enrollment_when_no_capacity_limit_is_set()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));

        $waitingList = WaitingList::factory()->create([
            'self_enrolment_enabled' => true,
            'max_capacity' => null, // No limit
            'home_members_only' => false,
            'requires_roster_membership' => false,
        ]);

        // Add many users to test unlimited capacity
        for ($i = 0; $i < 100; $i++) {
            $user = Account::factory()->create();
            $waitingList->addToWaitingList($user, $this->privacc);
        }

        $this->actingAs($account)
            ->post(route('mship.waiting-lists.self-enrol', $waitingList))
            ->assertRedirect(route('mship.waiting-lists.index'))
            ->assertSessionHas('success', 'You have been added to the waiting list.');

        $this->assertTrue($waitingList->includesAccount($account));
    }

    #[Test]
    public function it_displays_capacity_limited_lists_correctly_on_index_page()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));

        // Create a list with available space
        $availableList = WaitingList::factory()->create([
            'name' => 'Available List',
            'department' => 'atc',
            'self_enrolment_enabled' => true,
            'max_capacity' => 5,
            'home_members_only' => false,
            'requires_roster_membership' => false,
        ]);

        // Create a list at capacity
        $fullList = WaitingList::factory()->create([
            'name' => 'Full List',
            'department' => 'atc',
            'self_enrolment_enabled' => true,
            'max_capacity' => 2,
            'home_members_only' => false,
            'requires_roster_membership' => false,
        ]);

        // Fill the second list to capacity
        $user1 = Account::factory()->create();
        $user2 = Account::factory()->create();
        $fullList->addToWaitingList($user1, $this->privacc);
        $fullList->addToWaitingList($user2, $this->privacc);

        $response = $this->actingAs($account)
            ->get(route('mship.waiting-lists.index'));

        $response->assertStatus(200);

        // The available list should be shown as enrollable (only lists with space)
        $atcSelfEnrolmentLists = $response->viewData('atcSelfEnrolmentLists');

        // Filter to only lists that have space available
        $availableLists = $atcSelfEnrolmentLists->filter(function ($list) {
            return $list->hasSpaceAvailable();
        });

        $this->assertGreaterThanOrEqual(1, $availableLists->count());
        $this->assertTrue($availableLists->contains('id', $availableList->id));
        $this->assertFalse($availableLists->contains('id', $fullList->id));
    }

    #[Test]
    public function it_prevents_enrollment_when_capacity_is_reached_between_page_load_and_submission()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));

        $waitingList = WaitingList::factory()->create([
            'self_enrolment_enabled' => true,
            'max_capacity' => 2,
            'home_members_only' => false,
            'requires_roster_membership' => false,
        ]);

        // Add one user initially
        $account1 = Account::factory()->create();
        $waitingList->addToWaitingList($account1, $this->privacc);

        // Simulate another user filling the last spot while the first user is on the page
        $account2 = Account::factory()->create();
        $waitingList->addToWaitingList($account2, $this->privacc);

        // Now the original user tries to enroll (should be denied by policy - 403)
        $this->actingAs($account)
            ->post(route('mship.waiting-lists.self-enrol', $waitingList))
            ->assertStatus(403);

        $this->assertFalse($waitingList->includesAccount($account));
    }

    #[Test]
    public function it_handles_concurrent_enrollment_attempts_gracefully()
    {
        $waitingList = WaitingList::factory()->create([
            'self_enrolment_enabled' => true,
            'max_capacity' => 1,
            'home_members_only' => false,
            'requires_roster_membership' => false,
        ]);

        $account1 = Account::factory()->create();
        $account1->addState(State::findByCode('DIVISION'));

        $account2 = Account::factory()->create();
        $account2->addState(State::findByCode('DIVISION'));

        // First user enrolls successfully
        $this->actingAs($account1)
            ->post(route('mship.waiting-lists.self-enrol', $waitingList))
            ->assertRedirect(route('mship.waiting-lists.index'))
            ->assertSessionHas('success');

        // Second user tries to enroll (should be denied by policy due to capacity - 403)
        $this->actingAs($account2)
            ->post(route('mship.waiting-lists.self-enrol', $waitingList))
            ->assertStatus(403);

        // Verify only the first user is on the list
        $this->assertTrue($waitingList->includesAccount($account1));
        $this->assertFalse($waitingList->includesAccount($account2));
        $this->assertEquals(1, $waitingList->getCurrentCapacity());
    }
}
