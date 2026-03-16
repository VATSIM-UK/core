<?php

namespace Tests\Unit\Training\WaitingList;

use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WaitingListEndorsementTest extends TestCase
{
    use DatabaseTransactions, WaitingListTestHelper;

    private WaitingList $waitingList;

    protected function setUp(): void
    {
        parent::setUp();
        $this->waitingList = $this->createList();
        $this->actingAs($this->privacc);
    }

    #[Test]
    public function it_prevents_adding_account_without_required_endorsement()
    {
        $positionGroup = PositionGroup::factory()->create();

        $this->waitingList->update(['required_endorsement_id' => $positionGroup->id]);

        $account = Account::factory()->create();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot add account to waiting list '{$this->waitingList->name}' as they do not have the endorsement: {$positionGroup->name}.");

        $this->waitingList->addToWaitingList($account, $this->privacc);
    }

    #[Test]
    public function it_allows_adding_account_with_required_endorsement()
    {
        $positionGroup = PositionGroup::factory()->create();

        $this->waitingList->update(['required_endorsement_id' => $positionGroup->id]);

        $account = Account::factory()->create();
        $account->endorsements()->create([
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => $positionGroup->id,
        ]);

        $this->waitingList->addToWaitingList($account, $this->privacc);

        $this->assertTrue($this->waitingList->includesAccount($account));
    }
}
