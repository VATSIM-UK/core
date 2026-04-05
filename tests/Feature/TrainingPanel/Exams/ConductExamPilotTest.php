<?php

namespace Tests\Feature\TrainingPanel\Exams;

use App\Enums\ExamResultEnum;
use App\Events\Training\Exams\PracticalExamCompleted;
use App\Filament\Training\Pages\Exam\ConductExam;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class ConductExamPilotTest extends BaseTrainingPanelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
    }

    private function createPilotExamBooking(string $examType = 'P1'): array
    {
        $account = Account::factory()->withQualification()->create();
        $student = Member::factory()->create(['id' => $account->id, 'cid' => $account->id]);

        $exam = ExamBooking::factory()->create([
            'taken' => 1,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'exam' => $examType,
            'student_id' => $student->id,
            'student_rating' => Qualification::ofType('pilot')->where('code', 'P0')->first()?->vatsim ?? 0,
        ]);

        $exam->examiners()->create([
            'examid' => $exam->id,
            'senior' => $this->panelUser->id,
        ]);

        return [$account, $student, $exam];
    }

    #[Test]
    public function it_loads_pilot_exam_if_authorised()
    {
        [$account, $student, $exam] = $this->createPilotExamBooking('P1');
        $this->panelUser->givePermissionTo('training.exams.conduct.p1');

        Livewire::actingAs($this->panelUser)
            ->test(ConductExam::class, ['examId' => $exam->id])
            ->assertSuccessful();
    }

    #[Test]
    public function it_does_not_load_pilot_exam_without_permission()
    {
        [$account, $student, $exam] = $this->createPilotExamBooking('P1');

        Livewire::actingAs($this->panelUser)
            ->test(ConductExam::class, ['examId' => $exam->id])
            ->assertForbidden();
    }

    #[Test]
    public function it_does_not_load_p2_exam_with_only_p1_permission()
    {
        [$account, $student, $exam] = $this->createPilotExamBooking('P2');
        $this->panelUser->givePermissionTo('training.exams.conduct.p1');

        Livewire::actingAs($this->panelUser)
            ->test(ConductExam::class, ['examId' => $exam->id])
            ->assertForbidden();
    }

    #[Test]
    public function it_shows_pilot_result_options_including_partial_pass()
    {
        [$account, $student, $exam] = $this->createPilotExamBooking('P1');
        $this->panelUser->givePermissionTo('training.exams.conduct.p1');

        $component = Livewire::actingAs($this->panelUser)
            ->test(ConductExam::class, ['examId' => $exam->id])
            ->assertSuccessful();

        // Partial Pass should be available for pilot exams
        $component->assertSee('Partial Pass');
    }

    #[Test]
    public function it_does_not_show_partial_pass_for_atc_exams()
    {
        $account = Account::factory()->create();
        $student = Member::factory()->create(['id' => $account->id, 'cid' => $account->id]);
        $exam = ExamBooking::factory()->create([
            'taken' => 1,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'exam' => 'TWR',
            'student_id' => $student->id,
            'student_rating' => Qualification::code('S1')->first()->vatsim,
        ]);
        $exam->examiners()->create(['examid' => $exam->id, 'senior' => $this->panelUser->id]);
        $this->panelUser->givePermissionTo('training.exams.conduct.twr');

        $component = Livewire::actingAs($this->panelUser)
            ->test(ConductExam::class, ['examId' => $exam->id])
            ->assertSuccessful();

        $component->assertDontSee('Partial Pass');
    }

    #[Test]
    public function it_can_submit_pilot_exam_with_pass_result()
    {
        [$account, $student, $exam] = $this->createPilotExamBooking('P1');
        $this->panelUser->givePermissionTo('training.exams.conduct.p1');

        Livewire::actingAs($this->panelUser)
            ->test(ConductExam::class, ['examId' => $exam->id])
            ->set('examResultData.exam_result', ExamResultEnum::Pass->value)
            ->set('examResultData.additional_comments', 'Student passed all sections.')
            ->call('completeExam')
            ->assertHasNoFormErrors(formName: 'examResultForm');

        $this->assertDatabaseHas('practical_results', connection: 'cts', data: [
            'examid' => $exam->id,
            'student_id' => $student->id,
            'result' => ExamResultEnum::Pass->value,
            'notes' => 'Student passed all sections.',
            'exam' => 'P1',
        ]);

        Event::assertDispatched(PracticalExamCompleted::class, fn ($event) => $event->examBooking->id === $exam->id);
    }

    #[Test]
    public function it_can_submit_pilot_exam_with_partial_pass_result()
    {
        [$account, $student, $exam] = $this->createPilotExamBooking('P1');
        $this->panelUser->givePermissionTo('training.exams.conduct.p1');

        Livewire::actingAs($this->panelUser)
            ->test(ConductExam::class, ['examId' => $exam->id])
            ->set('examResultData.exam_result', ExamResultEnum::PartialPass->value)
            ->set('examResultData.additional_comments', 'Some sections not completed.')
            ->call('completeExam')
            ->assertHasNoFormErrors(formName: 'examResultForm');

        $this->assertDatabaseHas('practical_results', connection: 'cts', data: [
            'examid' => $exam->id,
            'student_id' => $student->id,
            'result' => ExamResultEnum::PartialPass->value,
            'exam' => 'P1',
        ]);
    }

    #[Test]
    public function it_can_submit_pilot_exam_with_fail_result()
    {
        [$account, $student, $exam] = $this->createPilotExamBooking('P2');
        $this->panelUser->givePermissionTo('training.exams.conduct.p2');

        Livewire::actingAs($this->panelUser)
            ->test(ConductExam::class, ['examId' => $exam->id])
            ->set('examResultData.exam_result', ExamResultEnum::Fail->value)
            ->set('examResultData.additional_comments', 'Did not meet required standard.')
            ->call('completeExam')
            ->assertHasNoFormErrors(formName: 'examResultForm');

        $this->assertDatabaseHas('practical_results', connection: 'cts', data: [
            'examid' => $exam->id,
            'student_id' => $student->id,
            'result' => ExamResultEnum::Fail->value,
            'exam' => 'P2',
        ]);
    }

    #[Test]
    public function it_marks_exam_as_finished_after_submission()
    {
        [$account, $student, $exam] = $this->createPilotExamBooking('P1');
        $this->panelUser->givePermissionTo('training.exams.conduct.p1');

        Livewire::actingAs($this->panelUser)
            ->test(ConductExam::class, ['examId' => $exam->id])
            ->set('examResultData.exam_result', ExamResultEnum::Pass->value)
            ->set('examResultData.additional_comments', '')
            ->call('completeExam');

        $exam->refresh();
        $this->assertEquals(ExamBooking::FINISHED_FLAG, $exam->finished);
    }

    #[Test]
    public function it_fires_practical_exam_completed_event_for_pilot_exam()
    {
        [$account, $student, $exam] = $this->createPilotExamBooking('P1');
        $this->panelUser->givePermissionTo('training.exams.conduct.p1');

        Livewire::actingAs($this->panelUser)
            ->test(ConductExam::class, ['examId' => $exam->id])
            ->set('examResultData.exam_result', ExamResultEnum::Pass->value)
            ->set('examResultData.additional_comments', '')
            ->call('completeExam');

        Event::assertDispatched(PracticalExamCompleted::class);
    }
}
