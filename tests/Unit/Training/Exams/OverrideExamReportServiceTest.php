<?php

namespace Tests\Unit\Training\Exams;

use App\Enums\ExamResultEnum;
use App\Models\Atc\Position;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\ExamCriteria;
use App\Models\Cts\ExamCriteriaAssessment;
use App\Models\Cts\Member;
use App\Models\Cts\PracticalResult;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Services\Training\ExamResubmissionService;
use App\Services\Training\OverrideExamReportService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OverrideExamReportServiceTest extends TestCase
{
    use DatabaseTransactions;

    private Account $studentAccount;

    private Member $studentMember;

    private ExamBooking $examBooking;

    private PracticalResult $practicalResult;

    private Account $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->studentAccount = Account::factory()->withQualification()->create();
        $this->studentMember = Member::factory()->create([
            'id' => $this->studentAccount->id,
            'cid' => $this->studentAccount->id,
        ]);

        $this->examBooking = ExamBooking::factory()->create([
            'taken' => 1,
            'finished' => ExamBooking::FINISHED_FLAG,
            'exam' => 'TWR',
            'student_id' => $this->studentMember->id,
            'student_rating' => Qualification::code('S1')->first()->vatsim,
            'position_1' => 'EGKK_TWR',
        ]);

        $this->practicalResult = PracticalResult::factory()->create([
            'examid' => $this->examBooking->id,
            'student_id' => $this->studentMember->id,
            'result' => PracticalResult::PASSED,
            'notes' => 'Original comments',
            'exam' => 'TWR',
        ]);

        $this->actor = Account::factory()->create();
    }

    public function test_updates_result_and_logs_note(): void
    {
        $service = app(OverrideExamReportService::class);
        $noteCount = $this->studentAccount->notes()->count();

        $result = $service->handle($this->practicalResult, [
            'exam_result' => ExamResultEnum::Fail->value,
            'reason' => 'Updated result',
            'additional_comments' => $this->practicalResult->notes,
            'criteria_updates' => [],
        ], $this->actor);

        $this->practicalResult->refresh();

        $this->assertSame(ExamResultEnum::Fail->value, $this->practicalResult->result);
        $this->assertTrue($result);
        $this->assertSame($noteCount + 1, $this->studentAccount->notes()->count());
        $this->assertStringContainsString('overridden from', $this->studentAccount->notes()->latest()->first()->content);
    }

    public function test_updates_additional_comments_and_logs_note(): void
    {
        $service = app(OverrideExamReportService::class);
        $noteCount = $this->studentAccount->notes()->count();

        $result = $service->handle($this->practicalResult, [
            'exam_result' => $this->practicalResult->result,
            'reason' => 'Updated comments',
            'additional_comments' => '<p>New comments</p>',
            'criteria_updates' => [],
        ], $this->actor);

        $this->practicalResult->refresh();

        $this->assertTrue($result);
        $this->assertSame($noteCount + 1, $this->studentAccount->notes()->count());
        $this->assertStringContainsString('New comments', $this->practicalResult->notes);
    }

    public function test_creates_missing_criteria_assessment_and_logs_grade_change(): void
    {
        $service = app(OverrideExamReportService::class);

        $criteria = ExamCriteria::create([
            'exam' => 'TWR',
            'criteria' => 'Separation',
            'deleted' => 0,
        ]);

        $noteCount = $this->studentAccount->notes()->count();

        $result = $service->handle($this->practicalResult, [
            'exam_result' => $this->practicalResult->result,
            'reason' => 'Criteria review',
            'additional_comments' => $this->practicalResult->notes,
            'criteria_updates' => [
                $criteria->id => [
                    'grade' => ExamCriteriaAssessment::FULLY_COMPETENT,
                    'notes' => '<p>Strong separation</p>',
                    'change_comments' => 'Reviewed after moderation',
                ],
            ],
        ], $this->actor);

        $assessment = ExamCriteriaAssessment::where('examid', $this->practicalResult->examid)
            ->where('criteria_id', $criteria->id)
            ->first();

        $this->assertTrue($result);
        $this->assertNotNull($assessment);
        $this->assertSame(ExamCriteriaAssessment::FULLY_COMPETENT, $assessment->result);
        $this->assertStringContainsString('Strong separation', $assessment->notes);
        $this->assertSame($noteCount + 1, $this->studentAccount->notes()->count());
    }

    public function test_updates_criteria_comments_only_and_logs_comment_change(): void
    {
        $service = app(OverrideExamReportService::class);

        $criteria = ExamCriteria::create([
            'exam' => 'TWR',
            'criteria' => 'Comms',
            'deleted' => 0,
        ]);

        ExamCriteriaAssessment::create([
            'examid' => $this->practicalResult->examid,
            'criteria_id' => $criteria->id,
            'result' => ExamCriteriaAssessment::MOSTLY_COMPETENT,
            'notes' => 'Original notes',
        ]);

        $noteCount = $this->studentAccount->notes()->count();

        $result = $service->handle($this->practicalResult, [
            'exam_result' => $this->practicalResult->result,
            'reason' => 'Update comments',
            'additional_comments' => $this->practicalResult->notes,
            'criteria_updates' => [
                $criteria->id => [
                    'grade' => ExamCriteriaAssessment::MOSTLY_COMPETENT,
                    'notes' => '<p>Updated comms notes</p>',
                    'change_comments' => 'Clarified report',
                ],
            ],
        ], $this->actor);

        $assessment = ExamCriteriaAssessment::where('examid', $this->practicalResult->examid)
            ->where('criteria_id', $criteria->id)
            ->firstOrFail();

        $this->assertTrue($result);
        $this->assertStringContainsString('Updated comms notes', $assessment->notes);
        $this->assertSame($noteCount + 1, $this->studentAccount->notes()->count());
        $this->assertStringContainsString('comments updated', $this->studentAccount->notes()->latest()->first()->content);
    }

    public function test_no_changes_returns_false_and_does_not_log_notes(): void
    {
        $service = app(OverrideExamReportService::class);
        $noteCount = $this->studentAccount->notes()->count();

        $result = $service->handle($this->practicalResult, [
            'exam_result' => $this->practicalResult->result,
            'reason' => 'No change',
            'additional_comments' => $this->practicalResult->notes,
            'criteria_updates' => [],
        ], $this->actor);

        $this->assertFalse($result);
        $this->assertSame($noteCount, $this->studentAccount->notes()->count());
    }

    public function test_calls_resubmission_service_on_incomplete_result(): void
    {
        $position = Position::factory()->create([
            'callsign' => 'EGKK_TWR',
        ]);
        TrainingPosition::factory()->create(['position_id' => $position->id]);

        $mock = $this->mock(ExamResubmissionService::class);
        $mock->shouldReceive('handle')
            ->once()
            ->withArgs(function (ExamBooking $booking, string $result, int $userId): bool {
                return $booking->is($this->examBooking)
                    && $result === ExamResultEnum::Incomplete->value
                    && $userId === $this->actor->id;
            });

        $service = app(OverrideExamReportService::class);

        $service->handle($this->practicalResult, [
            'exam_result' => ExamResultEnum::Incomplete->value,
            'reason' => 'Needs resubmission',
            'additional_comments' => $this->practicalResult->notes,
            'criteria_updates' => [],
        ], $this->actor);
    }

    public function test_criteria_updates_with_identical_values_returns_false(): void
    {
        $service = app(OverrideExamReportService::class);

        $criteria = ExamCriteria::create([
            'exam' => 'TWR',
            'criteria' => 'Comms',
            'deleted' => 0,
        ]);

        ExamCriteriaAssessment::create([
            'examid' => $this->practicalResult->examid,
            'criteria_id' => $criteria->id,
            'result' => ExamCriteriaAssessment::MOSTLY_COMPETENT,
            'notes' => 'Same notes',
        ]);

        $noteCount = $this->studentAccount->notes()->count();

        $result = $service->handle($this->practicalResult, [
            'exam_result' => $this->practicalResult->result,
            'reason' => 'No functional change',
            'additional_comments' => $this->practicalResult->notes,
            'criteria_updates' => [
                $criteria->id => [
                    'grade' => ExamCriteriaAssessment::MOSTLY_COMPETENT,
                    'notes' => 'Same notes',
                    'change_comments' => 'No changes made',
                ],
            ],
        ], $this->actor);

        $this->assertFalse($result);
        $this->assertSame($noteCount, $this->studentAccount->notes()->count());
    }
}
