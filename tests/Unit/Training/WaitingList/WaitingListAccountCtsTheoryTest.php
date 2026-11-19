<?php

namespace Tests\Unit\Training\WaitingList;

use App\Models\Mship\Account;
use App\Models\Mship\State;
use App\Models\Training\TheoryExamResult;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WaitingListAccountCtsTheoryTest extends TestCase
{
    use DatabaseTransactions;

    private Account $account;

    private int $theoryResultCount = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::factory()->create();
        $this->account->addState(State::findByCode('DIVISION'));

        $this->actingAs($this->privacc);
    }

    private function setupWaitingList(?string $ctsLevel, ?string $department = WaitingList::ATC_DEPARTMENT): WaitingList\WaitingListAccount
    {
        $waitingList = WaitingList::factory()->create(['cts_theory_exam_level' => $ctsLevel, 'department' => $department]);

        return $waitingList->addToWaitingList($this->account->fresh(), $this->privacc);
    }

    private function createTheoryResult(string $examLevel, bool $pass)
    {
        TheoryExamResult::createQuietly([
            'account_id' => $this->account->id,
            'exam' => $examLevel,
            'exam_datetime' => now()->subMinutes(++$this->theoryResultCount),
            'result' => $pass ? TheoryExamResult::PASS : TheoryExamResult::FAIL,
        ]);
    }

    #[Test]
    public function it_should_detect_when_theory_exam_passed()
    {
        $waitingListAccount = $this->setupWaitingList('S3');
        $this->createTheoryResult('S3', true);

        $this->assertTrue($waitingListAccount->theoryExamPassed);
    }

    #[Test]
    public function it_should_detect_when_theory_exam_failed()
    {
        $waitingListAccount = $this->setupWaitingList('S3');
        $this->createTheoryResult('S3', false);

        $this->assertFalse($waitingListAccount->theoryExamPassed);
    }

    #[Test]
    public function it_should_detect_when_theory_exam_passed_on_a_pilot_waiting_list()
    {
        $waitingListAccount = $this->setupWaitingList('P1', WaitingList::PILOT_DEPARTMENT);
        $this->createTheoryResult('P1', true);

        $this->assertTrue($waitingListAccount->theoryExamPassed);
    }

    #[Test]
    public function it_should_return_null_when_no_exam_configured()
    {
        $waitingListAccount = $this->setupWaitingList(null);
        $this->createTheoryResult('S3', true);

        $this->assertFalse($waitingListAccount->theoryExamPassed);
    }

    #[Test]
    public function it_should_return_null_when_no_theory_result_found()
    {
        $waitingListAccount = $this->setupWaitingList('S3');
        // note no exam pass

        $this->assertFalse($waitingListAccount->theoryExamPassed);
    }

    #[Test]
    public function it_should_only_detect_passes_at_the_configured_exam_level()
    {
        $waitingListAccount = $this->setupWaitingList('S3');
        $this->createTheoryResult('S2', true);

        $this->assertFalse($waitingListAccount->theoryExamPassed);
    }

    #[Test]
    public function it_should_disregard_multiple_failures_at_configured_level()
    {
        $waitingListAccount = $this->setupWaitingList('S3');
        $this->createTheoryResult('S3', false);
        $this->createTheoryResult('S3', false);

        $this->assertFalse($waitingListAccount->theoryExamPassed);
    }

    #[Test]
    public function it_should_display_passed_with_previous_failures_and_then_pass()
    {
        $waitingListAccount = $this->setupWaitingList('S3');
        $this->createTheoryResult('S3', false);
        $this->createTheoryResult('S3', false);
        $this->createTheoryResult('S3', true);

        $this->assertTrue($waitingListAccount->theoryExamPassed);
    }
}
