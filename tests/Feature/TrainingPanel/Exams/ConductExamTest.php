<?php

namespace Tests\Feature\TrainingPanel\Exams;

use App\Events\Training\Exams\PracticalExamCompleted;
use App\Filament\Training\Pages\ConductExam;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\ExamCriteria;
use App\Models\Cts\Member;
use App\Models\Cts\PracticalResult;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class ConductExamTest extends BaseTrainingPanelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();
    }

    #[Test]
    public function it_loads_if_authorised()
    {
        $account = Account::factory()->create();
        $student = factory(Member::class)->create(['id' => $account->id, 'cid' => $account->id]);
        $exam = ExamBooking::factory()->create([
            'taken' => 1,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'exam' => 'TWR',
            'student_id' => $student->id,
            'student_rating' => Qualification::code('S1')->first()->vatsim,
        ]);
        $exam->examiners()->create([
            'examid' => $exam->id,
            'senior' => $this->panelUser->id,
        ]);

        $this->panelUser->givePermissionTo('training.exams.conduct.twr');

        Livewire::actingAs($this->panelUser)
            ->test(ConductExam::class, ['examId' => $exam->id])
            ->assertSuccessful();
    }

    #[Test]
    public function it_does_not_load_if_unauthorised()
    {
        $exam = ExamBooking::factory()->create(['taken' => 1, 'finished' => ExamBooking::NOT_FINISHED_FLAG, 'exam' => 'TWR']);
        $exam->examiners()->create([
            'examid' => $exam->id,
            'senior' => $this->panelUser->id,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(ConductExam::class, ['examId' => $exam->id])
            ->assertForbidden();
    }

    #[Test]
    public function test_unauthorised_when_exam_doesnt_exist()
    {
        $examId = 9999; // Assuming this ID does not exist
        $this->panelUser->givePermissionTo('training.exams.conduct.twr');

        Livewire::actingAs($this->panelUser)
            ->test(ConductExam::class, ['examId' => $examId])
            ->assertForbidden();
    }

    #[Test]
    public function test_unauthorised_when_exam_not_taken()
    {
        $exam = ExamBooking::factory()->create(['taken' => 0, 'finished' => ExamBooking::NOT_FINISHED_FLAG, 'exam' => 'TWR']);
        $exam->examiners()->create([
            'examid' => $exam->id,
            'senior' => $this->panelUser->id,
        ]);

        $this->panelUser->givePermissionTo('training.exams.conduct.twr');

        Livewire::actingAs($this->panelUser)
            ->test(ConductExam::class, ['examId' => $exam->id])
            ->assertForbidden();
    }

    #[Test]
    public function test_unauthorised_when_exam_finished()
    {
        $exam = ExamBooking::factory()->create(['taken' => 1, 'finished' => ExamBooking::FINISHED_FLAG, 'exam' => 'TWR']);
        $exam->examiners()->create([
            'examid' => $exam->id,
            'senior' => $this->panelUser->id,
        ]);

        $this->panelUser->givePermissionTo('training.exams.conduct.twr');

        Livewire::actingAs($this->panelUser)
            ->test(ConductExam::class, ['examId' => $exam->id])
            ->assertForbidden();
    }

    #[Test]
    public function test_unauthorised_when_access_to_other_type_of_exam()
    {
        $exam = ExamBooking::factory()->create(['taken' => 1, 'finished' => ExamBooking::NOT_FINISHED_FLAG, 'exam' => 'APP']);
        $exam->examiners()->create([
            'examid' => $exam->id,
            'senior' => $this->panelUser->id,
        ]);

        $this->panelUser->givePermissionTo('training.exams.conduct.twr');

        Livewire::actingAs($this->panelUser)
            ->test(ConductExam::class, ['examId' => $exam->id])
            ->assertForbidden();
    }

    #[Test]
    public function test_can_fill_out_comments_one_of_criteria()
    {
        $account = Account::factory()->create();
        $student = factory(Member::class)->create(['id' => $account->id, 'cid' => $account->id]);
        $exam = ExamBooking::factory()->create([
            'taken' => 1,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'exam' => 'TWR',
            'student_id' => $student->id,
            'student_rating' => Qualification::code('S1')->first()->vatsim,
        ]);
        $exam->examiners()->create([
            'examid' => $exam->id,
            'senior' => $this->panelUser->id,
        ]);

        $this->panelUser->givePermissionTo('training.exams.conduct.twr');

        $examCriteria = ExamCriteria::create([
            'exam' => 'TWR',
            'criteria' => 'Test Criteria',
            'deleted' => 0,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(ConductExam::class, ['examId' => $exam->id])
            ->assertSuccessful()
            ->set("data.form.{$examCriteria->id}.comments", 'Test comment for test criteria')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('practical_criteria_assess', connection: 'cts', data: [
            'examid' => $exam->id,
            'criteria_id' => $examCriteria->id,
            'notes' => 'Test comment for test criteria',
            'result' => 'N',
        ]);
    }

    #[Test]
    public function test_can_change_grade_on_one_of_criteria()
    {
        $account = Account::factory()->create();
        $student = factory(Member::class)->create(['id' => $account->id, 'cid' => $account->id]);
        $exam = ExamBooking::factory()->create([
            'taken' => 1,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'exam' => 'TWR',
            'student_id' => $student->id,
            'student_rating' => Qualification::code('S1')->first()->vatsim,
        ]);
        $exam->examiners()->create([
            'examid' => $exam->id,
            'senior' => $this->panelUser->id,
        ]);

        $this->panelUser->givePermissionTo('training.exams.conduct.twr');

        $examCriteria = ExamCriteria::create([
            'exam' => 'TWR',
            'criteria' => 'Test Criteria',
            'deleted' => 0,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(ConductExam::class, ['examId' => $exam->id])
            ->assertSuccessful()
            ->set("data.form.{$examCriteria->id}.grade", 'P')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('practical_criteria_assess', connection: 'cts', data: [
            'examid' => $exam->id,
            'criteria_id' => $examCriteria->id,
            'notes' => '',
            'result' => 'P',
        ]);
    }

    #[Test]
    public function test_full_end_to_end_completion_of_form_pass()
    {
        $account = Account::factory()->create();
        $student = factory(Member::class)->create(['id' => $account->id, 'cid' => $account->id]);
        $exam = ExamBooking::factory()->create([
            'taken' => 1,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'exam' => 'TWR',
            'student_id' => $student->id,
            'student_rating' => Qualification::code('S1')->first()->vatsim,
        ]);
        $exam->examiners()->create([
            'examid' => $exam->id,
            'senior' => $this->panelUser->id,
        ]);

        $this->panelUser->givePermissionTo('training.exams.conduct.twr');

        // create exam criteria in case test database is empty
        ExamCriteria::create([
            'exam' => 'TWR',
            'criteria' => 'Test Criteria',
            'deleted' => 0,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(ConductExam::class, ['examId' => $exam->id])
            ->assertSuccessful()
            ->fillForm(function () use ($exam) {
                $criteria = ExamCriteria::byType($exam->exam)->get();

                return ['form' => $criteria->mapWithKeys(function ($item) {
                    return [$item->id => ['comments' => 'Test comment for test criteria', 'grade' => 'P']];
                })->toArray()];
            })
            ->set('examResultData.exam_result', PracticalResult::PASSED)
            ->set('examResultData.additional_comments', 'Test notes for test result')
            ->call('completeExam')
            ->assertHasNoFormErrors(formName: 'form')
            ->assertHasNoFormErrors(formName: 'examResultForm');

        $this->assertDatabaseHas('practical_results', connection: 'cts', data: [
            'examid' => $exam->id,
            'student_id' => $student->id,
            'result' => 'P',
            'notes' => 'Test notes for test result',
            'date' => now(),
            'exam' => 'TWR',
        ]);

        Event::assertDispatched(PracticalExamCompleted::class, function ($event) use ($exam) {
            return $event->examBooking->id === $exam->id && $event->practicalResult->examid === $exam->id;
        });
    }
}
