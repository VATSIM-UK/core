<?php

namespace Tests\Feature\TrainingPanel\WaitingLists;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WaitingListCapacityAdminTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->privacc);
    }

    #[Test]
    public function it_can_create_waiting_list_with_capacity_limit()
    {
        $waitingListData = [
            'name' => 'Test Capacity List',
            'slug' => 'test-capacity-list',
            'department' => 'atc',
            'max_capacity' => 10,
            'requires_roster_membership' => true,
            'self_enrolment_enabled' => false,
        ];

        $waitingList = WaitingList::create($waitingListData);

        $this->assertDatabaseHas('training_waiting_list', [
            'name' => 'Test Capacity List',
            'max_capacity' => 10,
        ]);

        $this->assertTrue($waitingList->hasCapacityLimit());
        $this->assertEquals(10, $waitingList->max_capacity);
    }

    #[Test]
    public function it_can_create_waiting_list_without_capacity_limit()
    {
        $waitingListData = [
            'name' => 'Test Unlimited List',
            'slug' => 'test-unlimited-list',
            'department' => 'pilot',
            'max_capacity' => null,
            'requires_roster_membership' => true,
            'self_enrolment_enabled' => false,
        ];

        $waitingList = WaitingList::create($waitingListData);

        $this->assertDatabaseHas('training_waiting_list', [
            'name' => 'Test Unlimited List',
            'max_capacity' => null,
        ]);

        $this->assertFalse($waitingList->hasCapacityLimit());
    }

    #[Test]
    public function it_can_update_waiting_list_capacity()
    {
        $waitingList = WaitingList::factory()->create([
            'max_capacity' => null,
        ]);

        // Update to add capacity limit
        $waitingList->update(['max_capacity' => 15]);

        $this->assertDatabaseHas('training_waiting_list', [
            'id' => $waitingList->id,
            'max_capacity' => 15,
        ]);

        $this->assertTrue($waitingList->fresh()->hasCapacityLimit());

        // Update to remove capacity limit
        $waitingList->update(['max_capacity' => null]);

        $this->assertDatabaseHas('training_waiting_list', [
            'id' => $waitingList->id,
            'max_capacity' => null,
        ]);

        $this->assertFalse($waitingList->fresh()->hasCapacityLimit());
    }

    #[Test]
    public function it_prevents_admin_from_adding_users_when_at_capacity()
    {
        $waitingList = WaitingList::factory()->create([
            'max_capacity' => 2,
        ]);

        // Fill to capacity
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();
        $waitingList->addToWaitingList($account1, $this->privacc);
        $waitingList->addToWaitingList($account2, $this->privacc);

        // Try to add another user via admin
        $account3 = Account::factory()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('maximum capacity of 2 users');

        $waitingList->addToWaitingList($account3, $this->privacc);
    }

    #[Test]
    public function it_allows_admin_to_add_users_when_below_capacity()
    {
        $waitingList = WaitingList::factory()->create([
            'max_capacity' => 5,
        ]);

        // Add 3 users (below capacity)
        for ($i = 0; $i < 3; $i++) {
            $account = Account::factory()->create();
            $waitingList->addToWaitingList($account, $this->privacc);
        }

        $this->assertEquals(3, $waitingList->getCurrentCapacity());
        $this->assertFalse($waitingList->isAtCapacity());

        // Should be able to add another user
        $account = Account::factory()->create();
        $waitingList->addToWaitingList($account, $this->privacc);

        $this->assertEquals(4, $waitingList->getCurrentCapacity());
        $this->assertTrue($waitingList->includesAccount($account));
    }

    #[Test]
    public function it_correctly_calculates_capacity_info_for_admin_display()
    {
        $waitingList = WaitingList::factory()->create([
            'max_capacity' => 10,
        ]);

        // Test empty list
        $this->assertEquals(0, $waitingList->getCurrentCapacity());
        $this->assertEquals(10, $waitingList->getRemainingCapacity());

        // Add some users
        for ($i = 0; $i < 7; $i++) {
            $account = Account::factory()->create();
            $waitingList->addToWaitingList($account, $this->privacc);
        }

        $this->assertEquals(7, $waitingList->getCurrentCapacity());
        $this->assertEquals(3, $waitingList->getRemainingCapacity());
        $this->assertFalse($waitingList->isAtCapacity());

        // Fill to capacity
        for ($i = 0; $i < 3; $i++) {
            $account = Account::factory()->create();
            $waitingList->addToWaitingList($account, $this->privacc);
        }

        $this->assertEquals(10, $waitingList->getCurrentCapacity());
        $this->assertEquals(0, $waitingList->getRemainingCapacity());
        $this->assertTrue($waitingList->isAtCapacity());
    }

    #[Test]
    public function it_handles_capacity_validation_when_editing_existing_list()
    {
        $waitingList = WaitingList::factory()->create([
            'max_capacity' => null, // Start unlimited
        ]);

        // Add 5 users
        for ($i = 0; $i < 5; $i++) {
            $account = Account::factory()->create();
            $waitingList->addToWaitingList($account, $this->privacc);
        }

        // Update capacity to be less than current users
        $waitingList->update(['max_capacity' => 3]);

        // List should be considered at capacity
        $this->assertTrue($waitingList->isAtCapacity());
        $this->assertEquals(0, $waitingList->getRemainingCapacity());

        // Should not be able to add more users
        $account = Account::factory()->create();
        $this->expectException(\InvalidArgumentException::class);
        $waitingList->addToWaitingList($account, $this->privacc);
    }

    #[Test]
    public function it_allows_removing_users_from_capacity_limited_list()
    {
        $waitingList = WaitingList::factory()->create([
            'max_capacity' => 2,
        ]);

        // Fill to capacity
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();
        $waitingList->addToWaitingList($account1, $this->privacc);
        $waitingList->addToWaitingList($account2, $this->privacc);

        $this->assertTrue($waitingList->isAtCapacity());

        // Remove one user
        $removal = new \App\Models\Training\WaitingList\Removal(
            \App\Models\Training\WaitingList\RemovalReason::TrainingPlace,
            $this->privacc->id
        );
        $waitingList->removeFromWaitingList($account1, $removal);

        // Should now have space available
        $this->assertFalse($waitingList->isAtCapacity());
        $this->assertEquals(1, $waitingList->getCurrentCapacity());
        $this->assertEquals(1, $waitingList->getRemainingCapacity());

        // Should be able to add another user
        $account3 = Account::factory()->create();
        $waitingList->addToWaitingList($account3, $this->privacc);

        $this->assertTrue($waitingList->isAtCapacity());
    }

    #[Test]
    public function it_validates_minimum_capacity_value()
    {
        $waitingList = WaitingList::factory()->create();

        // The max_capacity field accepts null or positive integers
        // Setting to 0 should be allowed (though not practical)
        $waitingList->update(['max_capacity' => 0]);
        $this->assertEquals(0, $waitingList->max_capacity);

        // Setting to null should be allowed (unlimited)
        $waitingList->update(['max_capacity' => null]);
        $this->assertNull($waitingList->max_capacity);

        // Setting to positive integer should be allowed
        $waitingList->update(['max_capacity' => 10]);
        $this->assertEquals(10, $waitingList->max_capacity);
    }
}
