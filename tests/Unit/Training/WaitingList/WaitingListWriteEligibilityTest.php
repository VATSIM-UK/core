<?php

namespace Tests\Unit\Training\WaitingList;

use App\Models\NetworkData\Atc;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListFlag;
use App\Services\Training\CheckWaitingListFlags;
use App\Services\Training\WriteWaitingListFlagSummary;
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
        $waitingListAccount = $this->waitingList->addToWaitingList($this->user, $this->privacc);
        $this->waitingList->refresh();

        factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'minutes_online' => 721,
            'disconnected_at' => now(),
        ]);

        $checkEligibility = new CheckWaitingListFlags($this->user);

        WriteWaitingListFlagSummary::handle($this->waitingList, $checkEligibility);

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

        $waitingListAccount = $this->waitingList->addToWaitingList($this->user, $this->privacc);
        $this->waitingList->refresh();

        $waitingListAccount->markFlag($flag);

        factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'minutes_online' => 721,
            'disconnected_at' => now(),
        ]);

        $checkEligibility = new CheckWaitingListFlags($this->user);

        WriteWaitingListFlagSummary::handle($this->waitingList, $checkEligibility);

        $this->assertEquals($waitingListAccount->fresh()->flags_status_summary,
            [
                'summary' => [
                    $flag->name => true,
                ],
            ]
        );
    }
}
