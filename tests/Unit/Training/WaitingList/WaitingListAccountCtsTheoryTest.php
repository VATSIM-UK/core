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
    }

    /** @test */
    public function itShouldDetectWhenTheoryExamPassed()
    {
        $waitingList = factory(WaitingList::class)->create([
            'cts_theory_exam_level' => 'S3',
        ]);
        $waitingList->addToWaitingList($this->account->fresh(), $this->privacc);

        TheoryResult::factory()->create([
            'student_id' => $this->member->id,
            'exam' => 'S3',
            'pass' => true,
        ]);

        $this->assertTrue($waitingList->fresh()->accounts->find($this->account->id)->pivot->theoryExamPassed);
    }

    /** @test */
    public function itShouldDetectWhenTheoryExamFailed()
    {
        $waitingList = factory(WaitingList::class)->create([
            'cts_theory_exam_level' => 'S3',
        ]);
        $waitingList->addToWaitingList($this->account, $this->privacc);

        TheoryResult::factory()->create([
            'student_id' => $this->member->id,
            'exam' => 'S3',
            'pass' => false,
        ]);

        $this->assertFalse($waitingList->accounts->find($this->account->id)->pivot->theoryExamPassed);
    }

    /** @test */
    public function itShouldReturnNullWhenPilotWaitingList()
    {
        $waitingList = factory(WaitingList::class)->create([
            'cts_theory_exam_level' => null,
            'department' => WaitingList::PILOT_DEPARTMENT,
        ]);

        $waitingList->addToWaitingList($this->account, $this->privacc);

        $this->assertNull($waitingList->accounts->find($this->account->id)->pivot->theoryExamPassed);
    }

    /** @test */
    public function itShouldReturnNullWhenNoExamConfigured()
    {
        $waitingList = factory(WaitingList::class)->create([
            'cts_theory_exam_level' => null,
        ]);

        $waitingList->addToWaitingList($this->account, $this->privacc);

        $this->assertNull($waitingList->accounts->find($this->account->id)->pivot->theoryExamPassed);
    }

    /** @test */
    public function itShouldReturnNullWhenNoTheoryResultFound()
    {
        $waitingList = factory(WaitingList::class)->create([
            'cts_theory_exam_level' => 'S3',
        ]);

        $waitingList->addToWaitingList($this->account, $this->privacc);

        $this->assertNull($waitingList->accounts->find($this->account->id)->pivot->theoryExamPassed);
    }

    /** @test */
    public function itShouldOnlyDetectPassesAtTheConfiguredExamLevel()
    {
        $waitingList = factory(WaitingList::class)->create([
            'cts_theory_exam_level' => 'S3',
        ]);
        $waitingList->addToWaitingList($this->account->fresh(), $this->privacc);

        TheoryResult::factory()->create([
            'student_id' => $this->member->id,
            'exam' => 'S2',
            'pass' => true,
        ]);

        $this->assertFalse($waitingList->fresh()->accounts->find($this->account->id)->pivot->theoryExamPassed);
    }

    /** @test */
    public function itShouldDisregardMultipleFailuresAtConfiguredLevel()
    {
        $waitingList = factory(WaitingList::class)->create([
            'cts_theory_exam_level' => 'S3',
        ]);
        $waitingList->addToWaitingList($this->account->fresh(), $this->privacc);

        TheoryResult::factory()->create([
            'student_id' => $this->member->id,
            'exam' => 'S3',
            'pass' => false,
        ]);

        TheoryResult::factory()->create([
            'student_id' => $this->member->id,
            'exam' => 'S3',
            'pass' => false,
        ]);

        $this->assertFalse($waitingList->fresh()->accounts->find($this->account->id)->pivot->theoryExamPassed);
    }

    /** @test */
    public function itShouldDisplayPassedWithPreviousFailuresAndThenPass()
    {
        $waitingList = factory(WaitingList::class)->create([
            'cts_theory_exam_level' => 'S3',
        ]);
        $waitingList->addToWaitingList($this->account->fresh(), $this->privacc);

        TheoryResult::factory()->create([
            'student_id' => $this->member->id,
            'exam' => 'S3',
            'pass' => false,
        ]);

        TheoryResult::factory()->create([
            'student_id' => $this->member->id,
            'exam' => 'S3',
            'pass' => true,
        ]);

        $this->assertTrue($waitingList->fresh()->accounts->find($this->account->id)->pivot->theoryExamPassed);
    }
}
