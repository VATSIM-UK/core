<?php

namespace Tests\Unit\Training\WaitingList;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListFlag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WaitingListTest extends TestCase
{
    use DatabaseTransactions, WaitingListTestHelper;

    private $waitingList;
    private $staffUser;

    public function setUp(): void
    {
        parent::setUp();

        $this->waitingList = $this->createList();
    }

    /** @test * */
    public function itDisplaysNameOnToString()
    {
        $this->assertEquals($this->waitingList->name, $this->waitingList);
    }

    /** @test * */
    public function itHasAName()
    {
        $this->assertNotNull($this->waitingList->name);
        $this->assertNotNull($this->waitingList->slug);
    }

    /** @test * */
    public function itDetectsIfAtcList()
    {
        $atcList = factory(WaitingList::class)->create(['department' => 'atc']);

        $this->assertTrue($atcList->isAtcList());
        $this->assertFalse($atcList->isPilotList());
    }

    /** @test * */
    public function itDetectsIfPilotList()
    {
        $atcList = factory(WaitingList::class)->create(['department' => 'pilot']);

        $this->assertTrue($atcList->isPilotList());
        $this->assertFalse($atcList->isAtcList());
    }

    /** @test * */
    public function itCanHaveStudents()
    {
        $account = factory(Account::class)->make();

        $this->waitingList->addToWaitingList($account, $this->privacc);

        $this->assertCount(1, $this->waitingList->accounts);

        $this->assertDatabaseHas('training_waiting_list_account',
            ['account_id' => $account->id, 'list_id' => $this->waitingList->id]);
    }

    /** @test * */
    public function itCanRemoveUsers()
    {
        $account = factory(Account::class)->make();

        $this->waitingList->addToWaitingList($account, $this->privacc);

        $this->assertDatabaseHas('training_waiting_list_account',
            ['account_id' => $account->id, 'list_id' => $this->waitingList->id]);

        $this->waitingList->removeFromWaitingList($account);

        $this->assertDatabaseHas('training_waiting_list_account',
            [
                'account_id' => $account->id, 'list_id' => $this->waitingList->id, 'deleted_at' => now(),
            ]);
    }

    /** @test * */
    public function itUpdatesPositionsOnWaitingListRemoval()
    {
        $accounts = factory(Account::class, 3)->create()->each(function ($account) {
            $this->waitingList->addToWaitingList($account, $this->privacc);
        });

        $this->waitingList->removeFromWaitingList($accounts[1]);

        $this->assertDatabaseHas('training_waiting_list_account', [
            'list_id' => $this->waitingList->id,
            'account_id' => $accounts[2]->id,
        ]);
    }

    /** @test */
    public function itCanHaveABooleanFlag()
    {
        $flag = factory(WaitingListFlag::class)->create();
        $this->waitingList->addFlag($flag);

        $this->assertTrue($this->waitingList->flags->contains($flag));
    }

    /** @test */
    public function itCanHaveFlagsRemoved()
    {
        $flag = factory(WaitingListFlag::class)->create();
        $this->waitingList->addFlag($flag);

        $this->waitingList->removeFlag($flag);

        $this->assertFalse($this->waitingList->flags->contains($flag));
    }
}
