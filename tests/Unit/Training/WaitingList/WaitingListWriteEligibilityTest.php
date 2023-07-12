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
    }

    /** @test */
    public function itShouldWriteEligibilityFalseToWaitingListAccount()
    {
        $this->waitingList->addToWaitingList($this->user, $this->privacc);
        $this->waitingList->refresh();

        $waitingListAccount = $this->waitingList->accounts()->where('account_id', $this->user->id)->first()->pivot;
        $waitingListAccount->addStatus(WaitingListStatus::find(WaitingListStatus::DEFAULT_STATUS));

        $checkEligibility = new CheckWaitingListEligibility($this->user);

        WriteWaitingListEligibility::handle($this->waitingList, $checkEligibility);

        $this->assertFalse($waitingListAccount->fresh()->eligible);
    }

    /** @test */
    public function itShouldWriteEligibilityTrueToWaitingListAccountWithNoFlags()
    {
        $this->waitingList->addToWaitingList($this->user, $this->privacc);
        $this->waitingList->refresh();

        $waitingListAccount = $this->waitingList->accounts()->where('account_id', $this->user->id)->first()->pivot;
        $waitingListAccount->addStatus(WaitingListStatus::find(WaitingListStatus::DEFAULT_STATUS));

        factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'minutes_online' => 721,
            'disconnected_at' => now(),
        ]);

        $checkEligibility = new CheckWaitingListEligibility($this->user);

        WriteWaitingListEligibility::handle($this->waitingList, $checkEligibility);

        $this->assertTrue($waitingListAccount->fresh()->eligible);

        $this->assertEquals($waitingListAccount->fresh()->eligibility_summary,
            [
                'base_controlling_hours' => true,
                'flags' => [
                    'overall' => true,
                    'summary' => null,
                ],
                'account_status' => true,
            ]
        );

        $this->assertEquals($waitingListAccount->fresh()->flags_status_summary,
            [
                'overall' => true,
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
        $waitingListAccount->addStatus(WaitingListStatus::find(WaitingListStatus::DEFAULT_STATUS));
        $waitingListAccount->markFlag($flag);

        factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'minutes_online' => 721,
            'disconnected_at' => now(),
        ]);

        $checkEligibility = new CheckWaitingListEligibility($this->user);

        WriteWaitingListEligibility::handle($this->waitingList, $checkEligibility);

        $this->assertTrue($waitingListAccount->fresh()->eligible);

        $this->assertEquals($waitingListAccount->fresh()->eligibility_summary,
            [
                'base_controlling_hours' => true,
                'flags' => [
                    'overall' => true,
                    'summary' => [
                        $flag->name => true,
                    ],
                ],
                'account_status' => true,
            ]
        );

        $this->assertEquals($waitingListAccount->fresh()->flags_status_summary,
            [
                'overall' => true,
                'summary' => [
                    $flag->name => true,
                ],
            ]
        );
    }
}
