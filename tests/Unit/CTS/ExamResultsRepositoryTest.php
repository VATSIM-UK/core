<?php

namespace Tests\Unit\CTS;

use App\Models\Cts\Member;
use App\Models\Cts\PracticalResult;
use App\Models\Mship\Account;
use App\Repositories\Cts\ExamResultRepository;
use Tests\TestCase;

class ExamResultsRepositoryTest extends TestCase
{
    public function test_retrieves_passed_exam_results_of_type_for_member()
    {
        $account = Account::factory()->create(['id' => 1111111]);
        $member = factory(Member::class)->create([
            'cid' => $account->id,
        ]);

        $examResult = PracticalResult::factory()->create([
            'result' => PracticalResult::PASSED,
            'student_id' => $member->id,
            'exam' => 'OBS',
        ]);

        // ensure failed result isn't returned
        PracticalResult::factory()->create([
            'result' => PracticalResult::FAILED,
            'student_id' => $member->id,
            'exam' => 'OBS',
        ]);

        $repository = new ExamResultRepository();
        $result = $repository->getPassedExamResultsOfTypeForMember($account->id, 'OBS');

        $this->assertEquals($examResult->id, $result->id);
    }

    public function test_does_not_retrieve_passed_exam_of_other_type()
    {
        $account = Account::factory()->create(['id' => 1111111]);
        $member = factory(Member::class)->create([
            'cid' => $account->id,
        ]);

        // ensure failed result isn't returned
        PracticalResult::factory()->create([
            'result' => PracticalResult::PASSED,
            'student_id' => $member->id,
            'exam' => 'OBS',
        ]);

        $repository = new ExamResultRepository();
        $result = $repository->getPassedExamResultsOfTypeForMember($account->id, 'TWR');

        $this->assertNull($result);
    }
}
