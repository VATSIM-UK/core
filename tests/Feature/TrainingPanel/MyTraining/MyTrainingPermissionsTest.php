<?php

namespace Tests\Feature\TrainingPanel\MyTraining;

use App\Filament\Training\Pages\Exam\ExamHistory;
use App\Filament\Training\Pages\Exam\ViewExamReport;
use App\Filament\Training\Pages\MyTraining\MyExamHistory;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Cts\PracticalResult;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class MyTrainingPermissionsTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    protected Account $studentAccount;

    protected Member $studentMember;

    protected ExamBooking $studentExamBooking;

    protected PracticalResult $studentPracticalResult;

    protected Account $otherStudentAccount;

    protected Member $otherStudentMember;

    protected ExamBooking $otherStudentExamBooking;

    protected PracticalResult $otherStudentPracticalResult;

    protected function setUp(): void
    {
        parent::setUp();

        // Primary student
        $this->studentAccount = Account::factory()->create();
        $this->studentMember = Member::factory()->create([
            'id' => $this->studentAccount->id,
            'cid' => $this->studentAccount->id,
        ]);
        $this->studentExamBooking = ExamBooking::factory()->create([
            'taken' => 1,
            'finished' => ExamBooking::FINISHED_FLAG,
            'exam' => 'TWR',
            'student_id' => $this->studentMember->id,
            'student_rating' => Qualification::code('S1')->first()->vatsim,
            'position_1' => 'EGKK_TWR',
        ]);
        $this->studentExamBooking->examiners()->create([
            'examid' => $this->studentExamBooking->id,
            'senior' => $this->panelUser->id,
        ]);
        $this->studentPracticalResult = PracticalResult::factory()->create([
            'examid' => $this->studentExamBooking->id,
            'student_id' => $this->studentMember->id,
            'result' => PracticalResult::PASSED,
            'exam' => 'TWR',
            'date' => now()->subDays(5),
        ]);

        // Second student
        $this->otherStudentAccount = Account::factory()->create();
        $this->otherStudentMember = Member::factory()->create([
            'id' => $this->otherStudentAccount->id,
            'cid' => $this->otherStudentAccount->id,
        ]);
        $this->otherStudentExamBooking = ExamBooking::factory()->create([
            'taken' => 1,
            'finished' => ExamBooking::FINISHED_FLAG,
            'exam' => 'TWR',
            'student_id' => $this->otherStudentMember->id,
            'student_rating' => Qualification::code('S1')->first()->vatsim,
            'position_1' => 'EGKK_TWR',
        ]);
        $this->otherStudentExamBooking->examiners()->create([
            'examid' => $this->otherStudentExamBooking->id,
            'senior' => $this->panelUser->id,
        ]);
        $this->otherStudentPracticalResult = PracticalResult::factory()->create([
            'examid' => $this->otherStudentExamBooking->id,
            'student_id' => $this->otherStudentMember->id,
            'result' => PracticalResult::FAILED,
            'exam' => 'TWR',
            'date' => now()->subDays(3),
        ]);
    }

    #[Test]
    public function member_with_training_access_can_enter_the_training_panel(): void
    {
        $this->studentAccount->givePermissionTo('training.access');

        Livewire::actingAs($this->studentAccount)
            ->test(MyExamHistory::class)
            ->assertSuccessful();
    }

    #[Test]
    public function member_without_training_access_cannot_enter_the_training_panel(): void
    {
        Livewire::actingAs($this->studentAccount)
            ->test(MyExamHistory::class)
            ->assertForbidden();
    }

    #[Test]
    public function member_sees_only_their_own_results_in_my_exam_history(): void
    {
        $this->studentAccount->givePermissionTo('training.access');

        Livewire::actingAs($this->studentAccount)
            ->test(MyExamHistory::class)
            ->assertSuccessful()
            ->assertDontSee($this->otherStudentAccount->name);

    }

    #[Test]
    public function member_with_no_results_sees_empty_my_exam_history(): void
    {
        $emptyAccount = Account::factory()->create();
        Member::factory()->create(['id' => $emptyAccount->id, 'cid' => $emptyAccount->id]);
        $emptyAccount->givePermissionTo('training.access');

        Livewire::actingAs($emptyAccount)
            ->test(MyExamHistory::class)
            ->assertSuccessful()
            ->assertDontSee($this->studentAccount->name);
    }

    #[Test]
    public function my_exam_history_does_not_leak_other_members_results(): void
    {
        // Both students have training.access
        $this->studentAccount->givePermissionTo('training.access');
        $this->otherStudentAccount->givePermissionTo('training.access');

        // Student A sees their result, not student B's
        Livewire::actingAs($this->studentAccount)
            ->test(MyExamHistory::class)
            ->assertSuccessful()
            ->assertDontSee($this->otherStudentAccount->name);

        // Student B sees their result, not student A's
        Livewire::actingAs($this->otherStudentAccount)
            ->test(MyExamHistory::class)
            ->assertSuccessful()
            ->assertDontSee($this->studentAccount->name);
    }

    #[Test]
    public function member_can_view_their_own_exam_report(): void
    {
        $this->studentAccount->givePermissionTo('training.access');

        Livewire::actingAs($this->studentAccount)
            ->test(ViewExamReport::class, ['examId' => $this->studentPracticalResult->examid])
            ->assertSuccessful();
    }

    #[Test]
    public function member_cannot_view_another_members_exam_report(): void
    {
        $this->studentAccount->givePermissionTo('training.access');

        Livewire::actingAs($this->studentAccount)
            ->test(ViewExamReport::class, ['examId' => $this->otherStudentPracticalResult->examid])
            ->assertForbidden();
    }

    #[Test]
    public function examiner_with_exams_access_and_conduct_permission_can_view_report(): void
    {
        $this->panelUser->givePermissionTo([
            'training.exams.access',
            'training.exams.conduct.twr',
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(ViewExamReport::class, ['examId' => $this->studentPracticalResult->examid])
            ->assertSuccessful();
    }

    #[Test]
    public function examiner_with_exams_access_but_wrong_conduct_level_cannot_view_report(): void
    {
        $this->panelUser->givePermissionTo([
            'training.exams.access',
            'training.exams.conduct.obs',
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(ViewExamReport::class, ['examId' => $this->studentPracticalResult->examid])
            ->assertForbidden();
    }

    #[Test]
    public function member_cannot_see_override_result_action_on_their_own_report(): void
    {
        $this->studentAccount->givePermissionTo('training.access');

        Livewire::actingAs($this->studentAccount)
            ->test(ViewExamReport::class, ['examId' => $this->studentPracticalResult->examid])
            ->assertSuccessful()
            ->assertDontSee('Override');
    }

    #[Test]
    public function member_with_only_training_access_cannot_access_exam_history(): void
    {
        $this->studentAccount->givePermissionTo('training.access');

        Livewire::actingAs($this->studentAccount)
            ->test(ExamHistory::class)
            ->assertForbidden();
    }

    #[Test]
    public function member_with_only_training_access_cannot_access_conduct_exam(): void
    {
        $this->studentAccount->givePermissionTo('training.access');

        Livewire::actingAs($this->studentAccount)
            ->test(\App\Filament\Training\Pages\Exam\ConductExam::class, [
                'examId' => $this->studentExamBooking->id,
            ])
            ->assertForbidden();
    }

    #[Test]
    public function member_with_only_training_access_cannot_access_exams(): void
    {
        $this->studentAccount->givePermissionTo('training.access');

        Livewire::actingAs($this->studentAccount)
            ->test(\App\Filament\Training\Pages\Exam\Exams::class)
            ->assertForbidden();
    }
}
