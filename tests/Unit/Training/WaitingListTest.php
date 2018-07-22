<?php

namespace Tests\Unit\Training;

use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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

    /** @test **/
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
}
