<?php

namespace Tests\Unit\Training\WaitingList;

use App\Models\NetworkData\Atc;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListFlag;
use App\Services\Training\CheckWaitingListFlags;
use App\Services\Training\WriteWaitingListFlagSummary;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WaitingListWriteEligibilityTest extends TestCase
{
    use DatabaseTransactions;

    public WaitingList $waitingList;

    protected function setUp(): void
    {
        parent::setUp();

        $this->waitingList = WaitingList::factory()->create();

        $this->actingAs($this->privacc);
    }

    #[Test]
    public function it_should_write_eligibility_true_to_waiting_list_account_with_no_flags()
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

    #[Test]
    public function it_should_write_eligibility_with_passing_manual_flags()
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
