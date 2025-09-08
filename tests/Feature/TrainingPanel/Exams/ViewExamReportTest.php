<?php

namespace Tests\Feature\TrainingPanel\Exams;

use App\Filament\Training\Pages\ViewExamReport;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\ExamCriteria;
use App\Models\Cts\ExamCriteriaAssessment;
use App\Models\Cts\Member;
use App\Models\Cts\PracticalResult;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class ViewExamReportTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    protected Account $student;

    protected Member $studentMember;

    protected ExamBooking $examBooking;

    protected PracticalResult $practicalResult;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a student account and member
        $this->student = Account::factory()->create();
        $this->studentMember = Member::factory()->create([
            'id' => $this->student->id,
            'cid' => $this->student->id,
        ]);

        // Create an exam booking
        $this->examBooking = ExamBooking::factory()->create([
            'taken' => 1,
            'finished' => ExamBooking::FINISHED_FLAG,
            'exam' => 'TWR',
            'student_id' => $this->studentMember->id,
            'student_rating' => Qualification::code('S1')->first()->vatsim,
            'position_1' => 'EGKK_TWR',
        ]);

        // Create examiners
        $this->examBooking->examiners()->create([
            'examid' => $this->examBooking->id,
            'senior' => $this->panelUser->id,
        ]);

        // Create a practical result
        $this->practicalResult = PracticalResult::factory()->create([
            'examid' => $this->examBooking->id,
            'student_id' => $this->studentMember->id,
            'result' => PracticalResult::PASSED,
            'notes' => 'Excellent performance throughout the exam.',
            'exam' => 'TWR',
        ]);
    }

    #[Test]
    public function it_loads_if_authorised()
    {
        $this->panelUser->givePermissionTo(['training.exams.access', 'training.exams.conduct.twr']);

        Livewire::actingAs($this->panelUser)
            ->test(ViewExamReport::class, ['examId' => $this->examBooking->id])
            ->assertSuccessful();
    }

    #[Test]
    public function it_does_not_load_if_unauthorised()
    {
        Livewire::actingAs($this->panelUser)
            ->test(ViewExamReport::class, ['examId' => $this->examBooking->id])
            ->assertForbidden();
    }

    #[Test]
    public function it_does_not_load_if_missing_basic_access_permission()
    {
        // Only give conduct permission but not basic access
        $this->panelUser->givePermissionTo('training.exams.conduct.twr');

        Livewire::actingAs($this->panelUser)
            ->test(ViewExamReport::class, ['examId' => $this->examBooking->id])
            ->assertForbidden();
    }

    #[Test]
    public function it_does_not_load_if_missing_conduct_permission_for_exam_level()
    {
        // Give basic access but wrong conduct permission (OBS instead of TWR)
        $this->panelUser->givePermissionTo(['training.exams.access', 'training.exams.conduct.obs']);

        Livewire::actingAs($this->panelUser)
            ->test(ViewExamReport::class, ['examId' => $this->examBooking->id])
            ->assertStatus(403);
    }

    #[Test]
    public function it_loads_with_correct_conduct_permission_for_different_exam_levels()
    {
        // Test APP exam
        $this->examBooking->update(['exam' => 'APP']);
        $this->practicalResult->update(['exam' => 'APP']);

        $this->panelUser->givePermissionTo(['training.exams.access', 'training.exams.conduct.app']);

        Livewire::actingAs($this->panelUser)
            ->test(ViewExamReport::class, ['examId' => $this->examBooking->id])
            ->assertSuccessful();
    }

    #[Test]
    public function test_unauthorized_when_exam_doesnt_exist()
    {
        $examId = 9999; // Assuming this ID does not exist
        $this->panelUser->givePermissionTo(['training.exams.access', 'training.exams.conduct.twr']);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($this->panelUser)
            ->test(ViewExamReport::class, ['examId' => $examId]);
    }

    #[Test]
    public function test_unauthorized_when_practical_result_doesnt_exist()
    {
        // Create an exam without a practical result
        $examWithoutResult = ExamBooking::factory()->create([
            'taken' => 1,
            'finished' => ExamBooking::FINISHED_FLAG,
            'exam' => 'TWR',
            'student_id' => $this->studentMember->id,
        ]);

        $this->panelUser->givePermissionTo(['training.exams.access', 'training.exams.conduct.twr']);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($this->panelUser)
            ->test(ViewExamReport::class, ['examId' => $examWithoutResult->id]);
    }

    #[Test]
    public function test_displays_student_information_correctly()
    {
        $this->panelUser->givePermissionTo(['training.exams.access', 'training.exams.conduct.twr']);

        $component = Livewire::actingAs($this->panelUser)
            ->test(ViewExamReport::class, ['examId' => $this->examBooking->id])
            ->assertSuccessful();

        // Check that student information is displayed
        $component->assertSee($this->student->name)
            ->assertSee($this->student->id)
            ->assertSee($this->examBooking->studentQualification->name ?? 'S1');
    }

    #[Test]
    public function test_displays_exam_information_correctly()
    {
        $this->panelUser->givePermissionTo(['training.exams.access', 'training.exams.conduct.twr']);

        $component = Livewire::actingAs($this->panelUser)
            ->test(ViewExamReport::class, ['examId' => $this->examBooking->id])
            ->assertSuccessful();

        // Check that exam information is displayed
        $component->assertSee($this->examBooking->exam)
            ->assertSee($this->examBooking->position_1);
    }

    #[Test]
    public function test_displays_exam_result_badge_correctly_for_passed()
    {
        $this->practicalResult->update(['result' => PracticalResult::PASSED]);

        $this->panelUser->givePermissionTo(['training.exams.access', 'training.exams.conduct.twr']);

        $component = Livewire::actingAs($this->panelUser)
            ->test(ViewExamReport::class, ['examId' => $this->examBooking->id])
            ->assertSuccessful();

        // Check that the passed result is displayed
        $component->assertSee('Passed');
    }

    #[Test]
    public function test_displays_exam_result_badge_correctly_for_failed()
    {
        $this->practicalResult->update(['result' => PracticalResult::FAILED]);

        $this->panelUser->givePermissionTo(['training.exams.access', 'training.exams.conduct.twr']);

        $component = Livewire::actingAs($this->panelUser)
            ->test(ViewExamReport::class, ['examId' => $this->examBooking->id])
            ->assertSuccessful();

        // Check that the failed result is displayed
        $component->assertSee('Failed');
    }

    #[Test]
    public function test_displays_exam_result_badge_correctly_for_incomplete()
    {
        $this->practicalResult->update(['result' => PracticalResult::INCOMPLETE]);

        $this->panelUser->givePermissionTo(['training.exams.access', 'training.exams.conduct.twr']);

        $component = Livewire::actingAs($this->panelUser)
            ->test(ViewExamReport::class, ['examId' => $this->examBooking->id])
            ->assertSuccessful();

        // Check that the incomplete result is displayed
        $component->assertSee('Incomplete');
    }

    #[Test]
    public function test_displays_additional_comments()
    {
        $this->practicalResult->update(['notes' => 'Additional comments about the exam performance.']);

        $this->panelUser->givePermissionTo(['training.exams.access', 'training.exams.conduct.twr']);

        $component = Livewire::actingAs($this->panelUser)
            ->test(ViewExamReport::class, ['examId' => $this->examBooking->id])
            ->assertSuccessful();

        // Check that additional comments are displayed
        $component->assertSee('Additional comments about the exam performance.');
    }

    #[Test]
    public function test_displays_criteria_assessments_correctly()
    {
        // Create exam criteria
        $criteria1 = ExamCriteria::create([
            'exam' => 'TWR',
            'criteria' => 'Radio Communication',
            'deleted' => 0,
        ]);

        $criteria2 = ExamCriteria::create([
            'exam' => 'TWR',
            'criteria' => 'Traffic Management',
            'deleted' => 0,
        ]);

        // Create criteria assessments
        ExamCriteriaAssessment::create([
            'examid' => $this->examBooking->id,
            'criteria_id' => $criteria1->id,
            'result' => ExamCriteriaAssessment::FULLY_COMPETENT,
            'notes' => 'Excellent radio work',
        ]);

        ExamCriteriaAssessment::create([
            'examid' => $this->examBooking->id,
            'criteria_id' => $criteria2->id,
            'result' => ExamCriteriaAssessment::MOSTLY_COMPETENT,
            'notes' => 'Good traffic management with minor issues',
        ]);

        $this->panelUser->givePermissionTo(['training.exams.access', 'training.exams.conduct.twr']);

        $component = Livewire::actingAs($this->panelUser)
            ->test(ViewExamReport::class, ['examId' => $this->examBooking->id])
            ->assertSuccessful();

        // Check that criteria and assessments are displayed
        $component->assertSee('Radio Communication')
            ->assertSee('Traffic Management')
            ->assertSee('Excellent radio work')
            ->assertSee('Good traffic management with minor issues')
            ->assertSee('Fully Competent')
            ->assertSee('Mostly Competent');
    }

    #[Test]
    public function test_displays_examiner_information()
    {
        // Create secondary and trainee examiners
        $secondaryExaminer = Account::factory()->create();
        $traineeExaminer = Account::factory()->create();

        Member::factory()->create(['id' => $secondaryExaminer->id, 'cid' => $secondaryExaminer->id]);
        Member::factory()->create(['id' => $traineeExaminer->id, 'cid' => $traineeExaminer->id]);

        $this->examBooking->examiners()->update([
            'other' => $secondaryExaminer->id,
            'trainee' => $traineeExaminer->id,
        ]);

        $this->panelUser->givePermissionTo(['training.exams.access', 'training.exams.conduct.twr']);

        $component = Livewire::actingAs($this->panelUser)
            ->test(ViewExamReport::class, ['examId' => $this->examBooking->id])
            ->assertSuccessful();

        // Check that examiner information is displayed
        $component->assertSee($this->panelUser->name) // Primary examiner
            ->assertSee($secondaryExaminer->name) // Secondary examiner
            ->assertSee($traineeExaminer->name); // Trainee examiner
    }

    #[Test]
    public function test_handles_missing_examiner_information_gracefully()
    {
        // Update exam to have no secondary or trainee examiners
        $this->examBooking->examiners()->update([
            'other' => null,
            'trainee' => null,
        ]);

        $this->panelUser->givePermissionTo(['training.exams.access', 'training.exams.conduct.twr']);

        Livewire::actingAs($this->panelUser)
            ->test(ViewExamReport::class, ['examId' => $this->examBooking->id])
            ->assertSuccessful();

        // Should load without errors even with missing examiner data
    }

    #[Test]
    public function test_displays_exam_date_correctly()
    {
        $examDate = now()->subDays(5);
        $this->examBooking->update([
            'taken_date' => $examDate->format('Y-m-d'),
            'taken_from' => $examDate->format('H:i'),
        ]);

        $this->panelUser->givePermissionTo(['training.exams.access', 'training.exams.conduct.twr']);

        $component = Livewire::actingAs($this->panelUser)
            ->test(ViewExamReport::class, ['examId' => $this->examBooking->id])
            ->assertSuccessful();

        // Check that the exam date is displayed (uses startDate accessor)
        $component->assertSee($examDate->format('Y-m-d'));
    }

    #[Test]
    public function test_criteria_infolist_loads_correctly()
    {
        // Create some criteria and assessments
        $criteria = ExamCriteria::create([
            'exam' => 'TWR',
            'criteria' => 'Test Criteria',
            'deleted' => 0,
        ]);

        ExamCriteriaAssessment::create([
            'examid' => $this->examBooking->id,
            'criteria_id' => $criteria->id,
            'result' => ExamCriteriaAssessment::FULLY_COMPETENT,
            'notes' => 'Test notes',
        ]);

        $this->panelUser->givePermissionTo(['training.exams.access', 'training.exams.conduct.twr']);

        $component = Livewire::actingAs($this->panelUser)
            ->test(ViewExamReport::class, ['examId' => $this->examBooking->id])
            ->assertSuccessful();

        // Ensure the criteria infolist method exists and returns an Infolist
        $this->assertTrue(method_exists($component->instance(), 'criteriaInfoList'));

        $infolist = new \Filament\Infolists\Infolist($component->instance());
        $result = $component->instance()->criteriaInfoList($infolist);
        $this->assertInstanceOf(\Filament\Infolists\Infolist::class, $result);
    }

    #[Test]
    public function test_page_handles_different_exam_types()
    {
        // Test with APP exam type
        $this->examBooking->update(['exam' => 'APP']);
        $this->practicalResult->update(['exam' => 'APP']);

        $this->panelUser->givePermissionTo(['training.exams.access', 'training.exams.conduct.app']);

        Livewire::actingAs($this->panelUser)
            ->test(ViewExamReport::class, ['examId' => $this->examBooking->id])
            ->assertSuccessful()
            ->assertSee('APP');
    }
}
