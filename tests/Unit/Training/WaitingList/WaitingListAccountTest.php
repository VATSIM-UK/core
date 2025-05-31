<?php

namespace Tests\Unit\Training\WaitingList;

use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function it_can_have_notes_added()
    {
        $account = Account::factory()->create();

        $waitingListAccount = $this->waitingList->addToWaitingList($account, $this->privacc);
        $waitingListAccount->notes = 'This is a note';

        $this->assertEquals('This is a note', $waitingListAccount->notes);
    }

    #[Test]
    public function it_should_default_created_at_to_now_if_not_provided()
    {
        $account = Account::factory()->create();
        $this->waitingList->addToWaitingList($account, $this->privacc);

        $this->assertDatabaseHas('training_waiting_list_account', [
            'account_id' => $account->id,
            'list_id' => $this->waitingList->id,
            'created_at' => $this->knownDate,
        ]);
    }

    #[Test]
    public function it_should_set_created_at_to_given_date_if_provided()
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

    #[Test]
    public function it_should_know_its_position()
    {
        /** @var Account $account */
        $account = Account::factory()->create();
        $waitingListAccount = $this->waitingList->addToWaitingList($account, $this->privacc);

        $this->assertEquals(1, $waitingListAccount->position);
    }
}
