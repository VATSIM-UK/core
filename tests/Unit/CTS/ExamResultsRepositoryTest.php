<?php

namespace Tests\Unit\CTS;

use App\Models\Cts\Member;
use App\Models\Cts\PracticalResult;
use App\Models\Mship\Account;
use App\Repositories\Cts\ExamResultRepository;
use Tests\TestCase;

class ExamResultsRepositoryTest extends TestCase
{
    public function test_retrieves_passed_exam_results_of_type()
    {
        $account = Account::factory()->create(['id' => 1111111]);
        $member = factory(Member::class)->create([
            'cid' => $account->id,
        ]);

        $examResult = PracticalResult::factory()->create([
            'result' => PracticalResult::PASSED,
            'student_id' => $member->id,
            'exam' => 'OBS',
            'date' => now()->subDays(1),
        ]);

        // ensure failed result isn't returned
        $notSuccessfulPracticalResult = PracticalResult::factory()->create([
            'result' => PracticalResult::FAILED,
            'student_id' => $member->id,
            'exam' => 'OBS',
        ]);

        $repository = new ExamResultRepository();
        $result = $repository->getRecentPassedExamsOfType('OBS');

        $this->assertNotNull($result->where('id', $examResult->id)->first());
        $this->assertNull($result->where('id', $notSuccessfulPracticalResult->id)->first());
    }

    public function test_doesnt_return_non_recent_exam_passes()
    {
        $account = Account::factory()->create(['id' => 1111111]);
        $member = factory(Member::class)->create([
            'cid' => $account->id,
        ]);

        $examResult = PracticalResult::factory()->create([
            'result' => PracticalResult::PASSED,
            'student_id' => $member->id,
            'exam' => 'OBS',
            'date' => now()->subDays(4),
        ]);

        $repository = new ExamResultRepository();
        $result = $repository->getRecentPassedExamsOfType('OBS');

        $this->assertNull($result->where('id', $examResult->id)->first());
    }

    public function test_only_returns_recent_successful_exams_of_specified_type()
    {
        $account = Account::factory()->create(['id' => 1111111]);
        $member = factory(Member::class)->create([
            'cid' => $account->id,
        ]);

        $examResult = PracticalResult::factory()->create([
            'result' => PracticalResult::PASSED,
            'student_id' => $member->id,
            'exam' => 'TWR',
            'date' => now()->subDays(1),
        ]);

        $notSuccessfulPracticalResult = PracticalResult::factory()->create([
            'result' => PracticalResult::FAILED,
            'student_id' => $member->id,
            'exam' => 'OBS',
            'date' => now()->subDays(1),
        ]);

        $repository = new ExamResultRepository();
        $result = $repository->getRecentPassedExamsOfType('OBS');

        $this->assertNull($result->where('id', $examResult->id)->first());
        $this->assertNull($result->where('id', $notSuccessfulPracticalResult->id)->first());
    }
}
