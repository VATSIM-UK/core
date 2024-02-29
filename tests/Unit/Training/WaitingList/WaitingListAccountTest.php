<?php

namespace Tests\Unit\Training\WaitingList;

use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WaitingListAccountTest extends TestCase
{
    use DatabaseTransactions, WaitingListTestHelper;

    private $waitingList;

    protected function setUp(): void
    {
        parent::setUp();

        $this->waitingList = $this->createList();

        $this->actingAs($this->privacc);
    }

    /** @test */
    public function itCanHaveNotesAdded()
    {
        $account = Account::factory()->create();

        $this->waitingList->addToWaitingList($account, $this->privacc);

        // grab the pivot model
        $waitingListAccount = $this->waitingList->accounts->find($account->id)->pivot;

        $waitingListAccount->notes = 'This is a note';

        $this->assertEquals('This is a note', $waitingListAccount->notes);
    }

    /** @test */
    public function itShouldDefaultCreatedAtToNowIfNotProvided()
    {
        $account = Account::factory()->create();
        $this->waitingList->addToWaitingList($account, $this->privacc);

        $this->assertDatabaseHas('training_waiting_list_account', [
            'account_id' => $account->id,
            'list_id' => $this->waitingList->id,
            'created_at' => $this->knownDate,
        ]);
    }

    /** @test */
    public function itShouldSetCreatedAtToGivenDateIfProvided()
    {
        $date = Carbon::parse('2020-01-01 12:00:00');
        $account = Account::factory()->create();
        $this->waitingList->addToWaitingList($account, $this->privacc, $date);

        $this->assertDatabaseHas('training_waiting_list_account', [
            'account_id' => $account->id,
            'list_id' => $this->waitingList->id,
            'created_at' => $date,
        ]);
    }
}
