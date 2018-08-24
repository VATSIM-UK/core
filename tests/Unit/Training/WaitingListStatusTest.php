<?php

namespace Tests\Unit\Training;

use App\Events\Training\AccountAddedToWaitingList;
use App\Listeners\Training\WaitingList\AssignDefaultStatus;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingListStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WaitingListStatusTest extends TestCase
{
    use RefreshDatabase;

    private $waitingListStatus;

    public function setUp()
    {
        parent::setUp();

        $this->waitingListStatus = factory(WaitingListStatus::class)->states(['default'])->create();
    }

    /** @test * */
    public function itDefaultsToActive()
    {
        $this->assertEquals(1, $this->waitingListStatus->default()->id);
    }

    /** @test **/
    public function itHasListenerToAssignDefaultStatus()
    {
        $account = factory(Account::class)->create();
        $waitingList = factory(WaitingList::class)->create();
        $staffAccount = factory(Account::class)->create();
        $waitingList->addToWaitingList($account, $staffAccount);

        $listener = \Mockery::spy(AssignDefaultStatus::class);

        app()->instance(AssignDefaultStatus::class, $listener);

        event(new AccountAddedToWaitingList($account, $waitingList, $staffAccount));

        $listener->shouldHaveReceived('handle')->with(\Mockery::on(function ($event) use ($account, $waitingList) {
            return $event->account->id == $account->id;
        }))->once();

        // TODO: fix
        $waitingListAccount = $waitingList->fresh()->accounts->where('id', $account->id)->first()->pivot->first()->status;

        dd($waitingListAccount);

        $this->assertDatabaseHas('training_waiting_list_account_status', [
            'id' => $waitingListAccount->id, 'status_id' => 1,
        ]);
    }
}
