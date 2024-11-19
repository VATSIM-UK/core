<?php

namespace Tests\Unit\Training\WaitingList;

use App\Models\Cts\Member;
use App\Models\Cts\TheoryResult;
use App\Models\Mship\Account;
use App\Models\Mship\State;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WaitingListAccountCtsTheoryTest extends TestCase
{
    use DatabaseTransactions;

    private Member $member;

    private Account $account;

    public function setUp(): void
    {
        parent::setUp();

        // create the member first as for some reason the CID is not overwritten
        // when using a factory
        $this->member = factory(Member::class)->create();
        $this->account = Account::factory()->create(['id' => $this->member->cid]);
        $this->account->addState(State::findByCode('DIVISION'));

        $this->actingAs($this->privacc);
    }

    private function setupWaitingList(?string $ctsLevel, ?string $department = WaitingList::ATC_DEPARTMENT): WaitingList\WaitingListAccount
    {
        $waitingList = factory(WaitingList::class)->create(['cts_theory_exam_level' => $ctsLevel, 'department' => $department]);

        return $waitingList->addToWaitingList($this->account->fresh(), $this->privacc);
    }

    private function createTheoryResult(string $ctsLevel, bool $pass)
    {
        TheoryResult::factory()->create([
            'student_id' => $this->member->id,
            'exam' => $ctsLevel,
            'pass' => $pass,
        ]);
    }

    /** @test */
    public function itShouldDetectWhenTheoryExamPassed()
    {
        $waitingListAccount = $this->setupWaitingList('S3');
        $this->createTheoryResult('S3', true);

        $this->assertTrue($waitingListAccount->theoryExamPassed);
    }

    /** @test */
    public function itShouldDetectWhenTheoryExamFailed()
    {
        $waitingListAccount = $this->setupWaitingList('S3');
        $this->createTheoryResult('S3', false);

        $this->assertFalse($waitingListAccount->theoryExamPassed);
    }

    /** @test */
    public function itShouldReturnFalseWhenPilotWaitingList()
    {
        $waitingListAccount = $this->setupWaitingList(null, WaitingList::PILOT_DEPARTMENT);

        $this->assertFalse($waitingListAccount->theoryExamPassed);
    }

    /** @test */
    public function itShouldReturnNullWhenNoExamConfigured()
    {
        $waitingListAccount = $this->setupWaitingList(null);
        $this->createTheoryResult('S3', true);

        $this->assertFalse($waitingListAccount->theoryExamPassed);
    }

    /** @test */
    public function itShouldReturnNullWhenNoTheoryResultFound()
    {
        $waitingListAccount = $this->setupWaitingList('S3');
        // note no exam pass

        $this->assertFalse($waitingListAccount->theoryExamPassed);
    }

    /** @test */
    public function itShouldOnlyDetectPassesAtTheConfiguredExamLevel()
    {
        $waitingListAccount = $this->setupWaitingList('S3');
        $this->createTheoryResult('S2', true);

        $this->assertFalse($waitingListAccount->theoryExamPassed);
    }

    /** @test */
    public function itShouldDisregardMultipleFailuresAtConfiguredLevel()
    {
        $waitingListAccount = $this->setupWaitingList('S3');
        $this->createTheoryResult('S3', false);
        $this->createTheoryResult('S3', false);

        $this->assertFalse($waitingListAccount->theoryExamPassed);
    }

    /** @test */
    public function itShouldDisplayPassedWithPreviousFailuresAndThenPass()
    {
        $waitingListAccount = $this->setupWaitingList('S3');
        $this->createTheoryResult('S3', false);
        $this->createTheoryResult('S3', false);
        $this->createTheoryResult('S3', true);

        $this->assertTrue($waitingListAccount->theoryExamPassed);
    }
}
