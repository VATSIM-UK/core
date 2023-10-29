<?php

namespace Tests\Unit\Training\WaitingList;

use App\Events\Training\AccountAddedToWaitingList;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WaitingListStatusTest extends TestCase
{
    use DatabaseTransactions;

    private $waitingListStatus;

    public function setUp(): void
    {
        parent::setUp();

        $this->waitingListStatus = factory(WaitingListStatus::class)->states(['default'])->create();

        $this->actingAs($this->privacc);
    }

    /** @test * */
    public function itDefaultsToActive()
    {
        $this->assertEquals(1, $this->waitingListStatus->default()->id);
    }

    /** @test * */
    public function itHasListenerToAssignDefaultStatus()
    {
        $account = Account::factory()->create();
        $waitingList = factory(WaitingList::class)->create();
        $waitingList->addToWaitingList($account, $this->privacc);

        event(new AccountAddedToWaitingList($account, $waitingList, $this->privacc));

        $waitingListAccount = $waitingList::find($waitingList->id)->accounts->where('id', $account->id)->first()->pivot->status;

        $this->assertDatabaseHas('training_waiting_list_account_status', [
            'waiting_list_account_id' => $waitingListAccount->first()->pivot->waiting_list_account_id, 'status_id' => 1,
        ]);
    }
}
