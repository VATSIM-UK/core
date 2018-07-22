<?php

namespace Tests\Unit\Training;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Training\WaitingList;
use App\Models\Mship\Account;
use Tests\TestCase;

class WaitingListTest extends TestCase
{
    use DatabaseTransactions;

    private $waitingList;

    public function setUp()
    {
        parent::setUp();

        $this->waitingList = factory(\App\Models\Training\WaitingList::class)->create();
    }

    /** @test * */
    public function itHasASlugRouteKey()
    {
        $this->assertEquals('slug', $this->waitingList->getRouteKeyName());
    }

    /** @test * */
    public function itHasAName()
    {
        $this->assertNotNull($this->waitingList->name);
        $this->assertNotNull($this->waitingList->slug);
    }

    /** @test * */
    public function itCanHaveStudents()
    {
        $account = factory(Account::class)->make();

        $this->waitingList->addToWaitingList($account);

        $this->assertCount(1, $this->waitingList->students);

        $this->assertDatabaseHas('training_waiting_list_account',
            ['account_id' => $account->id, 'list_id' => $this->waitingList->id]);
    }

    /** @test * */
    public function itCanRemoveUsers()
    {
        $account = factory(Account::class)->make();

        $this->waitingList->addToWaitingList($account);

        $this->assertDatabaseHas('training_waiting_list_account',
            ['account_id' => $account->id, 'list_id' => $this->waitingList->id]);

        $this->waitingList->removeFromWaitingList($account);

        $this->assertDatabaseMissing('training_waiting_list_account',
            ['account_id' => $account->id, 'list_id' => $this->waitingList->id]);
    }

    /** @test * */
    public function itCanScopeActiveLists()
    {
        $nonActiveList = factory(WaitingList::class)->create(['active' => false]);

        $this->assertEquals(1, $this->waitingList->active()->count());

        $this->assertEquals(2, $this->waitingList->all()->count());
    }
}
