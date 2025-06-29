<?php

namespace Tests\Unit\Training\WaitingList;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WaitingListCapacityTest extends TestCase
{
    use DatabaseTransactions, WaitingListTestHelper;

    private WaitingList $waitingList;

    protected function setUp(): void
    {
        parent::setUp();
        $this->waitingList = $this->createList();
        $this->actingAs($this->privacc);
    }

    #[Test]
    public function it_has_no_capacity_limit_by_default()
    {
        $this->assertFalse($this->waitingList->hasCapacityLimit());
        $this->assertNull($this->waitingList->max_capacity);
    }

    #[Test]
    public function it_can_set_a_capacity_limit()
    {
        $this->waitingList->update(['max_capacity' => 5]);

        $this->assertTrue($this->waitingList->hasCapacityLimit());
        $this->assertEquals(5, $this->waitingList->max_capacity);
    }

    #[Test]
    public function it_returns_current_capacity_correctly()
    {
        $this->assertEquals(0, $this->waitingList->getCurrentCapacity());

        // Add some users
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();

        $this->waitingList->addToWaitingList($account1, $this->privacc);
        $this->assertEquals(1, $this->waitingList->getCurrentCapacity());

        $this->waitingList->addToWaitingList($account2, $this->privacc);
        $this->assertEquals(2, $this->waitingList->getCurrentCapacity());
    }

    #[Test]
    public function it_is_not_at_capacity_when_no_limit_is_set()
    {
        // Add many users to a list with no capacity limit
        for ($i = 0; $i < 10; $i++) {
            $account = Account::factory()->create();
            $this->waitingList->addToWaitingList($account, $this->privacc);
        }

        $this->assertFalse($this->waitingList->isAtCapacity());
        $this->assertTrue($this->waitingList->hasSpaceAvailable());
        $this->assertNull($this->waitingList->getRemainingCapacity());
    }

    #[Test]
    public function it_is_not_at_capacity_when_below_limit()
    {
        $this->waitingList->update(['max_capacity' => 5]);

        // Add 3 users
        for ($i = 0; $i < 3; $i++) {
            $account = Account::factory()->create();
            $this->waitingList->addToWaitingList($account, $this->privacc);
        }

        $this->assertFalse($this->waitingList->isAtCapacity());
        $this->assertTrue($this->waitingList->hasSpaceAvailable());
        $this->assertEquals(2, $this->waitingList->getRemainingCapacity());
    }

    #[Test]
    public function it_is_at_capacity_when_limit_is_reached()
    {
        $this->waitingList->update(['max_capacity' => 3]);

        // Add exactly 3 users
        for ($i = 0; $i < 3; $i++) {
            $account = Account::factory()->create();
            $this->waitingList->addToWaitingList($account, $this->privacc);
        }

        $this->assertTrue($this->waitingList->isAtCapacity());
        $this->assertFalse($this->waitingList->hasSpaceAvailable());
        $this->assertEquals(0, $this->waitingList->getRemainingCapacity());
    }

    #[Test]
    public function it_throws_exception_when_trying_to_add_user_at_capacity()
    {
        $this->waitingList->update(['max_capacity' => 2]);

        // Fill the waiting list to capacity
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();
        $this->waitingList->addToWaitingList($account1, $this->privacc);
        $this->waitingList->addToWaitingList($account2, $this->privacc);

        // Try to add one more user
        $account3 = Account::factory()->create();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot add account to waiting list '{$this->waitingList->name}' as it has reached its maximum capacity of 2 users.");

        $this->waitingList->addToWaitingList($account3, $this->privacc);
    }

    #[Test]
    public function it_can_add_users_after_removing_someone_from_capacity_limited_list()
    {
        $this->waitingList->update(['max_capacity' => 2]);

        // Fill the waiting list to capacity
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();
        $this->waitingList->addToWaitingList($account1, $this->privacc);
        $this->waitingList->addToWaitingList($account2, $this->privacc);

        $this->assertTrue($this->waitingList->isAtCapacity());

        // Remove one user
        $removal = new \App\Models\Training\WaitingList\Removal(
            \App\Models\Training\WaitingList\RemovalReason::TrainingPlace,
            $this->privacc->id
        );
        $this->waitingList->removeFromWaitingList($account1, $removal);

        $this->assertFalse($this->waitingList->isAtCapacity());
        $this->assertEquals(1, $this->waitingList->getRemainingCapacity());

        // Now we can add another user
        $account3 = Account::factory()->create();
        $this->waitingList->addToWaitingList($account3, $this->privacc);

        $this->assertTrue($this->waitingList->isAtCapacity());
    }

    #[Test]
    public function it_handles_capacity_correctly_when_capacity_is_updated()
    {
        // Start with no capacity limit and add 5 users
        for ($i = 0; $i < 5; $i++) {
            $account = Account::factory()->create();
            $this->waitingList->addToWaitingList($account, $this->privacc);
        }

        $this->assertEquals(5, $this->waitingList->getCurrentCapacity());
        $this->assertFalse($this->waitingList->isAtCapacity());

        // Set capacity limit to 3 (below current count)
        $this->waitingList->update(['max_capacity' => 3]);

        // List should now be over capacity
        $this->assertTrue($this->waitingList->isAtCapacity());
        $this->assertEquals(0, $this->waitingList->getRemainingCapacity());

        // Should not be able to add more users
        $account = Account::factory()->create();
        $this->expectException(InvalidArgumentException::class);
        $this->waitingList->addToWaitingList($account, $this->privacc);
    }

    #[Test]
    public function it_returns_correct_remaining_capacity()
    {
        $this->waitingList->update(['max_capacity' => 10]);

        $this->assertEquals(10, $this->waitingList->getRemainingCapacity());

        // Add 3 users
        for ($i = 0; $i < 3; $i++) {
            $account = Account::factory()->create();
            $this->waitingList->addToWaitingList($account, $this->privacc);
        }

        $this->assertEquals(7, $this->waitingList->getRemainingCapacity());

        // Add 7 more users to reach capacity
        for ($i = 0; $i < 7; $i++) {
            $account = Account::factory()->create();
            $this->waitingList->addToWaitingList($account, $this->privacc);
        }

        $this->assertEquals(0, $this->waitingList->getRemainingCapacity());
    }

    #[Test]
    public function it_returns_zero_remaining_capacity_when_over_capacity()
    {
        // Add 5 users first
        for ($i = 0; $i < 5; $i++) {
            $account = Account::factory()->create();
            $this->waitingList->addToWaitingList($account, $this->privacc);
        }

        // Then set capacity to 3 (below current count)
        $this->waitingList->update(['max_capacity' => 3]);

        // Should return 0, not negative
        $this->assertEquals(0, $this->waitingList->getRemainingCapacity());
    }
}
