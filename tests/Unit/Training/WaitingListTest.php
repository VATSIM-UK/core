<?php

namespace Tests\Unit\Training;

use App\Models\Mship\Account;
use App\Models\Mship\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WaitingListTest extends TestCase
{
    use RefreshDatabase, WaitingListTestHelper;

    private $waitingList;
    private $staffUser;

    public function setUp()
    {
        parent::setUp();

        $this->waitingList = $this->createList();

        $this->staffUser = factory(Account::class)->create();

        $this->staffUser->roles()->attach(Role::find(1));
    }

    /** @test * */
    public function itHasASlugRouteKey()
    {
        $this->assertEquals('slug', $this->waitingList->getRouteKeyName());
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

    /** @test * */
    public function itCanHaveStudents()
    {
        $account = factory(Account::class)->make();

        $this->waitingList->addToWaitingList($account, $this->staffUser);

        $this->assertCount(1, $this->waitingList->accounts);

        $this->assertDatabaseHas('training_waiting_list_account',
            ['account_id' => $account->id, 'list_id' => $this->waitingList->id]);
    }

    /** @test * */
    public function itCanRemoveUsers()
    {
        $account = factory(Account::class)->make();

        $this->waitingList->addToWaitingList($account, $this->staffUser);

        $this->assertDatabaseHas('training_waiting_list_account',
            ['account_id' => $account->id, 'list_id' => $this->waitingList->id]);

        $this->waitingList->removeFromWaitingList($account);

        $this->assertDatabaseMissing('training_waiting_list_account',
            ['account_id' => $account->id, 'list_id' => $this->waitingList->id]);
    }

    /** @test * */
    public function itCanHaveMultipleAccountsAssociatedWithIt()
    {
        $accounts = factory(Account::class, 3)->create();

        $this->waitingList->addToWaitingList($accounts, $this->staffUser);

        $this->assertEquals(3, $this->waitingList->accounts->count());
    }

    /** @test */
    public function itAssignsTheNextPositionByDefault()
    {
        $account = factory(Account::class)->create();
        $accountSecond = factory(Account::class)->create();

        $this->waitingList->addToWaitingList($account, $this->staffUser);
        $this->waitingList->addToWaitingList($accountSecond, $this->staffUser);

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
            $this->waitingList->addToWaitingList($account, $this->staffUser);
        });

        $this->waitingList->removeFromWaitingList($accounts[1]);

        $this->assertDatabaseHas('training_waiting_list_account', [
            'list_id' => $this->waitingList->id,
            'account_id' => $accounts[2]->id,
            'position' => $accounts[2]->waitingList->first()->pivot->position,
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
            $this->waitingList->addToWaitingList($account, $this->staffUser);
        });

        $this->waitingList->promote($accounts[1], 1);

        $this->assertEquals(1, $accounts[1]->fresh()->waitingList->find($this->waitingList)->pivot->position);
        $this->assertEquals(2, $accounts[0]->fresh()->waitingList->find($this->waitingList)->pivot->position);
        $this->assertEquals(3, $accounts[2]->fresh()->waitingList->find($this->waitingList)->pivot->position);
    }

    /** @test **/
    public function itCanPromoteStudentsWithinTheListByMoreThanOne()
    {
        $accounts = factory(Account::class, 10)->create()->each(function ($account) {
            $this->waitingList->addToWaitingList($account, $this->staffUser);
        });

        $this->waitingList->promote($accounts[9], 9);

        $this->assertEquals(1, $accounts[9]->waitingList->first()->pivot->position);
        $this->assertEquals(3, $accounts[1]->fresh()->waitingList->first()->pivot->position);
        $this->assertEquals(2, $accounts[0]->fresh()->waitingList->first()->pivot->position);
        $this->assertEquals(4, $accounts[2]->fresh()->waitingList->first()->pivot->position);
    }

    /** @test */
    public function itCanDemoteStudentsWithinTheListByOne()
    {
        $accounts = factory(Account::class, 3)->create()->each(function ($account) {
            $this->waitingList->addToWaitingList($account, $this->staffUser);
        });

        $this->waitingList->demote($accounts[1], 1);

        $this->assertEquals(3, $accounts[1]->fresh()->waitingList->find($this->waitingList)->pivot->position);
        $this->assertEquals(1, $accounts[0]->fresh()->waitingList->find($this->waitingList)->pivot->position);
        $this->assertEquals(2, $accounts[2]->fresh()->waitingList->find($this->waitingList)->pivot->position);
    }
}
