<?php

namespace Tests\Feature\Training;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Listeners\Training\WaitingListEventSubscriber;
use App\Events\Training\AccountAddedToWaitingList;
use Illuminate\Support\Facades\Event;
use App\Models\Training\WaitingList;
use App\Models\Mship\Account;
use App\Models\Mship\Role;
use Tests\TestCase;

class WaitingListFeatureTest extends TestCase
{
    use DatabaseTransactions;

    private $waitingList;
    private $staffAccount;

    public function setUp()
    {
        parent::setUp();

        $this->waitingList = factory(WaitingList::class)->create();

        $this->staffAccount = factory(Account::class)->create();

        $this->staffAccount->roles()->attach(Role::find(1));
    }

    /** @test **/
    public function testStudentCanBeAddedToWaitingList()
    {
        Event::fake();

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
}
