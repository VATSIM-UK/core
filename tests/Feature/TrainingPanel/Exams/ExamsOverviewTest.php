<?php

namespace Tests\Feature\TrainingPanel\Exams;

use App\Filament\Training\Pages\Exam\ConductExam;
use App\Filament\Training\Pages\Exam\Exams;
use App\Livewire\Training\AcceptedExamsTable;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\ExamSetup;
use App\Models\Cts\Member;
use App\Models\Cts\PracticalExaminers;
use App\Models\Mship\Account;
use App\Notifications\Training\Exams\ExamCancelledByExaminerStudentNotification;
use App\Notifications\Training\Exams\ExamSessionCancelledForCoExaminerNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\View;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class ExamsOverviewTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_loads_if_authorised()
    {
        $this->panelUser->givePermissionTo('training.exams.access');

        Livewire::actingAs($this->panelUser)
            ->test(Exams::class)
            ->assertSuccessful();
    }

    #[Test]
    public function it_does_not_load_if_unauthorised()
    {
        Livewire::actingAs($this->panelUser)
            ->test(Exams::class)
            ->assertForbidden();
    }

    #[Test]
    public function test_can_see_exam_when_assigned_as_primary_examiner()
    {
        $this->panelUser->givePermissionTo('training.exams.access');

        $student = Account::factory()->create();
        Member::factory()->create(['id' => $student->id, 'cid' => $student->id]);

        $exam = ExamBooking::factory()->create(['taken' => 1, 'finished' => ExamBooking::NOT_FINISHED_FLAG, 'student_id' => $student->id]);
        $exam->examiners()->create([
            'examid' => $exam->id,
            'senior' => $this->panelUser->id,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(AcceptedExamsTable::class)
            ->assertSuccessful()
            ->assertSee($exam->student->cid)
            ->assertSee($exam->position_1)
            ->assertTableActionEnabled('Conduct', $exam);
    }

    #[Test]
    public function test_can_see_exam_when_assigned_as_other_examiner()
    {
        $this->panelUser->givePermissionTo('training.exams.access');

        $student = Account::factory()->create();
        Member::factory()->create(['id' => $student->id, 'cid' => $student->id]);

        $exam = ExamBooking::factory()->create(['taken' => 1, 'finished' => ExamBooking::NOT_FINISHED_FLAG, 'student_id' => $student->id]);
        $exam->examiners()->create([
            'examid' => $exam->id,
            'other' => $this->panelUser->id,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(AcceptedExamsTable::class)
            ->assertSuccessful()
            ->assertSee($exam->student->cid)
            ->assertSee($exam->position_1)
            ->assertTableActionEnabled('Conduct', $exam);
    }

    #[Test]
    public function test_can_see_exam_when_assigned_as_trainee_examiner()
    {
        $this->panelUser->givePermissionTo('training.exams.access');

        $student = Account::factory()->create();
        Member::factory()->create(['id' => $student->id, 'cid' => $student->id]);

        $exam = ExamBooking::factory()->create(['taken' => 1, 'finished' => ExamBooking::NOT_FINISHED_FLAG, 'student_id' => $student->id]);
        $exam->examiners()->create([
            'examid' => $exam->id,
            'trainee' => $this->panelUser->id,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(AcceptedExamsTable::class)
            ->assertSuccessful()
            ->assertSee($exam->student->cid)
            ->assertSee($exam->position_1)
            ->assertTableActionEnabled('Conduct', $exam);
    }

    #[Test]
    public function test_redirects_to_conduct_page_when_conducting_exam()
    {
        $this->panelUser->givePermissionTo('training.exams.access');

        $student = Account::factory()->create();
        Member::factory()->create(['id' => $student->id, 'cid' => $student->id]);

        $exam = ExamBooking::factory()->create(['taken' => 1, 'finished' => ExamBooking::NOT_FINISHED_FLAG, 'student_id' => $student->id]);
        $exam->examiners()->create([
            'examid' => $exam->id,
            'senior' => $this->panelUser->id,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(AcceptedExamsTable::class)
            ->assertTableActionHasUrl('Conduct', ConductExam::getUrl(['examId' => $exam->id]), $exam)
            ->callTableAction('Conduct', $exam);
    }

    #[Test]
    public function test_primary_examiner_can_cancel_accepted_exam_from_accepted_table(): void
    {
        Notification::fake();
        $this->panelUser->givePermissionTo(['training.exams.access', 'training.exams.conduct.twr']);

        $student = Account::factory()->create([
            'name_first' => 'Alex',
            'name_last' => 'Student',
        ]);
        Member::factory()->create(['id' => $student->id, 'cid' => $student->id]);

        $exam = ExamBooking::factory()->create([
            'taken' => 1,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'student_id' => $student->id,
            'exam' => 'TWR',
            'position_1' => 'EGKK_TWR',
            'taken_date' => now()->addDays(3)->format('Y-m-d'),
            'taken_from' => '14:00:00',
            'taken_to' => '16:00:00',
            'exmr_id' => $this->panelUser->id,
        ]);

        $examSetup = ExamSetup::create([
            'rts_id' => 1,
            'student_id' => $student->id,
            'position_1' => 'EGKK_TWR',
            'exam' => 'TWR',
            'bookid' => $exam->id,
            'booked' => 1,
        ]);

        $exam->examiners()->create([
            'examid' => $exam->id,
            'senior' => $this->panelUser->id,
        ]);

        $reason = 'Unforeseen circumstances.';

        Livewire::actingAs($this->panelUser)
            ->test(AcceptedExamsTable::class)
            ->assertTableActionVisible('CancelExam', $exam)
            ->callTableAction('CancelExam', $exam, [
                'reason' => $reason,
            ])
            ->assertHasNoTableActionErrors();

        $exam->refresh();
        $examSetup->refresh();

        $this->assertEquals(0, $exam->taken);
        $this->assertNull($exam->taken_date);
        $this->assertEquals(0, $examSetup->booked);
        $this->assertDatabaseMissing('practical_examiners', ['examid' => $exam->id], 'cts');
        $this->assertDatabaseHas('cancel_reason', [
            'sesh_id' => $exam->id,
            'sesh_type' => 'EX',
            'reason' => $reason,
            'reason_by' => $this->panelUser->id,
        ], 'cts');

        Notification::assertSentTo(
            $student,
            ExamCancelledByExaminerStudentNotification::class,
            function (ExamCancelledByExaminerStudentNotification $notification) use ($student): bool {
                $mail = $notification->toMail($student);
                $html = View::make($mail->view, $mail->data())->render();

                return $mail->subject === 'Your practical exam has been cancelled'
                    && $mail->viewData['cancelledByExaminer']->id === $this->panelUser->id
                    && str_contains($html, 'has cancelled your TWR practical exam')
                    && str_contains($html, 'remain in the system');
            },
        );
    }

    #[Test]
    public function test_cancelling_accepted_exam_from_accepted_table_notifies_co_examiner(): void
    {
        Notification::fake();
        $this->panelUser->givePermissionTo(['training.exams.access', 'training.exams.conduct.twr']);

        $coExaminer = Account::factory()->create();
        Member::factory()->create(['id' => $coExaminer->id, 'cid' => $coExaminer->id]);

        $student = Account::factory()->create();
        Member::factory()->create(['id' => $student->id, 'cid' => $student->id]);

        $exam = ExamBooking::factory()->create([
            'taken' => 1,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'student_id' => $student->id,
            'exam' => 'TWR',
        ]);

        ExamSetup::create([
            'rts_id' => 1,
            'student_id' => $student->id,
            'position_1' => 'EGKK_TWR',
            'exam' => 'TWR',
            'bookid' => $exam->id,
            'booked' => 1,
        ]);

        PracticalExaminers::create([
            'examid' => $exam->id,
            'senior' => $this->panelUser->id,
            'other' => $coExaminer->id,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(AcceptedExamsTable::class)
            ->callTableAction('CancelExam', $exam, [
                'reason' => 'Examiner unavailable.',
            ])
            ->assertHasNoTableActionErrors();

        Notification::assertSentTo($student, ExamCancelledByExaminerStudentNotification::class);
        Notification::assertSentTo($coExaminer, ExamSessionCancelledForCoExaminerNotification::class);
        Notification::assertNotSentTo($this->panelUser, ExamSessionCancelledForCoExaminerNotification::class);
    }
}
