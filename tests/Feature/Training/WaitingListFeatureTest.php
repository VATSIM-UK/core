<?php

namespace Tests\Feature\Training;

use App\Events\Training\AccountAddedToWaitingList;
use App\Listeners\Training\WaitingListEventSubscriber;
use App\Models\Mship\Account;
use App\Models\Mship\Role;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class WaitingListFeatureTest extends TestCase
{
    use DatabaseTransactions;

    private $waitingList;
    private $staffAccount;

    public function setUp()
    {
        parent::setUp();

        Event::fake();

        $this->waitingList = factory(WaitingList::class)->create();

        $this->staffAccount = factory(Account::class)->create();

        $this->staffAccount->roles()->attach(Role::find(1));
    }

    /** @test **/
    public function testStudentCanBeAddedToWaitingList()
    {
        $account = factory(Account::class)->create();

        $this->actingAs($this->staffAccount)->post(route('training.waitingList.store', $this->waitingList), [
            'account_id' => $account->id,
        ])->assertRedirect(route('training.waitingList.show', $this->waitingList))
            ->assertSessionHas('success', 'Account Added to Waiting List');

        Event::assertDispatched(AccountAddedToWaitingList::class);

        $listener = \Mockery::mock(WaitingListEventSubscriber::class);
        $listener->shouldReceive('userAdded');
    }

    /** @test **/
    public function testRedirectOnUnknownAccountId()
    {
        $this->actingAs($this->staffAccount)->post(route('training.waitingList.store', $this->waitingList), [
            'account_id' => 12345678,
        ])->assertRedirect(route('training.waitingList.show', $this->waitingList))
            ->assertSessionHas('error', 'Account Not Found.');
    }
    
    /** @test **/
    public function testAStudentCanOnlyBeInAListOnce() 
    {
        $account = factory(Account::class)->create();

        $this->actingAs($this->staffAccount)->post(route('training.waitingList.store', $this->waitingList), [
            'account_id' => $account->id,
        ])->assertRedirect(route('training.waitingList.show', $this->waitingList))
            ->assertSessionHas('success', 'Account Added to Waiting List');

        $this->actingAs($this->staffAccount)->post(route('training.waitingList.store', $this->waitingList), [
            'account_id' => $account->id,
        ])->assertSessionHasErrors('account_id', 'That account is already in this waiting list');
    }
}
