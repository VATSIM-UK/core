<?php

namespace Tests\Unit\Training;

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

    /** @test **/
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

    /** @test **/
    public function itDetectsIfAtcList()
    {
        $atcList = factory(WaitingList::class)->create(['department' => 1]);

        $this->assertTrue($atcList->isAtcList());
        $this->assertFalse($atcList->isPilotList());
    }

    /** @test **/
    public function itDetectsIfPilotList()
    {
        $atcList = factory(WaitingList::class)->create(['department' => 2]);

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
            ['account_id' => $account->id, 'list_id' => $this->waitingList->id, 'deleted_at' => now(), 'position' => -1]);
    }

    /** @test * */
    public function itCanHaveMultipleAccountsAssociatedWithIt()
    {
        $accounts = factory(Account::class, 3)->create();

        $this->waitingList->addToWaitingList($accounts, $this->privacc);

        $this->assertEquals(3, $this->waitingList->accounts->count());
    }

    /** @test */
    public function itAssignsTheNextPositionByDefault()
    {
        $account = factory(Account::class)->create();
        $accountSecond = factory(Account::class)->create();

        $this->waitingList->addToWaitingList($account, $this->privacc);
        $this->waitingList->addToWaitingList($accountSecond, $this->privacc);

        $this->assertDatabaseHas('training_waiting_list_account', [
            'list_id' => $this->waitingList->id,
            'account_id' => $account->id,
            'position' => 1,
        ]);

        $this->assertDatabaseHas('training_waiting_list_account', [
            'list_id' => $this->waitingList->id,
            'account_id' => $accountSecond->id,
            'position' => 2,
        ]);
    }

    /** @test **/
    public function itUpdatesPositionsOnWaitingListRemoval()
    {
        $accounts = factory(Account::class, 3)->create()->each(function ($account) {
            $this->waitingList->addToWaitingList($account, $this->privacc);
        });

        $this->waitingList->removeFromWaitingList($accounts[1]);

        $this->assertDatabaseHas('training_waiting_list_account', [
            'list_id' => $this->waitingList->id,
            'account_id' => $accounts[2]->id,
            'position' => $accounts[2]->waitingLists->first()->pivot->position,
        ]);
    }

    /** @test */
    public function itCanHaveStaffManagingTheList()
    {
        $staffAccount = factory(Account::class)->create();

        $this->waitingList->addManager($staffAccount);

        $this->assertDatabaseHas('training_waiting_list_staff',
            ['list_id' => $this->waitingList->id, 'account_id' => $staffAccount->id]);
    }

    /** @test * */
    public function itCanRetrieveStaffOnScope()
    {
        $staffAccount = factory(Account::class)->create();

        $this->waitingList->addManager($staffAccount);

        $this->assertEquals(1, $this->waitingList->staff()->get()->count());
    }

    /** @test **/
    public function itCanPromoteStudentsWithinTheListByOne()
    {
        $accounts = factory(Account::class, 3)->create()->each(function ($account) {
            $this->waitingList->addToWaitingList($account, $this->privacc);
        });

        $this->waitingList->promote($accounts[1], 1);

        $this->assertEquals(1, $accounts[1]->fresh()->waitingLists->find($this->waitingList)->pivot->position);
        $this->assertEquals(2, $accounts[0]->fresh()->waitingLists->find($this->waitingList)->pivot->position);
        $this->assertEquals(3, $accounts[2]->fresh()->waitingLists->find($this->waitingList)->pivot->position);
    }

    /** @test **/
    public function itCanPromoteStudentsWithinTheListByMoreThanOne()
    {
        $accounts = factory(Account::class, 10)->create()->each(function ($account) {
            $this->waitingList->addToWaitingList($account, $this->privacc);
        });

        $this->waitingList->promote($accounts[9], 9);

        $this->assertEquals(1, $accounts[9]->waitingLists->first()->pivot->position);
        $this->assertEquals(3, $accounts[1]->fresh()->waitingLists->first()->pivot->position);
        $this->assertEquals(2, $accounts[0]->fresh()->waitingLists->first()->pivot->position);
        $this->assertEquals(4, $accounts[2]->fresh()->waitingLists->first()->pivot->position);
    }

    /** @test */
    public function itCanDemoteStudentsWithinTheListByOne()
    {
        $accounts = factory(Account::class, 3)->create()->each(function ($account) {
            $this->waitingList->addToWaitingList($account, $this->privacc);
        });

        $this->waitingList->demote($accounts[1], 1);

        $this->assertEquals(3, $accounts[1]->fresh()->waitingLists->find($this->waitingList)->pivot->position);
        $this->assertEquals(1, $accounts[0]->fresh()->waitingLists->find($this->waitingList)->pivot->position);
        $this->assertEquals(2, $accounts[2]->fresh()->waitingLists->find($this->waitingList)->pivot->position);
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
