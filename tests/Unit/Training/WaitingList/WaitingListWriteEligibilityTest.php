<?php

namespace Tests\Unit\Training\WaitingList;

use App\Models\Mship\State;
use App\Models\NetworkData\Atc;
use App\Models\Roster;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListFlag;
use App\Models\Training\WaitingList\WaitingListStatus;
use App\Services\Training\CheckWaitingListEligibility;
use App\Services\Training\WriteWaitingListEligibility;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WaitingListWriteEligibilityTest extends TestCase
{
    use DatabaseTransactions;

    public WaitingList $waitingList;

    public function setUp(): void
    {
        parent::setUp();

        $this->waitingList = factory(WaitingList::class)->create();

        $this->actingAs($this->privacc);
    }

    /** @test */
    public function itShouldWriteEligibilityFalseToWaitingListAccount()
    {
        $this->waitingList->addToWaitingList($this->user, $this->privacc);
        $this->waitingList->refresh();

        $waitingListAccount = $this->waitingList->accounts()->where('account_id', $this->user->id)->first()->pivot;

        $checkEligibility = new CheckWaitingListEligibility($this->user);

        WriteWaitingListEligibility::handle($this->waitingList, $checkEligibility);

        $this->assertFalse($waitingListAccount->fresh()->eligible);
    }

    /** @test */
    public function itShouldWriteEligibilityTrueToWaitingListAccountOnRoster()
    {
        # make sure the user is on the roster global scope for home members
        $this->user->addState(State::findByCode('DIVISION'));

        $this->waitingList->addToWaitingList($this->user, $this->privacc);
        $this->waitingList->refresh();

        $waitingListAccount = $this->waitingList->accounts()->where('account_id', $this->user->id)->first()->pivot;

        Roster::create(['account_id' => $this->user->id]);

        $checkEligibility = new CheckWaitingListEligibility($this->user);

        WriteWaitingListEligibility::handle($this->waitingList, $checkEligibility);

        $this->assertTrue($waitingListAccount->fresh()->eligible);
    }
}
