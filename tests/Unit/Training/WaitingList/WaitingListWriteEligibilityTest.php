<?php

namespace Tests\Unit\Training\WaitingList;

use App\Models\NetworkData\Atc;
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
    public function itShouldWriteEligibilityTrueToWaitingListAccountWithNoFlags()
    {
        $this->waitingList->addToWaitingList($this->user, $this->privacc);
        $this->waitingList->refresh();

        $waitingListAccount = $this->waitingList->accounts()->where('account_id', $this->user->id)->first()->pivot;

        factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'minutes_online' => 721,
            'disconnected_at' => now(),
        ]);

        $checkEligibility = new CheckWaitingListEligibility($this->user);

        WriteWaitingListEligibility::handle($this->waitingList, $checkEligibility);

        $this->assertEquals($waitingListAccount->fresh()->flags_status_summary,
            [
                'summary' => null,
            ]
        );
    }

    /** @test */
    public function itShouldWriteEligibilityWithPassingManualFlags()
    {
        $flag = factory(WaitingListFlag::class)->create();
        $this->waitingList->addFlag($flag);

        $this->waitingList->addToWaitingList($this->user, $this->privacc);
        $this->waitingList->refresh();

        $waitingListAccount = $this->waitingList->accounts()->where('account_id', $this->user->id)->first()->pivot;
        $waitingListAccount->markFlag($flag);

        factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'minutes_online' => 721,
            'disconnected_at' => now(),
        ]);

        $checkEligibility = new CheckWaitingListEligibility($this->user);

        WriteWaitingListEligibility::handle($this->waitingList, $checkEligibility);

        $this->assertEquals($waitingListAccount->fresh()->flags_status_summary,
            [
                'summary' => [
                    $flag->name => true,
                ],
            ]
        );
    }
}
